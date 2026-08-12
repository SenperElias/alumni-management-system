<?php

session_start();

require_once "../config/database.php";
require_once "../config/config.php";
require_once "../includes/functions.php";


/*
|--------------------------------------------------------------------------
| ALUMNI ACCESS
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {

    header("Location: ../auth/login.php");
    exit;

}

if ($_SESSION["role"] !== "alumni") {

    header("Location: ../index.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| GET OPPORTUNITY ID
|--------------------------------------------------------------------------
*/

if (
    !isset($_GET["id"])
    || !is_numeric($_GET["id"])
) {

    die("Invalid opportunity ID.");

}


$opportunityId =
    (int) $_GET["id"];


/*
|--------------------------------------------------------------------------
| GET OPPORTUNITY
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("

    SELECT

        opportunity_id,
        title,
        description,
        type,
        company_name,
        location,
        deadline,
        status,
        created_at

    FROM opportunities

    WHERE opportunity_id = ?

      AND LOWER(TRIM(status)) = 'approved'

    LIMIT 1

");


if (!$stmt) {

    die(
        "Database error: "
        . $conn->error
    );

}


$stmt->bind_param(
    "i",
    $opportunityId
);


$stmt->execute();


$result =
    $stmt->get_result();


$opportunity =
    $result->fetch_assoc();


$stmt->close();


if (!$opportunity) {

    die("Opportunity not found.");

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

        <?= e(
            $opportunity["title"]
        ) ?>

        |

        <?= e(SITE_NAME) ?>

    </title>


    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >


    <style>

        .opportunity-wrapper {

            max-width: 950px;

            margin: 45px auto;

            padding: 20px;

        }


        .back-button {

            display: inline-block;

            margin-bottom: 20px;

            padding: 10px 16px;

            border-radius: 8px;

            border: 1px solid #8b5e3c;

            color: #8b5e3c;

            background: #ffffff;

            text-decoration: none;

            font-weight: 600;

        }


        .back-button:hover {

            background: #8b5e3c;

            color: #ffffff;

        }


        .opportunity-card {

            background: #ffffff;

            border-radius: 16px;

            padding: 40px;

            border: 1px solid #eeeeee;

            box-shadow:
                0 8px 30px
                rgba(0, 0, 0, 0.07);

        }


        .opportunity-type {

            display: inline-block;

            padding: 7px 13px;

            border-radius: 20px;

            background: #f8f5f2;

            color: #7a4b2a;

            font-size: 13px;

            font-weight: 700;

            margin-bottom: 15px;

        }


        .opportunity-title {

            color: #4a2c1d;

            font-size: 34px;

            margin: 0 0 10px;

        }


        .company-name {

            color: #7a4b2a;

            font-size: 18px;

            font-weight: 600;

            margin-bottom: 30px;

        }


        .description-title {

            color: #4a2c1d;

            margin-bottom: 10px;

        }


        .description {

            color: #555555;

            line-height: 1.8;

            white-space: normal;

            margin-bottom: 30px;

        }


        .info-grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 16px;

        }


        .info-box {

            background: #f8f5f2;

            border-radius: 10px;

            padding: 18px;

        }


        .info-label {
            display: block;

            font-size: 12px;

            color: #777777;

            margin-bottom: 6px;

        }


        .info-value {

            color: #4a2c1d;

            font-weight: 600;

        }


        @media (max-width: 650px) {

            .opportunity-card {

                padding: 25px;

            }


            .opportunity-title {

                font-size: 27px;

            }


            .info-grid {

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


            <a href="dashboard.php">
                Dashboard
            </a>


            <div class="nav-section">
                MY ACCOUNT
            </div>


            <a href="profile.php">
                My Profile
            </a>


            <a href="employment.php">
                Employment
            </a>


            <div class="nav-section">
                OPPORTUNITIES
            </div>


            <a
                href="jobs.php"
                class="active"
            >
                Jobs & Internships
            </a>


            <a href="mentorship/index.php">
                Mentorship
            </a>


            <div class="nav-section">
                ACTIVITIES
            </div>


            <a href="projects/index.php">
                Projects
            </a>


            <a href="events/events.php">
                Events
            </a>


            <div class="nav-section">
                SYSTEM
            </div>


            <a href="notifications.php">
                Notifications
            </a>


            <a href="settings.php">
                Settings
            </a>


            <a
                href="../auth/logout.php"
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
                    Opportunity Details
                </h1>

                <p>
                    Review the opportunity information below.
                </p>

            </div>


        </header>


        <section class="dashboard-content">


            <div class="opportunity-wrapper">


                <a
                    href="jobs.php"
                    class="back-button"
                >
                    ← Back to Jobs & Internships
                </a>


                <article class="opportunity-card">


                    <span class="opportunity-type">

                        <?= e(
                            $opportunity[
                                "type"
                            ]
                        ) ?>

                    </span>


                    <h1 class="opportunity-title">

                        <?= e(
                            $opportunity[
                                "title"
                            ]
                        ) ?>

                    </h1>


                    <div class="company-name">

                        <?= e(
                            $opportunity[
                                "company_name"
                            ]
                        ) ?>

                    </div>


                    <h3 class="description-title">
                        Opportunity Description

                    </h3>


                    <div class="description">

                        <?= nl2br(
                            e(
                                $opportunity[
                                    "description"
                                ]
                            )
                        ) ?>

                    </div>


                    <div class="info-grid">


                        <div class="info-box">


                            <span class="info-label">

                                Opportunity Type

                            </span>


                            <span class="info-value">

                                <?= e(
                                    $opportunity[
                                        "type"
                                    ]
                                ) ?>

                            </span>


                        </div>


                        <div class="info-box">


                            <span class="info-label">

                                Company

                            </span>


                            <span class="info-value">

                                <?= e(
                                    $opportunity[
                                        "company_name"
                                    ]
                                ) ?>

                            </span>


                        </div>


                        <div class="info-box">


                            <span class="info-label">

                                Location

                            </span>


                            <span class="info-value">

                                <?= e(
                                    $opportunity[
                                        "location"
                                    ]
                                ) ?>

                            </span>


                        </div>


                        <div class="info-box">


                            <span class="info-label">

                                Application Deadline

                            </span>


                            <span class="info-value">

                                <?= e(
                                    $opportunity[
                                        "deadline"
                                    ]
                                ) ?>

                            </span>


                        </div>


                        <div class="info-box">


                            <span class="info-label">

                                Published

                            </span>


                            <span class="info-value">

                                <?= e(
                                    $opportunity[
                                        "created_at"
                                    ]
                                ) ?>

                            </span>


                        </div>


                    </div>


                </article>


            </div>


        </section>


    </main>


</div>


</body>

</html>