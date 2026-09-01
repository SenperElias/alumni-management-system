<?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";


/*
|--------------------------------------------------------------------------
| Admin Access
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
| Form Variables
|--------------------------------------------------------------------------
*/

$type = "";
$title = "";
$company_name = "";
$description = "";
$requirements = "";
$location = "";
$contact_information = "";
$deadline = "";

$error = "";


/*
|--------------------------------------------------------------------------
| Handle Form Submission
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $type = trim($_POST["type"] ?? "");
    $title = trim($_POST["title"] ?? "");
    $company_name = trim($_POST["company_name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $requirements = trim($_POST["requirements"] ?? "");
    $location = trim($_POST["location"] ?? "");
    $contact_information = trim($_POST["contact_information"] ?? "");
    $deadline = trim($_POST["deadline"] ?? "");


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (
        $type === "" ||
        $title === "" ||
        $description === ""
    ) {

        $error = "Please fill in all required fields.";

    } elseif (
        !in_array(
            $type,
            ["Job", "Internship", "Training"],
            true
        )
    ) {

        $error = "Invalid opportunity type.";

    } else {


        /*
        |--------------------------------------------------------------------------
        | Insert Opportunity
        |--------------------------------------------------------------------------
        */

        $created_by = (int) $_SESSION["user_id"];

        $status = "pending";


        $sql = "
            INSERT INTO opportunities
            (
                created_by,
                type,
                title,
                company_name,
                description,
                requirements,
                location,
                contact_information,
                deadline,
                status
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";


        $stmt = $conn->prepare($sql);


        if (!$stmt) {

            $error =
                "Database error: " .
                $conn->error;

        } else {


            $stmt->bind_param(
                "isssssssss",
                $created_by,
                $type,
                $title,
                $company_name,
                $description,
                $requirements,
                $location,
                $contact_information,
                $deadline,
                $status
            );


            if ($stmt->execute()) {

                header(
                    "Location: index.php?success=created"
                );

                exit;

            } else {

                $error =
                    "Failed to create opportunity: " .
                    $stmt->error;
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
        Add Opportunity |
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

</head>


<body class="admin-body">


<div class="admin-layout">
<!-- =========================================================
         SIDEBAR
    ========================================================== -->

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


            <a
                href="index.php"
                class="active"
            >
                Opportunities
            </a>


            <a href="#">
                Events
            </a>


            <a href="#">
                Mentorship
            </a>


            <a href="#">
                Projects
            </a>


            <div class="nav-section">
                REPORTS
            </div>


            <a href="#">
                Employment Reports
            </a>


            <a href="#">
                Alumni Reports
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



    <!-- =========================================================
         MAIN
    ========================================================== -->

    <main class="admin-main">


        <header class="admin-topbar">


            <div>

                <h1>
                    Add Opportunity
                </h1>

                <p>
                    Create a new job, internship or training opportunity.
                </p>

            </div>


            <a
                href="index.php"
                class="secondary-button"
            >
                ← Back
            </a>


        </header>



        <section class="dashboard-content">


            <div class="dashboard-panel">


                <?php if ($error !== ""): ?>

                    <div class="alert alert-error">

                        <?= e($error) ?>

                    </div>

                <?php endif; ?>



                <form
                    method="POST"
                    class="admin-form"
                >


                    <!-- =================================================
                         TYPE
                    ================================================== -->

                    <div class="form-group">


                        <label for="type">

                            Type
                            <span>*</span>

                        </label>


                        <select
                            name="type"
                            id="type"
                            required
                        >

                            <option value="">
                                Select opportunity type
                            </option>


                            <option
                                value="Job"
                                <?= $type === "Job"
                                    ? "selected"
                                    : "" ?>
                            >
                                Job
                            </option>
                            <option
                                value="Internship"
                                <?= $type === "Internship"
                                    ? "selected"
                                    : "" ?>
                            >
                                Internship
                            </option>


                            <option
                                value="Training"
                                <?= $type === "Training"
                                    ? "selected"
                                    : "" ?>
                            >
                                Training
                            </option>


                        </select>


                    </div>



                    <!-- =================================================
                         TITLE
                    ================================================== -->

                    <div class="form-group">


                        <label for="title">

                            Title
                            <span>*</span>

                        </label>


                        <input
                            type="text"
                            name="title"
                            id="title"
                            value="<?= e($title) ?>"
                            placeholder="Example: Junior Web Developer"
                            required
                        >


                    </div>



                    <!-- =================================================
                         COMPANY
                    ================================================== -->

                    <div class="form-group">


                        <label for="company_name">

                            Company Name

                        </label>


                        <input
                            type="text"
                            name="company_name"
                            id="company_name"
                            value="<?= e($company_name) ?>"
                            placeholder="Enter company or organization name"
                        >


                    </div>



                    <!-- =================================================
                         DESCRIPTION
                    ================================================== -->

                    <div class="form-group">


                        <label for="description">

                            Description
                            <span>*</span>

                        </label>


                        <textarea
                            name="description"
                            id="description"
                            rows="6"
                            placeholder="Describe the opportunity..."
                            required
                        ><?= e($description) ?></textarea>


                    </div>



                    <!-- =================================================
                         REQUIREMENTS
                    ================================================== -->

                    <div class="form-group">


                        <label for="requirements">

                            Requirements

                        </label>


                        <textarea
                            name="requirements"
                            id="requirements"
                            rows="6"
                            placeholder="Enter qualifications, skills or requirements..."
                        ><?= e($requirements) ?></textarea>


                    </div>



                    <!-- =================================================
                         LOCATION
                    ================================================== -->

                    <div class="form-group">


                        <label for="location">

                            Location

                        </label>
                        <input
                            type="text"
                            name="location"
                            id="location"
                            value="<?= e($location) ?>"
                            placeholder="Example: Addis Ababa"
                        >


                    </div>



                    <!-- =================================================
                         CONTACT
                    ================================================== -->

                    <div class="form-group">


                        <label for="contact_info">

                            Contact Information

                        </label>


                        <textarea
                            name="contact_information"
                            id="contact_information"
                            rows="3"
                            placeholder="Email, phone number or application instructions..."
                        ><?= e($contact_information) ?></textarea>


                    </div>



                    <!-- =================================================
                         DEADLINE
                    ================================================== -->

                    <div class="form-group">


                        <label for="deadline">

                            Deadline

                        </label>


                        <input
                            type="date"
                            name="deadline"
                            id="deadline"
                            value="<?= e($deadline) ?>"
                        >


                    </div>



                    <!-- =================================================
                         ACTIONS
                    ================================================== -->

                    <div class="form-actions">


                        <a
                            href="index.php"
                            class="secondary-button"
                        >
                            Cancel
                        </a>


                        <button
                            type="submit"
                            class="primary-button"
                        >
                            Create Opportunity
                        </button>


                    </div>


                </form>


            </div>


        </section>


    </main>


</div>


</body>

</html>