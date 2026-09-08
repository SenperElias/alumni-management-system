<?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";

/*|--------------------------------------------------------------------------
| ALUMNI ACCESS
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "alumni") {
    header("Location: ../../index.php");
    exit;
}

$user_id = (int) $_SESSION["user_id"];

/*|--------------------------------------------------------------------------
| GET LOGGED-IN ALUMNI
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        alumni_id,
        first_name,
        last_name
    FROM alumni
    WHERE user_id = ?
    LIMIT 1
");

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$alumni = $result->fetch_assoc();

$stmt->close();

if (!$alumni) {
    die("Alumni profile not found.");
}

$mentee_id = (int) $alumni["alumni_id"];

/*|--------------------------------------------------------------------------
| GET MENTOR ID FROM URL
|--------------------------------------------------------------------------
| Example:
| request.php?mentor_id=2
|--------------------------------------------------------------------------
*/

$mentor_id = isset($_GET["mentor_id"])
    ? (int) $_GET["mentor_id"]
    : 0;

if ($mentor_id <= 0) {
    die("Invalid mentor.");
}

/*|--------------------------------------------------------------------------
| GET MENTOR INFORMATION
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        mp.mentor_profile_id,
        mp.expertise,
        mp.skills,
        mp.status,
        a.alumni_id,
        a.user_id,
        a.first_name,
        a.last_name
    FROM mentor_profiles mp
    INNER JOIN alumni a
        ON mp.alumni_id = a.alumni_id
    WHERE mp.mentor_profile_id = ?
      AND LOWER(TRIM(mp.status)) = 'active'
    LIMIT 1
");

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $mentor_id);
$stmt->execute();

$result = $stmt->get_result();
$mentor = $result->fetch_assoc();

$stmt->close();

if (!$mentor) {
    die("Mentor not found or mentor is not active.");
}

/*|--------------------------------------------------------------------------
| PREVENT REQUESTING YOURSELF
|--------------------------------------------------------------------------
*/

if ((int) $mentor["alumni_id"] === $mentee_id) {
    die("You cannot send a mentorship request to yourself.");
}

/*|--------------------------------------------------------------------------
| HANDLE REQUEST SUBMISSION
|--------------------------------------------------------------------------
*/

$error = "";
$success = "";
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $message = trim($_POST["message"] ?? "");

    /*----------------------------------------------------------------------
    | VALIDATE MESSAGE
    ----------------------------------------------------------------------
    */

    if ($message === "") {

        $error = "Please enter a message.";

    } elseif (strlen($message) < 10) {

        $error = "Please write a little more about why you want mentorship.";

    } else {

        /*------------------------------------------------------------------
        | CHECK FOR EXISTING PENDING REQUEST
        ------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            SELECT request_id
            FROM mentorship_requests
            WHERE mentor_id = ?
              AND mentee_id = ?
              AND LOWER(TRIM(status)) = 'pending'
            LIMIT 1
        ");

        if (!$stmt) {
            die("Database error: " . $conn->error);
        }

        $mentor_alumni_id = (int) $mentor["alumni_id"];
 $stmt->bind_param(
            "ii",
            $mentor_alumni_id,
            $mentee_id
        );

        $stmt->execute();

        $result = $stmt->get_result();
        $existing = $result->fetch_assoc();

        $stmt->close();

        if ($existing) {

            $error = "You already have a pending request with this mentor.";

        } else {

            /*--------------------------------------------------------------
            | INSERT NEW MENTORSHIP REQUEST
            --------------------------------------------------------------
            */

            $status = "pending";

            $stmt = $conn->prepare("
                INSERT INTO mentorship_requests
                (
                    mentor_id,
                    mentee_id,
                    message,
                    status,
                    created_at
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    NOW()
                )
            ");

            if (!$stmt) {
                die("Database error: " . $conn->error);
            }

            $stmt->bind_param(
                "iiss",
                $mentor_alumni_id,
                $mentee_id,
                $message,
                $status
            );

            if ($stmt->execute()) {

                $stmt->close();

                /*----------------------------------------------------------
                | NOTIFY MENTOR
                ----------------------------------------------------------
                */

                $notificationTitle = "New Mentorship Request";

                $notificationMessage =
                    $alumni["first_name"] . " " .
                    $alumni["last_name"] .
                    " has sent you a mentorship request.";

                $notificationType = "mentorship";

                $notifyStmt = $conn->prepare("
                    INSERT INTO notifications
                    (
                        user_id,
                        title,
                        message,
                        type,
                        opportunity_id,
                        event_id,
                        is_read
                    )
                    VALUES
                    (
                        ?,
                        ?,
                        ?,
                        ?,
                        NULL,
                        NULL,
                        0
                    )
                ");

                if ($notifyStmt) {

                    $mentor_user_id = (int) $mentor["user_id"];

                    $notifyStmt->bind_param(
                        "isss",
                        $mentor_user_id,
                        $notificationTitle,
                        $notificationMessage,
                        $notificationType
                    );

                    $notifyStmt->execute();
                    $notifyStmt->close();
                }

                $success = "Mentorship request sent successfully.";

                /* Clear message */
                $message = "";

            } else {

                $stmt->close();

                $error = "Unable to send mentorship request.";
            }
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
        Request Mentorship |
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

    <style>

        .request-wrapper {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
        }
 .back-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 16px;
            border: 1px solid #8b5e3c;
            border-radius: 8px;
            color: #8b5e3c;
            background: #ffffff;
            text-decoration: none;
        }

        .back-button:hover {
            background: #8b5e3c;
            color: #ffffff;
        }

        .request-panel {
            background: #ffffff;
            border: 1px solid #eeeeee;
            border-radius: 14px;
            padding: 30px;
            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.05);
        }

        .request-panel h2 {
            color: #4a2c1d;
            margin-top: 0;
        }

        .mentor-card {
            background: #f8f5f2;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .mentor-name {
            color: #4a2c1d;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .mentor-expertise {
            color: #7a4b2a;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .mentor-bio {
            color: #555555;
            line-height: 1.6;
        }

        .mentor-skills {
            margin-top: 12px;
            color: #555555;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #4a2c1d;
            font-weight: 600;
        }

        .form-group textarea {
            width: 100%;
            min-height: 160px;
            padding: 12px;
            border: 1px solid #dddddd;
            border-radius: 8px;
            resize: vertical;
            font-family: inherit;
            font-size: 15px;
            box-sizing: border-box;
        }

        .form-group textarea:focus {
            outline: none;
            border-color: #8b5e3c;
        }

        .submit-button {
            border: none;
            border-radius: 8px;
            padding: 12px 22px;
            background: #7a4b2a;
            color: #ffffff;
            font-weight: 600;
            cursor: pointer;
            font-size: 15px;
        }

        .submit-button:hover {
            background: #5f3921;
        }

        .success-message {
            margin-bottom: 20px;
            padding: 12px 15px;
            border-radius: 8px;
            background: #e8f5e9;
            color: #2e7d32;
        }

        .error-message {
            margin-bottom: 20px;
            padding: 12px 15px;
            border-radius: 8px;
            background: #ffebee;
            color: #c62828;
        }

        .view-requests {
            display: inline-block;
            margin-left: 10px;
            padding: 12px 18px;
            border-radius: 8px;
            border: 1px solid #7a4b2a;
            color: #7a4b2a;
            text-decoration: none;
            font-weight: 600;
        }

        .view-requests:hover {
            background: #7a4b2a;
            color: #ffffff;
        }

        @media (max-width: 650px) {

            .request-panel {
                padding: 20px;
            }

            .view-requests {
                display: block;
                margin-left: 0;
                margin-top: 10px;
                text-align: center;
            }

            .submit-button {
                width: 100%;
            }

        }

    </style>

</head>

<body class="admin-body">

<div class="admin-layout">

    <!-- =====================================================
         SIDEBAR
    ====================================================== -->

    <aside class="admin-sidebar">

        <div class="admin-brand">

            <div class="brand-logo">
                TM
            </div>

            <div>

                <strong>
                    Alumni System
                </strong>
 <small>
                    Alumni Portal
                </small>

            </div>

        </div>

        <nav class="admin-nav">

            <a href="../dashboard.php">
                Dashboard
            </a>

            <div class="nav-section">
                MY ACCOUNT
            </div>

            <a href="../profile.php">
                My Profile
            </a>

            <a href="../employment.php">
                Employment
            </a>

            <div class="nav-section">
                OPPORTUNITIES
            </div>

            <a href="../jobs.php">
                Jobs & Internships
            </a>

            <a
                href="index.php"
                class="active"
            >
                Mentorship
            </a>

            <div class="nav-section">
                ACTIVITIES
            </div>

            <a href="../projects.php">
                Projects
            </a>

            <a href="../events.php">
                Events
            </a>

            <div class="nav-section">
                SYSTEM
            </div>

            <a href="../notifications.php">
                Notifications
            </a>

            <a href="../settings.php">
                Settings
            </a>

            <a
                href="../../auth/logout.php"
                class="logout-link"
            >
                Logout
            </a>

        </nav>

    </aside>

    <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->

    <main class="admin-main">

        <header class="admin-topbar">

            <div>

                <h1>
                    Request Mentorship
                </h1>

                <p>
                    Send a mentorship request to this alumni mentor.
                </p>

            </div>

        </header>

        <section class="dashboard-content">

            <div class="request-wrapper">

                <a
                    href="index.php"
                    class="back-button"
                >
                    ← Back to Mentors
                </a>

                <div class="request-panel">

                    <h2>
                        Request Mentorship
                    </h2>

                    <!-- SUCCESS -->

                    <?php if ($success !== ""): ?>

                        <div class="success-message">

                            <?= e($success) ?>

                            <div style="margin-top: 12px;">

                                <a
                                    href="my-requests.php"
                                    class="view-requests"
                                    style="margin-left: 0;"
                                >
                                    View My Requests
                                </a>

                            </div>

                        </div>

                    <?php endif; ?>

                    <!-- ERROR -->

                    <?php if ($error !== ""): ?>

                        <div class="error-message">
                            <?= e($error) ?>
                        </div>

                    <?php endif; ?>

                    <!-- MENTOR INFORMATION -->

                    <div class="mentor-card">

                        <div class="mentor-name">

                            <?= e($mentor["first_name"]) ?>

                            <?= e($mentor["last_name"]) ?>

                        </div>

                        <div class="mentor-expertise">

                            <?= e($mentor["expertise"]) ?>

                        </div>

                        <?php if (!empty($mentor["bio"])): ?>

                            <div class="mentor-bio">

                                <?= nl2br(
                                    e($mentor["bio"])
                                ) ?>

                            </div>

                        <?php endif; ?>
 <?php if (!empty($mentor["skills"])): ?>

                            <div class="mentor-skills">

                                <strong>
                                    Skills:
                                </strong>

                                <?= e($mentor["skills"]) ?>

                            </div>

                        <?php endif; ?>

                    </div>

                    <!-- REQUEST FORM -->

                    <?php if ($success === ""): ?>

                        <form
                            method="POST"
                            action="request.php?mentor_id=<?= $mentor_id ?>"
                        >

                            <div class="form-group">

                                <label for="message">
                                    Your Message
                                </label>

                                <textarea
                                    id="message"
                                    name="message"
                                    placeholder="Introduce yourself and explain why you would like this mentor's guidance..."
                                    required
                                ><?= e($message ?? "") ?></textarea>

                            </div>

                            <button
                                type="submit"
                                class="submit-button"
                            >
                                Send Mentorship Request
                            </button>

                            <a
                                href="my-requests.php"
                                class="view-requests"
                            >
                                My Sent Requests
                            </a>

                        </form>

                    <?php endif; ?>

                </div>

            </div>

        </section>

    </main>

</div>

</body>

</html>