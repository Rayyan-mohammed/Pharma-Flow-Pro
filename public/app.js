/* Staff area shell: collapsible menu, mobile drawer and the Ctrl+K "find anything" palette. */
(function () {
    'use strict';
    var shell = document.getElementById('app-shell');
    if (!shell) return;

    var body = document.body;
    var side = document.getElementById('side');
    var scrim = document.getElementById('side-scrim');
    var KEY_COLLAPSED = 'osb:side-collapsed';
    var KEY_RECENT = 'osb:recent';

    function store(get, k, v) {
        try { if (get) return localStorage.getItem(k); localStorage.setItem(k, v); } catch (e) {}
        return null;
    }

    /* ---------- menu: collapse on desktop, drawer on mobile ---------- */
    var mq = window.matchMedia('(max-width: 991px)');
    function applyCollapsed(on) { body.classList.toggle('side-collapsed', on && !mq.matches); }
    applyCollapsed(store(true, KEY_COLLAPSED) === '1');

    document.getElementById('side-collapse').addEventListener('click', function () {
        var on = !body.classList.contains('side-collapsed');
        applyCollapsed(on);
        store(false, KEY_COLLAPSED, on ? '1' : '0');
    });
    function openDrawer(open) {
        body.classList.toggle('drawer-open', open);
        if (open) { var a = side.querySelector('a, button'); if (a) a.focus(); }
    }
    document.getElementById('menu-btn').addEventListener('click', function () { openDrawer(true); });
    scrim.addEventListener('click', function () { openDrawer(false); });
    mq.addEventListener ? mq.addEventListener('change', function () { openDrawer(false); applyCollapsed(store(true, KEY_COLLAPSED) === '1'); }) : null;

    // keep the active item in view in a long menu
    var active = side.querySelector('.nav-op.is-active');
    if (active && active.scrollIntoView) { try { active.scrollIntoView({ block: 'center' }); } catch (e) {} }

    /* ---------- remember the operations people actually open ---------- */
    var ops = [];
    try { ops = JSON.parse(shell.getAttribute('data-ops')) || []; } catch (e) {}
    side.addEventListener('click', function (e) {
        var a = e.target.closest('a.nav-op'); if (a) remember(a.getAttribute('href'));
    });
    function remember(href) {
        var list = [];
        try { list = JSON.parse(store(true, KEY_RECENT) || '[]'); } catch (e) {}
        list = [href].concat(list.filter(function (h) { return h !== href; })).slice(0, 5);
        store(false, KEY_RECENT, JSON.stringify(list));
    }
    function recent() {
        var list = [];
        try { list = JSON.parse(store(true, KEY_RECENT) || '[]'); } catch (e) {}
        return list.map(function (h) { return ops.filter(function (o) { return o.href === h; })[0]; }).filter(Boolean);
    }

    /* ---------- palette ---------- */
    var pal = document.getElementById('palette');
    var q = document.getElementById('palette-q');
    var listEl = document.getElementById('palette-list');
    var api = shell.getAttribute('data-api');
    var rows = [], sel = 0, lastFocus = null, timer = null, token = 0, remote = [];

    function esc(s) { return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }

    function score(o, terms) {
        var hay = (o.label + ' ' + o.group + ' ' + o.keys + ' ' + o.desc).toLowerCase();
        var s = 0;
        for (var i = 0; i < terms.length; i++) {
            var t = terms[i], idx = hay.indexOf(t);
            if (idx < 0) return -1;
            s += (o.label.toLowerCase().indexOf(t) === 0 ? 6 : (o.label.toLowerCase().indexOf(t) > 0 ? 4 : (o.keys.indexOf(t) >= 0 ? 3 : 1)));
        }
        return s;
    }

    function build() {
        var text = q.value.trim().toLowerCase();
        var out = [];
        if (!text) {
            var rec = recent();
            if (rec.length) out.push({ head: 'Recent' }), rec.forEach(function (o) { out.push(o); });
            out.push({ head: 'All operations' });
            ops.forEach(function (o) { out.push(o); });
        } else {
            var terms = text.split(/\s+/);
            var hits = ops.map(function (o) { return { o: o, s: score(o, terms) }; }).filter(function (x) { return x.s >= 0; })
                .sort(function (a, b) { return b.s - a.s; }).map(function (x) { return x.o; });
            if (hits.length) { out.push({ head: 'Operations' }); hits.forEach(function (o) { out.push(o); }); }
            if (remote.length) { out.push({ head: 'Records' }); remote.forEach(function (o) { out.push(o); }); }
            if (!hits.length && !remote.length) out.push({ empty: true });
        }
        rows = out;
        var firstItem = -1;
        listEl.innerHTML = out.map(function (r, i) {
            if (r.head) return '<div class="p-head mono">' + esc(r.head) + '</div>';
            if (r.empty) return '<div class="p-empty">Nothing matches. Try a shorter word, like "stock" or "bill".</div>';
            if (firstItem < 0) firstItem = i;
            return '<a class="p-item" role="option" data-i="' + i + '" href="' + esc(r.href) + '"><span class="p-ic"><i class="bi ' + esc(r.icon) + '"></i></span><span class="p-tx"><b>' + esc(r.label) + '</b><small>' + esc(r.desc) + '</small></span><span class="p-grp mono">' + esc(r.group) + '</span></a>';
        }).join('');
        sel = firstItem < 0 ? 0 : firstItem;
        mark();
    }

    function mark() {
        var items = listEl.querySelectorAll('.p-item');
        items.forEach(function (el) { el.classList.toggle('is-sel', Number(el.getAttribute('data-i')) === sel); });
        var cur = listEl.querySelector('.p-item.is-sel');
        if (cur && cur.scrollIntoView) cur.scrollIntoView({ block: 'nearest' });
    }
    function move(d) {
        var idxs = []; rows.forEach(function (r, i) { if (r.href) idxs.push(i); });
        if (!idxs.length) return;
        var pos = Math.max(0, idxs.indexOf(sel)) + d;
        sel = idxs[(pos + idxs.length) % idxs.length];
        mark();
    }
    function go(r) { if (r && r.href) { remember(r.href); window.location.href = r.href; } }

    function openPalette() {
        lastFocus = document.activeElement;
        pal.hidden = false; body.classList.add('palette-open');
        q.value = ''; remote = []; build();
        setTimeout(function () { q.focus(); }, 10);
    }
    function closePalette() {
        pal.hidden = true; body.classList.remove('palette-open');
        if (lastFocus && lastFocus.focus) lastFocus.focus();
    }

    document.querySelectorAll('[data-open-palette]').forEach(function (b) { b.addEventListener('click', function () { openDrawer(false); openPalette(); }); });
    pal.addEventListener('mousedown', function (e) { if (e.target === pal) closePalette(); });
    listEl.addEventListener('click', function (e) {
        var a = e.target.closest('.p-item'); if (!a) return;
        e.preventDefault(); go(rows[Number(a.getAttribute('data-i'))]);
    });
    listEl.addEventListener('mousemove', function (e) {
        var a = e.target.closest('.p-item'); if (!a) return;
        var i = Number(a.getAttribute('data-i')); if (i !== sel) { sel = i; mark(); }
    });

    q.addEventListener('input', function () {
        build();
        clearTimeout(timer);
        var text = q.value.trim();
        if (text.length < 2 || !api) { remote = []; return; }
        var mine = ++token;
        timer = setTimeout(function () {
            fetch(api + '?q=' + encodeURIComponent(text), { credentials: 'same-origin' })
                .then(function (r) { return r.ok ? r.json() : { results: [] }; })
                .then(function (d) {
                    if (mine !== token) return;
                    var base = new URL(api, window.location.href);
                    remote = (d.results || []).slice(0, 8).map(function (r) {
                        var icon = { medicine: 'bi-capsule', supplier: 'bi-truck', prescription: 'bi-file-earmark-medical', sale: 'bi-receipt' }[r.type] || 'bi-circle';
                        return { label: r.title, desc: r.detail || '', group: r.type, icon: icon, href: new URL(r.url, base).pathname + new URL(r.url, base).search, keys: '' };
                    });
                    var keepSel = sel; build(); if (keepSel) { /* new list, keep first */ }
                }).catch(function () {});
        }, 180);
    });

    q.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowDown') { e.preventDefault(); move(1); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); move(-1); }
        else if (e.key === 'Enter') { e.preventDefault(); go(rows[sel]); }
    });

    document.addEventListener('keydown', function (e) {
        var typing = /^(INPUT|TEXTAREA|SELECT)$/.test((document.activeElement || {}).tagName || '') || (document.activeElement || {}).isContentEditable;
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); pal.hidden ? openPalette() : closePalette(); }
        else if (e.key === '/' && !typing && pal.hidden) { e.preventDefault(); openPalette(); }
        else if (e.key === 'Escape') { if (!pal.hidden) closePalette(); else if (body.classList.contains('drawer-open')) openDrawer(false); }
    });
})();
