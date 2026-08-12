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
| GET MY PROJECTS
|--------------------------------------------------------------------------
|
| created_by references users.user_id
|
*/

$sql = "

    SELECT

        project_id,
        created_by,
        title,
        category,
        description,
        required_skills,
        start_date,
        end_date,
        status,
        approval_status,
        created_at

    FROM projects

    WHERE created_by = ?

    ORDER BY created_at DESC

";


$stmt = $conn->prepare($sql);


if (!$stmt) {

    die(
        "Database error: "
        . $conn->error
    );

}


$stmt->bind_param(
    "i",
    $user_id
);


$stmt->execute();


$projects =
    $stmt->get_result();

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

        My Projects |

        <?= e(SITE_NAME) ?>

    </title>


    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >


    <style>

        .projects-wrapper {

            max-width: 1150px;

            margin: 35px auto;

            padding: 20px;

        }


        .projects-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;

            margin-bottom: 25px;

        }


        .projects-header h1 {

            margin: 0 0 6px;

            color: #4a2c1d;

        }


        .projects-header p {

            margin: 0;

            color: #777;

        }


        .add-project-button {

            display: inline-block;

            padding: 11px 18px;

            border-radius: 8px;

            background: #7a4b2a;

            color: #ffffff;

            text-decoration: none;

            font-weight: 600;

            white-space: nowrap;

        }


        .add-project-button:hover {

            background: #5f3921;

        }


        .projects-grid {

            display: grid;

            grid-template-columns:
                repeat(3, minmax(0, 1fr));

            gap: 20px;

        }


        .project-card {

            background: #ffffff;

            border: 1px solid #eeeeee;

            border-radius: 14px;

            padding: 22px;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.05);

        }


        .project-category {

            display: inline-block;

            padding: 6px 10px;

            border-radius: 20px;

            background: #f8f5f2;

            color: #7a4b2a;

            font-size: 12px;

            font-weight: 700;

            margin-bottom: 12px;

        }


        .project-card h2 {

            margin: 0 0 10px;

            color: #4a2c1d;

            font-size: 20px;

        }


        .project-description {

            color: #666;

            line-height: 1.6;

            margin-bottom: 15px;

        }


        .project-skills {

            color: #666;

            font-size: 14px;

            line-height: 1.5;

            margin-bottom: 15px;

        }


        .project-dates {

            color: #777;

            font-size: 13px;

            margin-bottom: 15px;

            line-height: 1.6;

        }


        .status-row {

            display: flex;

            flex-wrap: wrap;

            gap: 7px;

            margin-bottom: 15px;

        }


        .project-status,
        .approval-status {

            display: inline-block;

            padding: 6px 10px;

            border-radius: 20px;

            font-size: 11px;

            font-weight: 700;

        }


        .status-active {

            background: #d4edda;

            color: #155724;

        }


        .status-completed {

            background: #e2e3e5;

            color: #383d41;

        }


        .status-draft {

            background: #e2e3e5;

            color: #383d41;

        }


        .approval-pending {

            background: #fff3cd;

            color: #856404;

        }


        .approval-approved {

            background: #d4edda;

            color: #155724;

        }


        .approval-rejected {

            background: #f8d7da;

            color: #721c24;

        }


        .view-project-button {

            display: block;

            text-align: center;

            padding: 10px;

            border-radius: 8px;

            background: #7a4b2a;

            color: #ffffff;

            text-decoration: none;

            font-weight: 600;

        }


        .view-project-button:hover {

            background: #5f3921;

        }


        .empty-projects {

            grid-column: 1 / -1;

            text-align: center;

            background: #ffffff;

            border: 1px solid #eeeeee;

            border-radius: 14px;

            padding: 60px 20px;

            color: #777;

        }


        .empty-projects h3 {

            color: #4a2c1d;

            margin-bottom: 8px;

        }


        @media (max-width: 900px) {

            .projects-grid {

                grid-template-columns:
                    1fr 1fr;

            }

        }


        @media (max-width: 650px) {

            .projects-header {

                flex-direction: column;

                align-items: flex-start;

            }


            .projects-grid {

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


            <a href="../mentorship/index.php">
                Mentorship
            </a>


            <div class="nav-section">
                ACTIVITIES
            </div>


            <a
                href="index.php"
                class="active"
            >
                Projects
            </a>


            <a href="../events/events.php">
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
                    Projects & Contributions
                </h1>

                <p>
                    Share your projects, achievements, and contributions with the alumni community.
                </p>

            </div>


        </header>


        <section class="dashboard-content">


            <div class="projects-wrapper">


                <div class="projects-header">


                    <div>

                        <h1>
                            My Projects
                        </h1>

                        <p>
                            Projects created by your account.
                        </p>

                    </div>


                    <a
                        href="create.php"
                        class="add-project-button"
                    >
                        + Add Project
                    </a>


                </div>


                <div class="projects-grid">


                    <?php if (
                        $projects->num_rows === 0
                    ): ?>


                        <div class="empty-projects">


                            <h3>
                                No Projects Yet
                            </h3>


                            <p>
                                You haven't submitted any projects or contributions yet.
                            </p>


                            <a
                                href="create.php"
                                class="add-project-button"
                            >
                                Add Your First Project
                            </a>


                        </div>


                    <?php else: ?>


                        <?php while (
                            $project =
                                $projects->fetch_assoc()
                        ): ?>


                            <?php

                            $approval =
                                strtolower(
                                    trim(
                                        $project[
                                            "approval_status"
                                        ]
                                    )
                                );


                            $status =
                                strtolower(
                                    trim(
                                        $project[
                                            "status"
                                        ]
                                    )
                                );


                            $approvalClass =
                                "approval-pending";


                            if (
                                $approval === "approved"
                            ) {

                                $approvalClass =
                                    "approval-approved";

                            }

                            elseif (
                                $approval === "rejected"
                            ) {

                                $approvalClass =
                                    "approval-rejected";

                            }


                            $statusClass =
                                "status-draft";


                            if (
                                $status === "active"
                            ) {

                                $statusClass =
                                    "status-active";

                            }

                            elseif (
                                $status === "completed"
                            ) {

                                $statusClass =
                                    "status-completed";
                                  }

                            ?>


                            <article class="project-card">


                                <span class="project-category">

                                    <?= e(
                                        $project[
                                            "category"
                                        ]
                                    ) ?>

                                </span>


                                <h2>

                                    <?= e(
                                        $project[
                                            "title"
                                        ]
                                    ) ?>

                                </h2>


                                <div class="project-description">

                                    <?= e(
                                        mb_strimwidth(
                                            strip_tags(
                                                $project[
                                                    "description"
                                                ]
                                            ),
                                            0,
                                            170,
                                            "..."
                                        )
                                    ) ?>

                                </div>


                                <?php if (
                                    trim(
                                        $project[
                                            "required_skills"
                                        ]
                                    ) !== ""
                                ): ?>


                                    <div class="project-skills">

                                        <strong>
                                            Skills:
                                        </strong>

                                        <?= e(
                                            $project[
                                                "required_skills"
                                            ]
                                        ) ?>

                                    </div>


                                <?php endif; ?>


                                <div class="project-dates">

                                    <strong>
                                        Start:
                                    </strong>

                                    <?= e(
                                        $project[
                                            "start_date"
                                        ]
                                    ) ?>


                                    <br>


                                    <strong>
                                        End:
                                    </strong>

                                    <?= e(
                                        $project[
                                            "end_date"
                                        ]
                                    ) ?>

                                </div>


                                <div class="status-row">


                                    <span
                                        class="project-status
                                        <?= e(
                                            $statusClass
                                        ) ?>"
                                    >

                                        Status:

                                        <?= e(
                                            $project[
                                                "status"
                                            ]
                                        ) ?>

                                    </span>
                                  <span
                                        class="approval-status
                                        <?= e(
                                            $approvalClass
                                        ) ?>"
                                    >

                                        Approval:

                                        <?= e(
                                            $project[
                                                "approval_status"
                                            ]
                                        ) ?>

                                    </span>


                                </div>


                                <a
                                    href="view.php?id=<?= (int) $project["project_id"] ?>"
                                    class="view-project-button"
                                >
                                    View Project
                                </a>


                            </article>


                        <?php endwhile; ?>


                    <?php endif; ?>


                </div>


            </div>


        </section>


    </main>


</div>


</body>

</html>    