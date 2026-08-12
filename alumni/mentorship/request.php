<?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";


/*
|--------------------------------------------------------------------------
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


/*
|--------------------------------------------------------------------------
| GET LOGGED-IN ALUMNI
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT alumni_id
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


/*
|--------------------------------------------------------------------------
| GET MENTOR PROFILE ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {

    die("Invalid mentor.");

}

$mentor_profile_id = (int) $_GET["id"];


/*
|--------------------------------------------------------------------------
| GET MENTOR
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        mp.mentor_profile_id,
        mp.alumni_id,
        mp.expertise,
        mp.skills,
        mp.experience_years,
        mp.biography,
        mp.availability,
        mp.status,

        a.first_name,
        a.last_name

    FROM mentor_profiles mp

    INNER JOIN alumni a
        ON mp.alumni_id = a.alumni_id

    WHERE mp.mentor_profile_id = ?

      AND LOWER(TRIM(mp.status)) = 'active'

    LIMIT 1
");

$stmt->bind_param("i", $mentor_profile_id);

$stmt->execute();

$result = $stmt->get_result();

$mentor = $result->fetch_assoc();

$stmt->close();


if (!$mentor) {

    die("Mentor not found or is not currently available.");

}


$mentor_id = (int) $mentor["mentor_profile_id"];


/*
|--------------------------------------------------------------------------
| PREVENT REQUESTING YOURSELF
|--------------------------------------------------------------------------
*/

if (
    (int) $mentor["alumni_id"] === $mentee_id
) {

    die("You cannot request yourself as a mentor.");

}


/*
|--------------------------------------------------------------------------
| HANDLE FORM SUBMISSION
|--------------------------------------------------------------------------
*/

$error = "";

$success = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $message = trim(
        $_POST["message"] ?? ""
    );


    /*
    |--------------------------------------------------------------------------
    | Validate Message
    |--------------------------------------------------------------------------
    */

    if ($message === "") {

        $error = "Please write a message to the mentor.";

    } elseif (strlen($message) < 10) {

        $error =
            "Your message should contain at least 10 characters.";

    } else {


        /*
        |--------------------------------------------------------------------------
        | Check Existing Request
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            SELECT
                request_id,
                status
            FROM mentorship_requests
            WHERE mentor_id = ?
              AND mentee_id = ?
              AND LOWER(TRIM(status))
                    IN ('pending', 'accepted')
            LIMIT 1
        ");

        $stmt->bind_param(
            "ii",
            $mentor_id,
            $mentee_id
        );
        $stmt->execute();

        $result = $stmt->get_result();

        $existingRequest =
            $result->fetch_assoc();

        $stmt->close();


        if ($existingRequest) {

            $error =
                "You already have an active mentorship request with this mentor.";

        } else {


            /*
            |--------------------------------------------------------------------------
            | Create Request
            |--------------------------------------------------------------------------
            */

            $status = "Pending";


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
                (?, ?, ?, ?, NOW())
            ");


            if (!$stmt) {

                $error =
                    "Database error: " . $conn->error;

            } else {


                $stmt->bind_param(
                    "iiss",
                    $mentor_id,
                    $mentee_id,
                    $message,
                    $status
                );


                if ($stmt->execute()) {

                    $stmt->close();

                    header(
                        "Location: index.php?request=success"
                    );

                    exit;

                } else {

                    $error =
                        "Unable to send mentorship request.";

                    $stmt->close();

                }

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

            max-width: 800px;

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


        .mentor-summary {

            background: #ffffff;

            border: 1px solid #eeeeee;

            border-radius: 14px;

            padding: 25px;

            margin-bottom: 20px;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.05);

        }


        .mentor-summary h1 {

            color: #4a2c1d;

            margin-top: 0;

            margin-bottom: 8px;

        }


        .mentor-expertise {

            color: #7a4b2a;

            font-weight: 600;

            margin-bottom: 15px;

        }


        .mentor-info {

            margin-bottom: 10px;

            color: #555555;

        }


        .request-panel {

            background: #ffffff;

            border: 1px solid #eeeeee;

            border-radius: 14px;

            padding: 25px;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.05);

        }


        .request-panel h2 {

            color: #4a2c1d;

            margin-top: 0;

        }


        .form-group {

            margin-bottom: 20px;

        }


        .form-group label {

            display: block;

            margin-bottom: 8px;

            font-weight: 600;

            color: #4a2c1d;

        }


        .form-group textarea {

            width: 100%;

            min-height: 180px;

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

            display: inline-block;

            padding: 11px 20px;

            border: none;

            border-radius: 8px;

            background: #7a4b2a;

            color: #ffffff;

            cursor: pointer;

            font-size: 15px;

            font-weight: 600;

        }


        .submit-button:hover {

            background: #5f3921;

        }


        .error-message {

            margin-bottom: 20px;

            padding: 12px 15px;

            border-radius: 8px;

            background: #ffebee;

            color: #c62828;

        }


        .success-message {

            margin-bottom: 20px;

            padding: 12px 15px;

            border-radius: 8px;

            background: #e8f5e9;

            color: #2e7d32;

        }


    </style>

</head>


<body class="admin-body">


<div class="admin-layout">


    <!-- SIDEBAR -->


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


            <a href="#">
                Projects
            </a>


            <a href="#">
                Events
            </a>


            <div class="nav-section">
                SYSTEM
            </div>


            <a href="#">
                Notifications
            </a>


            <a href="#">
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


    <!-- MAIN CONTENT -->


    <main class="admin-main">


        <header class="admin-topbar">

            <div>

                <h1>
                    Request Mentorship
                </h1>

                <p>
                    Send a request to this mentor.
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


                <!-- MENTOR SUMMARY -->


                <div class="mentor-summary">


                    <h1>

                        <?= e(
                            $mentor["first_name"]
                        ) ?>

                        <?= e(
                            $mentor["last_name"]
                        ) ?>

                    </h1>


                    <div class="mentor-expertise">

                        <?= e(
                            $mentor["expertise"]
                        ) ?>

                    </div>


                    <div class="mentor-info">
                        <strong>
                            Skills:
                        </strong>

                        <?= e(
                            $mentor["skills"]
                        ) ?>

                    </div>


                    <div class="mentor-info">

                        <strong>
                            Experience:
                        </strong>

                        <?= (int) $mentor[
                            "experience_years"
                        ] ?>

                        years

                    </div>


                    <div class="mentor-info">

                        <strong>
                            Availability:
                        </strong>

                        <?= e(
                            $mentor["availability"]
                        ) ?>

                    </div>


                    <?php if (
                        !empty($mentor["biography"])
                    ): ?>

                        <div class="mentor-info">

                            <strong>
                                About:
                            </strong>

                            <?= nl2br(
                                e(
                                    $mentor["biography"]
                                )
                            ) ?>

                        </div>

                    <?php endif; ?>


                </div>


                <!-- REQUEST FORM -->


                <div class="request-panel">


                    <h2>
                        Send Mentorship Request
                    </h2>


                    <?php if ($error !== ""): ?>


                        <div class="error-message">

                            <?= e($error) ?>

                        </div>


                    <?php endif; ?>


                    <?php if ($success !== ""): ?>


                        <div class="success-message">

                            <?= e($success) ?>

                        </div>


                    <?php endif; ?>


                    <form
                        method="POST"
                        action=""
                    >


                        <div class="form-group">


                            <label for="message">

                                Message

                            </label>


                            <textarea
                                id="message"
                                name="message"
                                placeholder="Introduce yourself and explain what you would like to learn from this mentor..."
                                required
                            ></textarea>


                        </div>


                        <button
                            type="submit"
                            class="submit-button"
                        >

                            Send Mentorship Request

                        </button>


                    </form>


                </div>


            </div>


        </section>


    </main>


</div>


</body>

</html>