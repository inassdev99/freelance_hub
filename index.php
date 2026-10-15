<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>FreelanceHub - Plateforme de Freelance</title>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    >

    <style>
        :root {
            --primary-color: #2f344d;
            --secondary-color: #671e97;
            --accent-color: #111a1d;
            --muted-color: #70808f;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            scroll-padding-top: 85px;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, sans-serif;
            background: #fff;
            color: var(--primary-color);
        }

        a {
            transition: 0.2s ease;
        }

        a:focus-visible {
            outline: 3px solid #a976ca;
            outline-offset: 5px;
        }

        .container {
            width: min(1180px, 90%);
            margin: 0 auto;
        }

        /* HEADER COMPACT */

        .main-header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #fff;
            border-bottom: 1px solid #e7e7ed;
        }

        .main-nav {
            min-height: 62px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 24px;
        }

        .main-logo {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 23px;
            font-weight: 800;
            letter-spacing: -1px;
            white-space: nowrap;
        }

        .main-logo i {
            margin-right: 7px;
        }

        .main-logo span {
            color: var(--secondary-color);
        }

        .main-menu {
            display: flex;
            align-items: center;
            gap: 28px;
        }

        .main-menu a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .main-menu a:hover {
            color: var(--secondary-color);
        }

        .main-menu a:last-child {
            padding: 8px 16px;
            border: 1px solid #ddd6e5;
            border-radius: 30px;
        }

        /* ACCUEIL : TEXTE PLUS HAUT */

        .hero-section {
            position: relative;
            isolation: isolate;
            min-height: 520px;
            padding: 55px 0 60px;
            display: flex;
            align-items: flex-start;
            overflow: hidden;
            color: #fff;
            background: var(--primary-color);
        }

        .hero-section::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -2;
            background-image: url("https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&w=2400&q=85");
            background-position: center 45%;
            background-size: cover;
            background-repeat: no-repeat;
        }

        .hero-section::after {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            background: linear-gradient(
                90deg,
                rgba(17, 26, 29, 0.94),
                rgba(47, 52, 77, 0.83) 48%,
                rgba(47, 52, 77, 0.25)
            );
        }

        .hero-content {
            max-width: 770px;
        }

        .hero-label {
            display: inline-block;
            margin-bottom: 22px;
            padding: 10px 16px;
            border: 1px solid #ffffff50;
            border-radius: 30px;
            background: #ffffff0c;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 2px;
        }

        .hero-content h1 {
            max-width: 750px;
            margin: 0 0 22px;
            font-size: clamp(38px, 5.1vw, 66px);
            font-weight: 750;
            line-height: 1.08;
            letter-spacing: -2px;
        }

        .hero-description {
            max-width: 590px;
            margin: 0 0 28px;
            color: #e0e3e9;
            font-size: 17px;
            line-height: 1.8;
        }

        .btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .hero-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 17px 21px;
            border: 1px solid transparent;
            border-radius: 7px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 650;
        }

        .hero-btn:hover {
            transform: translateY(-3px);
        }

        .btn-freelancer {
            color: var(--primary-color);
            background: #fff;
        }

        .btn-client {
            color: #fff;
            background: var(--secondary-color);
        }

        .btn-admin {
            color: #fff;
            background: #111a1d80;
            border-color: #ffffff55;
        }

        .register-note {
            margin: 22px 0 0;
            color: #e0e3e9;
            font-size: 13px;
        }

        .register-note a {
            color: #fff;
            text-underline-offset: 3px;
        }

        /* FONCTIONNALITÉS */

        .features-section {
            padding: 80px 0;
            background: #fff;
        }

        .section-label {
            display: block;
            margin-bottom: 15px;
            color: var(--secondary-color);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 2px;
        }

        .section-title {
            margin: 0 0 42px;
            font-size: clamp(28px, 3vw, 40px);
            font-weight: 700;
            letter-spacing: -1px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 24px;
            counter-reset: feature;
        }

        .feature-card {
            position: relative;
            counter-increment: feature;
            padding: 34px 28px;
            border: 1px solid #e5e5eb;
            border-radius: 12px;
            background: #fff;
            transition: 0.25s ease;
        }

        .feature-card::after {
            content: "0" counter(feature);
            position: absolute;
            top: 30px;
            right: 25px;
            color: var(--muted-color);
            font-size: 12px;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            border-color: var(--secondary-color);
            box-shadow: 0 16px 40px #2f344d10;
        }

        .feature-icon {
            display: grid;
            place-items: center;
            width: 54px;
            height: 54px;
            margin-bottom: 30px;
            border-radius: 14px;
            color: var(--secondary-color);
            background: #671e970d;
            font-size: 23px;
        }

        .feature-card h3 {
            margin: 0 0 14px;
            font-size: 21px;
            letter-spacing: -0.4px;
        }

        .feature-card p {
            margin: 0;
            color: #596774;
            font-size: 15px;
            line-height: 1.8;
        }

        /* STATISTIQUES */

        .stats-section {
            padding: 54px 0;
            color: #fff;
            background: var(--primary-color);
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 20px;
            text-align: center;
        }

        .stat-box {
            border-right: 1px solid #ffffff25;
        }

        .stat-box:last-child {
            border-right: none;
        }

        .stat-box h3 {
            margin: 0 0 8px;
            font-size: 42px;
            letter-spacing: -1px;
        }

        .stat-box p {
            margin: 0;
            color: #d4d9e3;
            font-size: 13px;
        }

        /* FOOTER */

        .main-footer {
            padding: 65px 0 25px;
            color: #fff;
            background: var(--accent-color);
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.3fr 1fr 1fr 1fr;
            gap: 35px;
            margin-bottom: 50px;
        }

        .footer-col h4 {
            margin: 0 0 20px;
            color: #fff;
            font-size: 15px;
        }

        .footer-col p,
        .footer-links a {
            color: #b8c2ca;
            font-size: 13px;
            line-height: 1.9;
        }

        .footer-col p {
            margin: 0 0 12px;
        }

        .footer-links {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .footer-links a {
            text-decoration: none;
        }

        .footer-links a:hover {
            color: #fff;
        }

        .footer-links i {
            margin-right: 7px;
            font-size: 9px;
        }

        .footer-bottom {
            padding-top: 22px;
            border-top: 1px solid #ffffff20;
            color: #aeb9c2;
            font-size: 12px;
        }

        .footer-bottom p {
            margin: 0;
        }

        /* TABLETTE */

        @media (max-width: 900px) {
            .main-menu {
                gap: 16px;
            }

            .hero-content {
                max-width: 650px;
            }

            .features-grid {
                gap: 14px;
            }

            .feature-card {
                padding: 28px 20px;
            }

            .footer-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* MOBILE */

        @media (max-width: 640px) {
            .main-nav {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
                padding: 10px 0;
            }

            .main-logo {
                font-size: 21px;
            }

            .main-menu {
                width: 100%;
                justify-content: space-between;
                gap: 8px;
            }

            .main-menu a {
                font-size: 11px;
            }

            .main-menu a:last-child {
                padding: 7px 10px;
            }

            .hero-section {
                min-height: auto;
                padding: 40px 0;
            }

            .hero-section::after {
                background: linear-gradient(
                    90deg,
                    #111a1de8,
                    #2f344dbd
                );
            }

            .hero-label {
                font-size: 9px;
                letter-spacing: 1px;
            }

            .hero-content h1 {
                letter-spacing: -1px;
            }

            .hero-description {
                font-size: 15px;
            }

            .btn-group {
                display: grid;
                grid-template-columns: 1fr;
            }

            .hero-btn {
                width: 100%;
                padding: 15px;
            }

            .features-section {
                padding: 58px 0;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .feature-icon {
                margin-bottom: 20px;
            }

            .stats-container {
                grid-template-columns: 1fr 1fr;
                gap: 30px 12px;
            }

            .stat-box:nth-child(2) {
                border-right: none;
            }

            .stat-box h3 {
                font-size: 34px;
            }

            .footer-grid {
                grid-template-columns: 1fr;
                gap: 26px;
            }

            .footer-bottom {
                line-height: 1.8;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            html {
                scroll-behavior: auto;
            }

            * {
                transition: none !important;
            }
        }
    </style>
</head>

<body>
    <header class="main-header">
        <div class="container">
            <nav class="main-nav" aria-label="Navigation principale">
                <a href="index.php" class="main-logo">
                    <i class="fas fa-handshake" aria-hidden="true"></i>
                    Freelance<span>Hub</span>
                </a>

                <div class="main-menu">
                    <a href="#accueil">Accueil</a>
                    <a href="#fonctionnalites">Fonctionnalités</a>
                    <a href="#statistiques">Statistiques</a>
                    <a href="#contact">Contact</a>
                </div>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero-section" id="accueil">
            <div class="container">
                <div class="hero-content">
                    <span class="hero-label">
                        TALENTS LIBRES. PROJETS AMBITIEUX.
                    </span>

                    <h1>La plateforme qui révolutionne le freelance</h1>

                    <p class="hero-description">
                        Connectez-vous avec les meilleurs talents ou trouvez
                        les projets qui correspondent à vos compétences.
                        Une expérience simplifiée pour freelancers et clients.
                    </p>

                    <div class="btn-group">
                        <a
                            href="freelancer/login.php"
                            class="hero-btn btn-freelancer"
                        >
                            <i class="fas fa-user-tie" aria-hidden="true"></i>
                            Espace Freelancer
                        </a>

                        <a
                            href="client/login.php"
                            class="hero-btn btn-client"
                        >
                            <i class="fas fa-briefcase" aria-hidden="true"></i>
                            Espace Client
                        </a>

                        <a
                            href="admin/login.php"
                            class="hero-btn btn-admin"
                        >
                            <i class="fas fa-cogs" aria-hidden="true"></i>
                            Administration
                        </a>
                    </div>

                    <p class="register-note">
                        Nouveau ?
                        <a href="freelancer/register.php">
                            Inscrivez-vous gratuitement
                        </a>
                    </p>
                </div>
            </div>
        </section>

        <section class="features-section" id="fonctionnalites">
            <div class="container">
                <span class="section-label">
                    PENSÉ POUR VOTRE RÉUSSITE
                </span>

                <h2 class="section-title">
                    Tout ce dont vous avez besoin
                </h2>

                <div class="features-grid">
                    <article class="feature-card">
                        <div class="feature-icon">
                            <i
                                class="fas fa-search-dollar"
                                aria-hidden="true"
                            ></i>
                        </div>

                        <h3>Trouvez des projets</h3>

                        <p>
                            Parcourez des milliers de projets correspondant
                            à vos compétences et fixez vos propres tarifs.
                        </p>
                    </article>

                    <article class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-users" aria-hidden="true"></i>
                        </div>

                        <h3>Trouvez des talents</h3>

                        <p>
                            Accédez à une base de freelancers qualifiés
                            dans tous les domaines pour vos projets.
                        </p>
                    </article>

                    <article class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-comments" aria-hidden="true"></i>
                        </div>

                        <h3>Communication intégrée</h3>

                        <p>
                            Discutez en direct, partagez des fichiers et
                            collaborez efficacement sur notre plateforme.
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <section
            class="stats-section"
            id="statistiques"
            aria-label="Statistiques de la plateforme"
        >
            <div class="container">
                <div class="stats-container">
                    <div class="stat-box">
                        <h3 data-value="1250" data-suffix="+">1,250+</h3>
                        <p>Freelancers actifs</p>
                    </div>

                    <div class="stat-box">
                        <h3 data-value="750" data-suffix="+">750+</h3>
                        <p>Projets réalisés</p>
                    </div>

                    <div class="stat-box">
                        <h3 data-value="300" data-suffix="+">300+</h3>
                        <p>Clients satisfaits</p>
                    </div>

                    <div class="stat-box">
                        <h3 data-value="97" data-suffix="%">97%</h3>
                        <p>Taux de satisfaction</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="main-footer" id="contact">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h4>À propos</h4>

                    <p>
                        FreelanceHub est la plateforme idéale pour connecter
                        les freelancers talentueux avec les clients
                        recherchant des compétences spécifiques.
                    </p>
                </div>

                <div class="footer-col">
                    <h4>Accès rapide</h4>

                    <div class="footer-links">
                        <a href="freelancer/login.php">
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                            Espace Freelancer
                        </a>

                        <a href="client/login.php">
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                            Espace Client
                        </a>

                        <a href="admin/login.php">
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                            Administration
                        </a>

                        <a href="freelancer/register.php">
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                            Devenir Freelancer
                        </a>
                    </div>
                </div>

                <div class="footer-col">
                    <h4>Contact</h4>

                    <p>
                        <i class="fas fa-envelope" aria-hidden="true"></i>
                        contact@freelancehub.com
                    </p>

                    <p>
                        <i class="fas fa-phone" aria-hidden="true"></i>
                        +212 6 XX XX XX XX
                    </p>

                    <p>
                        <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                        Marrakech, Maroc
                    </p>
                </div>

                <div class="footer-col">
                    <h4>Projet académique</h4>

                    <p>
                        Ce projet a été réalisé dans le cadre d'une
                        formation en développement web.
                    </p>
                </div>
            </div>

            <div class="footer-bottom">
                <p>
                    &copy; 2026 FreelanceHub. Tous droits réservés.
                    | Plateforme de freelance
                </p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const statsSection = document.querySelector(".stats-section");

            const reduceMotion = window.matchMedia(
                "(prefers-reduced-motion: reduce)"
            ).matches;

            if (
                !statsSection ||
                reduceMotion ||
                !("IntersectionObserver" in window)
            ) {
                return;
            }

            const statNumbers = statsSection.querySelectorAll(
                ".stat-box h3"
            );

            const observer = new IntersectionObserver(function (entries) {
                if (!entries.some(entry => entry.isIntersecting)) {
                    return;
                }

                observer.disconnect();

                statNumbers.forEach(function (stat) {
                    const finalValue = Number(stat.dataset.value);
                    const suffix = stat.dataset.suffix || "";
                    const duration = 1500;
                    const startTime = performance.now();

                    function animate(now) {
                        const progress = Math.min(
                            (now - startTime) / duration,
                            1
                        );

                        const easedProgress = 1 - Math.pow(
                            1 - progress,
                            3
                        );

                        const value = Math.round(
                            finalValue * easedProgress
                        );

                        stat.textContent =
                            value.toLocaleString("en-US") + suffix;

                        if (progress < 1) {
                            requestAnimationFrame(animate);
                        }
                    }

                    requestAnimationFrame(animate);
                });
            }, {
                threshold: 0.3
            });

            observer.observe(statsSection);
        });
    </script>
</body>
</html>