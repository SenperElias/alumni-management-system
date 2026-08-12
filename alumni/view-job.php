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

$opportunity_id = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;


if ($opportunity_id <= 0) {
    die("Invalid opportunity ID.");
}


/*
|--------------------------------------------------------------------------
| GET APPROVED OPPORTUNITY
|--------------------------------------------------------------------------
|
| IMPORTANT:
| Alumni can only view APPROVED opportunities.
|
*/

$sql = "
    SELECT
        opportunity_id,
        type,
        title,
        company_name,
        description,
        requirements,
        location,
        contact_information,
        deadline,
        status,
        created_at
    FROM opportunities
    WHERE opportunity_id = ?
      AND status = 'Approved'
    LIMIT 1
";


$stmt = $conn->prepare($sql);


if (!$stmt) {
    die("Database error: " . $conn->error);
}


$stmt->bind_param(
    "i",
    $opportunity_id
);


$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows === 0) {
    die("Opportunity not found or it is not available.");
}


$opportunity = $result->fetch_assoc();

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
        <?= e($opportunity["title"]) ?> |
        <?= e(SITE_NAME) ?>
    </title>


    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >


    <style>

        .job-details-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 25px;
        }


        .job-details-header h1 {
            margin: 10px 0;
        }


        .job-company {
            font-size: 17px;
            font-weight: 600;
        }


        .job-type {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            background: #f1e7df;
            color: #7a4b2a;
            font-size: 13px;
            font-weight: 600;
        }


        .approved-label {
            padding: 7px 13px;
            border-radius: 6px;
            background: #e8f5e9;
            color: #27632a;
            font-weight: 600;
            font-size: 13px;
        }


        .job-info-grid {
            display: grid;
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
            gap: 20px;
            margin-top: 25px;
        }


        .job-info-item {
            padding: 18px;
            background: #faf8f6;
            border-radius: 8px;
        }


        .job-info-item span {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            color: #777;
        }


        .job-info-item strong {
            color: #333;
        }


        .job-content {
            line-height: 1.8;
            color: #444;
        }


        .job-content p {
            margin: 0;
        }


        .back-button {
            display: inline-block;
            margin-bottom: 20px;
        }


        @media (max-width: 700px) {

            .job-details-header {
                flex-direction: column;
            }


            .job-info-grid {
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


            <a href="#">
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
                    View complete information about this opportunity.
                </p>

            </div>


        </header>



        <section class="dashboard-content">


            <a
                href="jobs.php"
                class="secondary-button back-button"
            >
                ← Back to Jobs & Internships
            </a>



            <!-- =================================================
                 MAIN JOB INFORMATION
            ================================================== -->

            <div class="dashboard-panel">


                <div class="job-details-header">


                    <div>


                        <span class="job-type">

                            <?= e(
                                $opportunity["type"]
                            ) ?>

                        </span>


                        <h1>

                            <?= e(
                                $opportunity["title"]
                            ) ?>

                        </h1>


                        <div class="job-company">

                            <?= e(
                                $opportunity["company_name"]
                            ) ?>

                        </div>


                    </div>


                    <span class="approved-label">

                        Approved

                    </span>


                </div>



                <!-- =================================================
                     JOB INFORMATION
                ================================================== -->

                <div class="job-info-grid">


                    <div class="job-info-item">

                        <span>
                            Location
                        </span>


                        <strong>

                            <?php
                            if (
                                !empty(
                                    $opportunity["location"]
                                )
                            ) {

                                echo e(
                                    $opportunity["location"]
                                );

                            } else {

                                echo "Not specified";

                            }

                            ?>

                        </strong>

                    </div>



                    <div class="job-info-item">

                        <span>
                            Deadline
                        </span>


                        <strong>

                            <?php

                            $deadline =
                                $opportunity["deadline"]
                                ?? "";


                            if (
                                empty($deadline) ||
                                $deadline === "0000-00-00"
                            ) {

                                echo "Not specified";

                            } else {

                                echo e($deadline);

                            }

                            ?>

                        </strong>

                    </div>



                    <div class="job-info-item">

                        <span>
                            Opportunity Type
                        </span>


                        <strong>

                            <?= e(
                                $opportunity["type"]
                            ) ?>

                        </strong>

                    </div>



                    <div class="job-info-item">

                        <span>
                            Published
                        </span>


                        <strong>

                            <?= e(
                                $opportunity["created_at"]
                            ) ?>

                        </strong>

                    </div>


                </div>


            </div>



            <!-- =================================================
                 DESCRIPTION
            ================================================== -->

            <div class="dashboard-panel">


                <h2>
                    Description
                </h2>


                <div class="job-content">


                    <?php

                    if (
                        !empty(
                            $opportunity["description"]
                        )
                    ) {

                        echo nl2br(
                            e(
                                $opportunity["description"]
                            )
                        );

                    } else {

                        echo "No description provided.";

                    }

                    ?>


                </div>


            </div>



            <!-- =================================================
                 REQUIREMENTS
            ================================================== -->

            <div class="dashboard-panel">


                <h2>
                    Requirements
                </h2>


                <div class="job-content">


                    <?php

                    if (
                        !empty(
                            $opportunity["requirements"]
                        )
                    ) {

                        echo nl2br(
                            e(
                                $opportunity["requirements"]
                            )
                        );

                    } else {

                        echo "No requirements provided.";

                    }

                    ?>


                </div>


            </div>
            <!-- =================================================
                 CONTACT INFORMATION
            ================================================== -->

            <div class="dashboard-panel">


                <h2>
                    Contact Information
                </h2>


                <div class="job-content">


                    <?php

                    if (
                        !empty(
                            $opportunity["contact_information"]
                        )
                    ) {

                        echo nl2br(
                            e(
                                $opportunity["contact_information"]
                            )
                        );

                    } else {

                        echo "No contact information provided.";

                    }

                    ?>


                </div>


            </div>


        </section>


    </main>


</div>


</body>

</html>