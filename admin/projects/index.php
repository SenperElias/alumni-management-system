<?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";


/*
|--------------------------------------------------------------------------
| ADMIN ACCESS
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {

    header("Location: ../../auth/login.php");
    exit;

}

if ($_SESSION["role"] !== "admin") {

    header("Location: ../../index.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| GET PROJECTS
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
        p.approval_status,
        p.reviewed_by,
        p.reviewed_at,
        p.created_at,

        u.user_id,
        u.email

    FROM projects p

    LEFT JOIN users u
        ON p.created_by = u.user_id

    ORDER BY p.created_at DESC

";


$result = $conn->query($sql);


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
        Projects Management |
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

    <style>

        .projects-management {

            padding: 30px;

        }


        .page-header {

            margin-bottom: 25px;

        }


        .page-header h1 {

            margin-bottom: 6px;

            color: #4a2c1d;

        }


        .page-header p {

            margin: 0;

            color: #777;

        }


        .table-wrapper {

            background: #ffffff;

            border-radius: 12px;

            border: 1px solid #eeeeee;

            overflow-x: auto;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.05);

        }


        .projects-table {

            width: 100%;

            border-collapse: collapse;

            min-width: 950px;

        }


        .projects-table th {

            background: #f8f5f2;

            color: #4a2c1d;

            text-align: left;

            padding: 14px;

            font-size: 13px;

            white-space: nowrap;

        }


        .projects-table td {

            padding: 14px;

            border-top: 1px solid #eeeeee;

            color: #555;

            vertical-align: middle;

        }


        .projects-table tr:hover {

            background: #fcfbfa;

        }


        .project-title {

            font-weight: 700;

            color: #4a2c1d;

        }


        .project-category {

            color: #7a4b2a;

            font-size: 13px;

        }


        .status-badge {

            display: inline-block;

            padding: 6px 10px;

            border-radius: 20px;

            font-size: 11px;

            font-weight: 700;

            white-space: nowrap;

        }


        .pending {

            background: #fff3cd;

            color: #856404;

        }


        .approved {

            background: #d4edda;

            color: #155724;

        }


        .rejected {

            background: #f8d7da;

            color: #721c24;

        }


        .action-button {

            display: inline-block;

            padding: 8px 12px;

            border-radius: 7px;

            background: #7a4b2a;

            color: #ffffff;

            text-decoration: none;

            font-size: 12px;

            font-weight: 600;

        }


        .action-button:hover {

            background: #5f3921;

        }


        .empty-projects {

            text-align: center;

            padding: 50px 20px;

            color: #777;

        }


    </style>
    </head>


<body class="admin-body">


<div class="admin-layout">


    <!-- SIDEBAR -->

      <?php require_once __DIR__ ."/../includes/sidebar.php"; ?>




    <!-- MAIN CONTENT -->

    <main class="admin-main">


        <header class="admin-topbar">

            <div>

                <h1>
                    Projects Management
                </h1>

                <p>
                    Review and manage alumni project submissions.
                </p>

            </div>

        </header>


        <section class="dashboard-content">


            <div class="projects-management">


                <div class="page-header">

                    <h1>
                        Submitted Projects
                    </h1>

                    <p>
                        Review projects submitted by alumni.
                    </p>

                </div>


                <div class="table-wrapper">


                    <?php if (
                        $result->num_rows === 0
                    ): ?>


                        <div class="empty-projects">

                            <h3>
                                No Projects Found
                            </h3>

                            <p>
                                There are currently no project submissions.
                            </p>

                        </div>


                    <?php else: ?>


                        <table class="projects-table">


                            <thead>

                                <tr>

                                    <th>
                                        ID
                                    </th>

                                    <th>
                                        Project
                                    </th>

                                    <th>
                                        Category
                                    </th>

                                    <th>
                                        Created By
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                    <th>
                                        Approval
                                    </th>

                                    <th>
                                        Submitted
                                    </th>
                                    <th>
                                        Action
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                                <?php while (
                                    $project =
                                        $result->fetch_assoc()
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


                                    $approvalClass =
                                        "pending";


                                    if (
                                        $approval === "approved"
                                    ) {

                                        $approvalClass =
                                            "approved";

                                    }

                                    elseif (
                                        $approval === "rejected"
                                    ) {

                                        $approvalClass =
                                            "rejected";

                                    }

                                    ?>


                                    <tr>


                                        <td>

                                            #<?= (int)
                                                $project[
                                                    "project_id"
                                                ]
                                            ?>

                                        </td>


                                        <td>

                                            <div class="project-title">

                                                <?= e(
                                                    $project[
                                                        "title"
                                                    ]
                                                ) ?>

                                            </div>

                                        </td>


                                        <td>

                                            <span class="project-category">

                                                <?= e(
                                                    $project[
                                                        "category"
                                                    ]
                                                ) ?>

                                            </span>

                                        </td>


                                        <td>

                                            User #<?= (int)
                                                $project[
                                                    "created_by"
                                                ]
                                            ?>


                                            <?php if (
                                                !empty(
                                                    $project[
                                                        "email"
                                                    ]
                                                )
                                            ): ?>

                                                <br>

                                                <small>
                                                   <?= e(
                                                        $project[
                                                            "email"
                                                        ]
                                                    ) ?>

                                                </small>

                                            <?php endif; ?>


                                        </td>


                                        <td>

                                            <?= e(
                                                $project[
                                                    "status"
                                                ]
                                            ) ?>

                                        </td>


                                        <td>


                                            <span
                                                class="status-badge
                                                <?= e(
                                                    $approvalClass
                                                ) ?>"
                                            >

                                                <?= e(
                                                    $project[
                                                        "approval_status"
                                                    ]
                                                ) ?>

                                            </span>


                                        </td>


                                        <td>

                                            <?= e(
                                                date(
                                                    "M d, Y",
                                                    strtotime(
                                                        $project[
                                                            "created_at"
                                                        ]
                                                    )
                                                )
                                            ) ?>

                                        </td>


                                        <td>

                                            <a
                                                href="view.php?id=<?= (int) $project["project_id"] ?>"
                                                class="action-button"
                                            >
                                                View
                                            </a>

                                        </td>


                                    </tr>


                                <?php endwhile; ?>


                            </tbody>


                        </table>


                    <?php endif; ?>


                </div>


            </div>


        </section>


    </main>


</div>


</body>


</html> 