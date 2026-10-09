/*
 * Om Sai Baba Medical and General Store - landing choreography
 *
 *   loader    counts real work (fonts, scene) then lifts like a curtain
 *   hero      a dark rounded frame holds a particle object under the header
 *   story     the frame opens to full bleed; five chapters scroll past while the
 *             object turns and re-forms into the next chapter's shape
 *   work      the services, pinned and scrolled sideways on desktop
 *   closing   a statement that fills in word by word, then contact
 *
 * Smooth scrolling is Lenis; every scroll-linked animation is a GSAP
 * ScrollTrigger driven by it, so scrolling back runs everything backwards.
 * Without WebGL, with reduced motion, or without the libraries the page is a
 * plain readable document.
 */
(function () {
    'use strict';

    var doc = document;
    var root = doc.documentElement;
    var gsap = window.gsap;
    var ScrollTrigger = window.ScrollTrigger;
    var SplitText = window.SplitText;
    var Lenis = window.Lenis;
    var THREE = window.THREE;

    var frame = doc.getElementById('frame');
    var canvas = doc.getElementById('scene');
    var top = doc.getElementById('top');
    var rail = doc.getElementById('rail');
    var loader = doc.getElementById('loader');
    var loaderN = doc.getElementById('loader-n');
    var loaderBar = doc.getElementById('loader-bar');
    var chapters = Array.prototype.slice.call(doc.querySelectorAll('.chapter'));
    var story = doc.getElementById('story');

    var reduce = root.classList.contains('no-motion');
    var loading = root.classList.contains('is-loading');
    var motion = !!(gsap && ScrollTrigger) && !reduce;
    var narrowMQ = window.matchMedia('(max-width: 859px)');
    var finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    var CHAPTERS = 7; // hero, five chapters, closing

    var scene = null;
    var lenis = null;

    function clamp(v, a, b) { return Math.min(b, Math.max(a, v)); }

    /* ------------------------------------------------------------------
       Shapes: each is N surface points with a tone (0 teal ... 1 amber)
       ------------------------------------------------------------------ */
    function Shape(n) { this.p = new Float32Array(n * 3); this.t = new Float32Array(n); this.i = 0; }
    Shape.prototype.add = function (x, y, z, tone) {
        var i = this.i++;
        this.p[i * 3] = x; this.p[i * 3 + 1] = y; this.p[i * 3 + 2] = z; this.t[i] = tone;
    };

    function randUnit() {
        var z = Math.random() * 2 - 1, a = Math.random() * Math.PI * 2, r = Math.sqrt(1 - z * z);
        return [r * Math.cos(a), z, r * Math.sin(a)];
    }

    function capsuleShape(n) {
        var s = new Shape(n), r = 0.44, h = 0.52;
        var cyl = 2 * Math.PI * r * (2 * h), caps = 4 * Math.PI * r * r, pc = cyl / (cyl + caps);
        while (s.i < n) {
            var x, y, z;
            if (Math.random() < pc) {
                var a = Math.random() * Math.PI * 2;
                y = (Math.random() * 2 - 1) * h; x = r * Math.cos(a); z = r * Math.sin(a);
            } else {
                var v = randUnit();
                y = v[1] * r + (v[1] > 0 ? h : -h); x = v[0] * r; z = v[2] * r;
            }
            var tone = y < -0.03 ? 1 : (y > 0.03 ? 0 : 0.5);
            s.add(x, y, z, tone);
        }
        return s;
    }

    function boxSurface(s, n, cx, cy, cz, hx, hy, hz, others, toneFn) {
        var ax = hy * hz, ay = hx * hz, az = hx * hy, tot = ax + ay + az;
        var tries = 0;
        while (n > 0 && tries++ < n * 40) {
            var u = Math.random() * tot, sg = Math.random() < 0.5 ? -1 : 1, x, y, z;
            if (u < ax) { x = sg * hx; y = (Math.random() * 2 - 1) * hy; z = (Math.random() * 2 - 1) * hz; }
            else if (u < ax + ay) { y = sg * hy; x = (Math.random() * 2 - 1) * hx; z = (Math.random() * 2 - 1) * hz; }
            else { z = sg * hz; x = (Math.random() * 2 - 1) * hx; y = (Math.random() * 2 - 1) * hy; }
            x += cx; y += cy; z += cz;
            var inside = false;
            for (var k = 0; k < others.length; k++) {
                var o = others[k];
                if (Math.abs(x - o[0]) < o[3] - 0.004 && Math.abs(y - o[1]) < o[4] - 0.004 && Math.abs(z - o[2]) < o[5] - 0.004) { inside = true; break; }
            }
            if (inside) continue;
            s.add(x, y, z, toneFn(x, y, z));
            n--;
        }
    }

    function crossShape(n) {
        var s = new Shape(n), L = 0.82, T = 0.27;
        var bars = [[0, 0, 0, L, T, T], [0, 0, 0, T, L, T]];
        var tone = function (x, y) { return (Math.abs(x) < T + 0.01 && Math.abs(y) < T + 0.01) ? 1 : 0; };
        var per = Math.floor(n / 2);
        boxSurface(s, per, 0, 0, 0, L, T, T, [bars[1]], tone);
        boxSurface(s, n - per, 0, 0, 0, T, L, T, [bars[0]], tone);
        while (s.i < n) s.add(0, 0, T, 1);
        return s;
    }

    function bottleShape(n) {
        var s = new Shape(n);
        var parts = [
            { w: 2 * Math.PI * 0.5 * 1.1, f: function () { var a = Math.random() * 6.2832, y = -0.8 + Math.random() * 1.1; return [0.5 * Math.cos(a), y, 0.5 * Math.sin(a), (y > -0.5 && y < 0) ? 0.5 : 0]; } },
            { w: Math.PI * 0.25, f: function () { var a = Math.random() * 6.2832, r = Math.sqrt(Math.random()) * 0.5; return [r * Math.cos(a), -0.8, r * Math.sin(a), 0]; } },
            { w: Math.PI * (0.25 - 0.0484), f: function () { var a = Math.random() * 6.2832, r = 0.22 + Math.sqrt(Math.random()) * 0.28; return [r * Math.cos(a), 0.3, r * Math.sin(a), 0]; } },
            { w: 2 * Math.PI * 0.22 * 0.2, f: function () { var a = Math.random() * 6.2832; return [0.22 * Math.cos(a), 0.3 + Math.random() * 0.2, 0.22 * Math.sin(a), 0]; } },
            { w: 2 * Math.PI * 0.3 * 0.3, f: function () { var a = Math.random() * 6.2832; return [0.3 * Math.cos(a), 0.5 + Math.random() * 0.3, 0.3 * Math.sin(a), 1]; } },
            { w: Math.PI * 0.09, f: function () { var a = Math.random() * 6.2832, r = Math.sqrt(Math.random()) * 0.3; return [r * Math.cos(a), 0.8, r * Math.sin(a), 1]; } }
        ];
        var tot = parts.reduce(function (a, p) { return a + p.w; }, 0);
        while (s.i < n) {
            var u = Math.random() * tot, k = 0;
            while (k < parts.length - 1 && u > parts[k].w) { u -= parts[k].w; k++; }
            var q = parts[k].f();
            s.add(q[0], q[1] - 0.05, q[2], q[3]);
        }
        return s;
    }

    function torusShape(n) {
        var s = new Shape(n), R = 0.72, r = 0.3;
        while (s.i < n) {
            var u = Math.random() * 6.2832, v = Math.random() * 6.2832;
            if (Math.random() > (R + r * Math.cos(v)) / (R + r)) continue;
            var x = (R + r * Math.cos(v)) * Math.cos(u), z = (R + r * Math.cos(v)) * Math.sin(u), y = r * Math.sin(v);
            var d = Math.abs(((u + 3.1416) % 6.2832) - 3.1416);
            s.add(x, y, z, clamp(1 - d / 1.1, 0, 1));
        }
        return s;
    }

    function heartF(x, y, z) {
        var a = x * x + 2.25 * y * y + z * z - 1;
        return a * a * a - x * x * z * z * z - 0.1125 * y * y * z * z * z;
    }

    function heartShape(n) {
        var s = new Shape(n), guard = 0;
        while (s.i < n && guard++ < n * 4000) {
            var x = (Math.random() * 2 - 1) * 1.25, y = (Math.random() * 2 - 1) * 1.05, z = (Math.random() * 2 - 1) * 1.35 + 0.1;
            var f = heartF(x, y, z);
            if (Math.abs(f) > 0.05) continue;
            var e = 0.002;
            var gx = (heartF(x + e, y, z) - heartF(x - e, y, z)) / (2 * e);
            var gy = (heartF(x, y + e, z) - heartF(x, y - e, z)) / (2 * e);
            var gz = (heartF(x, y, z + e) - heartF(x, y, z - e)) / (2 * e);
            var gl = Math.sqrt(gx * gx + gy * gy + gz * gz) + 1e-6;
            if (Math.abs(f) / gl > 0.0045) continue; // distance to the surface, so density is even
            s.add(x * 0.7, z * 0.7 - 0.05, y * 0.7, clamp((z + 0.6) / 1.8, 0, 1) * 0.55);
        }
        while (s.i < n) { var v = randUnit(); s.add(v[0] * 0.5, v[1] * 0.5, v[2] * 0.5, 0.3); }
        return s;
    }

    /* ------------------------------------------------------------------
       Scene: one point cloud morphing between shapes
       ------------------------------------------------------------------ */
    function createScene(cv) {
        var gl;
        try {
            if (!THREE || !window.WebGLRenderingContext) return null;
            var test = doc.createElement('canvas');
            if (!(test.getContext('webgl2') || test.getContext('webgl'))) return null;
        } catch (e) { return null; }

        var lowEnd = narrowMQ.matches || (navigator.hardwareConcurrency || 8) <= 4;
        var N = lowEnd ? 7000 : 15000;
        var renderer;
        try {
            renderer = new THREE.WebGLRenderer({ canvas: cv, antialias: false, alpha: true, powerPreference: 'high-performance' });
        } catch (e) { return null; }
        var dpr = Math.min(window.devicePixelRatio || 1, lowEnd ? 1.5 : 2);
        renderer.setPixelRatio(dpr);
        renderer.setClearColor(0x000000, 0);

        var sc = new THREE.Scene();
        var cam = new THREE.PerspectiveCamera(38, 1, 0.1, 50);
        cam.position.z = 4.4;

        var capsule = capsuleShape(N);
        var shapes = [capsule, capsule, crossShape(N), bottleShape(N), torusShape(N), heartShape(N), capsule];

        var geo = new THREE.BufferGeometry();
        var seed = new Float32Array(N * 3), size = new Float32Array(N);
        for (var i = 0; i < N; i++) {
            seed[i * 3] = Math.random(); seed[i * 3 + 1] = Math.random(); seed[i * 3 + 2] = Math.random();
            size[i] = 0.55 + Math.random() * 1.1;
        }
        var aA = new THREE.BufferAttribute(new Float32Array(shapes[0].p), 3);
        var aB = new THREE.BufferAttribute(new Float32Array(shapes[1].p), 3);
        var tA = new THREE.BufferAttribute(new Float32Array(shapes[0].t), 1);
        var tB = new THREE.BufferAttribute(new Float32Array(shapes[1].t), 1);
        geo.setAttribute('position', new THREE.BufferAttribute(new Float32Array(N * 3), 3));
        geo.setAttribute('aA', aA); geo.setAttribute('aB', aB);
        geo.setAttribute('aToneA', tA); geo.setAttribute('aToneB', tB);
        geo.setAttribute('aSeed', new THREE.BufferAttribute(seed, 3));
        geo.setAttribute('aSz', new THREE.BufferAttribute(size, 1));
        geo.boundingSphere = new THREE.Sphere(new THREE.Vector3(), 4);

        var uniforms = {
            uMix: { value: 0 }, uTime: { value: 0 }, uIntro: { value: 1 }, uPx: { value: dpr * (narrowMQ.matches ? 4.6 : (lowEnd ? 7.5 : 8.5)) },
            uTeal: { value: new THREE.Color(0x2dd4bf) }, uAmber: { value: new THREE.Color(0xf5b35b) }
        };
        var mat = new THREE.ShaderMaterial({
            uniforms: uniforms, transparent: true, depthWrite: false, blending: THREE.AdditiveBlending,
            vertexShader: [
                'attribute vec3 aA; attribute vec3 aB; attribute vec3 aSeed; attribute float aToneA; attribute float aToneB; attribute float aSz;',
                'uniform float uMix; uniform float uTime; uniform float uIntro; uniform float uPx;',
                'varying float vTone; varying float vAlpha;',
                'void main(){',
                '  float e = uMix * uMix * (3.0 - 2.0 * uMix);',
                '  vec3 p = mix(aA, aB, e);',
                '  float burst = sin(3.14159 * e);',
                '  vec3 d = (aSeed - 0.5) * 2.0;',
                '  p += d * (burst * 0.85 + uIntro * 2.6);',
                '  p += sin(aSeed * 37.0 + uTime * vec3(0.9, 1.1, 0.7)) * 0.012;',
                '  vec4 mv = modelViewMatrix * vec4(p, 1.0);',
                '  gl_Position = projectionMatrix * mv;',
                '  gl_PointSize = aSz * uPx * (1.0 + burst * 0.4) * (4.4 / -mv.z);',
                '  vTone = mix(aToneA, aToneB, e);',
                '  vAlpha = (0.5 + 0.5 * aSeed.x) * (1.0 - uIntro * 0.6);',
                '}'
            ].join('\n'),
            fragmentShader: [
                'precision mediump float;',
                'uniform vec3 uTeal; uniform vec3 uAmber; varying float vTone; varying float vAlpha;',
                'void main(){',
                '  vec2 c = gl_PointCoord - 0.5; float r = length(c); if (r > 0.5) discard;',
                '  float a = smoothstep(0.5, 0.0, r); a *= a;',
                '  vec3 col = mix(uTeal, uAmber, vTone);',
                '  col = mix(col, vec3(1.0), a * 0.4);',
                '  gl_FragColor = vec4(col, a * vAlpha);',
                '}'
            ].join('\n')
        });
        var pts = new THREE.Points(geo, mat);
        pts.frustumCulled = false;

        // two thin orbit rings, to give the object a place to live
        function ring(radius, count, tilt) {
            var g = new THREE.BufferGeometry(), arr = new Float32Array(count * 3);
            for (var k = 0; k < count; k++) {
                var a = Math.random() * 6.2832, j = (Math.random() - 0.5) * 0.02;
                arr[k * 3] = Math.cos(a) * (radius + j); arr[k * 3 + 1] = (Math.random() - 0.5) * 0.02; arr[k * 3 + 2] = Math.sin(a) * (radius + j);
            }
            g.setAttribute('position', new THREE.BufferAttribute(arr, 3));
            var m = new THREE.PointsMaterial({ size: 0.011, color: 0x7fe9dc, transparent: true, opacity: 0.38, depthWrite: false, blending: THREE.AdditiveBlending });
            var p = new THREE.Points(g, m); p.rotation.x = tilt; return p;
        }
        var rings = new THREE.Group();
        rings.add(ring(1.55, lowEnd ? 700 : 1500, 1.15));
        rings.add(ring(1.95, lowEnd ? 600 : 1300, 1.38));

        var group = new THREE.Group();
        group.add(pts); group.add(rings);
        sc.add(group);

        var state = { chapter: 0, spin: 0, key: -1, running: false, drawn: 0, tx: 0, ty: 0, mx: 0, my: 0, raf: 0, t0: performance.now() };
        var layouts = null;

        function computeLayouts() {
            var narrow = narrowMQ.matches;
            if (narrow) {
                var s = clamp(window.innerWidth / window.innerHeight * 1.2, 0.5, 0.78);
                var mid = { x: 0, y: 0.5, s: s * 0.82 };
                layouts = [{ x: 0, y: 0.78, s: s * 0.52 }, mid, mid, mid, mid, mid, { x: 0, y: 0.5, s: s * 0.95 }];
            } else {
                var w = window.innerWidth;
                var hx = w < 1200 ? 1.0 : 1.3;
                var chap = { x: w < 1200 ? 0.95 : 1.2, y: 0.02, s: 1.0 };
                layouts = [{ x: hx - 0.1, y: 0.4, s: 0.74 }, chap, chap, chap, chap, chap, { x: 0.55, y: 0.1, s: 1.15 }];
            }
        }

        function resize() {
            var w = window.innerWidth, h = window.innerHeight;
            renderer.setSize(w, h, false);
            cam.aspect = w / h; cam.updateProjectionMatrix();
            computeLayouts();
        }

        function bindKey(k) {
            if (k === state.key) return;
            state.key = k;
            aA.array.set(shapes[k].p); aA.needsUpdate = true;
            tA.array.set(shapes[k].t); tA.needsUpdate = true;
            var nk = Math.min(CHAPTERS - 1, k + 1);
            aB.array.set(shapes[nk].p); aB.needsUpdate = true;
            tB.array.set(shapes[nk].t); tB.needsUpdate = true;
        }

        function place() {
            var c = clamp(state.chapter, 0, CHAPTERS - 1);
            var k = Math.min(CHAPTERS - 2, Math.floor(c));
            var f = c - k;
            bindKey(k);
            uniforms.uMix.value = f;
            var e = f * f * (3 - 2 * f);
            var A = layouts[k], B = layouts[k + 1];
            var x = A.x + (B.x - A.x) * e, y = A.y + (B.y - A.y) * e, s = A.s + (B.s - A.s) * e;
            state.tx = x; state.ty = y;
            group.position.x += (x - group.position.x) * 0.1;
            group.position.y += (y - group.position.y) * 0.1;
            var sc2 = group.scale.x + (s - group.scale.x) * 0.1;
            group.scale.set(sc2, sc2, sc2);
        }

        function frameLoop(now) {
            state.raf = requestAnimationFrame(frameLoop);
            var t = (now - state.t0) / 1000;
            uniforms.uTime.value = t;
            place();
            var px = (state.px || 0), py = (state.py || 0);
            state.mx += (px - state.mx) * 0.06; state.my += (py - state.my) * 0.06;
            group.rotation.y = t * 0.16 + state.spin + state.mx * 0.35;
            group.rotation.x = 0.22 + state.my * 0.2;
            group.rotation.z = 0.28;
            rings.rotation.y = -t * 0.05;
            renderer.render(sc, cam);
            state.drawn++;
        }

        function renderOnce() { place(); group.rotation.set(0.22, 0.6 + state.spin, 0.28); renderer.render(sc, cam); state.drawn++; }

        window.addEventListener('pointermove', function (e) {
            state.px = (e.clientX / window.innerWidth - 0.5) * 2;
            state.py = (e.clientY / window.innerHeight - 0.5) * 2;
        }, { passive: true });

        resize();
        var onResize = (function () { var t; return function () { clearTimeout(t); t = setTimeout(resize, 120); }; })();
        window.addEventListener('resize', onResize);
        group.position.set(layouts[0].x, layouts[0].y, 0); group.scale.setScalar(layouts[0].s);

        return {
            setChapter: function (c) { state.chapter = c; },
            setSpin: function (s) { state.spin = s; },
            intro: function (dur) {
                if (!gsap) { uniforms.uIntro.value = 0; return; }
                gsap.to(uniforms.uIntro, { value: 0, duration: dur || 2.4, ease: 'power3.out' });
            },
            holdIntro: function () { uniforms.uIntro.value = 1; },
            skipIntro: function () { uniforms.uIntro.value = 0; },
            start: function () { if (!state.running) { state.running = true; state.raf = requestAnimationFrame(frameLoop); } },
            stop: function () { state.running = false; cancelAnimationFrame(state.raf); },
            renderOnce: renderOnce,
            drawn: function () { return state.drawn; }
        };
    }

    /* ------------------------------------------------------------------
       Helpers
       ------------------------------------------------------------------ */
    function padPx() {
        var probe = doc.createElement('div');
        probe.style.cssText = 'position:absolute;visibility:hidden;padding-left:var(--pad)';
        doc.body.appendChild(probe);
        var v = parseFloat(getComputedStyle(probe).paddingLeft) || 24;
        probe.remove();
        return v;
    }

    function wrapMask(el) {
        // give each block line an overflow mask and an inner element to slide
        var out = [];
        el.querySelectorAll('.hl, .ol').forEach(function (line) {
            var inner = doc.createElement('span');
            inner.className = 'in';
            inner.innerHTML = line.innerHTML;
            line.innerHTML = '';
            line.appendChild(inner);
            out.push(inner);
        });
        return out;
    }

    function fontsReady() {
        if (!doc.fonts || !doc.fonts.load) return Promise.resolve();
        return Promise.race([
            Promise.all([doc.fonts.load('600 40px "Bricolage Grotesque"'), doc.fonts.load('500 12px "JetBrains Mono"')]).then(function () { return doc.fonts.ready; }),
            new Promise(function (r) { setTimeout(r, 2500); })
        ]).catch(function () {});
    }

    /* ------------------------------------------------------------------
       Static fallback: no motion / no libraries
       ------------------------------------------------------------------ */
    function staticMode() {
        root.classList.add('no-motion');
        var s = scene;
        if (s) {
            s.skipIntro(); s.setChapter(0); s.renderOnce();
            canvas.classList.add('is-live');
        }
        if (rail) rail.style.display = 'none';
        var mqScroll = function (e) {
            var a = e.target.closest && e.target.closest('a[href^="#"]');
            if (!a) return;
            var t = doc.querySelector(a.getAttribute('href'));
            if (t) { e.preventDefault(); t.scrollIntoView({ block: 'start' }); }
        };
        doc.addEventListener('click', mqScroll);
    }

    /* ------------------------------------------------------------------
       Motion
       ------------------------------------------------------------------ */
    function motionMode() {
        gsap.registerPlugin(ScrollTrigger);
        if (SplitText) gsap.registerPlugin(SplitText);

        // smooth scroll
        if (Lenis) {
            lenis = new Lenis({ lerp: 0.09, wheelMultiplier: 0.95, smoothWheel: true, syncTouch: false, autoRaf: false });
            lenis.on('scroll', ScrollTrigger.update);
            gsap.ticker.add(function (time) { lenis.raf(time * 1000); });
            gsap.ticker.lagSmoothing(0);
        }
        if (loading && lenis) lenis.stop();

        // in-page links glide
        doc.addEventListener('click', function (e) {
            var a = e.target.closest && e.target.closest('a[href^="#"]');
            if (!a || a.getAttribute('href').length < 2) return;
            var t = doc.querySelector(a.getAttribute('href'));
            if (!t) return;
            e.preventDefault();
            var y;
            if (t.classList.contains('chapter')) y = t.getBoundingClientRect().top + window.scrollY + (t.offsetHeight - window.innerHeight) / 2;
            else y = t.getBoundingClientRect().top + window.scrollY - (t.id === 'work' || t.id === 'contact' || t.id === 'proprietor' ? 0 : 0);
            if (lenis) lenis.scrollTo(y, { duration: 1.7, easing: function (x) { return 1 - Math.pow(1 - x, 4); } });
            else window.scrollTo(0, y);
        });

        var pad = padPx();
        var mobile = narrowMQ.matches;
        var startVars = { '--ft': (mobile ? 70 : 84) + 'px', '--fx': (mobile ? 10 : pad) + 'px', '--fb': (mobile ? 10 : pad) + 'px', '--fr': (mobile ? 22 : 28) + 'px' };

        // initial hidden states (before the intro plays)
        var heroTitle = doc.getElementById('hero-title');
        var heroInners = wrapMask(heroTitle);
        var heroSide = doc.getElementById('hero-side');
        var meta = doc.querySelector('.hero-meta');
        var cue = doc.querySelector('.scroll-cue');
        gsap.set(heroInners, { yPercent: 115 });
        gsap.set([heroSide, meta, cue], { opacity: 0, y: 24 });
        gsap.set(top, { opacity: 0, y: -18 });

        // frame opens as you leave the hero
        gsap.fromTo(frame, startVars, {
            '--ft': '0px', '--fx': '0px', '--fb': '0px', '--fr': '0px', ease: 'none',
            scrollTrigger: { trigger: '#home', start: 'top top', end: 'bottom 35%', scrub: 0.6 }
        });

        // header colour follows the dark frame

        // hide the frame (and pause the scene) once the sheets cover it
        ScrollTrigger.create({
            trigger: '#work', start: 'top top',
            onEnter: function () { frame.style.visibility = 'hidden'; if (scene) scene.stop(); },
            onLeaveBack: function () { frame.style.visibility = ''; if (scene) scene.start(); }
        });

        // chapter copy
        fontsReady().then(function () {
            chapters.forEach(function (sec) {
                var h2 = sec.querySelector('.k-h2');
                var rest = sec.querySelectorAll('.idx, .k-p');
                var inner = sec.querySelector('.chapter-inner');
                var lines = null;
                if (SplitText && h2) {
                    var sp = SplitText.create(h2, { type: 'lines', mask: 'lines', linesClass: 'k-ln' });
                    lines = sp.lines;
                }
                var tl = gsap.timeline({
                    scrollTrigger: { trigger: sec, start: 'top 72%', end: 'bottom 28%', scrub: 0.7 }
                });
                if (lines) tl.from(lines, { yPercent: 112, duration: 1.1, stagger: 0.12, ease: 'power3.out' }, 0);
                else tl.from(h2, { y: 40, opacity: 0, duration: 1 }, 0);
                tl.from(rest, { y: 26, opacity: 0, duration: 0.9, stagger: 0.12, ease: 'power2.out' }, 0.28);
                tl.to({}, { duration: 2.4 });
                tl.to(inner, { y: -50, opacity: 0, duration: 1, ease: 'power1.in' });
            });
            ScrollTrigger.refresh();
        });

        // pinned sideways gallery (desktop)
        var mm = gsap.matchMedia();
        mm.add('(min-width: 860px)', function () {
            var track = doc.getElementById('track');
            var bar = doc.getElementById('work-bar');
            var dist = function () { return Math.max(0, track.scrollWidth - window.innerWidth); };
            gsap.to(track, {
                x: function () { return -dist(); }, ease: 'none',
                scrollTrigger: {
                    trigger: '#work', start: 'top top', end: function () { return '+=' + dist(); },
                    pin: true, scrub: 0.8, invalidateOnRefresh: true, anticipatePin: 1,
                    onUpdate: function (self) { bar.style.transform = 'scaleX(' + self.progress.toFixed(4) + ')'; }
                }
            });
            // cards drift a touch against the scroll for depth
            gsap.utils.toArray('.card .ic').forEach(function (ic, i) {
                gsap.fromTo(ic, { yPercent: 0 }, { yPercent: i % 2 ? -18 : 18, ease: 'none', scrollTrigger: { trigger: '#work', start: 'top top', end: function () { return '+=' + dist(); }, scrub: 1 } });
            });
        });

        // proprietor
        var ownerName = doc.getElementById('owner-name');
        var ownerInners = wrapMask(ownerName);
        gsap.set(ownerInners, { yPercent: 115 });
        ScrollTrigger.create({
            trigger: ownerName, start: 'top 82%', once: true,
            onEnter: function () { gsap.to(ownerInners, { yPercent: 0, duration: 1.2, stagger: 0.12, ease: 'power4.out' }); }
        });

        // generic reveals
        ScrollTrigger.batch('[data-reveal]', {
            start: 'top 90%', once: true,
            onEnter: function (els) { gsap.to(els, { opacity: 1, y: 0, duration: 1.1, stagger: 0.12, ease: 'power3.out' }); }
        });

        // counters
        doc.querySelectorAll('[data-count]').forEach(function (el) {
            var target = parseInt(el.getAttribute('data-count'), 10) || 0, o = { v: 0 };
            el.textContent = '0';
            ScrollTrigger.create({
                trigger: el, start: 'top 90%', once: true,
                onEnter: function () { gsap.to(o, { v: target, duration: 1.8, ease: 'power2.out', onUpdate: function () { el.textContent = Math.round(o.v); } }); }
            });
        });

        // statement fills in word by word
        var st = doc.getElementById('statement-text');
        if (SplitText && st) {
            fontsReady().then(function () {
                var sp = SplitText.create(st, { type: 'words', wordsClass: 'w' });
                gsap.fromTo(sp.words, { opacity: 0.16 }, {
                    opacity: 1, ease: 'none', stagger: 0.12,
                    scrollTrigger: { trigger: st, start: 'top 82%', end: 'bottom 52%', scrub: true }
                });
                ScrollTrigger.refresh();
            });
        }

        // magnetic buttons
        if (finePointer) {
            doc.querySelectorAll('[data-magnetic]').forEach(function (el) {
                el.addEventListener('pointermove', function (e) {
                    var r = el.getBoundingClientRect();
                    gsap.to(el, { x: (e.clientX - r.left - r.width / 2) * 0.22, y: (e.clientY - r.top - r.height / 2) * 0.3, duration: 0.4, ease: 'power3.out' });
                });
                el.addEventListener('pointerleave', function () { gsap.to(el, { x: 0, y: 0, duration: 0.7, ease: 'elastic.out(1, 0.45)' }); });
            });
        }

        // intro sequence (after the loader)
        function playIntro() {
            if (lenis) lenis.start();
            if (scene) scene.intro(2.6);
            var tl = gsap.timeline({ defaults: { ease: 'power4.out' } });
            tl.to(top, { opacity: 1, y: 0, duration: 0.9 }, 0.1)
              .to(heroInners, { yPercent: 0, duration: 1.5, stagger: 0.14 }, 0.1)
              .to([meta, cue], { opacity: 1, y: 0, duration: 1, stagger: 0.1 }, 0.5)
              .to(heroSide, { opacity: 1, y: 0, duration: 1.1 }, 0.7);
        }

        return playIntro;
    }

    /* ------------------------------------------------------------------
       Scroll -> chapter mapping, rail
       ------------------------------------------------------------------ */
    function setupChapterTracking() {
        var secs = [doc.getElementById('home')].concat(chapters, [doc.getElementById('story-end')]);
        var anchors = [], vh = window.innerHeight, storyEnd = Infinity;
        var items = rail ? Array.prototype.slice.call(rail.querySelectorAll('li')) : [];
        var railOn = false, railIdx = -1;

        function measure() {
            vh = window.innerHeight;
            var y = window.scrollY;
            anchors = secs.map(function (s, i) {
                var r = s.getBoundingClientRect(), top = r.top + y;
                if (i === 0) return 0;
                if (i === secs.length - 1) return top - vh * 0.1;
                return top + (r.height - vh) / 2;
            });
            storyEnd = story.getBoundingClientRect().bottom + y;
        }
        function chapterAt(y) {
            if (!anchors.length || y <= anchors[0]) return 0;
            for (var k = 0; k < anchors.length - 1; k++) {
                if (y < anchors[k + 1]) {
                    var f = (y - anchors[k]) / Math.max(1, anchors[k + 1] - anchors[k]);
                    return k + clamp((f - 0.2) / 0.6, 0, 1);
                }
            }
            return anchors.length - 1;
        }
        var workEl = doc.getElementById('work'), darkSecs = [doc.getElementById('statement'), doc.getElementById('contact')];
        function headerState() {
            var st = story.getBoundingClientRect(), wk = workEl.getBoundingClientRect();
            top.classList.toggle('on-dark', st.top < vh * 0.45 && st.bottom > 80);
            top.classList.toggle('is-solid', wk.top <= 70);
            var over = darkSecs.some(function (s) { var r = s.getBoundingClientRect(); return r.top <= 70 && r.bottom >= 70; });
            top.classList.toggle('over-dark', over);
        }
        function update() {
            headerState();
            var y = window.scrollY, c = chapterAt(y);
            if (scene) {
                scene.setChapter(c);
                scene.setSpin((Math.min(y, storyEnd) / Math.max(1, vh)) * 0.42 * Math.PI);
            }
            if (rail) {
                var on = c > 0.55 && c < 5.45;
                if (on !== railOn) { railOn = on; rail.classList.toggle('is-on', on); }
                var idx = clamp(Math.round(c) - 1, 0, items.length - 1);
                if (idx !== railIdx) {
                    railIdx = idx;
                    items.forEach(function (li, j) { li.classList.toggle('is-active', j === idx); });
                    var li = items[idx];
                    if (li) rail.querySelector('ul').style.setProperty('--ry', (li.offsetTop + (li.offsetHeight - 22) / 2) + 'px');
                }
            }
        }
        measure();
        window.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', function () { measure(); update(); });
        if (ScrollTrigger) ScrollTrigger.addEventListener('refresh', function () { measure(); update(); });
        update();
    }

    /* ------------------------------------------------------------------
       Boot
       ------------------------------------------------------------------ */
    function boot() {
        // WebGL scene (skipped quietly if unavailable)
        try { scene = createScene(canvas); } catch (e) { scene = null; }

        if (!motion) {
            staticMode();
            if (loader) loader.remove();
            return;
        }

        if (scene) { scene.holdIntro(); scene.start(); }
        var playIntro = motionMode();
        setupChapterTracking();

        var sceneReady = new Promise(function (resolve) {
            if (!scene) return resolve();
            var t0 = performance.now();
            (function wait() {
                if (scene.drawn() > 6 || performance.now() - t0 > 3000) resolve();
                else requestAnimationFrame(wait);
            })();
        });

        function revealScene() { if (scene) canvas.classList.add('is-live'); }

        if (!loading || !loader) {
            revealScene();
            if (loader) loader.remove();
            // wait a beat for fonts so the hero lines do not jump
            fontsReady().then(function () { playIntro(); ScrollTrigger.refresh(); });
            return;
        }

        // loader: counts toward 85% on its own, finishes when fonts and scene are really ready
        var o = { v: 0 };
        function paint() {
            var n = Math.round(o.v * 100);
            loaderN.textContent = ('00' + n).slice(-3);
            loaderBar.style.transform = 'scaleX(' + o.v.toFixed(3) + ')';
        }
        gsap.to(o, { v: 0.85, duration: 2.0, ease: 'power2.out', onUpdate: paint });
        Promise.all([fontsReady(), sceneReady, new Promise(function (r) { setTimeout(r, 1500); })]).then(function () {
            gsap.to(o, {
                v: 1, duration: 0.7, ease: 'power2.inOut', overwrite: true, onUpdate: paint,
                onComplete: function () {
                    try { sessionStorage.setItem('osb:seen', '1'); } catch (e) {}
                    revealScene();
                    gsap.to(loader, {
                        yPercent: -100, duration: 1.1, ease: 'power4.inOut',
                        onComplete: function () { root.classList.remove('is-loading'); loader.remove(); ScrollTrigger.refresh(); }
                    });
                    gsap.delayedCall(0.35, playIntro);
                }
            });
        });
    }

    if (doc.readyState === 'loading') doc.addEventListener('DOMContentLoaded', boot);
    else boot();
})();
