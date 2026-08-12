<?php
session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "admin") {
    header("Location: ../../index.php");
    exit;
}

$eventId = (int) ($_GET["id"] ?? 0);

if ($eventId <= 0) {
    header("Location: index.php");
    exit;
}

/* Get Event */

$stmt = $conn->prepare("
    SELECT
        event_id,
        title,
        description,
        event_date,
        start_time,
        end_time,
        location,
        registration_deadline,
        max_capacity,
        status,
        created_by,
        created_at,
        updated_at
    FROM events
    WHERE event_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $eventId);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();
    die("Event not found.");
}

$event = $result->fetch_assoc();

$stmt->close();

/* Get Registration Count */

$registrationCount = 0;

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM event_registrations
    WHERE event_id = ?
    AND registration_status = 'Registered'
");

if ($stmt) {

    $stmt->bind_param("i", $eventId);
    $stmt->execute();

    $registrationResult = $stmt->get_result();
    $registrationData = $registrationResult->fetch_assoc();

    $registrationCount = (int) (
        $registrationData["total"] ?? 0
    );

    $stmt->close();
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
        View Event | <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
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

            <a href="../opportunities/">
                Opportunities
            </a>

            <a
                href="index.php"
                class="active"
            >
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


    <!-- MAIN -->

    <main class="admin-main">

        <header class="admin-topbar">

            <div>

                <h1>
                    View Event
                </h1>

                <p>
                    View complete event information.
                </p>

            </div>

            <div>

                <a
                    href="index.php"
                    class="secondary-button"
                >
                    ← Back to Events
                </a>
                <a
                    href="edit.php?id=<?= (int)$event["event_id"] ?>"
                    class="primary-button"
                >
                    Edit Event
                </a>

            </div>

        </header>


        <section class="dashboard-content">

            <!-- EVENT INFORMATION -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <span class="opportunity-type">
                            Event
                        </span>

                        <h2>
                            <?= e($event["title"]) ?>
                        </h2>

                    </div>

                    <span
                        class="status-badge <?= e(
                            strtolower($event["status"])
                        ) ?>"
                    >
                        <?= e($event["status"]) ?>
                    </span>

                </div>


                <!-- DESCRIPTION -->

                <div class="form-group">

                    <label>
                        Description
                    </label>

                    <div class="view-content">

                        <?= nl2br(
                            e(
                                $event["description"]
                                ?: "No description provided."
                            )
                        ) ?>

                    </div>

                </div>


                <!-- DETAILS -->

                <div class="form-grid">

                    <div class="form-group">

                        <label>
                            Event Date
                        </label>

                        <strong>
                            <?= e($event["event_date"]) ?>
                        </strong>

                    </div>


                    <div class="form-group">

                        <label>
                            Time
                        </label>

                        <strong>

                            <?= e($event["start_time"]) ?>

                            -

                            <?= e($event["end_time"]) ?>

                        </strong>

                    </div>


                    <div class="form-group">

                        <label>
                            Location
                        </label>

                        <strong>
                            <?= e(
                                $event["location"]
                                ?: "Not specified"
                            ) ?>
                        </strong>

                    </div>


                    <div class="form-group">

                        <label>
                            Registration Deadline
                        </label>

                        <strong>
                            <?= e(
                                $event["registration_deadline"]
                                ?: "No deadline"
                            ) ?>
                        </strong>

                    </div>


                    <div class="form-group">

                        <label>
                            Maximum Capacity
                        </label>

                        <strong>
                            <?= e(
                                $event["max_capacity"]
                                ?: "Unlimited"
                            ) ?>
                        </strong>

                    </div>


                    <div class="form-group">

                        <label>
                            Registered Alumni
                        </label>

                        <strong>
                            <?= $registrationCount ?>
                        </strong>

                    </div>

                </div>

            </div>


            <!-- REGISTRATION SUMMARY -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>
                    <h2>
                            Registration Summary
                        </h2>

                        <p>
                            Current registration information.
                        </p>

                    </div>

                </div>


                <div class="form-grid">

                    <div class="form-group">

                        <label>
                            Registered
                        </label>

                        <strong>
                            <?= $registrationCount ?>
                        </strong>

                    </div>


                    <div class="form-group">

                        <label>
                            Capacity
                        </label>

                        <strong>

                            <?= e(
                                $event["max_capacity"]
                                ?: "Unlimited"
                            ) ?>

                        </strong>

                    </div>


                    <div class="form-group">

                        <label>
                            Available Seats
                        </label>

                        <strong>

                            <?php if (
                                !empty($event["max_capacity"])
                            ): ?>

                                <?= max(
                                    0,
                                    (int)$event["max_capacity"]
                                    - $registrationCount
                                ) ?>

                            <?php else: ?>

                                Unlimited

                            <?php endif; ?>

                        </strong>

                    </div>

                </div>

            </div>

        </section>

    </main>

</div>

</body>

</html>