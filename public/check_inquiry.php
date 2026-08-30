 <?php

require_once "../config/database.php";
require_once "../config/config.php";
require_once "../includes/functions.php";

$error = "";
$inquiry = null;

$inquiry_id = "";
$email = "";

/* -------------------------------------------------
   CHECK INQUIRY
------------------------------------------------- */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $inquiry_id = trim(
        $_POST["inquiry_id"] ?? ""
    );

    $email = trim(
        $_POST["email"] ?? ""
    );

    if ($inquiry_id === "" || $email === "") {

        $error = "Please enter your Inquiry ID and email address.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif (!ctype_digit($inquiry_id)) {

        $error = "Invalid Inquiry ID.";

    } else {

        $id = (int) $inquiry_id;

        $sql = "
            SELECT
                inquiry_id,
                name,
                email,
                subject,
                message,
                status,
                admin_response,
                created_at,
                responded_at
            FROM contact_inquiries
            WHERE inquiry_id = ?
              AND email = ?
            LIMIT 1
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            $error =
                "Database error: " .
                $conn->error;

        } else {

            $stmt->bind_param(
                "is",
                $id,
                $email
            );

            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows === 1) {

                $inquiry =
                    $result->fetch_assoc();

            } else {

                $error =
                    "No inquiry was found with that Inquiry ID and email address.";
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
        Check Inquiry |
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

        /* -------------------------------------------------
           NAVBAR
        ------------------------------------------------- */

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

        /* -------------------------------------------------
           PAGE
        ------------------------------------------------- */

        .page-wrapper {
            max-width: 850px;
            margin: 60px auto;
            padding: 0 25px;
        }

        .page-heading {
            text-align: center;
            margin-bottom: 35px;
        }

        .page-heading h1 {
            margin: 0 0 12px;
            color: #4a2c1d;
            font-size: 36px;
        }

        .page-heading p {
            color: #777777;
            line-height: 1.7;
        }

        .inquiry-card {
            background: #ffffff;
            border: 1px solid #eeeeee;
            border-radius: 14px;
            padding: 30px;
            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
        }

        .inquiry-card h2 {
            margin-top: 0;
            color: #4a2c1d;
        }

        /* -------------------------------------------------
           FORM
        ------------------------------------------------- */

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            color: #4a2c1d;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 13px;
            border: 1px solid #dddddd;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
        }

        .form-group input:focus {
            outline: none;
            border-color: #8b5e3c;
        }

        .check-button {
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            background: #7a4b2a;
            color: #ffffff;
            font-weight: 600;
            cursor: pointer;
        }

        .check-button:hover {
            background: #5f3921;
        }

        /* -------------------------------------------------
           ALERT
        ------------------------------------------------- */

        .alert-error {
            padding: 14px 16px;
            margin-bottom: 20px;
            border-radius: 8px;
            background: #ffebee;
            color: #b71c1c;
            border: 1px solid #ffcdd2;
        }

        /* -------------------------------------------------
           INQUIRY RESULT
        ------------------------------------------------- */

        .result-card {
            background: #ffffff;
            border: 1px solid #eeeeee;
            border-radius: 14px;
            padding: 30px;
            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.05);
        }

        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 25px;
        }

        .result-header h2 {
            margin: 0 0 8px;
            color: #4a2c1d;
        }

        .result-header p {
            margin: 0;
            color: #777777;
        }

        .status-badge {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            text-transform: capitalize;
            white-space: nowrap;
        }

        .status-new {
            background: #fff3cd;
            color: #856404;
        }

        .status-read {
            background: #e8f0fe;
            color: #2457a6;
        }

        .status-responded {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-closed {
            background: #eeeeee;
            color: #555555;
        }
 .info-grid {
            display: grid;
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .info-item {
            padding: 15px;
            background: #faf8f6;
            border-radius: 8px;
        }

        .info-item span {
            display: block;
            color: #777777;
            font-size: 13px;
            margin-bottom: 5px;
        }

        .info-item strong {
            color: #333333;
        }

        .content-section {
            margin-top: 25px;
        }

        .content-section h3 {
            color: #4a2c1d;
            margin-bottom: 10px;
        }

        .message-box {
            padding: 18px;
            background: #faf8f6;
            border-radius: 8px;
            line-height: 1.7;
            color: #444444;
        }

        .response-box {
            padding: 18px;
            background: #f3ebe5;
            border-left: 4px solid #7a4b2a;
            border-radius: 8px;
            line-height: 1.7;
            color: #4a2c1d;
        }

        .waiting-box {
            padding: 18px;
            background: #fff7e6;
            border: 1px solid #ead49a;
            border-radius: 8px;
            color: #805b00;
            line-height: 1.6;
        }

        .new-search {
            display: inline-block;
            margin-top: 25px;
            padding: 11px 18px;
            border: 1px solid #8b5e3c;
            border-radius: 8px;
            color: #8b5e3c;
            font-weight: 600;
        }

        .new-search:hover {
            background: #8b5e3c;
            color: #ffffff;
        }

        /* -------------------------------------------------
           FOOTER
        ------------------------------------------------- */

        .public-footer {
            background: #3d271b;
            color: #ffffff;
            padding: 35px 8%;
            margin-top: 60px;
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

        /* -------------------------------------------------
           MOBILE
        ------------------------------------------------- */

        @media (max-width: 650px) {

            .public-navbar {
                flex-direction: column;
                gap: 15px;
            }

            .public-nav {
                flex-wrap: wrap;
                justify-content: center;
            }

            .page-wrapper {
                margin: 40px auto;
                padding: 0 15px;
            }

            .page-heading h1 {
                font-size: 30px;
            }

            .inquiry-card,
            .result-card {
                padding: 20px;
            }

            .result-header {
                flex-direction: column;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>


<!-- =================================================
     NAVIGATION
================================================== -->

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


<!-- =================================================
     MAIN CONTENT
================================================== -->

<main class="page-wrapper">


    <div class="page-heading">

        <h1>
            Check Your Inquiry
        </h1>

        <p>
            Enter the Inquiry ID and email address
            you used when submitting your contact form
            to view your inquiry and any response from
            the administrator.
        </p>

    </div>


    <!-- =================================================
         SEARCH FORM
    ================================================== -->

    <div class="inquiry-card">

        <h2>
            Find Your Inquiry
        </h2>

        <?php if ($error !== ""): ?>

            <div class="alert-error">

                <?= e($error) ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            action=""
        >

            <div class="form-group">

                <label for="inquiry_id">
                    Inquiry ID
                </label>

                <input
                    type="number"
                    id="inquiry_id"
                    name="inquiry_id"
                    placeholder="e.g. 15"
                    value="<?= e($inquiry_id) ?>"
                    min="1"
                    required
                >

            </div>


            <div class="form-group">

                <label for="email">
                    Email Address
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter the email you used"
                    value="<?= e($email) ?>"
                    required
                >

            </div>


            <button
                type="submit"
                class="check-button"
            >
                Check Inquiry
            </button>

        </form>

    </div>


    <!-- =================================================
         RESULT
    ================================================== -->

    <?php if ($inquiry !== null): ?>

        <?php

        $status = strtolower(
            trim(
                $inquiry["status"]
            )
        );

        ?>

        <div class="result-card">


            <div class="result-header">

                <div>

                    <h2>
                        <?= e(
                            $inquiry["subject"]
                        ) ?>
                    </h2>

                    <p>
                        Inquiry #<?= (int)
                            $inquiry["inquiry_id"] ?>
                    </p>

                </div>


                <span
                    class="
                        status-badge
                        status-<?= e($status) ?>
                    "
                >

                    <?= e(
                        $inquiry["status"]
                    ) ?>

                </span>

            </div>


            <!-- INFORMATION -->

            <div class="info-grid">
 <div class="info-item">

                    <span>
                        Name
                    </span>

                    <strong>
                        <?= e(
                            $inquiry["name"]
                        ) ?>
                    </strong>

                </div>


                <div class="info-item">

                    <span>
                        Email
                    </span>

                    <strong>
                        <?= e(
                            $inquiry["email"]
                        ) ?>
                    </strong>

                </div>


                <div class="info-item">

                    <span>
                        Submitted
                    </span>

                    <strong>
                        <?= e(
                            $inquiry["created_at"]
                        ) ?>
                    </strong>

                </div>


                <div class="info-item">

                    <span>
                        Responded
                    </span>

                    <strong>

                        <?php

                        if (
                            empty(
                                $inquiry["responded_at"]
                            )
                        ) {

                            echo "Not yet";

                        } else {

                            echo e(
                                $inquiry["responded_at"]
                            );

                        }

                        ?>

                    </strong>

                </div>

            </div>


            <!-- ORIGINAL MESSAGE -->

            <div class="content-section">

                <h3>
                    Your Message
                </h3>

                <div class="message-box">

                    <?= nl2br(
                        e(
                            $inquiry["message"]
                        )
                    ) ?>

                </div>

            </div>


            <!-- ADMIN RESPONSE -->

            <div class="content-section">

                <h3>
                    Administrator Response
                </h3>


                <?php if (
                    !empty(
                        $inquiry["admin_response"]
                    )
                ): ?>

                    <div class="response-box">

                        <?= nl2br(
                            e(
                                $inquiry["admin_response"]
                            )
                        ) ?>

                    </div>

                <?php else: ?>

                    <div class="waiting-box">

                        Your inquiry has been received.
                        The administrator has not responded
                        yet. Please check again later.

                    </div>

                <?php endif; ?>

            </div>


            <a
                href="check_inquiry.php"
                class="new-search"
            >
                Check Another Inquiry
            </a>

        </div>

    <?php endif; ?>


</main>


<!-- =================================================
     FOOTER
================================================== -->

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

            <a href="success_stories.php">
                Success Stories
            </a>
 <a href="announcements.php">
                Announcements
            </a>

            <a href="contact.php">
                Contact
            </a>

            <a href="check_inquiry.php">
                Check Inquiry
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