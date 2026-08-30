<?php
session_start();

require_once "../config/database.php";
require_once "../config/config.php";
require_once "../includes/functions.php";

/* ---------------------------------------------------------
   VARIABLES
--------------------------------------------------------- */
$error = "";
$success = "";

$name = "";
$email = "";
$subject = "";
$message = "";

/* ---------------------------------------------------------
   SUBMIT CONTACT FORM
--------------------------------------------------------- */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf_token();

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $subject = trim($_POST["subject"] ?? "");
    $message = trim($_POST["message"] ?? "");

    /* -----------------------------------------------------
       VALIDATION
    ----------------------------------------------------- */

    if (
        $name === "" ||
        $email === "" ||
        $subject === "" ||
        $message === ""
    ) {

        $error = "Please fill in all required fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        /* -------------------------------------------------
           INSERT INQUIRY
        ------------------------------------------------- */

    

        $sql = "
            INSERT INTO contact_inquiries (
                name,
                email,
                subject,
                message,
                status,
                created_at
            )
            VALUES (?, ?, ?, ?,'new', NOW())
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            $error = "Database error: " . $conn->error;

        } else {

            $stmt->bind_param(
                "ssss",
                $name,
                $email,
                $subject,
                $message,
            
            );

            if ($stmt->execute()) {

               $success =
    "Your message has been sent successfully. " .
    "Please save your Inquiry ID: #" .
    $conn->insert_id .
    ". You can use it to check our response later.";

                /* Clear form */

                $name = "";
                $email = "";
                $subject = "";
                $message = "";

            } else {

                $error =
                    "Failed to send your message: " .
                    $stmt->error;
            }

            $stmt->close();
        }
    }
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
        Contact | <?= e(SITE_NAME) ?>
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
           NAVIGATION
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

        .login-button {
            background: #7a4b2a !important;
            color: #ffffff !important;

            padding: 10px 17px;
            border-radius: 8px;
        }

        /* =====================================================
           PAGE HEADER
        ====================================================== */

        .contact-header {
            background: #f8f5f2;
            padding: 65px 8%;
            text-align: center;
        }

        .contact-header h1 {
            margin: 0 0 12px;
            color: #4a2c1d;
            font-size: 40px;
        }

        .contact-header p {
            max-width: 650px;
            margin: 0 auto;
            color: #777777;
            line-height: 1.7;
        }

        /* =====================================================
           CONTACT SECTION
        ====================================================== */

        .contact-section {
            padding: 70px 8%;
        }

        .contact-grid {
            max-width: 1000px;
            margin: 0 auto;

            display: grid;
            grid-template-columns:
                0.8fr 1.2fr;

            gap: 35px;
        }

        /* =====================================================
           CONTACT INFORMATION
        ====================================================== */
.social-links {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 12px;
}

.social-links a {
    display: inline-block;
    padding: 9px 13px;
    border-radius: 7px;
    background: #f3ebe5;
    color: #7a4b2a;
    font-size: 13px;
    font-weight: 600;
    transition: 0.2s;
}

.social-links a:hover {
    background: #7a4b2a;
    color: #ffffff;
}
        .contact-info {
            background: #f8f5f2;
            border-radius: 14px;
            padding: 30px;
        }

        .contact-info h2 {
            margin-top: 0;
            color: #4a2c1d;
        }

        .contact-info p {
            color: #666666;
            line-height: 1.7;
        }

        .contact-item {
            margin-top: 25px;
        }

        .contact-item strong {
            display: block;
            color: #4a2c1d;
            margin-bottom: 5px;
        }

        .contact-item span {
            color: #777777;
            line-height: 1.6;
        }

        /* =====================================================
           FORM
        ====================================================== */

        .contact-form-card {
            background: #ffffff;
            border: 1px solid #eeeeee;
            border-radius: 14px;
            padding: 30px;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.05);
        }

        .contact-form-card h2 {
            margin-top: 0;
            color: #4a2c1d;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;

            color: #4a2c1d;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;

            padding: 12px 13px;

            border: 1px solid #dddddd;
            border-radius: 8px;

            font-family: inherit;
            font-size: 14px;
        }

        .form-group textarea {
            min-height: 160px;
            resize: vertical;
            line-height: 1.6;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #8b5e3c;
        }

        .required {
            color: #b3261e;
        }

        /* =====================================================
           BUTTON
        ====================================================== */
 .submit-button {
            border: none;

            padding: 12px 22px;

            border-radius: 8px;

            background: #7a4b2a;
            color: #ffffff;

            font-weight: 600;
            cursor: pointer;
        }

        .submit-button:hover {
            background: #5f3921;
        }

        /* =====================================================
           ALERTS
        ====================================================== */

        .alert {
            padding: 13px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: #ffebee;
            color: #b71c1c;
            border: 1px solid #ffcdd2;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
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
            border-top:
                1px solid
                rgba(255,255,255,0.15);

            margin-top: 25px;
            padding-top: 18px;

            color: #c9bbb2;
            font-size: 12px;
        }

        /* =====================================================
           MOBILE
        ====================================================== */

        @media (max-width: 800px) {

            .public-navbar {
                flex-direction: column;
                gap: 15px;
            }

            .public-nav {
                flex-wrap: wrap;
                justify-content: center;
            }

            .contact-grid {
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

<section class="contact-header">

    <h1>
        Contact Us
    </h1>

    <p>
        Have a question, suggestion, or message for
        Taferi Mekonnen Polytechnic Technical College?
        Send us a message using the form below.
    </p>

</section>


<!-- =========================================================
     CONTACT
========================================================== -->

<section class="contact-section">

    <div class="contact-grid">

        <!-- CONTACT INFORMATION -->

        <div class="contact-info">
 <h2>
                Get in Touch
            </h2>

            <p>
                We welcome questions and feedback from
                alumni, students, employers, and visitors.
            </p>

            <div class="contact-item">

                <strong>
                    Institution
                </strong>

                <span>
                    Taferi Mekonnen Polytechnic
                    Technical College
                </span>

            </div>

            <div class="contact-item">

                <strong>
                    Purpose
                </strong>

                <span>
                    Alumni support, opportunities,
                    events, mentorship, and general
                    inquiries.
                </span>

            </div>

            <div class="contact-item">

                <strong>
                    Response
                </strong>

                <span>
                    Your inquiry will be reviewed by
                    the administrator.
                </span>

            </div>
<!-- SOCIAL MEDIA LINKS -->
<div class="contact-item">

    <strong>Follow Us</strong>

    <div class="social-links">

        <a href="https://t.me/YOUR_TELEGRAM" target="_blank">
            Telegram
        </a>

        <a href="https://www.facebook.com/YOUR_FACEBOOK" target="_blank">
            Facebook
        </a>

        <a href="https://www.instagram.com/YOUR_INSTAGRAM" target="_blank">
            Instagram
        </a>

        <a href="https://www.linkedin.com/in/YOUR_LINKEDIN" target="_blank">
            LinkedIn
        </a>

    </div>

</div>
        </div>


        <!-- CONTACT FORM -->

        <div class="contact-form-card">

            <h2>
                Send a Message
            </h2>

            <?php if ($error !== ""): ?>

                <div class="alert alert-error">
                    <?= e($error) ?>
                </div>

            <?php endif; ?>


            <?php if ($success !== ""): ?>

                <div class="alert alert-success">
                    <?= e($success) ?>
                </div>

            <?php endif; ?>


            <form
                method="POST"
                action=""
            >
    <input
        type="hidden"
        name="csrf_token"
        value="<?= e(csrf_token()) ?>"
    >

    
                <!-- NAME -->

                <div class="form-group">

                    <label for="name">

                        Name

                        <span class="required">
                            *
                        </span>

                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Enter your name"
                        value="<?= e($name) ?>"
                        required
                    >

                </div>


                <!-- EMAIL -->

                <div class="form-group">

                    <label for="email">

                        Email

                        <span class="required">
                            *
                        </span>

                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        value="<?= e($email) ?>"
                        required
                    >

                </div>


                <!-- SUBJECT -->

                <div class="form-group">

                    <label for="subject">

                        Subject

                        <span class="required">
                            *
                        </span>

                    </label>

                    <input
                        type="text"
                        id="subject"
                        name="subject"
                        placeholder="Enter message subject"
                        value="<?= e($subject) ?>"
                        required
                    >

                </div>


                <!-- MESSAGE -->

                <div class="form-group">

                    <label for="message">

                        Message

                        <span class="required">
                            *
                        </span>

                    </label>

                    <textarea
                        id="message"
                        name="message"
                        placeholder="Write your message..."
                        required
                    ><?= e($message) ?></textarea>
</div>


                <!-- SUBMIT -->

                <button
                    type="submit"
                    class="submit-button"
                >
                    Send Message
                </button>

            </form>

        </div>

    </div>

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
                Taferi Mekonnen Polytechnic
                Technical College
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

            <a href="gallery.php">
                Gallery
            </a>

            <a href="success_stories.php">
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

        Taferi Mekonnen Polytechnic
        Technical College.

        All rights reserved.

    </div>

</footer>

</body>

</html>