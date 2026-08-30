 <?php
session_start();

require_once "../config/database.php";
require_once "../config/config.php";
require_once "../includes/functions.php";

/*
|--------------------------------------------------------------------------
| GET PUBLISHED ANNOUNCEMENTS
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        announcement_id,
        title,
        content,
        category,
        audience,
        published_at,
        created_at
    FROM announcements
    WHERE status = 'published'
    ORDER BY published_at DESC, created_at DESC
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
        Announcements |
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

            position: sticky;
            top:0;
            z-index: 1000;
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

        .public-nav a.active {
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
            padding: 65px 8%;
            text-align: center;
        }

        .page-header h1 {
            margin: 0 0 12px;
            color: #4a2c1d;
            font-size: 40px;
        }

        .page-header p {
            max-width: 650px;
            margin: 0 auto;
            color: #777777;
            line-height: 1.7;
        }

        /* =====================================================
           ANNOUNCEMENTS
        ====================================================== */

        .announcements-section {
            padding: 65px 8%;
            min-height: 450px;
        }

        .announcements-container {
            max-width: 950px;
            margin: 0 auto;
        }
.announcement-card {
            background: #ffffff;
            border: 1px solid #eeeeee;
            border-radius: 14px;

            padding: 25px;
            margin-bottom: 20px;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.04);
        }

        .announcement-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;

            gap: 20px;
            margin-bottom: 12px;
        }

        .announcement-card h2 {
            margin: 0;
            color: #4a2c1d;
            font-size: 23px;
        }

        .announcement-category {
            display: inline-block;

            padding: 5px 10px;
            border-radius: 6px;

            background: #f3ebe5;
            color: #7a4b2a;

            font-size: 12px;
            font-weight: 700;

            white-space: nowrap;
        }

        .announcement-meta {
            color: #888888;
            font-size: 13px;
            margin-bottom: 18px;
        }

        .announcement-content {
            color: #555555;
            line-height: 1.8;
            font-size: 15px;
        }

        .announcement-audience {
            margin-top: 18px;
            padding-top: 15px;

            border-top: 1px solid #eeeeee;

            color: #777777;
            font-size: 13px;
        }

        .announcement-audience strong {
            color: #5f3921;
        }

        /* =====================================================
           EMPTY STATE
        ====================================================== */

        .empty-state {
            text-align: center;
            padding: 70px 20px;
            color: #777777;
        }

        .empty-state-icon {
            font-size: 45px;
            margin-bottom: 15px;
        }

        .empty-state h2 {
            margin: 0 0 10px;
            color: #4a2c1d;
        }

        .empty-state p {
            margin: 0;
            line-height: 1.6;
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
        }

        .footer-links a {
            color: #eaded6;
            font-size: 13px;
        }

        .footer-links a:hover {
            color: #ffffff;
        }

        .copyright {
            border-top: 1px solid
                rgba(255,255,255,0.15);

            margin-top: 25px;
            padding-top: 18px;

            color: #c9bbb2;
            font-size: 12px;
        }

        /* =====================================================
           MOBILE
        ====================================================== */

        @media (max-width: 700px) {

            .public-navbar {
                flex-direction: column;
                gap: 15px;
            }

            .public-nav {
                flex-wrap: wrap;
                justify-content: center;
                gap: 15px;
            }

            .page-header {
                padding: 50px 7%;
            }

            .page-header h1 {
                font-size: 32px;
            }

            .announcements-section {
                padding: 50px 7%;
            }

            .announcement-top {
                flex-direction: column;
                gap: 10px;
            }

            .announcement-category {
                align-self: flex-start;
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


<!-- =====================================================
     PAGE HEADER
====================================================== -->

<section class="page-header">

    <h1>
        Public Announcements
    </h1>

    <p>
        Stay informed about important news,
        updates, activities, and announcements
        from Taferi Mekonnen Polytechnic Technical College.
    </p>

</section>


<!-- =====================================================
     ANNOUNCEMENTS
====================================================== -->

<section class="announcements-section">

    <div class="announcements-container">

        <?php if ($result->num_rows > 0): ?>

            <?php while ($announcement = $result->fetch_assoc()): ?>

                <article class="announcement-card">

                    <div class="announcement-top">

                        <h2>
                            <?= e($announcement["title"]) ?>
                        </h2>

                        <?php if (!empty($announcement["category"])): ?>

                            <span class="announcement-category">
                                <?= e($announcement["category"]) ?>
                            </span>

                        <?php endif; ?>

                    </div>


                    <div class="announcement-meta">

                        <?php

                        $published_date =
                            $announcement["published_at"]
                            ?? $announcement["created_at"];

                        if (!empty($published_date)) {

                            echo e(
                                date(
                                    "F j, Y",
                                    strtotime($published_date)
                                )
                            );

                        }

                        ?>

                    </div>


                    <div class="announcement-content">

                        <?= nl2br(
                            e($announcement["content"])
                        ) ?>

                    </div>


                    <?php if (!empty($announcement["audience"])): ?>

                        <div class="announcement-audience">

                            <strong>
                                Audience:
                            </strong>

                            <?= e(
                                $announcement["audience"]
                            ) ?>

                        </div>

                    <?php endif; ?>

                </article>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="empty-state">

                <div class="empty-state-icon">
                    📢
                </div>

                <h2>
                    No Announcements Available
                </h2>
 <p>
                    There are currently no published
                    announcements. Please check back later.
                </p>

            </div>

        <?php endif; ?>

    </div>

</section>


<!-- =====================================================
     FOOTER
====================================================== -->

<footer class="public-footer">

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

            <a href="index.php#about">
                About
            </a>

            <a href="index.php#services">
                Services
            </a>

            <a href="announcements.php">
                Announcements
            </a>

            <a href="gallery.php">
                Gallery
            </a>

            <a href="success-stories.php">
                Success Stories
            </a>

            <a href="contact.php">
                Contact
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