<?php
session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "alumni") {
    header("Location: ../..index.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| Get Alumni ID
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT alumni_id
    FROM alumni
    WHERE user_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();
    die("Alumni profile not found.");
}

$alumni = $result->fetch_assoc();

$stmt->close();

$alumniId = (int) $alumni["alumni_id"];

/*
|--------------------------------------------------------------------------
| Get Approved Events
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        e.event_id,
        e.title,
        e.description,
        e.event_date,
        e.start_time,
        e.end_time,
        e.location,
        e.registration_deadline,
        e.max_capacity,

        (
            SELECT COUNT(*)
            FROM event_registrations er
            WHERE er.event_id = e.event_id
            AND er.registration_status = 'Registered'
        ) AS registered_count,

        (
            SELECT er2.registration_status
            FROM event_registrations er2
            WHERE er2.event_id = e.event_id
            AND er2.alumni_id = ?
            ORDER BY er2.registration_id DESC
            LIMIT 1
        ) AS my_registration_status

    FROM events e

    WHERE e.status = 'published'

    ORDER BY e.event_date ASC, e.start_time ASC
");

$stmt->bind_param("i", $alumniId);
$stmt->execute();

$eventsResult = $stmt->get_result();

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
        Events | <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
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

            <a href="../dashboard.php">
                Dashboard
            </a>


            <div class="nav-section">
                MY ACCOUNT
            </div>

            <a href="../profile.php">
                My Profile
            </a>

            <a href="../employment/index.php">
                Employment
            </a>


            <div class="nav-section">
                OPPORTUNITIES
            </div>

            <a href="jobs.php">
                Jobs & Internships
            </a>

            <a href="../mentorship/index.php">
                Mentorship
            </a>


            <div class="nav-section">
                ACTIVITIES
            </div>

            <a href="../projects/index.php">
                Projects
            </a>

            <a
                href="events.php"
                class="active"
            >
                Events
            </a>


            <div class="nav-section">
                SYSTEM
            </div>

            <a href="#">
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


    <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->

    <main class="admin-main">


        <header class="admin-topbar">

            <div>

                <h1>
                    Events
                </h1>

                <p>
                    Discover and register for upcoming college events.
                </p>

            </div>

        </header>


        <section class="dashboard-content">


            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Upcoming Events
                        </h2>

                        <p>
                            Events available for alumni registration.
                        </p>

                    </div>

                </div>


                <?php if ($eventsResult->num_rows > 0): ?>

                    <div class="opportunity-list">


                        <?php while (
                            $event = $eventsResult->fetch_assoc()
                        ): ?>


                            <?php

                            $registeredCount =
                                (int) $event["registered_count"];

                            $maxCapacity =
                                $event["max_capacity"] !== null
                                ? (int) $event["max_capacity"]
                                : 0;

                            $isFull =
                                $maxCapacity > 0 &&
                                $registeredCount >= $maxCapacity;

                            $myStatus =
                                $event["my_registration_status"] ?? "";

                            ?>


                            <div class="opportunity-card">


                                <div class="opportunity-card-header">

                                    <div>

                                        <span class="opportunity-type">
                                            Event
                                        </span>

                                        <h3>
                                            <?= e(
                                                $event["title"]
                                            ) ?>
                                        </h3>

                                    </div>

                                    <?php if (
                                        $myStatus === "Registered"
                                    ): ?>

                                        <span class="approved-badge">
                                            Registered
                                        </span>

                                    <?php elseif ($isFull): ?>

                                        <span class="status-badge rejected">
                                            Full
                                        </span>

                                    <?php else: ?>

                                        <span class="status-badge approved">
                                            Open
                                        </span>

                                    <?php endif; ?>

                                </div>


                                <!-- EVENT DETAILS -->

                                <div class="opportunity-details">


                                    <div>

                                        <span>
                                            Date
                                        </span>
                                        <strong>
                                            <?= e(
                                                $event["event_date"]
                                            ) ?>
                                        </strong>

                                    </div>


                                    <div>

                                        <span>
                                            Time
                                        </span>

                                        <strong>

                                            <?= e(
                                                $event["start_time"]
                                            ) ?>

                                            -

                                            <?= e(
                                                $event["end_time"]
                                            ) ?>

                                        </strong>

                                    </div>


                                    <div>

                                        <span>
                                            Location
                                        </span>

                                        <strong>
                                            <?= e(
                                                $event["location"]
                                            ) ?>
                                        </strong>

                                    </div>


                                    <div>

                                        <span>
                                            Registered
                                        </span>

                                        <strong>

                                            <?= $registeredCount ?>

                                            <?php if (
                                                $maxCapacity > 0
                                            ): ?>

                                                /
                                                <?= $maxCapacity ?>

                                            <?php endif; ?>

                                        </strong>

                                    </div>


                                </div>


                                <!-- DESCRIPTION -->

                                <p class="opportunity-description">

                                    <?= e(
                                        mb_strimwidth(
                                            $event["description"] ?? "",
                                            0,
                                            180,
                                            "..."
                                        )
                                    ) ?>

                                </p>


                                <!-- ACTION -->

                                <div style="margin-top: 20px;">

                                    <a
                                        href="view-event.php?id=<?= (int)$event["event_id"] ?>"
                                        class="primary-button"
                                    >
                                        View Event
                                    </a>

                                </div>


                            </div>


                        <?php endwhile; ?>


                    </div>


                <?php else: ?>


                    <div class="empty-dashboard">

                        <div>
                            📅
                        </div>

                        <h3>
                            No upcoming events
                        </h3>

                        <p>
                            There are currently no approved events available.
                        </p>

                    </div>


                <?php endif; ?>


            </div>


        </section>

    </main>

</div>

</body>

</html>