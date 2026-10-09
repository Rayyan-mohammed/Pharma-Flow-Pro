<?php
require_once '../app/init.php';

$phoneTel   = '+919885144064';
$phoneWa    = '919885144064';
$mapsLink   = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode('Yenugonda Mahabubnagar');
$mapsEmbed  = 'https://www.google.com/maps?q=' . rawurlencode('Yenugonda, Mahabubnagar') . '&output=embed';
$isLoggedIn = isset($_SESSION['currentUser']);
$dashLink   = 'dashboard/dashboard.php';
if ($isLoggedIn) {
    $role = $_SESSION['currentUser']['role'] ?? '';
    if ($role === 'Pharmacist') $dashLink = 'dashboard/pharmacist_dashboard.php';
    if ($role === 'Staff')      $dashLink = 'dashboard/staff_dashboard.php';
}
$staffHref  = $isLoggedIn ? $dashLink : 'login.php';
$staffLabel = $isLoggedIn ? 'Open dashboard' : 'Staff login';
$h = function ($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); };
?>
<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Om Sai Baba Medical and General Store - Mahabubnagar</title>
    <meta name="description" content="Om Sai Baba Medical and General Store in Yenugonda, Mahabubnagar. Quality medicines and general items at affordable prices, guided by a B.Pharmacy qualified chemist. Serving the community for 15 years.">
    <meta name="theme-color" content="#e8efed">
    <meta property="og:title" content="Om Sai Baba Medical and General Store - Mahabubnagar">
    <meta property="og:description" content="Your trusted healthcare partner in Mahabubnagar. Quality medicines and general items at affordable prices.">
    <meta property="og:type" content="website">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 32 32%27%3E%3Crect width=%2732%27 height=%2732%27 rx=%278%27 fill=%27%23040a09%27/%3E%3Cpath fill=%27%232dd4bf%27 d=%27M16 24.5 8.4 17c-2.4-2.5-2.2-6.4.5-8.2 2.1-1.4 5-.9 7.1 1.3 2.1-2.2 5-2.7 7.1-1.3 2.7 1.8 2.9 5.7.5 8.2z%27/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wdth,wght@12..96,75..100,200..800&amp;family=JetBrains+Mono:wght@400;500;600&amp;display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="home.css" rel="stylesheet">
    <script>
        // Decide the motion mode before first paint so there is no flash of unstyled content.
        (function () {
            var c = document.documentElement.classList;
            c.remove('no-js'); c.add('js');
            try { if (matchMedia('(prefers-reduced-motion: reduce)').matches) c.add('no-motion'); } catch (e) {}
            try { if (!c.contains('no-motion') && !sessionStorage.getItem('osb:seen')) c.add('is-loading'); } catch (e) {}
        })();
    </script>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Pharmacy",
        "name": "Om Sai Baba Medical and General Store",
        "telephone": "<?php echo $phoneTel; ?>",
        "email": "<?php echo $h(SHOP_EMAIL); ?>",
        "address": { "@type": "PostalAddress", "streetAddress": "Yenugonda", "addressLocality": "Mahabubnagar", "addressCountry": "IN" }
    }
    </script>
</head>
<body>
    <a class="skip" href="#main">Skip to content</a>
    <div class="grain" aria-hidden="true"></div>

    <!-- loader -->
    <div class="loader" id="loader" aria-hidden="true">
        <div class="name">om sai baba</div>
        <div class="count"><span id="loader-n">000</span><small>%</small></div>
        <div class="bar" id="loader-bar"></div>
    </div>

    <!-- header -->
    <header class="top" id="top">
        <a class="wordmark" href="#home" aria-label="Om Sai Baba Medical and General Store, home"><i class="bi bi-heart-pulse-fill"></i>om sai baba</a>
        <p class="top-tag">Medicines and general items, close to home in Yenugonda.</p>
        <nav class="top-nav" aria-label="Main">
            <a class="t-link" href="#work">What we offer</a>
            <a class="t-link" href="#proprietor">Proprietor</a>
            <a class="t-link" href="#contact">Contact</a>
            <a class="pill dark" href="<?php echo $staffHref; ?>"><?php echo $staffLabel; ?><span class="dot"></span></a>
        </nav>
    </header>

    <!-- fixed frame with the particle scene -->
    <div class="frame" id="frame" aria-hidden="true">
        <div class="glow"></div>
        <canvas id="scene"></canvas>
        <div class="vignette"></div>
    </div>

    <!-- chapter rail -->
    <aside class="rail" id="rail" aria-label="Chapters">
        <ul>
            <li><a href="#ch1"><span>01</span>Medicines</a></li>
            <li><a href="#ch2"><span>02</span>Advice</a></li>
            <li><a href="#ch3"><span>03</span>Everyday</a></li>
            <li><a href="#ch4"><span>04</span>Affordable</a></li>
            <li><a href="#ch5"><span>05</span>Trust</a></li>
        </ul>
    </aside>

    <main id="main">
        <!-- HERO -->
        <section class="hero" id="home">
            <span class="hero-meta mono">Yenugonda, Mahabubnagar</span>
            <span class="scroll-cue mono">Scroll</span>
            <div class="hero-inner">
                <h1 id="hero-title" aria-label="om sai baba"><span class="hl">om sai</span><span class="hl l2">baba</span></h1>
                <div class="hero-side" id="hero-side">
                    <p>Your trusted healthcare partner in Mahabubnagar. Quality medicines and general items at affordable prices.</p>
                    <div class="acts">
                        <a class="pill light" data-magnetic href="tel:<?php echo $phoneTel; ?>">Call now <i class="bi bi-arrow-right"></i></a>
                        <a class="pill ghost" data-magnetic href="<?php echo $mapsLink; ?>" target="_blank" rel="noopener">Get directions</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- STORY -->
        <div class="story" id="story">
            <section class="chapter" id="ch1" data-chapter>
                <div class="chapter-inner">
                    <span class="idx mono">01 / Medicines</span>
                    <h2 class="k-h2">Every prescription, handled with care.</h2>
                    <p class="k-p">A wide range of pharmaceutical products and general medical supplies, dispensed with the attention each one deserves.</p>
                    <div class="ch-link k-p"><b>Prescription medicines</b><span class="mono">Yenugonda</span></div>
                </div>
            </section>
            <section class="chapter" id="ch2" data-chapter>
                <div class="chapter-inner">
                    <span class="idx mono">02 / Advice</span>
                    <h2 class="k-h2">Ask. Understand. Decide.</h2>
                    <p class="k-p">Personalised advice from an experienced chemist, so you can make informed decisions about your health.</p>
                    <div class="ch-link k-p"><b>B.Pharmacy qualified</b><span class="mono">25 yrs</span></div>
                </div>
            </section>
            <section class="chapter" id="ch3" data-chapter>
                <div class="chapter-inner">
                    <span class="idx mono">03 / Everyday</span>
                    <h2 class="k-h2">Everything else you need, too.</h2>
                    <p class="k-p">Alongside medicines, the store stocks general items, so a single visit covers more of what your family needs.</p>
                    <div class="ch-link k-p"><b>Medical and general store</b><span class="mono">One stop</span></div>
                </div>
            </section>
            <section class="chapter" id="ch4" data-chapter>
                <div class="chapter-inner">
                    <span class="idx mono">04 / Affordable</span>
                    <h2 class="k-h2">Healthcare that stays affordable.</h2>
                    <p class="k-p">Quality medicines and general items at affordable prices, because good care should never feel out of reach.</p>
                    <div class="ch-link k-p"><b>Fair prices</b><span class="mono">Every day</span></div>
                </div>
            </section>
            <section class="chapter" id="ch5" data-chapter>
                <div class="chapter-inner">
                    <span class="idx mono">05 / Trust</span>
                    <h2 class="k-h2">Fifteen years of trust.</h2>
                    <p class="k-p">Serving Yenugonda with dedication and integrity, and earning a reputation for reliability, quality and service.</p>
                    <div class="ch-link k-p"><b>Wishing you a speedy recovery</b><span class="mono">15 yrs</span></div>
                </div>
            </section>
            <div class="chapter-end" id="story-end"></div>
        </div>

        <!-- WHAT WE OFFER: pinned, scrolls sideways -->
        <section class="sheet round work" id="work">
            <div class="work-pin" id="work-pin">
                <div class="work-head">
                    <div>
                        <span class="section-label mono">What we offer</span>
                        <h2 style="margin-top:16px">Everything your family needs, under one roof.</h2>
                    </div>
                    <p>From prescribed medicines to everyday general items, healthcare made simple, affordable and close to home.</p>
                </div>
                <div class="track" id="track">
                    <article class="card card-intro"><div class="card-top"><span class="card-n mono">00 / Overview</span><span class="arrow bi bi-arrow-right"></span></div><div><h3>Six things we do well.</h3><p>Keep scrolling to see what the store offers.</p></div></article>
                    <article class="card"><div class="card-top"><span class="card-n mono">01 / Prescriptions</span><span class="ic bi bi-capsule"></span></div><div><h3>Prescription medicines</h3><p>Medicines dispensed with care, exactly as your doctor prescribed.</p></div></article>
                    <article class="card"><div class="card-top"><span class="card-n mono">02 / Daily health</span><span class="ic bi bi-bandaid"></span></div><div><h3>Everyday health needs</h3><p>Common remedies, first-aid and wellness products for the whole family.</p></div></article>
                    <article class="card"><div class="card-top"><span class="card-n mono">03 / General</span><span class="ic bi bi-bag-heart"></span></div><div><h3>General items</h3><p>A range of general store items, so you can pick up daily essentials in one visit.</p></div></article>
                    <article class="card"><div class="card-top"><span class="card-n mono">04 / Supplies</span><span class="ic bi bi-clipboard2-pulse"></span></div><div><h3>Medical supplies</h3><p>General medical supplies to support care at home and recovery.</p></div></article>
                    <article class="card"><div class="card-top"><span class="card-n mono">05 / Guidance</span><span class="ic bi bi-chat-heart"></span></div><div><h3>Personal advice</h3><p>Clear, caring guidance from our pharmacist, so you can make informed health decisions.</p></div></article>
                    <article class="card"><div class="card-top"><span class="card-n mono">06 / Value</span><span class="ic bi bi-piggy-bank"></span></div><div><h3>Affordable prices</h3><p>Quality healthcare should not be out of reach. We keep our prices fair for everyone.</p></div></article>
                </div>
                <div class="progress" aria-hidden="true"><i id="work-bar"></i></div>
            </div>
        </section>

        <!-- PROPRIETOR -->
        <section class="sheet owner" id="proprietor">
            <span class="section-label mono">About the proprietor</span>
            <div class="owner-grid">
                <h2 id="owner-name" aria-label="Talpalikar Rajesh Kumar"><span class="ol">Talpalikar</span><em class="ol">Rajesh Kumar</em></h2>
                <div class="owner-copy" data-reveal>
                    <p>An experienced chemist residing in Mahabubnagar, with a distinguished career spanning 25 years in the medical field. After graduating with a B.Pharmacy degree from Karnataka, he became known for his expertise and guidance in pharmaceuticals.</p>
                    <p>His commitment to patient care goes beyond dispensing medications: he provides personalised advice and empowers individuals to make informed health decisions. His empathetic approach and deep understanding of medicines have earned him a trusted place in his community.</p>
                </div>
            </div>
            <div class="facts">
                <div class="fact" data-reveal><span class="n"><span data-count="15">15</span><sup>+</sup></span><span class="mono">Years serving Mahabubnagar</span></div>
                <div class="fact" data-reveal><span class="n"><span data-count="25">25</span><sup>+</sup></span><span class="mono">Years in the medical field</span></div>
                <div class="fact" data-reveal><span class="n" style="font-size:clamp(44px,5.4vw,84px)">B.Pharm</span><span class="mono">Karnataka, qualified chemist</span></div>
            </div>
        </section>

        <!-- STATEMENT: fills in word by word -->
        <section class="sheet statement" id="statement">
            <p id="statement-text">Quality medicines and general items, at fair prices, with honest advice from a chemist who has spent twenty-five years caring for this community.</p>
            <span class="by mono">Wishing you a speedy recovery</span>
        </section>

        <!-- CONTACT -->
        <section class="sheet contact" id="contact">
            <div class="contact-box">
                <div>
                    <span class="section-label mono" style="color:var(--on-dark-muted)">Get in touch</span>
                    <h2 data-reveal>Visit us, or call.</h2>
                    <a class="big-tel" data-reveal href="tel:<?php echo $phoneTel; ?>"><?php echo $h(SHOP_PHONE); ?></a>
                    <div class="c-list" data-reveal>
                        <div><span class="mono">Store</span><span><?php echo $h(SHOP_NAME); ?></span></div>
                        <div><span class="mono">Address</span><span><?php echo $h(SHOP_ADDRESS); ?></span></div>
                        <div><span class="mono">Email</span><a href="mailto:<?php echo $h(SHOP_EMAIL); ?>"><?php echo $h(SHOP_EMAIL); ?></a></div>
                        <div><span class="mono">Proprietor</span><span><?php echo $h(SHOP_PROPRIETOR); ?></span></div>
                    </div>
                    <div class="acts" data-reveal>
                        <a class="pill light" data-magnetic href="<?php echo $mapsLink; ?>" target="_blank" rel="noopener">Get directions <i class="bi bi-arrow-up-right"></i></a>
                        <a class="pill ghost" data-magnetic href="https://wa.me/<?php echo $phoneWa; ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> WhatsApp</a>
                    </div>
                </div>
                <div class="map" data-reveal>
                    <iframe title="Map showing Yenugonda, Mahabubnagar" src="<?php echo $h($mapsEmbed); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                </div>
            </div>
            <div class="foot mono">
                <span>&copy; <?php echo date('Y'); ?> <?php echo $h(SHOP_NAME); ?></span>
                <a href="<?php echo $staffHref; ?>"><?php echo $staffLabel; ?></a>
            </div>
        </section>
    </main>

    <a class="fab-call" href="tel:<?php echo $phoneTel; ?>" aria-label="Call the store"><i class="bi bi-telephone-fill"></i></a>

    <script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/ScrollTrigger.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/SplitText.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lenis@1.1.20/dist/lenis.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.150.1/build/three.min.js"></script>
    <script src="home.js"></script>
</body>
</html>
