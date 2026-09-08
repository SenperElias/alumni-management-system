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

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid event ID.");
}

$eventId = (int) $_GET["id"];


/*
|--------------------------------------------------------------------------
| Get Event
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        event_id,
        title,
        event_date,
        start_time,
        end_time,
        location,
        max_capacity,
        status
    FROM events
    WHERE event_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $eventId);
$stmt->execute();

$result = $stmt->get_result();
$event = $result->fetch_assoc();

$stmt->close();

if (!$event) {
    die("Event not found.");
}


/*
|--------------------------------------------------------------------------
| Get Registrations
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        er.registration_id,
        er.registration_status,
        er.registered_at,
        er.cancelled_at,

        a.alumni_id,
        a.first_name,
        a.last_name,
        a.college_id_number,
        a.phone,

        u.email

    FROM event_registrations er

    INNER JOIN alumni a
        ON er.alumni_id = a.alumni_id

    LEFT JOIN users u
        ON a.user_id = u.user_id

    WHERE er.event_id = ?

    ORDER BY er.registered_at DESC
");

$stmt->bind_param("i", $eventId);
$stmt->execute();

$registrations = $stmt->get_result();

$stmt->close();


/*
|--------------------------------------------------------------------------
| Count Registered
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total_registered
    FROM event_registrations
    WHERE event_id = ?
      AND LOWER(TRIM(registration_status)) = 'registered'
");

$stmt->bind_param("i", $eventId);
$stmt->execute();

$result = $stmt->get_result();
$countData = $result->fetch_assoc();

$stmt->close();

$totalRegistered = (int) ($countData["total_registered"] ?? 0);

$maxCapacity = (int) ($event["max_capacity"] ?? 0);

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
        Event Registrations
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

    <style>

        .registrations-wrapper {
            padding: 30px;
        }

        .event-summary {
            background: #ffffff;
            border-radius: 14px;
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid #eeeeee;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.06);
        }

        .event-summary h2 {
            color: #4a2c1d;
            margin-bottom: 15px;
        }

        .event-summary p {
            margin: 7px 0;
            color: #666666;
        }

        .registration-count {
            display: inline-block;
            margin-top: 15px;
            padding: 8px 14px;
            border-radius: 8px;
            background: #f8f5f2;
            color: #4a2c1d;
            font-weight: 600;
        }

        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 18px;
            border: 1px solid #8b5e3c;
            border-radius: 8px;
            background: #ffffff;
            color: #8b5e3c;
            text-decoration: none;
        }

        .back-button:hover {
            background: #8b5e3c;
            color: #ffffff;
        }
        .registration-status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .status-registered {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-cancelled {
            background: #ffebee;
            color: #c62828;
        }

        .empty-registrations {
            text-align: center;
            padding: 50px 20px;
            color: #777777;
        }

        .empty-registrations-icon {
            font-size: 45px;
            margin-bottom: 15px;
        }

        .alumni-name {
            color: #4a2c1d;
            font-weight: 600;
        }

        @media (max-width: 800px) {

            .registrations-wrapper {
                padding: 15px;
            }

            .table-responsive {
                overflow-x: auto;
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


            <a href="../opportunities/index.php">
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
                    Event Registrations
                </h1>

                <p>
                    View alumni registered for this event.
                </p>

            </div>

        </header>


        <section class="dashboard-content">


            <div class="registrations-wrapper">


                <a
                    href="index.php"
                    class="back-button"
                >
                    ← Back to Events
                </a>


                <!-- EVENT SUMMARY -->

                <div class="event-summary">

                    <h2>
                        <?= e($event["title"]) ?>
                    </h2>


                    <p>

                        <strong>
                            Date:
                        </strong>

                        <?= e($event["event_date"]) ?>

                    </p>


                    <p>

                        <strong>
                            Time:
                        </strong>

                        <?= e($event["start_time"]) ?>

                        -

                        <?= e($event["end_time"]) ?>

                    </p>


                    <p>

                        <strong>
                            Location:
                        </strong>
                        <?= e($event["location"]) ?>

                    </p>


                    <p>

                        <strong>
                            Status:
                        </strong>

                        <?= e($event["status"]) ?>

                    </p>


                    <span class="registration-count">

                        Currently Registered:

                        <?= $totalRegistered ?>

                        <?php if ($maxCapacity > 0): ?>

                            / <?= $maxCapacity ?>

                        <?php else: ?>

                            / Unlimited

                        <?php endif; ?>

                    </span>

                </div>


                <!-- REGISTRATIONS -->

                <div class="dashboard-panel">


                    <div class="panel-header">

                        <div>

                            <h2>
                                Registered Alumni
                            </h2>

                            <p>
                                Alumni who registered for this event.
                            </p>

                        </div>

                    </div>


                    <?php if ($registrations->num_rows > 0): ?>


                        <div class="table-responsive">


                            <table class="admin-table">


                                <thead>

                                    <tr>

                                        <th>
                                            Alumni
                                        </th>

                                        <th>
                                            Alumni ID
                                        </th>

                                        <th>
                                            Email
                                        </th>

                                        <th>
                                            Phone
                                        </th>

                                        <th>
                                            Status
                                        </th>

                                        <th>
                                            Registered At
                                        </th>

                                        <th>
                                            Cancelled At
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>


                                <?php while ($registration = $registrations->fetch_assoc()): ?>


                                    <?php

                                    $statusClass = strtolower(
                                        trim(
                                            $registration["registration_status"]
                                        )
                                    );

                                    ?>


                                    <tr>


                                        <td>

                                            <span class="alumni-name">

                                                <?= e(
                                                    $registration["first_name"]
                                                ) ?>

                                                <?= e(
                                                    $registration["last_name"]
                                                ) ?>

                                            </span>

                                        </td>


                                        <td>

                                            <?= e(
                                                $registration["college_id_number"]
                                                ?: "—"
                                            ) ?>

                                        </td>


                                        <td>
                                         <?= e(
                                                $registration["email"]
                                                ?: "—"
                                            ) ?>

                                        </td>


                                        <td>

                                            <?= e(
                                                $registration["phone"]
                                                ?: "—"
                                            ) ?>

                                        </td>


                                        <td>

                                            <span
                                                class="registration-status status-<?= e($statusClass) ?>"
                                            >

                                                <?= e(
                                                    $registration[
                                                        "registration_status"
                                                    ]
                                                ) ?>

                                            </span>

                                        </td>


                                        <td>

                                            <?= e(
                                                $registration["registered_at"]
                                            ) ?>

                                        </td>


                                        <td>

                                            <?= e(
                                                $registration["cancelled_at"]
                                                ?: "—"
                                            ) ?>

                                        </td>


                                    </tr>


                                <?php endwhile; ?>


                                </tbody>


                            </table>


                        </div>


                    <?php else: ?>


                        <div class="empty-registrations">

                            <div class="empty-registrations-icon">
                                📋
                            </div>

                            <h3>
                                No registrations yet
                            </h3>

                            <p>
                                No alumni have registered for this event.
                            </p>

                        </div>


                    <?php endif; ?>


                </div>


            </div>


        </section>


    </main>


</div>


</body>

</html>   