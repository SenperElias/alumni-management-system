<?php

require_once("../../config/database.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "alumni") {
    header("Location: ../../index.php");
    exit;
}

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid event ID.");
}

$eventId = (int) $_GET["id"];
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
| Get Event
|--------------------------------------------------------------------------
*/

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
        status
    FROM events
    WHERE event_id = ?
      AND status = 'Published'
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
| Get Current Alumni Registration
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        registration_id,
        registration_status
    FROM event_registrations
    WHERE event_id = ?
      AND alumni_id = ?
    ORDER BY registration_id DESC
    LIMIT 1
");

$stmt->bind_param(
    "ii",
    $eventId,
    $alumniId
);

$stmt->execute();

$result = $stmt->get_result();

$registration = $result->fetch_assoc();

$stmt->close();


$registrationStatus = trim(
    $registration["registration_status"] ?? ""
);


/*
|--------------------------------------------------------------------------
| Count Registered Alumni
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT COUNT(*) AS registered_count
    FROM event_registrations
    WHERE event_id = ?
      AND LOWER(TRIM(registration_status)) = 'registered'
");

$stmt->bind_param(
    "i",
    $eventId
);

$stmt->execute();

$result = $stmt->get_result();

$countData = $result->fetch_assoc();

$stmt->close();

$registeredCount = (int) (
    $countData["registered_count"] ?? 0
);


/*
|--------------------------------------------------------------------------
| Event Capacity
|--------------------------------------------------------------------------
*/

$maxCapacity = (int) (
    $event["max_capacity"] ?? 0
);

$isFull =
    $maxCapacity > 0 &&
    $registeredCount >= $maxCapacity;


/*
|--------------------------------------------------------------------------
| Registration Deadline
|--------------------------------------------------------------------------
*/

$deadlinePassed = false;

if (!empty($event["registration_deadline"])) {

    $deadlinePassed =
        date("Y-m-d") >
        $event["registration_deadline"];

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
        <?= htmlspecialchars($event["title"]) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

    <style>

        .event-details-wrapper {
            max-width: 950px;
            margin: 50px auto;
            padding: 0 20px;
        }
        .back-button {
            display: inline-block;
            margin-bottom: 25px;
            padding: 10px 18px;
            text-decoration: none;
            border-radius: 8px;
            border: 1px solid #8b5e3c;
            color: #8b5e3c;
            background: #ffffff;
            transition: 0.2s ease;
        }

        .back-button:hover {
            background: #8b5e3c;
            color: #ffffff;
        }

        .event-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid #eeeeee;
        }

        .event-title {
            font-size: 34px;
            margin-bottom: 15px;
            color: #4a2c1d;
        }

        .event-status {
            display: inline-block;
            padding: 7px 14px;
            border-radius: 20px;
            background: #e8f5e9;
            color: #2e7d32;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 25px;
        }

        .event-description {
            font-size: 16px;
            line-height: 1.8;
            color: #555555;
            margin-bottom: 35px;
        }

        .event-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
        }

        .event-info {
            background: #f8f5f2;
            border-radius: 10px;
            padding: 18px;
        }

        .event-info-label {
            display: block;
            font-size: 13px;
            color: #777777;
            margin-bottom: 6px;
        }

        .event-info-value {
            font-size: 16px;
            font-weight: 600;
            color: #4a2c1d;
        }

        .registration-area {
            margin-top: 30px;
        }

        .register-button {
            display: inline-block;
            padding: 12px 24px;
            background: #8b5e3c;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: 0.2s ease;
        }

        .register-button:hover {
            background: #6f472d;
        }

        .cancel-button {
            display: inline-block;
            margin-left: 10px;
            padding: 12px 24px;
            background: #ffffff;
            color: #b3261e;
            text-decoration: none;
            border: 1px solid #b3261e;
            border-radius: 8px;
            font-weight: 600;
            transition: 0.2s ease;
        }

        .cancel-button:hover {
            background: #b3261e;
            color: #ffffff;
        }

        .registered-message {
            display: inline-block;
            padding: 12px 18px;
            background: #e8f5e9;
            color: #2e7d32;
            border-radius: 8px;
            font-weight: 600;
        }

        .event-message {
            margin-bottom: 25px;
            padding: 12px 16px;
            border-radius: 8px;
            font-weight: 600;
        }

        .success-message {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .error-message {
            background: #ffebee;
            color: #b3261e;
        }

        @media (max-width: 700px) {

            .event-card {
                padding: 25px;
            }

            .event-title {
                font-size: 27px;
            }

            .event-info-grid {
                grid-template-columns: 1fr;
            }

            .cancel-button {
                margin-left: 0;
                margin-top: 10px;
            }

        }

    </style>

</head>


<body>


<div class="event-details-wrapper">


    <a
        href="events.php"
        class="back-button"
    >
        ← Back to Events
    </a>


    <div class="event-card">


        <!-- SUCCESS MESSAGE -->

        <?php if (isset($_GET["registered"])): ?>

            <div class="event-message success-message">

                You have successfully registered for this event.
                </div>

        <?php endif; ?>


        <!-- CANCEL MESSAGE -->

        <?php if (isset($_GET["cancelled"])): ?>

            <div class="event-message success-message">

                Your event registration has been cancelled.

            </div>

        <?php endif; ?>


        <!-- ERROR MESSAGE -->

        <?php if (isset($_GET["error"])): ?>

            <div class="event-message error-message">

                <?php if ($_GET["error"] === "already_registered"): ?>

                    You are already registered for this event.

                <?php elseif ($_GET["error"] === "full"): ?>

                    This event is already full.

                <?php elseif ($_GET["error"] === "not_registered"): ?>

                    You are not currently registered for this event.

                <?php else: ?>

                    Something went wrong.

                <?php endif; ?>

            </div>

        <?php endif; ?>


        <!-- EVENT TITLE -->

        <h1 class="event-title">

            <?= htmlspecialchars(
                $event["title"]
            ) ?>

        </h1>


        <!-- EVENT STATUS -->

        <span class="event-status">

            <?= htmlspecialchars(
                $event["status"]
            ) ?>

        </span>


        <!-- DESCRIPTION -->

        <div class="event-description">

            <?= nl2br(
                htmlspecialchars(
                    $event["description"]
                )
            ) ?>

        </div>


        <!-- EVENT INFORMATION -->

        <div class="event-info-grid">


            <div class="event-info">

                <span class="event-info-label">
                    Date
                </span>

                <span class="event-info-value">

                    <?= htmlspecialchars(
                        $event["event_date"]
                    ) ?>

                </span>

            </div>


            <div class="event-info">

                <span class="event-info-label">
                    Time
                </span>

                <span class="event-info-value">

                    <?= htmlspecialchars(
                        $event["start_time"]
                    ) ?>

                    -

                    <?= htmlspecialchars(
                        $event["end_time"]
                    ) ?>

                </span>

            </div>


            <div class="event-info">

                <span class="event-info-label">
                    Location
                </span>

                <span class="event-info-value">

                    <?= htmlspecialchars(
                        $event["location"]
                    ) ?>

                </span>

            </div>


            <div class="event-info">

                <span class="event-info-label">
                    Registration Deadline
                </span>

                <span class="event-info-value">

                    <?= htmlspecialchars(
                        $event["registration_deadline"]
                    ) ?>

                </span>

            </div>


            <div class="event-info">

                <span class="event-info-label">
                    Maximum Capacity
                </span>

                <span class="event-info-value">

                    <?= htmlspecialchars(
                        $event["max_capacity"]
                    ) ?>

                </span>

            </div>


            <div class="event-info">

                <span class="event-info-label">
                    Registered
                </span>

                <span class="event-info-value">

                    <?= $registeredCount ?>

                    <?php if ($maxCapacity > 0): ?>

                        /
                        <?= $maxCapacity ?>

                    <?php else: ?>

                        / Unlimited

                    <?php endif; ?>

                </span>

            </div>


        </div>


        <!-- REGISTRATION AREA -->
         <div class="registration-area">


            <?php if (
                strtolower($registrationStatus)
                === "registered"
            ): ?>


                <span class="registered-message">

                    ✓ You are registered

                </span>


                <a
                    href="cancel-registration.php?id=<?= $eventId ?>"
                    class="cancel-button"
                    onclick="return confirm('Are you sure you want to cancel your registration?');"
                >

                    Cancel Registration

                </a>


            <?php elseif ($deadlinePassed): ?>


                <span class="event-message error-message">

                    Registration has closed.

                </span>


            <?php elseif ($isFull): ?>


                <span class="event-message error-message">

                    This event is full.

                </span>


            <?php else: ?>


                <a
                    href="register.php?id=<?= $eventId ?>"
                    class="register-button"
                >

                    Register for Event

                </a>


            <?php endif; ?>


        </div>


    </div>


</div>


</body>

</html>