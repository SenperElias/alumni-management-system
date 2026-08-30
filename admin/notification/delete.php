<?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";

/*
|--------------------------------------------------------------------------
| Admin Access
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "admin") {
    header("Location: ../../index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Get Notification ID
|--------------------------------------------------------------------------
*/

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
   GET NOTIFICATION ID FROM POST
--------------------------------------------------------- */

$notificationId = (int) ($_POST["id"] ?? 0);

if ($notificationId <= 0) {
    header("Location: index.php");
    exit;
}
/*
|--------------------------------------------------------------------------
| Delete Notification
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    DELETE FROM notifications
    WHERE notification_id = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $notificationId
);

$stmt->execute();

$stmt->close();

/*
|--------------------------------------------------------------------------
| Return to Notification List
|--------------------------------------------------------------------------
*/

header("Location: index.php");
exit;