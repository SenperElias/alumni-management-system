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

/*
|--------------------------------------------------------------------------
| Check Event Exists
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT event_id
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

$stmt->close();

/*
|--------------------------------------------------------------------------
| Delete Event Registrations First
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    DELETE FROM event_registrations
    WHERE event_id = ?
");

$stmt->bind_param("i", $eventId);
$stmt->execute();
$stmt->close();

/*
|--------------------------------------------------------------------------
| Delete Event
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    DELETE FROM events
    WHERE event_id = ?
");

$stmt->bind_param("i", $eventId);

if ($stmt->execute()) {

    $stmt->close();

    header("Location: index.php?deleted=1");
    exit;

} else {

    $stmt->close();

    die("Unable to delete event.");
}
?>