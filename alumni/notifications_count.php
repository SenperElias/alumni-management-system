<?php

session_start();

require_once "../config/database.php";
require_once "../config/config.php";
require_once "../includes/functions.php";

/*
|--------------------------------------------------------------------------
| Check Login
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "count" => 0
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Check Alumni Role
|--------------------------------------------------------------------------
*/

if ($_SESSION["role"] !== "alumni") {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "count" => 0
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Get Current User
|--------------------------------------------------------------------------
*/

$userId = (int) $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| Get Unread Notification Count
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT COUNT(*) AS unread_count
    FROM notifications
    WHERE user_id = ?
      AND is_read = 0
");

if (!$stmt) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "count" => 0
    ]);

    exit;
}

$stmt->bind_param("i", $userId);

if (!$stmt->execute()) {
    $stmt->close();

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "count" => 0
    ]);

    exit;
}

$result = $stmt->get_result();

$row = $result->fetch_assoc();

$unreadCount = (int) ($row["unread_count"] ?? 0);

$stmt->close();

/*
|--------------------------------------------------------------------------
| Return JSON
|--------------------------------------------------------------------------
*/

header("Content-Type: application/json");

echo json_encode([
    "success" => true,
    "count" => $unreadCount
]);

exit;