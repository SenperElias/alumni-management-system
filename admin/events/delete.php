<?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";

/* ---------------------------------------------------------
   ADMIN ACCESS
--------------------------------------------------------- */

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "admin") {
    header("Location: ../../index.php");
    exit;
}

/* ---------------------------------------------------------
   DELETE ONLY THROUGH POST
--------------------------------------------------------- */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

/* ---------------------------------------------------------
   CSRF PROTECTION
--------------------------------------------------------- */

verify_csrf_token();

/* ---------------------------------------------------------
   GET EVENT ID FROM POST
--------------------------------------------------------- */

$eventId = (int) ($_POST["id"] ?? 0);

if ($eventId <= 0) {
    header("Location: index.php");
    exit;
}

/* ---------------------------------------------------------
   CHECK EVENT EXISTS
--------------------------------------------------------- */

$stmt = $conn->prepare("
    SELECT event_id
    FROM events
    WHERE event_id = ?
    LIMIT 1
");

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $eventId);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();
    die("Event not found.");
}

$stmt->close();

/* ---------------------------------------------------------
   DELETE EVENT REGISTRATIONS FIRST
--------------------------------------------------------- */

$stmt = $conn->prepare("
    DELETE FROM event_registrations
    WHERE event_id = ?
");

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $eventId);
$stmt->execute();
$stmt->close();

/* ---------------------------------------------------------
   DELETE EVENT
--------------------------------------------------------- */

$stmt = $conn->prepare("
    DELETE FROM events
    WHERE event_id = ?
");

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $eventId);

if ($stmt->execute()) {
    $stmt->close();

    header("Location: index.php?deleted=1");
    exit;
} else {
    $error = $stmt->error;
    $stmt->close();

    die("Unable to delete event: " . $error);
}
?>