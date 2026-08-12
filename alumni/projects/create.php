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

$error = "";

$success = "";


/*
|--------------------------------------------------------------------------
| FORM VALUES
|--------------------------------------------------------------------------
*/

$title = "";

$category = "";

$description = "";

$required_skills = "";

$start_date = "";

$end_date = "";

$status = "Draft";


/*
|--------------------------------------------------------------------------
| CREATE PROJECT
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $title = trim(
        $_POST["title"] ?? ""
    );


    $category = trim(
        $_POST["category"] ?? ""
    );


    $description = trim(
        $_POST["description"] ?? ""
    );


    $required_skills = trim(
        $_POST["required_skills"] ?? ""
    );


    $start_date = trim(
        $_POST["start_date"] ?? ""
    );


    $end_date = trim(
        $_POST["end_date"] ?? ""
    );


    $status = trim(
        $_POST["status"] ?? "Draft"
    );


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */


    if (
        $title === ""
        || $category === ""
        || $description === ""
        || $required_skills === ""
        || $start_date === ""
        || $end_date === ""
    ) {

        $error =
            "Please fill in all required fields.";

    }


    /*
    |--------------------------------------------------------------------------
    | STATUS VALIDATION
    |--------------------------------------------------------------------------
    */

    elseif (
        !in_array(
            $status,
            [
                "Draft",
                "Active",
                "Completed"
            ],
            true
        )
    ) {

        $error =
            "Invalid project status.";

    }


    /*
    |--------------------------------------------------------------------------
    | DATE VALIDATION
    |--------------------------------------------------------------------------
    */

    elseif (
        $end_date < $start_date
    ) {

        $error =
            "End date cannot be before the start date.";

    }


    /*
    |--------------------------------------------------------------------------
    | INSERT PROJECT
    |--------------------------------------------------------------------------
    */

    else {


        /*
        |--------------------------------------------------------------------------
        | NEW PROJECTS START AS PENDING
        |--------------------------------------------------------------------------
        */

        $approval_status = "Pending";


        $sql = "

            INSERT INTO projects (

                created_by,
                title,
                category,
                description,
                required_skills,
                start_date,
                end_date,
                status,
                approval_status,
                created_at,
                updated_at

            )

            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())

        ";


        $stmt =
            $conn->prepare($sql);


        if (!$stmt) {

            $error =
                "Database error: "
                . $conn->error;

        } else {


            $stmt->bind_param(

                "issssssss",
                $user_id,
                $title,
                $category,
                $description,
                $required_skills,
                $start_date,
                $end_date,
                $status,
                $approval_status

            );


            if ($stmt->execute()) {


                $success =
                    "Project submitted successfully and is waiting for admin approval.";


                /*
                |--------------------------------------------------------------------------
                | CLEAR FORM
                |--------------------------------------------------------------------------
                */

                $title = "";

                $category = "";

                $description = "";

                $required_skills = "";

                $start_date = "";

                $end_date = "";

                $status = "Draft";


            } else {

                $error =
                    "Failed to create project: "
                    . $stmt->error;

            }


            $stmt->close();

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

        Add Project |

        <?= e(SITE_NAME) ?>

    </title>


    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >


    <style>

        .project-form-wrapper {

            max-width: 900px;

            margin: 35px auto;

            padding: 20px;

        }


        .project-form-card {

            background: #ffffff;

            border: 1px solid #eeeeee;

            border-radius: 14px;

            padding: 30px;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.05);

        }


        .project-form-card h1 {

            margin-top: 0;

            color: #4a2c1d;

        }


        .project-form-card > p {

            color: #777777;

            line-height: 1.6;

        }


        .form-group {

            margin-bottom: 20px;

        }


        .form-group label {

            display: block;

            margin-bottom: 7px;

            color: #4a2c1d;

            font-weight: 600;

            font-size: 14px;

        }


        .required {

            color: #b3261e;

        }


        .form-group input,

        .form-group select,

        .form-group textarea {

            width: 100%;

            box-sizing: border-box;

            padding: 12px 13px;

            border: 1px solid #dddddd;

            border-radius: 8px;

            font-family: inherit;

            font-size: 14px;

        }


        .form-group textarea {

            min-height: 170px;

            resize: vertical;

            line-height: 1.6;

        }


        .form-group input:focus,

        .form-group select:focus,

        .form-group textarea:focus {

            outline: none;

            border-color: #8b5e3c;

        }


        .help-text {

            display: block;

            margin-top: 6px;

            font-size: 12px;

            color: #888888;

        }


        .form-row {

            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 18px;

        }


        .alert {

            padding: 13px 15px;

            border-radius: 8px;

            margin-bottom: 20px;

        }


        .alert-error {

            background: #ffebee;

            color: #b71c1c;

            border: 1px solid #ffcdd2;

        }


        .alert-success {

            background: #e8f5e9;

            color: #2e7d32;

            border: 1px solid #c8e6c9;

        }


        .approval-info {

            margin: 20px 0;

            padding: 15px;

            border-radius: 9px;

            background: #f8f5f2;

            color: #6b5140;

            line-height: 1.6;

            font-size: 14px;

        }


        .form-actions {

            display: flex;

            gap: 10px;
            margin-top: 25px;

        }


        .submit-button {

            border: none;

            padding: 12px 20px;

            border-radius: 8px;

            background: #7a4b2a;

            color: #ffffff;

            font-weight: 600;

            cursor: pointer;

        }


        .submit-button:hover {

            background: #5f3921;

        }


        .cancel-button {

            display: inline-block;

            padding: 12px 20px;

            border-radius: 8px;

            border: 1px solid #8b5e3c;

            color: #8b5e3c;

            text-decoration: none;

            font-weight: 600;

        }


        .cancel-button:hover {

            background: #8b5e3c;

            color: #ffffff;

        }


        @media (max-width: 650px) {

            .project-form-wrapper {

                padding: 10px;

            }


            .project-form-card {

                padding: 20px;

            }


            .form-row {

                grid-template-columns: 1fr;

            }


            .form-actions {

                flex-direction: column;

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
                    Add Project
                </h1>

                <p>
                    Share a project or contribution with the alumni community.
                </p>

            </div>


        </header>


        <section class="dashboard-content">


            <div class="project-form-wrapper">


                <div class="project-form-card">


                    <h1>
                        New Project
                    </h1>


                    <p>
                        Complete the information below to submit your project.
                    </p>


                    <?php if ($error !== ""): ?>


                        <div class="alert alert-error">
                            <?= e($error) ?>

                        </div>


                    <?php endif; ?>


                    <?php if ($success !== ""): ?>


                        <div class="alert alert-success">

                            <?= e($success) ?>

                        </div>


                    <?php endif; ?>


                    <form
                        method="POST"
                        action=""
                    >


                        <!-- TITLE -->


                        <div class="form-group">


                            <label for="title">

                                Project Title

                                <span class="required">
                                    *
                                </span>

                            </label>


                            <input
                                type="text"
                                id="title"
                                name="title"
                                placeholder="e.g. Alumni Career Mentorship Platform"
                                value="<?= e($title) ?>"
                                required
                            >


                        </div>


                        <!-- CATEGORY -->


                        <div class="form-group">


                            <label for="category">

                                Category

                                <span class="required">
                                    *
                                </span>

                            </label>


                            <input
                                type="text"
                                id="category"
                                name="category"
                                placeholder="e.g. Web Development, Research, Community Service"
                                value="<?= e($category) ?>"
                                required
                            >


                            <span class="help-text">
                                Enter the main category of your project.
                            </span>


                        </div>


                        <!-- DESCRIPTION -->


                        <div class="form-group">


                            <label for="description">

                                Description

                                <span class="required">
                                    *
                                </span>

                            </label>


                            <textarea
                                id="description"
                                name="description"
                                placeholder="Describe your project, its purpose, activities, and expected results..."
                                required
                            ><?= e($description) ?></textarea>


                        </div>


                        <!-- REQUIRED SKILLS -->


                        <div class="form-group">


                            <label for="required_skills">

                                Required Skills

                                <span class="required">
                                    *
                                </span>

                            </label>


                            <textarea
                                id="required_skills"
                                name="required_skills"
                                placeholder="e.g. PHP, MySQL, HTML, CSS, teamwork..."
                                required
                            ><?= e($required_skills) ?></textarea>


                        </div>


                        <!-- DATES -->


                        <div class="form-row">


                            <div class="form-group">


                                <label for="start_date">

                                    Start Date
                      <span class="required">
                                        *
                                    </span>

                                </label>


                                <input
                                    type="date"
                                    id="start_date"
                                    name="start_date"
                                    value="<?= e($start_date) ?>"
                                    required
                                >


                            </div>


                            <div class="form-group">


                                <label for="end_date">

                                    End Date

                                    <span class="required">
                                        *
                                    </span>

                                </label>


                                <input
                                    type="date"
                                    id="end_date"
                                    name="end_date"
                                    value="<?= e($end_date) ?>"
                                    required
                                >


                            </div>


                        </div>


                        <!-- STATUS -->


                        <div class="form-group">


                            <label for="status">

                                Project Status

                                <span class="required">
                                    *
                                </span>

                            </label>


                            <select
                                id="status"
                                name="status"
                                required
                            >


                                <option
                                    value="Draft"
                                    <?= $status === "Draft"
                                        ? "selected"
                                        : ""
                                    ?>
                                >
                                    Draft
                                </option>


                                <option
                                    value="Active"
                                    <?= $status === "Active"
                                        ? "selected"
                                        : ""
                                    ?>
                                >
                                    Active
                                </option>


                                <option
                                    value="Completed"
                                    <?= $status === "Completed"
                                        ? "selected"
                                        : ""
                                    ?>
                                >
                                    Completed
                                </option>


                            </select>


                        </div>


                        <!-- APPROVAL INFO -->


                        <div class="approval-info">


                            <strong>
                                Approval:
                            </strong>


                            <br>


                            Your project will automatically be submitted with an
                            <strong>Pending</strong> approval status.


                            An administrator must review and approve it before it can be displayed publicly.


                        </div>


                        <!-- ACTIONS -->


                        <div class="form-actions">


                            <button
                                type="submit"
                                class="submit-button"
                            >

                                Submit Project

                            </button>
                               <a
                                href="index.php"
                                class="cancel-button"
                            >

                                Cancel

                            </a>


                        </div>


                    </form>


                </div>


            </div>


        </section>


    </main>


</div>


</body>


</html>           