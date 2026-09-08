<?php

session_start();

require_once "../../../config/database.php";
require_once "../../../config/config.php";
require_once "../../../includes/functions.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "system_admin") {
    header("Location: ../../../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

if (!verify_csrf_token($_POST["csrf_token"] ?? "")) {
    die("Invalid security token.");
}

$user_id = (int) ($_POST["user_id"] ?? 0);

if ($user_id <= 0) {
    die("Invalid user ID.");
}

$current_user_id = (int) $_SESSION["user_id"];

/*
 * Prevent the System Administrator
 * from deactivating their own account.
 */
if ($user_id === $current_user_id) {
    die("You cannot deactivate your own account.");
}

$stmt = $conn->prepare("
    SELECT account_status
    FROM users
    WHERE user_id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

if (!$user) {
    die("User account not found.");
}

$current_status = $user["account_status"];

if ($current_status === "active") {

    $new_status = "inactive";
    $action = "DEACTIVATE";
    $description = "User account deactivated.";

} else {

    $new_status = "active";
    $action = "ACTIVATE";
    $description = "User account activated.";
}

$stmt = $conn->prepare("
    UPDATE users
    SET account_status = ?
    WHERE user_id = ?
");

$stmt->bind_param(
    "si",
    $new_status,
    $user_id
);

if (!$stmt->execute()) {
    die("Unable to update the account status.");
}

$stmt->close();

logAudit(
    $conn,
    $current_user_id,
    $action,
    "users",
    $user_id,
    $description
);

header("Location: index.php");
exit;