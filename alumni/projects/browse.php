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


/*
|--------------------------------------------------------------------------
| GET APPROVED PROJECTS
|--------------------------------------------------------------------------
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
        p.created_at,

        u.email

    FROM projects p

    LEFT JOIN users u
        ON p.created_by = u.user_id

    WHERE LOWER(TRIM(p.approval_status)) = 'approved'

    ORDER BY p.created_at DESC

";


$result =
    $conn->query($sql);


if (!$result) {

    die(
        "Database error: "
        . $conn->error
    );

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

        Projects & Contributions |

        <?= e(SITE_NAME) ?>

    </title>


    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >


    <style>

        .projects-browse {

            padding: 30px;

        }


        .page-heading {

            margin-bottom: 25px;

        }


        .page-heading h1 {

            margin: 0 0 7px;

            color: #4a2c1d;

        }


        .page-heading p {

            margin: 0;

            color: #777;

        }


        .browse-grid {

            display: grid;

            grid-template-columns:
                repeat(3, minmax(0, 1fr));

            gap: 20px;

        }


        .browse-card {

            background: #ffffff;

            border: 1px solid #eeeeee;

            border-radius: 14px;

            padding: 22px;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.05);

            display: flex;

            flex-direction: column;

        }


        .category-badge {

            display: inline-block;

            align-self: flex-start;

            padding: 6px 10px;

            border-radius: 20px;

            background: #f8f5f2;

            color: #7a4b2a;

            font-size: 12px;

            font-weight: 700;

            margin-bottom: 12px;

        }


        .browse-card h2 {

            margin: 0 0 10px;

            color: #4a2c1d;

            font-size: 20px;

        }


        .description {

            color: #666;

            line-height: 1.6;

            margin-bottom: 15px;

            flex-grow: 1;

        }


        .skills {

            margin-bottom: 15px;

            color: #666;

            font-size: 13px;

            line-height: 1.5;

        }


        .dates {

            margin-bottom: 18px;

            color: #777;

            font-size: 13px;

            line-height: 1.6;

        }


        .view-button {

            display: block;

            text-align: center;

            padding: 10px;

            border-radius: 8px;

            background: #7a4b2a;

            color: #ffffff;

            text-decoration: none;

            font-weight: 600;

        }


        .view-button:hover {

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


        @media (max-width: 950px) {

            .browse-grid {

                grid-template-columns:
                    1fr 1fr;

            }

        }


        @media (max-width: 650px) {

            .browse-grid {

                grid-template-columns: 1fr;

            }


            .projects-browse {

                padding: 15px;

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
                href="browse.php"
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
                    Discover projects and contributions shared by fellow alumni.
                </p>

            </div>


        </header>


        <section class="dashboard-content">


            <div class="projects-browse">


                <div class="page-heading">


                    <h1>
                        Alumni Projects
                    </h1>


                    <p>
                        Explore approved projects and contributions from the alumni community.
                    </p>


                </div>


                <div class="browse-grid">


                    <?php if (
                        $result->num_rows === 0
                    ): ?>


                        <div class="empty-projects">


                            <h3>
                                No Approved Projects
                            </h3>


                            <p>
                                There are currently no approved projects available.
                            </p>


                        </div>


                    <?php else: ?>


                        <?php while (
                            $project =
                                $result->fetch_assoc()
                        ): ?>


                            <article class="browse-card">


                                <span class="category-badge">
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


                                <div class="description">

                                    <?= e(
                                        mb_strimwidth(
                                            strip_tags(
                                                $project[
                                                    "description"
                                                ]
                                            ),
                                            0,
                                            180,
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


                                    <div class="skills">


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


                                <div class="dates">


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


                                <a
                                    href="view.php?id=<?= (int) $project["project_id"] ?>"
                                    class="view-button"
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
