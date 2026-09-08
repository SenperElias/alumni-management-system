<?php

session_start();

require_once "../config/database.php";
require_once "../config/config.php";
require_once "../includes/functions.php";

/*|--------------------------------------------------------------------------
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


requirePasswordChange();
$userId = (int) $_SESSION["user_id"];
/*|--------------------------------------------------------------------------
| Get Alumni Information
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        a.alumni_id,
        a.college_id_number,
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
    LIMIT 1
");

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Alumni profile not found.");
}

$alumni = $result->fetch_assoc();

$stmt->close();


/*|--------------------------------------------------------------------------
| Alumni Information
|--------------------------------------------------------------------------
*/

$fullName =
    $alumni["first_name"] . " " . $alumni["last_name"];

$firstName =
    $alumni["first_name"];

$department =
    $alumni["department_name"] ?? "Not assigned";

$graduationYear =
    $alumni["graduation_year"] ?? "Not available";

$profilePhoto =
    $alumni["profile_photo"] ?? "";


/*|--------------------------------------------------------------------------
| Unread Notification Count
|--------------------------------------------------------------------------
*/

$notificationStmt = $conn->prepare("
    SELECT COUNT(*) AS unread_count
    FROM notifications
    WHERE user_id = ?
      AND is_read = 0
");

if (!$notificationStmt) {
    die("Notification database error: " . $conn->error);
}

$notificationStmt->bind_param(
    "i",
    $userId
);

$notificationStmt->execute();

$notificationResult =
    $notificationStmt->get_result();

$notificationRow =
    $notificationResult->fetch_assoc();

$unreadNotificationCount =
    (int) ($notificationRow["unread_count"] ?? 0);

$notificationStmt->close();


/*|--------------------------------------------------------------------------
| Profile Completion
|--------------------------------------------------------------------------
*/

$totalFields = 7;

$completedFields = 0;

$fieldsToCheck = [
    $alumni["first_name"],
    $alumni["last_name"],
    $alumni["email"],
    $alumni["college_id_number"],
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

    <style>

        /*|--------------------------------------------------------------------------
        | Notification Bell
        |--------------------------------------------------------------------------
        */

        .notification-area {
            position: relative;
            margin-left: auto;
            margin-right: 25px;
        }

        .notification-bell {
            position: relative;
 display: flex;
            align-items: center;
            justify-content: center;

            width: 42px;
            height: 42px;

            border: none;
            background: transparent;

            text-decoration: none;

            font-size: 23px;

            cursor: pointer;

            border-radius: 50%;

            transition: 0.2s;
        }

        .notification-bell:hover {
            background: rgba(0, 0, 0, 0.06);
        }

        .notification-badge {
            position: absolute;

            top: -2px;
            right: -2px;

            min-width: 19px;
            height: 19px;

            padding: 0 5px;

            border-radius: 50%;

            background: #b00020;
            color: #ffffff;

            font-size: 11px;
            font-weight: bold;

            display: flex;
            align-items: center;
            justify-content: center;
        }

    </style>

</head>


<body class="admin-body">


<div class="admin-layout">


    <!-- =====================================================
         SIDEBAR
    ====================================================== -->

    <?php
    require_once __DIR__ . "/includes/sidebar.php";
    ?>


    <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->

    <main class="admin-main">


        <!-- =================================================
             TOP BAR
        ================================================== -->

        <header class="admin-topbar">


            <!-- DASHBOARD TITLE -->

            <div>

                <h1>
                    Alumni Dashboard
                </h1>

                <p>
                    Welcome back,
                    <?= e($firstName) ?>.
                </p>

            </div>


            <!-- =================================================
                 NOTIFICATION BELL
            ================================================== -->

            <div class="notification-area">

    <a
        href="notifications/index.php"
        class="notification-bell"
        title="Notifications"
    >
        🔔

        <span
            class="notification-badge"
            id="notificationBadge"
            style="<?= $unreadNotificationCount > 0 ? 'display: flex;' : 'display: none;' ?>"
        >
            <?= (int) $unreadNotificationCount ?>
        </span>

    </a>

</div>


            <!-- =================================================
                 ALUMNI USER
            ================================================== -->

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


        <!-- =====================================================
             DASHBOARD CONTENT
        ====================================================== -->

        <section class="dashboard-content">


            <!-- =================================================
                 WELCOME CARD
            ================================================== -->

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


            <!-- =================================================
                 STAT CARDS
            ================================================== -->

            <div class="dashboard-stats">


                <!-- DEPARTMENT -->

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


                <!-- GRADUATION YEAR -->

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


                <!-- ALUMNI ID -->

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
                                $alumni["college_id_number"]
                            ) ?>

                        </strong>

                    </div>

                </div>


                <!-- PROFILE -->

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


            <!-- =================================================
                 TWO COLUMN AREA
            ================================================== -->

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


            <!-- =================================================
                 QUICK ACTIONS
            ================================================== -->

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
                        href="jobs.php"
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
                        href="./mentorship/index.php"
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
                        href="./events/events.php"
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


            <!-- =================================================
                 RECENT OPPORTUNITIES
            ================================================== -->

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


            <!-- =================================================
                 EVENTS
            ================================================== -->

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

<script>
function updateNotificationCount() {

    fetch("notifications_count.php")
        .then(response => response.json())
        .then(data => {

            if (!data.success) {
                return;
            }

            const badge =
                document.getElementById("notificationBadge");

            if (!badge) {
                return;
            }

            const count = parseInt(data.count, 10) || 0;

            badge.textContent = count;

            if (count > 0) {
                badge.style.display = "flex";
            } else {
                badge.style.display = "none";
            }
        })
        .catch(error => {
            console.log("Notification count error:", error);
        });
}


/*
|--------------------------------------------------------------------------
| Check immediately when dashboard loads
|--------------------------------------------------------------------------
*/

updateNotificationCount();


/*
|--------------------------------------------------------------------------
| Check every 5 seconds
|--------------------------------------------------------------------------
*/

setInterval(updateNotificationCount, 5000);
</script>
</body>

</html>