<?php
require_once '../app/init.php';

$storeName   = 'Om Sai Baba Medical and General Store';
$phoneShow   = '+91 98851 44064';
$phoneTel    = '+919885144064';
$phoneWa     = '919885144064';
$email       = 'omsaibaba@mystore.com';
$address     = 'Yenugonda, Mahabubnagar';
$mapsLink    = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode('Yenugonda Mahabubnagar');
$mapsEmbed   = 'https://www.google.com/maps?q=' . rawurlencode('Yenugonda, Mahabubnagar') . '&output=embed';
$isLoggedIn  = isset($_SESSION['currentUser']);
$dashLink    = 'dashboard/dashboard.php';
if ($isLoggedIn) {
    $role = $_SESSION['currentUser']['role'] ?? '';
    if ($role === 'Pharmacist') $dashLink = 'dashboard/pharmacist_dashboard.php';
    if ($role === 'Staff')      $dashLink = 'dashboard/staff_dashboard.php';
}
$staffHref  = $isLoggedIn ? $dashLink : 'login.php';
$staffLabel = $isLoggedIn ? 'Open Dashboard' : 'Staff Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Om Sai Baba Medical and General Store - Mahabubnagar</title>
    <meta name="description" content="Om Sai Baba Medical and General Store in Yenugonda, Mahabubnagar. Quality medicines and general items at affordable prices, guided by a B.Pharmacy qualified chemist. Serving the community for 15 years.">
    <meta name="theme-color" content="#0f766e">
    <meta property="og:title" content="Om Sai Baba Medical and General Store - Mahabubnagar">
    <meta property="og:description" content="Your trusted healthcare partner in Mahabubnagar. Quality medicines and general items at affordable prices.">
    <meta property="og:type" content="website">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 16 16%27%3E%3Crect width=%2716%27 height=%2716%27 rx=%273%27 fill=%27%230f766e%27/%3E%3Cpath fill=%27white%27 d=%27M8 3.5c-1.2-1.3-3.6-1-4.5.8-.7 1.5-.1 3 .9 4.1L8 12l3.6-3.6c1-1.1 1.6-2.6.9-4.1C11.6 2.5 9.2 2.2 8 3.5z%27/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="home.css" rel="stylesheet">
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Pharmacy",
        "name": "Om Sai Baba Medical and General Store",
        "telephone": "<?php echo $phoneTel; ?>",
        "email": "<?php echo $email; ?>",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "Yenugonda",
            "addressLocality": "Mahabubnagar",
            "addressCountry": "IN"
        }
    }
    </script>
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>

    <div class="topbar">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div><i class="bi bi-heart-pulse me-1"></i>Wishing you a speedy recovery</div>
            <div class="d-none d-md-block">
                <a href="tel:<?php echo $phoneTel; ?>"><i class="bi bi-telephone me-1"></i><?php echo $phoneShow; ?></a>
                <span class="sep">|</span>
                <a href="mailto:<?php echo $email; ?>"><i class="bi bi-envelope me-1"></i><?php echo $email; ?></a>
            </div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg site-nav" aria-label="Main navigation">
        <div class="container">
            <a class="brand" href="#home">
                <span class="brand-mark"><i class="bi bi-heart-pulse-fill"></i></span>
                <span class="brand-text"><strong>Om Sai Baba</strong><span>Medical &amp; General Store</span></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
                <i class="bi bi-list fs-3"></i>
            </button>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1 mt-3 mt-lg-0">
                    <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#services">What We Offer</a></li>
                    <li class="nav-item"><a class="nav-link" href="#proprietor">Proprietor</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                        <a class="btn-sm-login" href="<?php echo $staffHref; ?>"><i class="bi bi-box-arrow-in-right"></i><?php echo $staffLabel; ?></a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main id="main">
        <!-- HERO -->
        <header class="hero" id="home">
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6">
                        <span class="eyebrow"><i class="bi bi-geo-alt-fill"></i>Yenugonda, Mahabubnagar</span>
                        <h1>Your trusted <span class="hl">healthcare partner</span> in Mahabubnagar</h1>
                        <p class="lead">Welcome to Om Sai Baba Medical and General Store. We provide quality medicines and general items at affordable prices, with honest advice from an experienced chemist.</p>
                        <div class="hero-actions">
                            <a class="btn-brand" href="tel:<?php echo $phoneTel; ?>"><i class="bi bi-telephone-fill"></i>Call Now</a>
                            <a class="btn-ghost" href="<?php echo $mapsLink; ?>" target="_blank" rel="noopener"><i class="bi bi-geo-alt"></i>Get Directions</a>
                        </div>
                        <div class="hero-note"><i class="bi bi-shield-check fs-5"></i>Serving the Yenugonda community for 15 years</div>
                    </div>
                    <div class="col-lg-6">
                        <div class="hero-visual" aria-hidden="true">
                            <div class="hero-disc">
                                <svg viewBox="0 0 120 120" role="img" aria-label="Medical cross">
                                    <defs>
                                        <linearGradient id="g1" x1="0" y1="0" x2="1" y2="1">
                                            <stop offset="0" stop-color="#14b8a6"/><stop offset="1" stop-color="#0f766e"/>
                                        </linearGradient>
                                    </defs>
                                    <rect x="40" y="10" width="40" height="100" rx="12" fill="url(#g1)"/>
                                    <rect x="10" y="40" width="100" height="40" rx="12" fill="url(#g1)"/>
                                    <path d="M30 60h14l6-12 10 24 7-12h23" fill="none" stroke="#fff" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="float-card fc-1"><span class="ic"><i class="bi bi-mortarboard-fill"></i></span><div><strong>B.Pharmacy</strong><span>Qualified chemist</span></div></div>
                            <div class="float-card fc-2"><span class="ic"><i class="bi bi-currency-rupee"></i></span><div><strong>Affordable</strong><span>Fair prices, every day</span></div></div>
                            <div class="float-card fc-3"><span class="ic"><i class="bi bi-people-fill"></i></span><div><strong>15 Years</strong><span>Trusted locally</span></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- STATS -->
        <div class="stats">
            <div class="container">
                <div class="stats-card reveal">
                    <div class="row g-0">
                        <div class="col-6 col-md-3 stat"><div class="num">15+</div><div class="lbl">Years serving Mahabubnagar</div></div>
                        <div class="col-6 col-md-3 stat"><div class="num">25+</div><div class="lbl">Years of pharmacy experience</div></div>
                        <div class="col-6 col-md-3 stat"><div class="num">B.Pharm</div><div class="lbl">Qualified proprietor</div></div>
                        <div class="col-6 col-md-3 stat"><div class="num">1 Stop</div><div class="lbl">Medicines and general items</div></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ABOUT -->
        <section id="about">
            <div class="container">
                <div class="row g-5 align-items-center">
                    <div class="col-lg-7 reveal">
                        <div class="section-head text-start mx-0 mb-4">
                            <span class="tag">About the shop</span>
                            <h2>A cornerstone of healthcare in Yenugonda</h2>
                        </div>
                        <p class="text-secondary fs-6">Om Sai Baba Medical and General Stores, located in Mahabubnagar's Yenugonda area, has been serving the community for the past 15 years with dedication and integrity. The store provides a wide range of pharmaceutical products and general medical supplies to meet the diverse needs of its customers.</p>
                        <p class="text-secondary fs-6">Under the leadership of its proprietor, the store has earned a reputation for reliability, quality and customer-centric service, and strives every day to ensure the well-being and satisfaction of its patrons through a commitment to excellence in healthcare.</p>
                        <ul class="about-points">
                            <li><span class="tick"><i class="bi bi-check-lg"></i></span><span><strong>Reliable and trusted.</strong> A name the neighbourhood has depended on for 15 years.</span></li>
                            <li><span class="tick"><i class="bi bi-check-lg"></i></span><span><strong>Quality you can count on.</strong> Medicines and general items chosen with care.</span></li>
                            <li><span class="tick"><i class="bi bi-check-lg"></i></span><span><strong>Customer-first service.</strong> Friendly, efficient and focused on your satisfaction.</span></li>
                        </ul>
                    </div>
                    <div class="col-lg-5 reveal">
                        <div class="about-panel">
                            <div class="big">15<small> years</small></div>
                            <h3 class="mt-2">Of care, integrity and community trust</h3>
                            <hr>
                            <div class="d-flex align-items-center gap-3 mb-3"><i class="bi bi-geo-alt-fill fs-4"></i><span>Yenugonda, Mahabubnagar</span></div>
                            <div class="d-flex align-items-center gap-3 mb-3"><i class="bi bi-person-badge-fill fs-4"></i><span>Proprietor: Rajesh Kumar</span></div>
                            <div class="d-flex align-items-center gap-3"><i class="bi bi-telephone-fill fs-4"></i><a class="text-white text-decoration-none" href="tel:<?php echo $phoneTel; ?>"><?php echo $phoneShow; ?></a></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SERVICES -->
        <section id="services" class="bg-soft">
            <div class="container">
                <div class="section-head reveal">
                    <span class="tag">What we offer</span>
                    <h2>Everything your family needs, under one roof</h2>
                    <p>From prescribed medicines to everyday general items, we make healthcare simple, affordable and close to home.</p>
                </div>
                <div class="row g-4">
                    <div class="col-md-6 col-lg-4 reveal"><div class="svc"><div class="ic ic-1"><i class="bi bi-capsule"></i></div><h3>Prescription Medicines</h3><p>Medicines dispensed with care, exactly as your doctor prescribed.</p></div></div>
                    <div class="col-md-6 col-lg-4 reveal"><div class="svc"><div class="ic ic-2"><i class="bi bi-bandaid"></i></div><h3>Everyday Health Needs</h3><p>Common remedies, first-aid and wellness products for the whole family.</p></div></div>
                    <div class="col-md-6 col-lg-4 reveal"><div class="svc"><div class="ic ic-3"><i class="bi bi-bag-heart"></i></div><h3>General Items</h3><p>A range of general store items, so you can pick up daily essentials in one visit.</p></div></div>
                    <div class="col-md-6 col-lg-4 reveal"><div class="svc"><div class="ic ic-4"><i class="bi bi-clipboard2-pulse"></i></div><h3>Medical Supplies</h3><p>General medical supplies to support care at home and recovery.</p></div></div>
                    <div class="col-md-6 col-lg-4 reveal"><div class="svc"><div class="ic ic-5"><i class="bi bi-chat-heart"></i></div><h3>Personal Advice</h3><p>Ask our pharmacist: clear, caring guidance so you can make informed health decisions.</p></div></div>
                    <div class="col-md-6 col-lg-4 reveal"><div class="svc"><div class="ic ic-6"><i class="bi bi-piggy-bank"></i></div><h3>Affordable Prices</h3><p>Quality healthcare should not be out of reach. We keep our prices fair for everyone.</p></div></div>
                </div>
            </div>
        </section>

        <!-- WHY US -->
        <section id="why">
            <div class="container">
                <div class="section-head reveal">
                    <span class="tag">Why choose us</span>
                    <h2>The values behind every prescription</h2>
                </div>
                <div class="row g-4">
                    <div class="col-sm-6 col-lg-3 reveal"><div class="pillar"><div class="ring"><i class="bi bi-patch-check"></i></div><h3>Reliability</h3><p>Dependable service you can count on, visit after visit.</p></div></div>
                    <div class="col-sm-6 col-lg-3 reveal"><div class="pillar"><div class="ring"><i class="bi bi-gem"></i></div><h3>Quality</h3><p>Medicines and supplies that meet the standard your health deserves.</p></div></div>
                    <div class="col-sm-6 col-lg-3 reveal"><div class="pillar"><div class="ring"><i class="bi bi-emoji-smile"></i></div><h3>Customer-centric</h3><p>Your satisfaction and well-being come first in everything we do.</p></div></div>
                    <div class="col-sm-6 col-lg-3 reveal"><div class="pillar"><div class="ring"><i class="bi bi-shield-check"></i></div><h3>Integrity</h3><p>Honest advice and fair dealing, earned over 15 years.</p></div></div>
                </div>
            </div>
        </section>

        <!-- PROPRIETOR -->
        <section id="proprietor" class="bg-soft">
            <div class="container">
                <div class="section-head reveal">
                    <span class="tag">About the proprietor</span>
                    <h2>Meet the chemist behind the counter</h2>
                </div>
                <div class="owner-card reveal">
                    <div class="row g-4 align-items-center">
                        <div class="col-md-3 text-center"><div class="avatar mx-auto" aria-hidden="true">RK</div></div>
                        <div class="col-md-9">
                            <h3 class="mb-1">Talpalikar Rajesh Kumar</h3>
                            <p class="mb-3 text-secondary fw-semibold">Proprietor and Chemist, Mahabubnagar</p>
                            <div class="mb-3">
                                <span class="badge-soft"><i class="bi bi-mortarboard-fill"></i>B.Pharmacy (Karnataka)</span>
                                <span class="badge-soft"><i class="bi bi-award-fill"></i>25 years in the medical field</span>
                            </div>
                            <p class="text-secondary">An experienced chemist residing in Mahabubnagar, Rajesh Kumar has a distinguished career spanning 25 years in the medical field. After graduating with a B.Pharmacy degree from Karnataka, he became known for his expertise and guidance in pharmaceuticals.</p>
                            <p class="text-secondary mb-0">His commitment to patient care goes beyond dispensing medications: he provides personalised advice and empowers individuals to make informed health decisions. His empathetic approach and deep understanding of medicines have earned him a trusted place in his community.</p>
                            <blockquote>Wishing you a speedy recovery.</blockquote>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="cta-band">
            <div class="container">
                <div class="cta-inner reveal">
                    <h2>Need a medicine or a little advice?</h2>
                    <p>Call us or drop by the store in Yenugonda. We are happy to help you and your family.</p>
                    <div class="acts">
                        <a class="btn-accent" href="tel:<?php echo $phoneTel; ?>"><i class="bi bi-telephone-fill"></i>Call <?php echo $phoneShow; ?></a>
                        <a class="btn-ghost" href="https://wa.me/<?php echo $phoneWa; ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i>WhatsApp Us</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- CONTACT -->
        <section id="contact">
            <div class="container">
                <div class="section-head reveal">
                    <span class="tag">Get in touch</span>
                    <h2>Visit us or reach out</h2>
                    <p>We are in Yenugonda, Mahabubnagar. Find us on the map or contact us directly.</p>
                </div>
                <div class="row g-4">
                    <div class="col-lg-5 reveal">
                        <div class="contact-card">
                            <div class="c-row"><span class="ic"><i class="bi bi-geo-alt-fill"></i></span><div><small>Address</small><span class="v"><?php echo htmlspecialchars($storeName); ?><br><?php echo htmlspecialchars($address); ?></span></div></div>
                            <div class="c-row"><span class="ic"><i class="bi bi-telephone-fill"></i></span><div><small>Phone</small><a href="tel:<?php echo $phoneTel; ?>"><?php echo $phoneShow; ?></a><div class="text-secondary small">Proprietor: Rajesh Kumar</div></div></div>
                            <div class="c-row"><span class="ic"><i class="bi bi-envelope-fill"></i></span><div><small>Email</small><a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a></div></div>
                            <div class="mt-3 d-flex flex-wrap gap-2">
                                <a class="btn-brand" href="<?php echo $mapsLink; ?>" target="_blank" rel="noopener"><i class="bi bi-signpost-split"></i>Get Directions</a>
                                <a class="btn-ghost" href="https://wa.me/<?php echo $phoneWa; ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i>WhatsApp</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7 reveal">
                        <div class="map-wrap">
                            <iframe title="Map showing Yenugonda, Mahabubnagar" src="<?php echo htmlspecialchars($mapsEmbed); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-5">
                    <a class="brand mb-3" href="#home">
                        <span class="brand-mark"><i class="bi bi-heart-pulse-fill"></i></span>
                        <span class="brand-text"><strong style="color:#fff">Om Sai Baba</strong><span style="color:#a7cfcb">Medical &amp; General Store</span></span>
                    </a>
                    <p class="mb-0">Your trusted healthcare partner in Mahabubnagar. Quality medicines and general items at affordable prices.</p>
                </div>
                <div class="col-6 col-lg-3">
                    <h4>Explore</h4>
                    <ul>
                        <li><a href="#about">About the shop</a></li>
                        <li><a href="#services">What we offer</a></li>
                        <li><a href="#proprietor">Proprietor</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-4">
                    <h4>Contact</h4>
                    <ul>
                        <li><i class="bi bi-geo-alt me-2"></i><?php echo htmlspecialchars($address); ?></li>
                        <li><i class="bi bi-telephone me-2"></i><a href="tel:<?php echo $phoneTel; ?>"><?php echo $phoneShow; ?></a></li>
                        <li><i class="bi bi-envelope me-2"></i><a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a></li>
                    </ul>
                </div>
            </div>
            <div class="foot-bottom">
                <span>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($storeName); ?>. All rights reserved.</span>
                <span><a href="<?php echo $staffHref; ?>"><i class="bi bi-lock me-1"></i><?php echo $staffLabel; ?></a></span>
            </div>
        </div>
    </footer>

    <a class="fab-call" href="tel:<?php echo $phoneTel; ?>" aria-label="Call the store"><i class="bi bi-telephone-fill"></i></a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Reveal sections as they scroll into view
        (function () {
            var items = document.querySelectorAll('.reveal');
            if (!('IntersectionObserver' in window)) { items.forEach(function (el) { el.classList.add('in'); }); return; }
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
            }, { threshold: 0.12 });
            items.forEach(function (el) { io.observe(el); });
        })();

        // Highlight the current section in the menu and close the mobile menu after a click
        (function () {
            var links = document.querySelectorAll('.site-nav .nav-link');
            var sections = Array.prototype.map.call(links, function (a) { return document.querySelector(a.getAttribute('href')); });
            function onScroll() {
                var y = window.scrollY + 120, current = 0;
                sections.forEach(function (s, i) { if (s && s.offsetTop <= y) current = i; });
                links.forEach(function (a, i) { a.classList.toggle('active', i === current); });
            }
            window.addEventListener('scroll', onScroll, { passive: true }); onScroll();
            links.forEach(function (a) {
                a.addEventListener('click', function () {
                    var m = document.getElementById('navMenu');
                    if (m.classList.contains('show')) { bootstrap.Collapse.getOrCreateInstance(m).hide(); }
                });
            });
        })();
    </script>
</body>
</html>
