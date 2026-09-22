<?php

session_start();

require_once "../config/database.php";
require_once "../config/config.php";
require_once "../includes/functions.php";

header("Content-Type: application/json; charset=UTF-8");

$departmentId = filter_input(
    INPUT_GET,
    "department_id",
    FILTER_VALIDATE_INT
);

if (!$departmentId || $departmentId < 1) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid department."
    ]);

    exit;
}

$stmt = $conn->prepare("
    SELECT
        section_id,
        section_name
    FROM sections
    WHERE department_id = ?
      AND status = 'active'
    ORDER BY section_name ASC
");

if (!$stmt) {
    error_log(
        "Database prepare error in auth/get_sections.php: " .
        $conn->error
    );

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Unable to load sections."
    ]);

    exit;
}

$stmt->bind_param("i", $departmentId);

if (!$stmt->execute()) {
    error_log(
        "Database execute error in auth/get_sections.php: " .
        $stmt->error
    );

    $stmt->close();

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Unable to load sections."
    ]);

    exit;
}

$result = $stmt->get_result();

$sections = [];

while ($row = $result->fetch_assoc()) {

    $sections[] = [
        "section_id" => (int) $row["section_id"],
        "section_name" => $row["section_name"]
    ];
}

$stmt->close();

echo json_encode([
    "success" => true,
    "sections" => $sections
]);