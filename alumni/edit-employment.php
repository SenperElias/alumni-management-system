<?php
error_reporting(E_ALL);

session_start();

require_once "../config/database.php";
require_once "../config/config.php";
require_once "../includes/functions.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "alumni") {
    header("Location: ../index.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];

$error = "";
$success = "";

/*
|--------------------------------------------------------------------------
| Get Alumni ID
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT alumni_id
     FROM alumni
     WHERE user_id = ?
     LIMIT 1"
);

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Alumni profile not found.");
}

$alumni = $result->fetch_assoc();
$alumniId = (int) $alumni["alumni_id"];

$stmt->close();

/*
|--------------------------------------------------------------------------
| Get Employment ID
|--------------------------------------------------------------------------
*/

$employmentId = (int) ($_GET["id"] ?? 0);

if ($employmentId <= 0) {
    header("Location: employment.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Get Employment
|--------------------------------------------------------------------------
|
| IMPORTANT:
| alumni_id is checked here so an alumni cannot edit
| another alumni's employment record.
|
*/

$stmt = $conn->prepare(
    "SELECT
        employment_id,
        employment_status,
        company_name,
        job_position,
        Work_location,
        industry,
        employment_date,
        End_date,
        Verification_status,
        Verification_notes
     FROM employment
     WHERE employment_id = ?
     AND alumni_id = ?
     LIMIT 1"
);

$stmt->bind_param(
    "ii",
    $employmentId,
    $alumniId
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Employment record not found.");
}

$employment = $result->fetch_assoc();

$stmt->close();

/*
|--------------------------------------------------------------------------
| Update Employment
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $employmentStatus =
        trim($_POST["employment_status"] ?? "");

    $companyName =
        trim($_POST["company_name"] ?? "");

    $jobPosition =
        trim($_POST["job_position"] ?? "");

    $workLocation =
        trim($_POST["Work_location"] ?? "");

    $industry =
        trim($_POST["industry"] ?? "");

    $employmentDate =
        trim($_POST["employment_date"] ?? "");

    $endDate =
        trim($_POST["End_date"] ?? "");


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (
        $employmentStatus === "" ||
        $companyName === "" ||
        $jobPosition === "" ||
        $employmentDate === ""
    ) {

        $error =
            "Please fill in all required employment fields.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        |
        | Verification status and verification notes are NOT changed.
        |
        */

        $stmt = $conn->prepare(
            "UPDATE employment
             SET
                employment_status = ?,
                company_name = ?,
                job_position = ?,
                Work_location = ?,
                industry = ?,
                employment_date = ?,
                End_date = ?,
                updated_at = NOW(),
                Updated_by = ?
             WHERE employment_id = ?
             AND alumni_id = ?"
        );
        $stmt->bind_param(
            "sssssssiii",
            $employmentStatus,
            $companyName,
            $jobPosition,
            $workLocation,
            $industry,
            $employmentDate,
            $endDate,
            $userId,
            $employmentId,
            $alumniId
        );

        if ($stmt->execute()) {

            $success =
                "Employment information updated successfully.";

            /*
            | Reload record
            */

            $reload = $conn->prepare(
                "SELECT
                    employment_id,
                    employment_status,
                    company_name,
                    job_position,
                    Work_location,
                    industry,
                    employment_date,
                    End_date,
                    Verification_status,
                    Verification_notes
                 FROM employment
                 WHERE employment_id = ?
                 AND alumni_id = ?
                 LIMIT 1"
            );

            $reload->bind_param(
                "ii",
                $employmentId,
                $alumniId
            );

            $reload->execute();

            $reloadResult = $reload->get_result();

            $employment = $reloadResult->fetch_assoc();

            $reload->close();

        } else {

            $error =
                "Unable to update employment information.";
        }

        $stmt->close();
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
        Edit Employment |
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

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

            <a
                href="employment.php"
                class="active"
            >
                Employment
            </a>

            <div class="nav-section">
                OPPORTUNITIES
            </div>

            <a href="#">
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


    <!-- MAIN -->

    <main class="admin-main">

        <header class="admin-topbar">

            <div>

                <h1>
                    Edit Employment
                </h1>

                <p>
                    Update your employment information.
                </p>

            </div>

        </header>


        <section class="dashboard-content">


            <?php if ($error !== ""): ?>
                <div class="error-message">
                    <?= e($error) ?>
                </div>

            <?php endif; ?>


            <?php if ($success !== ""): ?>

                <div class="success-message">
                    <?= e($success) ?>
                </div>

            <?php endif; ?>


            <!-- VERIFICATION STATUS -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Verification Status
                        </h2>

                        <p>
                            This status is controlled by the administrator.
                        </p>

                    </div>

                </div>


                <div>

                    <span
                        class="verification-badge <?= e(
                            strtolower(
                                $employment["Verification_status"]
                                ?? "pending"
                            )
                        ) ?>"
                    >

                        <?= e(
                            ucfirst(
                                strtolower(
                                    $employment["Verfication_status"]

                                    ?? "pending"
                                )
                            )
                        ) ?>

                    </span>

                </div>

                <?php if (
                    !empty(
                        $employment["Verification_notes"]
                    )
                ): ?>

                    <div class="verification-notes">

                        <strong>
                            Admin Note:
                        </strong>

                        <?= e(
                            $employment["Verification_notes"]
                        ) ?>

                    </div>

                <?php endif; ?>

            </div>


            <!-- EDIT FORM -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Employment Information
                        </h2>

                    </div>

                </div>


                <form method="POST">

                    <div class="form-grid">


                        <div class="form-group">

                            <label for="employment_status">
                                Employment Status *
                            </label>

                            <select
                                id="employment_status"
                                name="employment_status"
                                required
                            >

                                <option value="">
                                    Select status
                                </option>

                                <option
                                    value="Employed"
                                    <?= $employment["employment_status"] === "Employed"
                                        ? "selected"
                                        : "" ?>
                                >
                                    Employed
                                </option>

                                <option
                                    value="Self-employed"
                                    <?= $employment["employment_status"] === "Self-employed"
                                        ? "selected"
                                        : "" ?>
                                >
                                    Self-employed
                                </option>

                                <option
                      value="Unemployed"
                                    <?= $employment["employment_status"] === "Unemployed"
                                        ? "selected"
                                        : "" ?>
                                >
                                    Unemployed
                                </option>

                                <option
                                    value="Student"
                                    <?= $employment["employment_status"] === "Student"
                                        ? "selected"
                                        : "" ?>
                                >
                                    Student
                                </option>

                                <option
                                    value="Retired"
                                    <?= $employment["employment_status"] === "Retired"
                                        ? "selected"
                                        : "" ?>
                                >
                                    Retired
                                </option>

                            </select>

                        </div>


                        <div class="form-group">

                            <label for="company_name">
                                Company / Organization *
                            </label>

                            <input
                                type="text"
                                id="company_name"
                                name="company_name"
                                value="<?= e(
                                    $employment["company_name"]
                                ) ?>"
                                required
                            >

                        </div>


                        <div class="form-group">

                            <label for="job_position">
                                Job Position *
                            </label>

                            <input
                                type="text"
                                id="job_position"
                                name="job_position"
                                value="<?= e(
                                    $employment["job_position"]
                                ) ?>"
                                required
                            >

                        </div>


                        <div class="form-group">

                            <label for="Work_location">
                                Work Location
                            </label>

                            <input
                                type="text"
                                id="Work_location"
                                name="Work_location"
                                value="<?= e(
                                    $employment["Work_location"]
                                ) ?>"
                            >

                        </div>


                        <div class="form-group">

                            <label for="industry">
                                Industry
                            </label>

                            <input
                                type="text"
                                id="industry"
                                name="industry"
                                value="<?= e(
                                    $employment["industry"]
                                ) ?>"
                            >

                        </div>


                        <div class="form-group">

                            <label for="employment_date">
                                Employment Start Date *
                            </label>
                             <input
                                type="date"
                                id="employment_date"
                                name="employment_date"
                                value="<?= e(
                                    $employment["employment_date"]
                                ) ?>"
                                required
                            >

                        </div>


                        <div class="form-group">

                            <label for="End_date">
                                End Date
                            </label>

                            <input
                                type="date"
                                id="End_date"
                                name="End_date"
                                value="<?= e(
                                    $employment["End_date"] ?? ""
                                ) ?>"
                            >

                            <small>
                                Leave empty if you currently work here.
                            </small>

                        </div>

                    </div>


                    <div class="profile-form-actions">

                        <a
                            href="employment.php"
                            class="secondary-button"
                        >
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="primary-button"
                        >
                            Save Changes
                        </button>

                    </div>

                </form>

            </div>


        </section>

    </main>

</div>

</body>

</html>         