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
| GET PROJECT ID
|--------------------------------------------------------------------------
*/

$project_id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);


if (!$project_id) {

    die("Invalid project ID.");

}


/*
|--------------------------------------------------------------------------
| GET APPROVED PROJECT
|--------------------------------------------------------------------------
|
| Only approved projects can be viewed from the public
| alumni projects browsing area.
|
*/

$sql = "

    SELECT

        p.project_id,
        p.created_by,
        p.title,
        p.category,
        p.description,
        p.required_skills,
        p.start_date,
        p.end_date,
        p.status,
        p.approval_status,
        p.created_at,

        u.email AS creator_email

    FROM projects p

    LEFT JOIN users u
        ON p.created_by = u.user_id

    WHERE p.project_id = ?
AND (
      LOWER(TRIM(p.approval_status)) = 'approved'
      or p.created_by = ?
      )

    LIMIT 1

";


$stmt = $conn->prepare($sql);


if (!$stmt) {

    die(
        "Database error: "
        . $conn->error
    );

}


$stmt->bind_param(
    "ii",
    $project_id,
    $user_id
);


$stmt->execute();


$result =
    $stmt->get_result();


$project =
    $result->fetch_assoc();


$stmt->close();


if (!$project) {

    die("Project not found or it has not been approved.");

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

        <?= e($project["title"]) ?>

        |

        <?= e(SITE_NAME) ?>

    </title>


    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >


    <style>

        .project-view-wrapper {

            max-width: 950px;

            margin: 35px auto;

            padding: 20px;

        }


        .project-view-card {

            background: #ffffff;

            border: 1px solid #eeeeee;

            border-radius: 15px;

            padding: 30px;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.05);

        }


        .back-link {

            display: inline-block;

            margin-bottom: 20px;

            color: #7a4b2a;

            text-decoration: none;

            font-weight: 600;

        }


        .back-link:hover {

            text-decoration: underline;

        }


        .project-header {

            border-bottom: 1px solid #eeeeee;

            padding-bottom: 20px;

            margin-bottom: 25px;

        }


        .category-badge {

            display: inline-block;

            padding: 7px 12px;

            border-radius: 20px;

            background: #f8f5f2;

            color: #7a4b2a;

            font-size: 12px;

            font-weight: 700;

            margin-bottom: 12px;

        }


        .project-header h1 {

            margin: 0 0 10px;

            color: #4a2c1d;

            font-size: 30px;

        }


        .project-status {

            display: inline-block;

            padding: 6px 11px;

            border-radius: 20px;

            background: #d4edda;

            color: #155724;

            font-size: 12px;

            font-weight: 700;

        }


        .project-section {

            margin-bottom: 28px;

        }


        .project-section h2 {

            margin-bottom: 10px;

            color: #4a2c1d;
          font-size: 20px;

        }


        .project-section p {

            margin: 0;

            color: #555555;

            line-height: 1.8;

            white-space: pre-line;

        }


        .project-info-grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 15px;

            margin: 25px 0;

        }


        .info-box {

            background: #f8f5f2;

            border-radius: 10px;

            padding: 16px;

        }


        .info-box span {

            display: block;

            color: #777777;

            font-size: 12px;

            margin-bottom: 6px;

        }


        .info-box strong {

            color: #4a2c1d;

        }


        .creator-box {

            border-top: 1px solid #eeeeee;

            padding-top: 20px;

            margin-top: 25px;

            color: #666666;

            font-size: 14px;

        }


        .creator-box strong {

            color: #4a2c1d;

        }


        .bottom-actions {

            margin-top: 30px;

            padding-top: 20px;

            border-top: 1px solid #eeeeee;

        }


        .back-button {

            display: inline-block;

            padding: 11px 18px;

            border-radius: 8px;

            background: #7a4b2a;

            color: #ffffff;

            text-decoration: none;

            font-weight: 600;

        }


        .back-button:hover {

            background: #5f3921;

        }


        @media (max-width: 650px) {

            .project-view-wrapper {

                padding: 12px;

            }


            .project-view-card {

                padding: 20px;

            }


            .project-header h1 {

                font-size: 24px;

            }


            .project-info-grid {

                grid-template-columns: 1fr;

            }

        }

    </style>

</head>


<body class="admin-body">


<div class="admin-layout">


   <?php
$currentPage = "projects";
require_once __DIR__ . "/../includes/sidebar.php";
?>


    <!-- MAIN -->

    <main class="admin-main">


        <header class="admin-topbar">


            <div>

                <h1>
                    Project Details
                </h1>

                <p>
                    View the full details of this alumni project.
                </p>

            </div>


        </header>
          <section class="dashboard-content">


            <div class="project-view-wrapper">


                <a
                    href="index.php"
                    class="back-link"
                >
                    ← Back to Projects
                </a>


                <div class="project-view-card">


                    <div class="project-header">


                        <span class="category-badge">

                            <?= e(
                                $project["category"]
                            ) ?>

                        </span>


                        <h1>

                            <?= e(
                                $project["title"]
                            ) ?>

                        </h1>


                        <span class="project-status">

                            <?= e(
                                $project["status"]
                            ) ?>

                        </span>


                    </div>


                    <!-- DESCRIPTION -->


                    <div class="project-section">


                        <h2>
                            About This Project
                        </h2>


                        <p>

                            <?= e(
                                $project["description"]
                            ) ?>

                        </p>


                    </div>


                    <!-- SKILLS -->


                    <div class="project-section">


                        <h2>
                            Required Skills
                        </h2>


                        <p>

                            <?= e(
                                $project["required_skills"]
                            ) ?>

                        </p>


                    </div>


                    <!-- INFORMATION -->


                    <div class="project-info-grid">


                        <div class="info-box">


                            <span>
                                Start Date
                            </span>


                            <strong>

                                <?= e(
                                    $project["start_date"]
                                ) ?>

                            </strong>


                        </div>


                        <div class="info-box">


                            <span>
                                End Date
                            </span>


                            <strong>

                                <?= e(
                                    $project["end_date"]
                                ) ?>

                            </strong>


                        </div>


                        <div class="info-box">


                            <span>
                                Project Status
                            </span>


                            <strong>

                                <?= e(
                                    $project["status"]
                                ) ?>

                            </strong>


                        </div>


                        <div class="info-box">


                            <span>
                                Approval
                            </span>


                            <strong>
                                Approved
                            </strong>


                        </div>


                    </div>


                    <!-- CREATOR -->


                    <div class="creator-box">


                        <strong>
                            Submitted by:
                        </strong>


                        User #<?= (int)
                            $project["created_by"]
                        ?>


                        <?php if (
                            !empty(
                                $project[
                                    "creator_email"
                                ]
                            )
                        ): ?>
                        <br>


                            <?= e(
                                $project[
                                    "creator_email"
                                ]
                            ) ?>


                        <?php endif; ?>


                    </div>


                    <!-- ACTION -->


                    <div class="bottom-actions">


                        <a
                            href="index.php"
                            class="back-button"
                        >
                            Back to Projects
                        </a>


                    </div>


                </div>


            </div>


        </section>


    </main>


</div>


</body>


</html>