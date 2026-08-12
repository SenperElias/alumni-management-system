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
| GET MY MENTORSHIP REQUESTS
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT

        mr.request_id,
        mr.mentor_id,
        mr.mentee_id,
        mr.message,
        mr.status,
        mr.created_at,
        mr.responded_at,

        mp.expertise,
        mp.skills,

        a.first_name,
        a.last_name

    FROM mentorship_requests mr

    INNER JOIN mentor_profiles mp
        ON mr.mentor_id = mp.mentor_profile_id

    INNER JOIN alumni a
        ON mp.alumni_id = a.alumni_id

    WHERE mr.mentee_id = ?

    ORDER BY mr.created_at DESC
";


$stmt = $conn->prepare($sql);

if (!$stmt) {

    die("Database error: " . $conn->error);

}

$stmt->bind_param("i", $mentee_id);

$stmt->execute();

$requests = $stmt->get_result();

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
        My Mentorship Requests |
        <?= e(SITE_NAME) ?>
    </title>


    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >


    <style>

        .requests-wrapper {

            max-width: 1100px;

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


        .requests-panel {

            background: #ffffff;

            border: 1px solid #eeeeee;

            border-radius: 14px;

            padding: 25px;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.05);

        }


        .requests-panel h1 {

            color: #4a2c1d;

            margin-top: 0;

        }


        .request-card {

            border: 1px solid #eeeeee;

            border-radius: 12px;

            padding: 20px;

            margin-bottom: 18px;

            background: #ffffff;

        }


        .request-card:last-child {

            margin-bottom: 0;

        }


        .request-header {

            display: flex;

            justify-content: space-between;

            align-items: flex-start;

            gap: 15px;

            margin-bottom: 15px;

        }


        .mentor-name {

            color: #4a2c1d;

            font-size: 19px;

            font-weight: 700;

            margin-bottom: 5px;

        }


        .mentor-expertise {

            color: #7a4b2a;

            font-weight: 600;

        }


        .status-badge {
            display: inline-block;

            padding: 6px 12px;

            border-radius: 20px;

            font-size: 13px;

            font-weight: 600;

            white-space: nowrap;

        }


        .status-pending {

            background: #fff3cd;

            color: #856404;

        }


        .status-accepted {

            background: #e8f5e9;

            color: #2e7d32;

        }


        .status-rejected {

            background: #ffebee;

            color: #c62828;

        }


        .status-cancelled {

            background: #eeeeee;

            color: #666666;

        }


        .request-message {

            background: #f8f5f2;

            border-radius: 8px;

            padding: 15px;

            margin-bottom: 15px;

            color: #555555;

            line-height: 1.6;

        }


        .request-meta {

            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 15px;

            font-size: 14px;

        }


        .meta-label {

            display: block;

            color: #888888;

            font-size: 12px;

            margin-bottom: 4px;

        }


        .meta-value {

            color: #4a2c1d;

            font-weight: 600;

        }


        .no-requests {

            text-align: center;

            padding: 50px 20px;

            color: #777777;

        }


        .browse-button {

            display: inline-block;

            margin-top: 15px;

            padding: 10px 16px;

            border-radius: 8px;

            background: #7a4b2a;

            color: #ffffff;

            text-decoration: none;

            font-weight: 600;

        }


        .browse-button:hover {

            background: #5f3921;

        }


        @media (max-width: 650px) {

            .request-header {

                flex-direction: column;

            }


            .request-meta {

                grid-template-columns: 1fr;

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


    <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->
    <main class="admin-main">


        <header class="admin-topbar">


            <div>

                <h1>
                    My Mentorship Requests
                </h1>

                <p>
                    Track the mentorship requests you have sent.
                </p>

            </div>


        </header>


        <section class="dashboard-content">


            <div class="requests-wrapper">


                <a
                    href="index.php"
                    class="back-button"
                >
                    ← Browse Mentors
                </a>


                <div class="requests-panel">


                    <h1>
                        My Requests
                    </h1>


                    <?php if ($requests->num_rows === 0): ?>


                        <div class="no-requests">


                            <h3>
                                No Mentorship Requests
                            </h3>


                            <p>
                                You have not sent any mentorship requests yet.
                            </p>


                            <a
                                href="index.php"
                                class="browse-button"
                            >
                                Browse Available Mentors
                            </a>


                        </div>


                    <?php else: ?>


                        <?php while (
                            $request = $requests->fetch_assoc()
                        ): ?>


                            <?php

                            $status =
                                strtolower(
                                    trim(
                                        $request["status"]
                                    )
                                );


                            $statusClass =
                                "status-pending";


                            if ($status === "accepted") {

                                $statusClass =
                                    "status-accepted";

                            } elseif ($status === "rejected") {

                                $statusClass =
                                    "status-rejected";

                            } elseif ($status === "cancelled") {

                                $statusClass =
                                    "status-cancelled";

                            }

                            ?>


                            <div class="request-card">


                                <div class="request-header">


                                    <div>


                                        <div class="mentor-name">

                                            <?= e(
                                                $request["first_name"]
                                            ) ?>

                                            <?= e(
                                                $request["last_name"]
                                            ) ?>

                                        </div>


                                        <div class="mentor-expertise">

                                            <?= e(
                                                $request["expertise"]
                                            ) ?>

                                        </div>


                                    </div>


                                    <span
                                        class="status-badge <?= e($statusClass) ?>"
                                    >

                                        <?= e(
                                            ucfirst($status)
                                        ) ?>

                                    </span>


                                </div>


                                <div class="request-message">
                                    <?= nl2br(
                                        e(
                                            $request["message"]
                                        )
                                    ) ?>


                                </div>


                                <div class="request-meta">


                                    <div>


                                        <span class="meta-label">

                                            Request Sent

                                        </span>


                                        <span class="meta-value">

                                            <?= e(
                                                $request["created_at"]
                                            ) ?>

                                        </span>


                                    </div>


                                    <div>


                                        <span class="meta-label">

                                            Responded

                                        </span>


                                        <span class="meta-value">

                                            <?php if (
                                                !empty(
                                                    $request[
                                                        "responded_at"
                                                    ]
                                                )
                                            ): ?>

                                                <?= e(
                                                    $request[
                                                        "responded_at"
                                                    ]
                                                ) ?>

                                            <?php else: ?>

                                                Not yet

                                            <?php endif; ?>

                                        </span>


                                    </div>


                                </div>


                            </div>


                        <?php endwhile; ?>


                    <?php endif; ?>


                </div>


            </div>


        </section>


    </main>


</div>


</body>

</html>