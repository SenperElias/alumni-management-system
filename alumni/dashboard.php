<?php

session_start();

require_once "../config/database.php";
require_once "../config/config.php";
require_once "../includes/functions.php";

/*
|--------------------------------------------------------------------------
| Check Login
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "alumni") {
    header("Location: ../index.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| Get Alumni Information
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT
        a.alumni_id,
        a.alumni_id_number,
        a.first_name,
        a.last_name,
        a.profile_photo,
        a.department_id,
        a.graduation_year,
        a.bio,
        d.department_name,
        u.email
     FROM alumni a
     LEFT JOIN users u
        ON a.user_id = u.user_id
     LEFT JOIN departments d
        ON a.department_id = d.department_id
     WHERE a.user_id = ?
     LIMIT 1"
);

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Alumni profile not found.");
}

$alumni = $result->fetch_assoc();

$stmt->close();

/*
|--------------------------------------------------------------------------
| Alumni Information
|--------------------------------------------------------------------------
*/

$fullName =
    $alumni["first_name"] . " " . $alumni["last_name"];

$firstName = $alumni["first_name"];

$department =
    $alumni["department_name"] ?? "Not assigned";

$graduationYear =
    $alumni["graduation_year"] ?? "Not available";

$profilePhoto =
    $alumni["profile_photo"] ?? "";

/*
|--------------------------------------------------------------------------
| Profile Completion
|--------------------------------------------------------------------------
*/

$totalFields = 7;
$completedFields = 0;

$fieldsToCheck = [
    $alumni["first_name"],
    $alumni["last_name"],
    $alumni["email"],
    $alumni["alumni_id_number"],
    $alumni["department_id"],
    $alumni["graduation_year"],
    $alumni["bio"]
];

foreach ($fieldsToCheck as $field) {

    if (!empty($field)) {
        $completedFields++;
    }
}

$profileCompletion =
    round(
        ($completedFields / $totalFields) * 100
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
        Alumni Dashboard |
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

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

            <a
                href="dashboard.php"
                class="active"
            >
                Dashboard
            </a>


            <div class="nav-section">
                MY ACCOUNT
            </div>


            <a href="profile.php">
                My Profile
            </a>


            <a href="employment.php">
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


            <a href="../notifications/index.php">
                Notifications
            </a>


            <a href="../settings/index.php">
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


    <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->

    <main class="admin-main">


        <!-- TOP BAR -->

        <header class="admin-topbar">

            <div>

                <h1>
                    Alumni Dashboard
                </h1>

                <p>
                    Welcome back,
                    <?= e($firstName) ?>.
                </p>

            </div>


            <div class="admin-user">

                <div class="admin-avatar">

                    <?= strtoupper(
                        substr(
                            $firstName,
                            0,
                            1
                        )
                    ) ?>

                </div>


                <div>

                    <strong>
                        <?= e($fullName) ?>
                    </strong>

                    <small>
                        Alumni
                    </small>

                </div>

            </div>

        </header>


        <!-- =================================================
             DASHBOARD CONTENT
        ================================================== -->

        <section class="dashboard-content">


            <!-- WELCOME CARD -->

            <div class="alumni-welcome-card">

                <div>

                    <span class="welcome-label">
                        WELCOME BACK
                    </span>

                    <h2>
                        Hello,
                        <?= e($firstName) ?>!
                    </h2>

                    <p>
                        Stay connected with your college,
                        update your career information,
                        and explore new opportunities.
                    </p>

                </div>


                <div class="welcome-profile">

                    <?php if (!empty($profilePhoto)): ?>

                        <img
                            src="../uploads/<?= e($profilePhoto) ?>"
                            alt="Profile photo"
                        >

                    <?php else: ?>

                        <div class="profile-initial">

                            <?= strtoupper(
                                substr(
                                    $firstName,
                                    0,
                                    1
                                )
                            ) ?>

                        </div>

                    <?php endif; ?>

                </div>

            </div>


            <!-- STAT CARDS -->

            <div class="dashboard-stats">


                <div class="stat-card">

                    <div class="stat-icon">
                        🎓
                    </div>

                    <div>

                        <span>
                            Department
                        </span>

                        <strong>
                            <?= e($department) ?>
                        </strong>

                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-icon">
                        📅
                    </div>

                    <div>

                        <span>
                            Graduation Year
                        </span>
                        <strong>
                            <?= e($graduationYear) ?>
                        </strong>

                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-icon">
                        🆔
                    </div>

                    <div>

                        <span>
                            Alumni ID
                        </span>

                        <strong>
                            <?= e(
                                $alumni["alumni_id_number"]
                            ) ?>
                        </strong>

                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-icon">
                        📊
                    </div>

                    <div>

                        <span>
                            Profile
                        </span>

                        <strong>
                            <?= $profileCompletion ?>%
                        </strong>

                    </div>

                </div>


            </div>


            <!-- TWO COLUMN AREA -->

            <div class="dashboard-two-column">


                <!-- PROFILE COMPLETION -->

                <div class="dashboard-panel">

                    <div class="panel-header">

                        <div>

                            <h2>
                                Profile Completion
                            </h2>

                            <p>
                                Keep your profile up to date.
                            </p>

                        </div>

                    </div>


                    <div class="profile-progress">

                        <div class="progress-bar">

                            <div
                                class="progress-fill"
                                style="width: <?= $profileCompletion ?>%;"
                            ></div>

                        </div>


                        <div class="progress-info">

                            <strong>
                                <?= $profileCompletion ?>%
                            </strong>

                            <span>
                                Complete
                            </span>

                        </div>

                    </div>


                    <a
                        href="profile.php"
                        class="primary-button"
                    >
                        Complete Profile
                    </a>

                </div>


                <!-- EMPLOYMENT -->

                <div class="dashboard-panel">

                    <div class="panel-header">

                        <div>

                            <h2>
                                Employment Status
                            </h2>

                            <p>
                                Keep your career information updated.
                            </p>

                        </div>

                    </div>


                    <div class="employment-placeholder">

                        <div class="employment-icon">
                            💼
                        </div>

                        <div>

                            <strong>
                                Employment information
                            </strong>

                            <p>
                                Update your current employment
                                status and career details.
                            </p>

                        </div>

                    </div>


                    <a
                        href="employment.php"
                        class="secondary-button"
                    >
                        Update Employment
                    </a>

                </div>

            </div>


            <!-- QUICK ACTIONS -->

            <div class="dashboard-panel">

                <div class="panel-header">
                    <div>

                        <h2>
                            Quick Actions
                        </h2>

                        <p>
                            Frequently used alumni services.
                        </p>

                    </div>

                </div>


                <div class="quick-actions">


                    <a
                        href="profile.php"
                        class="quick-action"
                    >

                        <span>
                            👤
                        </span>

                        <strong>
                            My Profile
                        </strong>

                        <small>
                            View and update your profile
                        </small>

                    </a>


                    <a
                        href="#"
                        class="quick-action"
                    >

                        <span>
                            💼
                        </span>

                        <strong>
                            Jobs & Internships
                        </strong>

                        <small>
                            Find new opportunities
                        </small>

                    </a>


                    <a
                        href="#"
                        class="quick-action"
                    >

                        <span>
                            🤝
                        </span>

                        <strong>
                            Mentorship
                        </strong>

                        <small>
                            Connect with mentors
                        </small>

                    </a>


                    <a
                        href="#"
                        class="quick-action"
                    >

                        <span>
                            📅
                        </span>

                        <strong>
                            Events
                        </strong>

                        <small>
                            View upcoming events
                        </small>

                    </a>

                </div>

            </div>


            <!-- RECENT OPPORTUNITIES -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Recent Opportunities
                        </h2>

                        <p>
                            Jobs and internships posted by the college.
                        </p>

                    </div>

                    <a href="#">
                        View All
                    </a>

                </div>


                <div class="empty-dashboard">

                    <div>
                        💼
                    </div>

                    <h3>
                        No opportunities yet
                    </h3>

                    <p>
                        New jobs and internship opportunities
                        will appear here.
                    </p>

                </div>

            </div>


            <!-- EVENTS -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Upcoming Events
                        </h2>

                        <p>
                            Stay connected with college activities.
                        </p>

                    </div>

                    <a href="#">
                        View All
                    </a>

                </div>


                <div class="empty-dashboard">

                    <div>
                        📅
                    </div>

                    <h3>
                        No upcoming events
                    </h3>
                    <p>
                        Upcoming alumni events will appear here.
                    </p>

                </div>

            </div>


        </section>

    </main>

</div>

</body>

</html>