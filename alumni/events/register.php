<?php
session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "alumni") {
    header("Location: ../../index.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];
$eventId = (int) ($_GET["id"] ?? 0);

if ($eventId <= 0) {
    die("Invalid event.");
}

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
        registration_deadline,
        max_capacity,
        status
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

/*
|--------------------------------------------------------------------------
| Check Approval
|--------------------------------------------------------------------------
*/

if ($event["status"] !== "published") {
    die("This event is not available for registration.");
}

/*
|--------------------------------------------------------------------------
| Check Registration Deadline
|--------------------------------------------------------------------------
*/

if (
    !empty($event["registration_deadline"]) &&
    date("Y-m-d") > $event["registration_deadline"]
) {
    die("Registration for this event has closed.");
}

/*
|--------------------------------------------------------------------------
| Check Existing Registration
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT registration_id
    FROM event_registrations
    WHERE event_id = ?
    AND alumni_id = ?
    AND registration_status = 'Registered'
    LIMIT 1
");

$stmt->bind_param(
    "ii",
    $eventId,
    $alumniId
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $stmt->close();

    header(
        "Location: view-event.php?id=" .
        $eventId .
        "&error=already_registered"
    );

    exit;
}

$stmt->close();

/*
|--------------------------------------------------------------------------
| Check Capacity
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT COUNT(*) AS registered_count
    FROM event_registrations
    WHERE event_id = ?
    AND registration_status = 'Registered'
");

$stmt->bind_param("i", $eventId);
$stmt->execute();

$result = $stmt->get_result();

$countData = $result->fetch_assoc();

$stmt->close();

$registeredCount = (int) $countData["registered_count"];

$maxCapacity =
    $event["max_capacity"] !== null
    ? (int) $event["max_capacity"]
    : 0;

if (
    $maxCapacity > 0 &&
    $registeredCount >= $maxCapacity
) {
    header(
        "Location: view-event.php?id=" .
        $eventId .
        "&error=full"
    );

    exit;
}

/*--------------------------------------------------------------------------
| Register Alumni
*--------------------------------------------------------------------------*/

/* Reactivate a previous cancelled registration */
$stmt = $conn->prepare("
    UPDATE event_registrations
    SET
        registration_status = 'Registered',
        registered_at = NOW(),
        cancelled_at = NULL
    WHERE event_id = ?
      AND alumni_id = ?
      AND registration_status = 'Cancelled'
");

$stmt->bind_param(
    "ii",
    $eventId,
    $alumniId
);

$stmt->execute();

$reactivated = $stmt->affected_rows;

$stmt->close();

/* If no cancelled registration exists, create a new registration */
if ($reactivated === 0) {

    $stmt = $conn->prepare("
        INSERT INTO event_registrations (
            event_id,
            alumni_id,
            registration_status,
            registered_at
        )
        VALUES (?, ?, 'Registered', NOW())
    ");

    $stmt->bind_param(
        "ii",
        $eventId,
        $alumniId
    );

    if (!$stmt->execute()) {
        $stmt->close();
        die("Unable to register for this event.");
    }

    $stmt->close();
}

/* Registration successful */
header(
    "Location: view-event.php?id=" .
    $eventId .
    "&registered=1"
);
exit;