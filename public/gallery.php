 <?php
session_start();

require_once "../config/database.php";
require_once "../config/config.php";
require_once "../includes/functions.php";

/*
|--------------------------------------------------------------------------
| PUBLIC GALLERY
|--------------------------------------------------------------------------
| Only published gallery records are visible to visitors.
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        gallery_id,
        title,
        description,
        image_path,
        created_at
    FROM gallery
    WHERE LOWER(status) = 'published'
    ORDER BY created_at DESC
";

$result = $conn->query($sql);

if (!$result) {
    die("Unable to load gallery.");
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
        Gallery | <?= e(SITE_NAME) ?>
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

        .gallery-hero {
            background: #f8f5f2;
            padding: 70px 8%;
            text-align: center;
        }

        .gallery-hero h1 {
            margin: 0 0 12px;
            color: #4a2c1d;
            font-size: 42px;
        }

        .gallery-hero p {
            max-width: 700px;
            margin: 0 auto;
            color: #777777;
            line-height: 1.7;
        }

        /* =====================================================
           GALLERY
        ====================================================== */

        .gallery-section {
            padding: 70px 8%;
        }
 .gallery-grid {
            display: grid;
            grid-template-columns:
                repeat(3, minmax(0, 1fr));
            gap: 25px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .gallery-card {
            background: #ffffff;
            border: 1px solid #eeeeee;
            border-radius: 14px;
            overflow: hidden;
            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.05);
        }

        .gallery-image {
            width: 100%;
            height: 230px;
            object-fit: cover;
            display: block;
        }

        .gallery-placeholder {
            width: 100%;
            height: 230px;
            background: #f3ebe5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 45px;
        }

        .gallery-content {
            padding: 20px;
        }

        .gallery-content h3 {
            margin: 0 0 10px;
            color: #4a2c1d;
        }

        .gallery-content p {
            margin: 0;
            color: #777777;
            line-height: 1.6;
            font-size: 14px;
        }

        .gallery-empty {
            max-width: 700px;
            margin: 0 auto;
            padding: 60px 20px;
            text-align: center;
            background: #f8f5f2;
            border-radius: 14px;
        }

        .gallery-empty h2 {
            color: #4a2c1d;
            margin-bottom: 10px;
        }

        .gallery-empty p {
            color: #777777;
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

        @media (max-width: 900px) {

            .public-nav {
                gap: 12px;
            }

            .gallery-grid {
                grid-template-columns:
                    repeat(2, 1fr);
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

            .gallery-section {
                padding: 50px 7%;
            }

            .gallery-hero {
                padding: 55px 7%;
            }

            .gallery-hero h1 {
                font-size: 34px;
            }

            .gallery-grid {
                grid-template-columns: 1fr;
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


<!-- =========================================================
     PAGE HEADER
========================================================== -->

<section class="gallery-hero">

    <h1>
        Gallery
    </h1>

    <p>
        Explore moments, activities and achievements
        from the Taferi Mekonnen Polytechnic Technical
        College alumni community.
    </p>

</section>


<!-- =========================================================
     GALLERY
========================================================== -->

<section class="gallery-section">

    <?php if ($result->num_rows > 0): ?>

        <div class="gallery-grid">

            <?php while (
                $gallery = $result->fetch_assoc()
            ): ?>

                <article class="gallery-card">

                    <?php
                    $imagePath = trim(
                        $gallery["image_path"] ?? ""
                    );
                    ?>

                    <?php if ($imagePath !== ""): ?>

                        <img
                            src="<?= e($imagePath) ?>"
                            alt="<?= e($gallery["title"]) ?>"
                            class="gallery-image"
                        >

                    <?php else: ?>

                        <div
                            class="gallery-placeholder"
                            aria-label="No image available"
                        >
                            🖼️
                        </div>

                    <?php endif; ?>


                    <div class="gallery-content">

                        <h3>
                            <?= e(
                                $gallery["title"]
                            ) ?>
                        </h3>

                        <?php if (
                            !empty(
                                $gallery["description"]
                            )
                        ): ?>

                            <p>
                                <?= nl2br(
                                    e(
                                        $gallery[
                                            "description"
                                        ]
                                    )
                                ) ?>
                            </p>

                        <?php endif; ?>

                    </div>

                </article>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="gallery-empty">

            <h2>
                No Gallery Items Available
            </h2>

            <p>
                There are currently no published
                gallery items.
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

        </div>


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


    <div class="copyright">

        © <?= date("Y") ?>

        Taferi Mekonnen Polytechnic Technical College.

        All rights reserved.

    </div>

</footer>

</body>

</html>