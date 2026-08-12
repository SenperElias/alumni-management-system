<?php

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
if (isset($_GET["deleted"])) {
    $success = "Employment record deleted successfully.";
}

if (isset($_GET["error"])) {
    $error = "Unable to delete employment record.";
}

/*
|--------------------------------------------------------------------------
| Get Alumni ID
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT alumni_id, first_name, last_name
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

$stmt->close();

$alumniId = (int) $alumni["alumni_id"];

/*
|--------------------------------------------------------------------------
| Add Employment
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
        | Insert Employment
        |--------------------------------------------------------------------------
        |
        | Verification fields are controlled by the admin.
        |
        */

        $verificationStatus = "pending";

        $stmt = $conn->prepare(
            "INSERT INTO employment
            (
                alumni_id,
                employment_status,
                company_name,
                job_position,
                Work_location,
                industry,
                employment_date,
                End_date,
                Verification_status,
                Verification_notes,
                Updated_by
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, ?)"
        );

        $stmt->bind_param(
            "isssssssss",
            $alumniId,
            $employmentStatus,
            $companyName,
            $jobPosition,
            $workLocation,
            $industry,
            $employmentDate,
            $endDate,
            $verificationStatus,
            $userId

        );

        if ($stmt->execute()) {

            $success =
                "Employment information submitted successfully.";

        } else {

            $error =
                "Unable to save employment information.";
        }

        $stmt->close();
    }
}

/*
|--------------------------------------------------------------------------
| Get Employment Records
|--------------------------------------------------------------------------
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
        Verification_notes,
        created_at,
        updated_at
     FROM employment
     WHERE alumni_id = ?
    

     ORDER BY employment_date DESC"
);

$stmt->bind_param("i", $alumniId);
$stmt->execute();
$employmentResult = $stmt->get_result();

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
        Employment |
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

            <a href="jobs.php">
                Jobs & Internships
            </a>

            <a href="mentorship/index.php">
                Mentorship
            </a>

            <div class="nav-section">
                ACTIVITIES
            </div>

            <a href="projects/index.php">
                Projects
            </a>

            <a href="events/events.php">
                Events
            </a>

            <div class="nav-section">
                SYSTEM
            </div>

            <a href="notifications.php">
                Notifications
            </a>

            <a href="settings.php">
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
                    Employment
                </h1>

                <p>
                    Manage your employment history.
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


            <!-- ADD EMPLOYMENT -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Add Employment
                        </h2>

                        <p>
                            Add your current or previous employment.
                        </p>

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

                                <option value="Employed">
                                    Employed
                                </option>
                                <option value="Self-employed">
                                    Self-employed
                                </option>

                                <option value="Unemployed">
                                    Unemployed
                                </option>

                                <option value="Continuing Education">
                                    Continuing Education
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
                                placeholder="e.g. Addis Ababa"
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
                                placeholder="e.g. Information Technology"
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
                            >

                            <small>
                                Leave empty if you currently work here.
                            </small>

                        </div>

                    </div>


                    <div class="profile-form-actions">

                        <button
                            type="submit"
                            class="primary-button"
                        >
                            Add Employment
                        </button>

                    </div>

                </form>

            </div>
            <!-- EMPLOYMENT HISTORY -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Employment History
                        </h2>

                        <p>
                            Your submitted employment records.
                        </p>

                    </div>

                </div>


                <?php if ($employmentResult->num_rows > 0): ?>


                    <div class="employment-list">

                        <?php while (
                            $job = $employmentResult->fetch_assoc()
                        ): ?>

                            <div class="employment-card">


                                <div class="employment-card-header">

                                    <div>

                                        <h3>
                                            <?= e(
                                                $job["job_position"]
                                            ) ?>
                                        </h3>

                                        <strong>
                                            <?= e(
                                                $job["company_name"]
                                            ) ?>
                                        </strong>

                                    </div>


                                    <?php

                                    $status =
                                        strtolower(
                                            $job["Verification_status"]
                                            ?? "pending"
                                        );

                                    ?>

                                    <span
                                        class="verification-badge <?= e($status) ?>"
                                    >
                                        <?= e(
                                            ucfirst($status)
                                        ) ?>
                                    </span>

                                </div>


                                <div class="employment-details">


                                    <div>

                                        <span>
                                            Status
                                        </span>

                                        <strong>
                                            <?= e(
                                                $job["employment_status"]
                                            ) ?>
                                        </strong>

                                    </div>


                                    <div>

                                        <span>
                                            Location
                                        </span>

                                        <strong>
                                            <?= e(
                                                $job["Work_location"]
                                                ?: "Not provided"
                                            ) ?>
                                        </strong>

                                    </div>


                                    <div>

                                        <span>
                                            Industry
                                        </span>

                                        <strong>
                                            <?= e(
                                                $job["industry"]
                                                ?: "Not provided"
                                            ) ?>
                                        </strong>

                                    </div>


                                    <div>
                                    <span>
                                            Start Date
                                        </span>

                                        <strong>
                                            <?= e(
                                                $job["employment_date"]
                                            ) ?>
                                        </strong>

                                    </div>


                                    <div>

                                        <span>
                                            End Date
                                        </span>

                                        <strong>

                                            <?php if (
                                                !empty($job["End_date"])
                                            ): ?>

                                                <?= e(
                                                    $job["End_date"]
                                                ) ?>

                                            <?php else: ?>

                                                Present

                                            <?php endif; ?>

                                        </strong>

                                    </div>

                                </div>


                                <?php if (
                                    !empty(
                                        $job["Verification_notes"]
                                    )
                                ): ?>

                                    <div class="verification-notes">

                                        <strong>
                                            Admin Note:
                                        </strong>

                                        <?= e(
                                            $job["Verification_notes"]
                                        ) ?>

                                    </div>

                                <?php endif; ?>


                            </div>
<div class="employment-actions">

    <a
        href="edit-employment.php?id=<?= (int)$job['employment_id'] ?>"
        class="secondary-button"
    >
        Edit
    </a>

    <a
        href="delete-employment.php?id=<?= (int)$job['employment_id'] ?>"
        class="danger-button"
        onclick="return confirm('Are you sure you want to delete this employment record?');"
    >
        Delete
    </a>

</div>

                    </div>

 <?php endwhile; ?>
                <?php else: ?>


                    <div class="empty-dashboard">

                        <div>
                            💼
                        </div>

                        <h3>
                            No employment records
                        </h3>

                        <p>
                            Add your current or previous employment above.
                        </p>

                    </div>


                <?php endif; ?>


            </div>


        </section>

    </main>

</div>

</body>

</html>