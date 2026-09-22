<?php

session_start();

require_once "../config/database.php";
require_once "../config/config.php";
require_once "../includes/functions.php";

header("Content-Type: application/json; charset=UTF-8");

$sectionId = filter_input(
    INPUT_GET,
    "section_id",
    FILTER_VALIDATE_INT
);

if (!$sectionId || $sectionId < 1) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid section."
    ]);

    exit;
}

$stmt = $conn->prepare("
    SELECT
        specialization_id,
        specialization_name
    FROM specializations
    WHERE section_id = ?
      AND status = 'active'
    ORDER BY specialization_name ASC
");

if (!$stmt) {

    error_log(
        "Database prepare error in auth/get_specializations.php: " .
        $conn->error
    );

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Unable to load specializations."
    ]);

    exit;
}

$stmt->bind_param("i", $sectionId);

if (!$stmt->execute()) {

    error_log(
        "Database execute error in auth/get_specializations.php: " .
        $stmt->error
    );

    $stmt->close();

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Unable to load specializations."
    ]);

    exit;
}

$result = $stmt->get_result();

$specializations = [];

while ($row = $result->fetch_assoc()) {

    $specializations[] = [
        "specialization_id" => (int) $row["specialization_id"],
        "specialization_name" => $row["specialization_name"]
    ];
}

$stmt->close();

echo json_encode([
    "success" => true,
    "specializations" => $specializations
]);