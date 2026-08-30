Create a new file:
admin/Opportunities/edit.php
Use this complete code:
<?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";

/*|--------------------------------------------------------------------------
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

/*|--------------------------------------------------------------------------
| GET OPPORTUNITY ID
|--------------------------------------------------------------------------
*/

$opportunity_id = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;

if ($opportunity_id <= 0) {
    die("Invalid opportunity ID.");
}

/*|--------------------------------------------------------------------------
| VARIABLES
|--------------------------------------------------------------------------
*/

$error = "";
$success = "";

/*|--------------------------------------------------------------------------
| GET EXISTING OPPORTUNITY
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT *
    FROM opportunities
    WHERE opportunity_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $opportunity_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Opportunity not found.");
}

$opportunity = $result->fetch_assoc();

$stmt->close();

/*|--------------------------------------------------------------------------
| DEFAULT FORM VALUES
|--------------------------------------------------------------------------
*/

$title = $opportunity["title"] ?? "";
$type = $opportunity["type"] ?? "";
$company_name = $opportunity["company_name"] ?? "";
$description = $opportunity["description"] ?? "";
$requirements = $opportunity["requirements"] ?? "";
$location = $opportunity["location"] ?? "";
$contact_information = $opportunity["contact_information"] ?? "";
$deadline = $opportunity["deadline"] ?? "";
$status = $opportunity["status"] ?? "Pending";

/*|--------------------------------------------------------------------------
| UPDATE OPPORTUNITY
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {
      verify_csrf_token();
    $title = trim($_POST["title"] ?? "");
    $type = trim($_POST["type"] ?? "");
    $company_name = trim($_POST["company_name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $requirements = trim($_POST["requirements"] ?? "");
    $location = trim($_POST["location"] ?? "");
    $contact_information = trim($_POST["contact_information"] ?? "");
    $deadline = trim($_POST["deadline"] ?? "");
    $status = trim($_POST["status"] ?? "");

    /*----------------------------------------------------------------------
    | VALIDATION
    ----------------------------------------------------------------------*/

    if (
        $title === "" ||
        $type === "" ||
        $company_name === "" ||
        $description === "" ||
        $location === "" ||
        $deadline === ""
    ) {

        $error = "Please fill in all required fields.";

    }

    /*----------------------------------------------------------------------
    | TYPE VALIDATION
    ----------------------------------------------------------------------*/

    elseif (
        !in_array(
            $type,
            [
                "Job",
                "Internship"
            ],
            true
        )
    ) {

        $error = "Invalid opportunity type.";

    }

    /*----------------------------------------------------------------------
    | STATUS VALIDATION
    ----------------------------------------------------------------------*/
    elseif (
        !in_array(
            $status,
            [
                "Draft",
                "Pending",
                "Approved",
                "Rejected",
                "Expired"
            ],
            true
        )
    ) {

        $error = "Invalid opportunity status.";

    }

    /*----------------------------------------------------------------------
    | UPDATE DATABASE
    ----------------------------------------------------------------------*/

    else {

        $sql = "
            UPDATE opportunities
            SET
                type = ?,
                title = ?,
                company_name = ?,
                description = ?,
                requirements = ?,
                location = ?,
                contact_information = ?,
                deadline = ?,
                status = ?,
                updated_at = NOW()
            WHERE opportunity_id = ?
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            $error = "Database error: " . $conn->error;

        } else {

            $stmt->bind_param(
                "sssssssssi",
                $type,
                $title,
                $company_name,
                $description,
                $requirements,
                $location,
                $contact_information,
                $deadline,
                $status,
                $opportunity_id
            );

            if ($stmt->execute()) {

                $success = "Opportunity updated successfully.";

                /*
                |--------------------------------------------------------------
                | Refresh opportunity data
                |--------------------------------------------------------------
                */

                $opportunity["type"] = $type;
                $opportunity["title"] = $title;
                $opportunity["company_name"] = $company_name;
                $opportunity["description"] = $description;
                $opportunity["requirements"] = $requirements;
                $opportunity["location"] = $location;
                $opportunity["contact_information"] = $contact_information;
                $opportunity["deadline"] = $deadline;
                $opportunity["status"] = $status;

            } else {

                $error = "Failed to update opportunity: " . $stmt->error;

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
        Edit Opportunity |
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

    <style>

        .edit-wrapper {
            max-width: 900px;
            margin: 35px auto;
            padding: 20px;
        }

        .form-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #eeeeee;
            padding: 30px;
            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.05);
        }

        .form-card h2 {
            margin-top: 0;
            color: #4a2c1d;
        }

        .form-card > p {
            color: #777777;
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
            min-height: 150px;
            resize: vertical;
            line-height: 1.6;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #8b5e3c;
        }

        .required {
            color: #b3261e;
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

        .status-info {
            margin-top: 20px;
            padding: 15px;
            border-radius: 9px;
            background: #f8f5f2;
            color: #6b5140;
            line-height: 1.6;
            font-size: 14px;
        }

        @media (max-width: 600px) {

            .edit-wrapper {
                padding: 10px;
            }

            .form-card {
                padding: 20px;
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

            <a href="../Alumni/index.php">
                Alumni
            </a>

            <a href="../Employment/index.php">
                Employment
            </a>

            <a href="../Mentors/index.php">
                Mentors
            </a>

            <a
                href="index.php"
                class="active"
            >
                Opportunities
            </a>

            <a href="../Events/index.php">
                Events
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


    <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->

    <main class="admin-main">

        <header class="admin-topbar">
        <div>

                <h1>
                    Edit Opportunity
                </h1>

                <p>
                    Update the information for this opportunity.
                </p>

            </div>

        </header>


        <section class="dashboard-content">

            <div class="edit-wrapper">

                <div class="form-card">

                    <h2>
                        Edit Job / Internship
                    </h2>

                    <p>
                        Update the opportunity information below.
                    </p>


                    <!-- ALERTS -->

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
                    <?=csrf_field() ?>

                        <!-- TITLE -->

                        <div class="form-group">

                            <label for="title">

                                Opportunity Title

                                <span class="required">
                                    *
                                </span>

                            </label>

                            <input
                                type="text"
                                id="title"
                                name="title"
                                value="<?= e($title) ?>"
                                required
                            >

                        </div>


                        <!-- TYPE -->

                        <div class="form-group">

                            <label for="type">

                                Opportunity Type

                                <span class="required">
                                    *
                                </span>

                            </label>

                            <select
                                id="type"
                                name="type"
                                required
                            >

                                <option value="">
                                    Select Type
                                </option>

                                <option
                                    value="Job"
                                    <?= $type === "Job" ? "selected" : "" ?>
                                >
                                    Job
                                </option>

                                <option
                                    value="Internship"
                                    <?= $type === "Internship" ? "selected" : "" ?>
                                >
                                    Internship
                                </option>

                            </select>

                        </div>


                        <!-- COMPANY -->

                        <div class="form-group">

                            <label for="company_name">

                                Company Name

                                <span class="required">
                                    *
                                </span>

                            </label>

                            <input
                                type="text"
                                id="company_name"
                                name="company_name"
                                value="<?= e($company_name) ?>"
                                required
                            >

                        </div>


                        <!-- LOCATION -->

                        <div class="form-group">
                            <label for="location">

                                Location

                                <span class="required">
                                    *
                                </span>

                            </label>

                            <input
                                type="text"
                                id="location"
                                name="location"
                                value="<?= e($location) ?>"
                                required
                            >

                        </div>


                        <!-- DEADLINE -->

                        <div class="form-group">

                            <label for="deadline">

                                Application Deadline

                                <span class="required">
                                    *
                                </span>

                            </label>

                            <input
                                type="date"
                                id="deadline"
                                name="deadline"
                                value="<?= e($deadline) ?>"
                                required
                            >

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
                                required
                            ><?= e($description) ?></textarea>

                        </div>


                        <!-- REQUIREMENTS -->

                        <div class="form-group">

                            <label for="requirements">

                                Requirements

                            </label>

                            <textarea
                                id="requirements"
                                name="requirements"
                                placeholder="Education, skills, experience, qualifications, etc."
                            ><?= e($requirements) ?></textarea>

                        </div>


                        <!-- CONTACT -->

                        <div class="form-group">

                            <label for="contact_information">

                                Contact Information

                            </label>

                            <textarea
                                id="contact_information"
                                name="contact_information"
                                placeholder="Email, phone number, website, application instructions, etc."
                            ><?= e($contact_information) ?></textarea>

                        </div>


                        <!-- STATUS -->

                        <div class="form-group">

                            <label for="status">

                                Status

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
                                    <?= $status === "Draft" ? "selected" : "" ?>
                                >
                                    Draft
                                </option>
                                <option
                                    value="Pending"
                                    <?= $status === "Pending" ? "selected" : "" ?>
                                >
                                    Pending
                                </option>

                                <option
                                    value="Approved"
                                    <?= $status === "Approved" ? "selected" : "" ?>
                                >
                                    Approved
                                </option>

                                <option
                                    value="Rejected"
                                    <?= $status === "Rejected" ? "selected" : "" ?>
                                >
                                    Rejected
                                </option>

                                <option
                                    value="Expired"
                                    <?= $status === "Expired" ? "selected" : "" ?>
                                >
                                    Expired
                                </option>

                            </select>

                        </div>


                        <!-- STATUS INFORMATION -->

                        <div class="status-info">

                            <strong>
                                Status workflow:
                            </strong>

                            <br>

                            <strong>Draft</strong> —
                            Not ready for publication.

                            <br>

                            <strong>Pending</strong> —
                            Waiting for administrator review.

                            <br>

                            <strong>Approved</strong> —
                            Available to alumni.

                            <br>

                            <strong>Rejected</strong> —
                            Not available to alumni.

                            <br>

                            <strong>Expired</strong> —
                            The application deadline has passed.

                        </div>


                        <!-- ACTIONS -->

                        <div class="form-actions">

                            <button
                                type="submit"
                                class="submit-button"
                            >
                                Save Changes
                            </button>

                            <a
                                href="view.php?id=<?= $opportunity_id ?>"
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