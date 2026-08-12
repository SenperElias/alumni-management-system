<?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";


/*
|--------------------------------------------------------------------------
| Alumni Access
|--------------------------------------------------------------------------
*/

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

$stmt->bind_param(
    "i",
    $userId
);

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
| Find Registration
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


if ($result->num_rows !== 1) {

    $stmt->close();

    header(
        "Location: view-event.php?id=" .
        $eventId .
        "&error=not_registered"
    );

    exit;

}


$registration = $result->fetch_assoc();

$stmt->close();


$registrationId =
    (int) $registration["registration_id"];


/*
|--------------------------------------------------------------------------
| Cancel Registration
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    UPDATE event_registrations
    SET
        registration_status = 'Cancelled',
        cancelled_at = NOW()
    WHERE registration_id = ?
    AND alumni_id = ?
");

$stmt->bind_param(
    "ii",
    $registrationId,
    $alumniId
);


if ($stmt->execute()) {

    $stmt->close();

    header(
        "Location: view-event.php?id=" .
        $eventId .
        "&cancelled=1"
    );

    exit;

}


$stmt->close();

die("Unable to cancel registration.");

?>