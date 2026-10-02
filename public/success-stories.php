<?php

session_start();

require_once "../config/database.php";
require_once "../config/config.php";
require_once "../includes/functions.php";

/*
|--------------------------------------------------------------------------
| GET APPROVED SUCCESS STORIES
|--------------------------------------------------------------------------
| Only approved stories are visible on the public website.
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        ss.story_id,
        ss.title,
        ss.achievement,
        ss.story_content,
        ss.photo,
        a.first_name,
        a.last_name
    FROM success_stories ss
    INNER JOIN alumni a
        ON ss.alumni_id = a.alumni_id
    WHERE LOWER(ss.status) = 'approved'
    ORDER BY ss.created_at DESC
";

$result = $conn->query($sql);

if (!$result) {
    die("Database error: " . $conn->error);
}

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
        Success Stories |
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

        /* =====================================================
           NAVBAR
        ====================================================== */

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
            gap: 18px;
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

        /* =====================================================
           PAGE HEADER
        ====================================================== */

        .page-header {
            background: #f8f5f2;
            text-align: center;

            padding: 70px 8%;
        }

        .page-header h1 {
            margin: 0 0 15px;

            color: #4a2c1d;
            font-size: 40px;
        }

        .page-header p {
            max-width: 700px;
            margin: 0 auto;

            color: #777777;
            line-height: 1.7;
            font-size: 16px;
        }

        /* =====================================================
           STORIES SECTION
        ====================================================== */

        .stories-section {
            padding: 70px 8%;
        }
.stories-grid {
            max-width: 1100px;
            margin: 0 auto;

            display: grid;
            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 25px;
        }

        .story-card {
            background: #ffffff;

            border: 1px solid #eeeeee;
            border-radius: 14px;

            padding: 25px;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.04);
        }

        /* =====================================================
           PHOTO
        ====================================================== */

        .story-photo {
            width: 100%;
            height: 230px;

            border-radius: 10px;

            object-fit: cover;

            margin-bottom: 20px;
        }

        .no-photo {
            width: 100%;
            height: 230px;

            border-radius: 10px;

            background: #f3ebe5;

            display: flex;
            align-items: center;
            justify-content: center;

            color: #8b6b57;
            font-size: 14px;

            margin-bottom: 20px;
        }

        /* =====================================================
           STORY CONTENT
        ====================================================== */

        .story-card h2 {
            margin: 0 0 8px;

            color: #4a2c1d;
            font-size: 23px;
        }

        .story-author {
            color: #7a4b2a;

            font-weight: 600;
            font-size: 14px;

            margin-bottom: 15px;
        }

        .achievement {
            background: #f8f5f2;

            border-left: 4px solid #7a4b2a;

            padding: 12px 15px;

            margin-bottom: 18px;

            color: #5f4738;
            line-height: 1.6;
        }

        .achievement strong {
            display: block;

            margin-bottom: 4px;

            color: #4a2c1d;
        }

        .story-content {
            color: #666666;

            line-height: 1.8;

            font-size: 14px;
        }

        /* =====================================================
           EMPTY STATE
        ====================================================== */

        .empty-state {
            max-width: 700px;
            margin: 0 auto;

            text-align: center;

            padding: 50px 25px;

            background: #faf8f6;

            border: 1px solid #eeeeee;
            border-radius: 14px;
        }

        .empty-state h2 {
            margin-top: 0;

            color: #4a2c1d;
        }

        .empty-state p {
            color: #777777;
            line-height: 1.7;
        }

        /* =====================================================
           FOOTER
        ====================================================== */

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

            margin-top: 12px;
        }

        .footer-links a {
            color: #eaded6;
            font-size: 13px;
        }

        .footer-links a:hover {
            color: #ffffff;
        }

        .copyright {
            border-top:
                1px solid
                rgba(255, 255, 255, 0.15);

            margin-top: 25px;

            padding-top: 18px;

            color: #c9bbb2;

            font-size: 12px;
        }

        /* =====================================================
           MOBILE
        ====================================================== */

        @media (max-width: 900px) {
 .public-nav {
                gap: 12px;
            }

            .stories-grid {
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

            .page-header {
                padding: 55px 7%;
            }

            .page-header h1 {
                font-size: 34px;
            }

            .stories-section {
                padding: 50px 7%;
            }

        }

    </style>

</head>

<body>

<!-- =========================================================
     NAVIGATION
========================================================== -->

<header class="public-navbar">

    <div class="public-brand">

                <div class="public-logo">
            <img src="/almuni-management-system/assets/img/college-logo.jpg" alt="Logo" style="width: 100%; height: 100%; object-fit: cover;">
        </div>

        <div>

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
    <a href="index.php">Home</a>
    <a href="index.php#about">About</a>
    <a href="index.php#services">Services</a>
    <a href="gallery.php">Gallery</a>
    <a href="success-stories.php">Success Stories</a>
    <a href="announcements.php">Announcements</a>
    <a href="contact.php">Contact</a>
    <a href="check_inquiry.php">Check Inquiry</a>
    <a href="../auth/login.php" class="login-button">Login</a>
</nav>
       

</header>


<!-- =========================================================
     PAGE HEADER
========================================================== -->

<section class="page-header">

    <h1>
        Alumni Success Stories
    </h1>

    <p>
        Discover the achievements and experiences
        of our alumni and celebrate their continued
        growth after graduation.
    </p>

</section>


<!-- =========================================================
     SUCCESS STORIES
========================================================== -->

<section class="stories-section">

    <?php if ($result->num_rows > 0): ?>

        <div class="stories-grid">

            <?php while ($story = $result->fetch_assoc()): ?>

                <article class="story-card">

                    <!-- PHOTO -->

                    <?php if (
                        !empty($story["photo"])
                    ): ?>

                        <img
                            src="<?= e($story["photo"]) ?>"
                            alt="<?= e($story["title"]) ?>"
                            class="story-photo"
                        >

                    <?php else: ?>

                        <div class="no-photo">
                            No photo available
                        </div>

                    <?php endif; ?>


                    <!-- TITLE -->

                    <h2>
                        <?= e($story["title"]) ?>
                    </h2>


                    <!-- ALUMNI NAME -->

                    <div class="story-author">

                        <?= e(
                            trim(
                                ($story["first_name"] ?? "") .
                                " " .
                                ($story["last_name"] ?? "")
                            )
                        ) ?>

                    </div>


                    <!-- ACHIEVEMENT -->

                    <?php if (
                        !empty($story["achievement"])
                    ): ?>

                        <div class="achievement">

                            <strong>
                                Achievement
                            </strong>
 <?= e(
                                $story["achievement"]
                            ) ?>

                        </div>

                    <?php endif; ?>


                    <!-- STORY -->

                    <div class="story-content">

                        <?= nl2br(
                            e(
                                $story["story_content"]
                                ?? ""
                            )
                        ) ?>

                    </div>

                </article>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="empty-state">

            <h2>
                No Success Stories Yet
            </h2>

            <p>
                There are currently no approved
                alumni success stories available.
                Please check back later.
            </p>

        </div>

    <?php endif; ?>

</section>


<!-- =========================================================
     FOOTER
========================================================== -->

<footer class="public-footer">

    <div class="footer-content">

        <div>

            <strong>
                Alumni Management System
            </strong>

            <p>
                Taferi Mekonnen Polytechnic Technical College
            </p>

            <p>
                Connecting graduates, supporting careers,
                and strengthening our alumni community.
            </p>

        </div>


        <div>

            <strong>
                Quick Links
            </strong>

            <div class="footer-links">

                <a href="index.php">
                    Home
                </a>

                <a href="about.php">
                    About
                </a>

                <a href="services.php">
                    Services
                </a>

                <a href="gallery.php">
                    Gallery
                </a>

                <a href="success-stories.php">
                    Success Stories
                </a>

                <a href="announcements.php">
                    Announcements
                </a>

                <a href="contact.php">
                    Contact
                </a>

                <a href="../auth/login.php">
                    Login
                </a>

            </div>

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