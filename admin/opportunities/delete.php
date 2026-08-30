<?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";

/*
|--------------------------------------------------------------------------
| ADMIN ACCESS
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
verify_csrf_token();

/*
|--------------------------------------------------------------------------
| ONLY POST REQUESTS CAN DELETE
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php?error=invalid_request");
    exit;
}

/*
|--------------------------------------------------------------------------
| CSRF PROTECTION
|--------------------------------------------------------------------------
*/

verify_csrf_token();

/*
|--------------------------------------------------------------------------
| GET OPPORTUNITY ID FROM POST
|--------------------------------------------------------------------------
*/

$opportunity_id = isset($_POST["opportunity_id"])
    ? (int) $_POST["opportunity_id"]
    : 0;

if ($opportunity_id <= 0) {
    header("Location: index.php?error=invalid_id");
    exit;
}

/*
|--------------------------------------------------------------------------
| CHECK OPPORTUNITY EXISTS
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT opportunity_id
    FROM opportunities
    WHERE opportunity_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    header("Location: index.php?error=database");
    exit;
}

$stmt->bind_param("i", $opportunity_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();

    header("Location: index.php?error=not_found");
    exit;
}

$stmt->close();

/*
|--------------------------------------------------------------------------
| DELETE OPPORTUNITY
|--------------------------------------------------------------------------
*/

$sql = "
    DELETE FROM opportunities
    WHERE opportunity_id = ?
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    header("Location: index.php?error=database");
    exit;
}

$stmt->bind_param("i", $opportunity_id);

if ($stmt->execute()) {

    $stmt->close();

    header("Location: index.php?success=deleted");
    exit;

} else {

    $stmt->close();

    header("Location: index.php?error=delete_failed");
    exit;
}