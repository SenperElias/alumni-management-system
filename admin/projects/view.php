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


$admin_id = (int) $_SESSION["user_id"];


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
| PROCESS APPROVAL / REJECTION
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $action = $_POST["action"] ?? "";


    if (
        $action === "approve"
        || $action === "reject"
    ) {


        $new_status =
            $action === "approve"
                ? "Approved"
                : "Rejected";


        $sql = "

            UPDATE projects

            SET

                approval_status = ?,
                reviewed_by = ?,
                reviewed_at = NOW(),
                updated_at = NOW()

            WHERE project_id = ?

        ";


        $stmt =
            $conn->prepare($sql);


        if (!$stmt) {

            die(
                "Database error: "
                . $conn->error
            );

        }


        $stmt->bind_param(

            "sii",

            $new_status,
            $admin_id,
            $project_id

        );


        if ($stmt->execute()) {


            $stmt->close();


            header(
                "Location: view.php?id="
                . $project_id
                . "&success="
                . urlencode(
                    "Project "
                    . strtolower(
                        $new_status
                    )
                    . " successfully."
                )
            );


            exit;


        }


        $error =
            "Unable to update project: "
            . $stmt->error;


        $stmt->close();

    }

}


/*
|--------------------------------------------------------------------------
| GET PROJECT
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
        p.updated_at,

        u.email AS creator_email

    FROM projects p

    LEFT JOIN users u
        ON p.created_by = u.user_id

    WHERE p.project_id = ?

    LIMIT 1

";


$stmt =
    $conn->prepare($sql);


if (!$stmt) {

    die(
        "Database error: "
        . $conn->error
    );

}


$stmt->bind_param(
    "i",
    $project_id
);


$stmt->execute();


$result =
    $stmt->get_result();


$project =
    $result->fetch_assoc();


$stmt->close();


if (!$project) {

    die("Project not found.");

}


$success =
    $_GET["success"] ?? "";


$error =
    $error ?? "";


$approval =
    strtolower(
        trim(
            $project[
                "approval_status"
            ]
        )
    );


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

        View Project |

        <?= e(SITE_NAME) ?>

    </title>


    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >


    <style>

        .project-view-wrapper {
            max-width: 1000px;

            margin: 35px auto;

            padding: 20px;

        }


        .project-view-card {

            background: #ffffff;

            border: 1px solid #eeeeee;

            border-radius: 14px;

            padding: 30px;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.05);

        }


        .project-view-header {

            display: flex;

            justify-content: space-between;

            align-items: flex-start;

            gap: 20px;

            margin-bottom: 25px;

        }


        .project-view-header h1 {

            margin: 0 0 8px;

            color: #4a2c1d;

        }


        .project-category {

            color: #7a4b2a;

            font-weight: 600;

        }


        .status-badge {

            display: inline-block;

            padding: 7px 12px;

            border-radius: 20px;

            font-size: 12px;

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


        .detail-section {

            margin-bottom: 25px;

        }


        .detail-section h3 {

            margin-bottom: 10px;

            color: #4a2c1d;

        }


        .detail-section p {

            margin: 0;

            color: #555;

            line-height: 1.7;

            white-space: pre-line;

        }


        .details-grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 15px;

            margin: 25px 0;

        }


        .detail-box {

            padding: 15px;

            border-radius: 9px;

            background: #f8f5f2;

        }


        .detail-box span {

            display: block;

            font-size: 12px;

            color: #777;

            margin-bottom: 5px;

        }


        .detail-box strong {

            color: #4a2c1d;

        }


        .approval-actions {

            border-top: 1px solid #eeeeee;

            padding-top: 25px;

            margin-top: 25px;

        }


        .approval-actions h3 {

            color: #4a2c1d;

            margin-bottom: 8px;

        }


        .approval-actions p {

            color: #777;

            margin-bottom: 18px;

        }


        .action-buttons {

            display: flex;

            gap: 10px;

            flex-wrap: wrap;

        }


        .approve-button,

        .reject-button,

        .back-button {

            border: none;

            padding: 11px 18px;

            border-radius: 8px;

            font-weight: 600;

            cursor: pointer;

            text-decoration: none;

            display: inline-block;

        }


        .approve-button {

            background: #2e7d32;

            color: #ffffff;

        }


        .approve-button:hover {

            background: #256628;

        }


        .reject-button {

            background: #c62828;

            color: #ffffff;

        }


        .reject-button:hover {

            background: #a51f1f;

        }


        .back-button {

            background: #eeeeee;

            color: #444444;

        }


        .back-button:hover {

            background: #dddddd;

        }


        .alert {

            padding: 13px 15px;

            border-radius: 8px;

            margin-bottom: 20px;

        }


        .alert-success {

            background: #e8f5e9;

            color: #2e7d32;

            border: 1px solid #c8e6c9;

        }


        .alert-error {

            background: #ffebee;

            color: #b71c1c;

            border: 1px solid #ffcdd2;

        }


        @media (max-width: 650px) {

            .project-view-header {

                flex-direction: column;

            }


            .details-grid {
                grid-template-columns: 1fr;

            }

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
                    Admin Portal
                </small>

            </div>


        </div>


        <nav class="admin-nav">


            <a href="../dashboard.php">
                Dashboard
            </a>


            <div class="nav-section">
                MANAGEMENT
            </div>


            <a href="../alumni/index.php">
                Alumni
            </a>


            <a href="../employment/index.php">
                Employment
            </a>


            <a href="../events/index.php">
                Events
            </a>


            <a href="../mentors/index.php">
                Mentors
            </a>


            <a href="../opportunities/index.php">
                Opportunities
            </a>


            <a
                href="index.php"
                class="active"
            >
                Projects
            </a>


            <div class="nav-section">
                SYSTEM
            </div>


            <a href="#">
                Reports
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
                    Project Review
                </h1>

                <p>
                    Review the submitted project before approving or rejecting it.
                </p>

            </div>


        </header>


        <section class="dashboard-content">


            <div class="project-view-wrapper">


                <div class="project-view-card">


                    <?php if (
                        $success !== ""
                    ): ?>


                        <div class="alert alert-success">

                            <?= e($success) ?>

                        </div>


                    <?php endif; ?>


                    <?php if (
                        $error !== ""
                    ): ?>


                        <div class="alert alert-error">

                            <?= e($error) ?>

                        </div>


                    <?php endif; ?>


                    <div class="project-view-header">


                        <div>


                            <h1>

                                <?= e(
                                    $project[
                                        "title"
                                    ]
                                ) ?>

                            </h1>


                            <div class="project-category">

                                <?= e(
                                    $project[
                                        "category"
                                    ]
                                ) ?>

                            </div>


                        </div>


                        <span
                            class="status-badge
                            <?= e(
                                $approval
                            ) ?>"
                        >

                            <?= e(
                                $project[
                                    "approval_status"
                                ]
                            ) ?>

                        </span>


                    </div>


                    <!-- PROJECT DETAILS -->


                    <div class="detail-section">
                        <h3>
                            Description
                        </h3>


                        <p>

                            <?= e(
                                $project[
                                    "description"
                                ]
                            ) ?>

                        </p>


                    </div>


                    <div class="detail-section">


                        <h3>
                            Required Skills
                        </h3>


                        <p>

                            <?= e(
                                $project[
                                    "required_skills"
                                ]
                            ) ?>

                        </p>


                    </div>


                    <!-- INFORMATION GRID -->


                    <div class="details-grid">


                        <div class="detail-box">


                            <span>
                                Created By
                            </span>


                            <strong>

                                User #<?= (int)
                                    $project[
                                        "created_by"
                                    ]
                                ?>

                            </strong>


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


                        <div class="detail-box">


                            <span>
                                Project Status
                            </span>


                            <strong>

                                <?= e(
                                    $project[
                                        "status"
                                    ]
                                ) ?>

                            </strong>


                        </div>


                        <div class="detail-box">


                            <span>
                                Start Date
                            </span>


                            <strong>

                                <?= e(
                                    $project[
                                        "start_date"
                                    ]
                                ) ?>

                            </strong>


                        </div>


                        <div class="detail-box">


                            <span>
                                End Date
                            </span>


                            <strong>

                                <?= e(
                                    $project[
                                        "end_date"
                                    ]
                                ) ?>

                            </strong>


                        </div>


                        <div class="detail-box">


                            <span>
                                Submitted
                            </span>


                            <strong>

                                <?= e(
                                    date(
                                        "M d, Y H:i",
                                     strtotime(
                                            $project[
                                                "created_at"
                                            ]
                                        )
                                    )
                                ) ?>

                            </strong>


                        </div>


                        <div class="detail-box">


                            <span>
                                Last Updated
                            </span>


                            <strong>

                                <?= e(
                                    date(
                                        "M d, Y H:i",
                                        strtotime(
                                            $project[
                                                "updated_at"
                                            ]
                                        )
                                    )
                                ) ?>

                            </strong>


                        </div>


                    </div>


                    <!-- APPROVAL ACTIONS -->


                    <div class="approval-actions">


                        <h3>
                            Admin Decision
                        </h3>


                        <?php if (
                            $approval === "pending"
                        ): ?>


                            <p>
                                This project is waiting for your review.
                            </p>


                            <div class="action-buttons">


                                <form
                                    method="POST"
                                    action=""
                                >


                                    <input
                                        type="hidden"
                                        name="action"
                                        value="approve"
                                    >


                                    <button
                                        type="submit"
                                        class="approve-button"
                                        onclick="return confirm('Approve this project?');"
                                    >

                                        Approve Project

                                    </button>


                                </form>


                                <form
                                    method="POST"
                                    action=""
                                >


                                    <input
                                        type="hidden"
                                        name="action"
                                        value="reject"
                                    >


                                    <button
                                        type="submit"
                                        class="reject-button"
                                        onclick="return confirm('Reject this project?');"
                                    >

                                        Reject Project

                                    </button>


                                </form>


                                <a
                                    href="index.php"
                                    class="back-button"
                                >
                                    Back
                                </a>


                            </div>


                        <?php else: ?>


                            <p>

                                This project has already been
                            <strong>
                                    <?= e(
                                        $project[
                                            "approval_status"
                                        ]
                                    ) ?>
                                </strong>.

                            </p>


                            <?php if (
                                !empty(
                                    $project[
                                        "reviewed_at"
                                    ]
                                )
                            ): ?>


                                <p>

                                    Reviewed on:

                                    <?= e(
                                        date(
                                            "M d, Y H:i",
                                            strtotime(
                                                $project[
                                                    "reviewed_at"
                                                ]
                                            )
                                        )
                                    ) ?>

                                </p>


                            <?php endif; ?>


                            <div class="action-buttons">


                                <a
                                    href="index.php"
                                    class="back-button"
                                >
                                    Back to Projects
                                </a>


                            </div>


                        <?php endif; ?>


                    </div>


                </div>


            </div>


        </section>


    </main>


</div>


</body>


</html>
