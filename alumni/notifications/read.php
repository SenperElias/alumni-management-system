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

/*
|--------------------------------------------------------------------------
| Get Notification ID
|--------------------------------------------------------------------------
*/

$notificationId = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;

if ($notificationId <= 0) {
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Mark Notification as Read
|--------------------------------------------------------------------------
|
| The user_id condition is important.
| It prevents one alumni from marking another user's
| notification as read.
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    UPDATE notifications
    SET is_read = 1
    WHERE notification_id = ?
    AND user_id = ?
");

$stmt->bind_param(
    "ii",
    $notificationId,
    $userId
);

$stmt->execute();

$stmt->close();

/*
|--------------------------------------------------------------------------
| Return to Notifications
|--------------------------------------------------------------------------
*/

header("Location: index.php");
exit;