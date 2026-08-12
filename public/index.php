<?php

define('DIR', dirname(__DIR__));

require_once DIR . '/config/database.php';
require_once DIR . '/config/config.php';
require_once DIR . '/includes/functions.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family:
                Arial,
                Helvetica,
                sans-serif;
            color: #3f2a20;
            background: #ffffff;
        }

        a {
            text-decoration: none;
        }

        /* NAVBAR */

        .public-navbar {
            width: 100%;
            background: #ffffff;
            border-bottom: 1px solid #eeeeee;
            padding: 16px 6%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 10;
        }

        .public-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .public-logo {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: #7a4b2a;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
        }

        .public-brand strong {
            display: block;
            color: #4a2c1d;
            font-size: 17px;
        }

        .public-brand span {
            display: block;
            color: #888888;
            font-size: 12px;
            margin-top: 2px;
        }

        .public-nav {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .public-nav a {
            color: #555555;
            font-size: 14px;
            font-weight: 600;
        }

        .public-nav a:hover {
            color: #7a4b2a;
        }

        .login-button {
            background: #7a4b2a !important;
            color: #ffffff !important;
            padding: 10px 17px;
            border-radius: 8px;
        }

        /* HERO */

        .public-hero {
            min-height: 560px;
            display: flex;
            align-items: center;
            padding: 80px 8%;
            background:
                linear-gradient(
                    rgba(65, 40, 25, 0.78),
                    rgba(65, 40, 25, 0.78)
                ),
                url("assets/logo.png");

            background-size: cover;
            background-position: center;
        }

        .hero-content {
            max-width: 700px;
            color: #ffffff;
        }

        .hero-content small {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .hero-content h1 {
            font-size: 48px;
            line-height: 1.15;
            margin: 15px 0;
        }

        .hero-content p {
            max-width: 620px;
            font-size: 18px;
            line-height: 1.7;
            color: #f4eeee;
        }

        .hero-buttons {
            display: flex;
            gap: 12px;
            margin-top: 28px;
            flex-wrap: wrap;
        }

        .primary-button {
            display: inline-block;
            background: #ffffff;
            color: #6b4125;
            padding: 13px 22px;
            border-radius: 8px;
            font-weight: 700;
        }

        .secondary-hero-button {
            display: inline-block;
            border: 1px solid #ffffff;
            color: #ffffff;
            padding: 13px 22px;
            border-radius: 8px;
            font-weight: 700;
        }

        /* SECTION */

        .public-section {
            padding: 70px 8%;
        }
        .section-heading {
            text-align: center;
            max-width: 700px;
            margin: 0 auto 40px;
        }

        .section-heading h2 {
            margin: 0 0 12px;
            color: #4a2c1d;
            font-size: 32px;
        }

        .section-heading p {
            color: #777777;
            line-height: 1.7;
        }

        /* SERVICES */

        .services-grid {
            display: grid;
            grid-template-columns:
                repeat(4, 1fr);
            gap: 20px;
        }

        .service-card {
            background: #ffffff;
            border: 1px solid #eeeeee;
            border-radius: 14px;
            padding: 25px;
            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.04);
        }

        .service-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: #f3ebe5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 18px;
        }

        .service-card h3 {
            margin: 0 0 10px;
            color: #4a2c1d;
        }

        .service-card p {
            color: #777777;
            line-height: 1.6;
            margin: 0;
            font-size: 14px;
        }

        /* ABOUT */

        .about-section {
            background: #f8f5f2;
        }

        .about-grid {
            display: grid;
            grid-template-columns:
                1fr 1fr;
            gap: 50px;
            align-items: center;
        }

        .about-grid h2 {
            color: #4a2c1d;
            font-size: 34px;
        }

        .about-grid p {
            color: #666666;
            line-height: 1.8;
        }

        .about-box {
            background: #ffffff;
            border-radius: 16px;
            padding: 35px;
            border: 1px solid #eeeeee;
        }

        .about-box strong {
            color: #7a4b2a;
            font-size: 34px;
            display: block;
            margin-bottom: 8px;
        }

        /* CTA */

        .public-cta {
            background: #7a4b2a;
            color: #ffffff;
            text-align: center;
            padding: 65px 8%;
        }

        .public-cta h2 {
            margin: 0 0 12px;
            font-size: 32px;
        }

        .public-cta p {
            color: #f3e9e2;
            margin-bottom: 25px;
        }

        /* FOOTER */

        .public-footer {
            background: #3d271b;
            color: #ffffff;
            padding: 35px 8%;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            gap: 30px;
            flex-wrap: wrap;
        }

        .footer-content strong {
            font-size: 17px;
        }

        .footer-content p {
            color: #d7cbc3;
            font-size: 13px;
        }

        .footer-links {
            display: flex;
            gap: 18px;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: #eaded6;
            font-size: 13px;
        }

        .copyright {
            border-top: 1px solid
                rgba(255,255,255,0.15);
            margin-top: 25px;
            padding-top: 18px;
            color: #c9bbb2;
            font-size: 12px;
        }

        /* MOBILE */

        @media (max-width: 900px) {

            .public-nav {
                gap: 12px;
            }

            .services-grid {
                grid-template-columns:
                    repeat(2, 1fr);
            }

            .about-grid {
                grid-template-columns: 1fr;
            }

        }

        @media (max-width: 650px) {

            .public-navbar {
                flex-direction: column;
                gap: 15px;
            }

            .public-nav {
                flex-wrap: wrap;
                justify-content: center;
            }

            .public-hero {
                padding: 65px 7%;
            }
            .hero-content h1 {
                font-size: 36px;
            }

            .services-grid {
                grid-template-columns: 1fr;
            }

            .public-section {
                padding: 50px 7%;
            }

        }

    </style>

</head>


<body>


<!-- NAVIGATION -->

<header class="public-navbar">

    <div class="public-brand">

        <div class="public-logo">
            TM
        </div>

        <div>

            <strong>
                Alumni System
            </strong>

            <span>
                Taferi Mekonnen Polytechnic Technical College
            </span>

        </div>

    </div>


    <nav class="public-nav">

        <a href="index.php">
            Home
        </a>

        <a href="#about">
            About
        </a>

        <a href="#services">
            Services
        </a>

        <a href="#contact">
            Contact
        </a>

        <a
            href="../auth/login.php"
            class="login-button"
        >
            Login
        </a>

    </nav>

</header>


<!-- HERO -->

<section class="public-hero">

    <div class="hero-content">

        <small>
            Taferi Mekonnen Polytechnic Technical College
        </small>

        <h1>
            Welcome to Our Alumni Community
        </h1>

        <p>
            Connecting graduates, tracking career progress,
            sharing opportunities, supporting mentorship,
            and celebrating the achievements of our alumni.
        </p>

        <div class="hero-buttons">

            <a
                href="#about"
                class="primary-button"
            >
                Learn More
            </a>

            <a
                href="../auth/login.php"
                class="secondary-hero-button"
            >
                Alumni Login
            </a>

        </div>

    </div>

</section>


<!-- SERVICES -->

<section
    class="public-section"
    id="services"
>

    <div class="section-heading">

        <h2>
            What We Offer
        </h2>

        <p>
            The Alumni Management System helps graduates
            stay connected with the college and with one another.
        </p>

    </div>


    <div class="services-grid">


        <div class="service-card">

            <div class="service-icon">
                💼
            </div>

            <h3>
                Career Tracking
            </h3>

            <p>
                Keep your employment information updated
                and help the college understand graduate
                career outcomes.
            </p>

        </div>


        <div class="service-card">

            <div class="service-icon">
                🎯
            </div>

            <h3>
                Jobs & Internships
            </h3>

            <p>
                Discover employment, internship and
                training opportunities shared through
                the alumni community.
            </p>

        </div>


        <div class="service-card">

            <div class="service-icon">
                🤝
            </div>

            <h3>
                Mentorship
            </h3>

            <p>
                Connect alumni with mentors and support
                professional growth through meaningful
                relationships.
            </p>

        </div>


        <div class="service-card">

            <div class="service-icon">
                📅
            </div>

            <h3>
                Events
            </h3>

            <p>
                Stay informed about alumni events,
                college activities and community
                opportunities.
            </p>

        </div>


    </div>

</section>


<!-- ABOUT -->

<section
    class="public-section about-section"
    id="about"
>

    <div class="about-grid">


        <div>

            <h2>
                Connecting Our Graduates
            </h2>
            <p>
                The Taferi Mekonnen Polytechnic Technical
                College Alumni Management System provides
                a central platform for maintaining alumni
                relationships and supporting graduates
                after completing their studies.
            </p>

            <p>
                Alumni can update their professional
                information, discover opportunities,
                participate in mentorship, contribute
                projects and take part in college events.
            </p>

        </div>


        <div class="about-box">

            <strong>
                10
            </strong>

            <p>
                Academic departments represented
                within our alumni community.
            </p>

            <strong>
                One Community
            </strong>

            <p>
                Connecting graduates and the college
                for continued growth and collaboration.
            </p>

        </div>


    </div>

</section>


<!-- CTA -->

<section class="public-cta">

    <h2>
        Are You a Graduate?
    </h2>

    <p>
        Join the alumni community and keep your
        professional information connected with the college.
    </p>

    <a
        href="../auth/login.php"
        class="primary-button"
    >
        Access Alumni Portal
    </a>

</section>


<!-- FOOTER -->

<footer
    class="public-footer"
    id="contact"
>

    <div class="footer-content">


        <div>

            <strong>
                Alumni Management System
            </strong>

            <p>
                Taferi Mekonnen Polytechnic Technical College
            </p>

        </div>


        <div class="footer-links">

            <a href="index.php">
                Home
            </a>

            <a href="#about">
                About
            </a>

            <a href="#services">
                Services
            </a>

            <a href="../auth/login.php">
                Login
            </a>

        </div>


    </div>


    <div class="copyright">

        © <?= date("Y") ?>
        Taferi Mekonnen Polytechnic Technical College.
        All rights reserved.

    </div>

</footer>


</body>

</html>