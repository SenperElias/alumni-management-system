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

$specializationId = filter_input(
    INPUT_GET,
    "specialization_id",
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

if ($specializationId) {

    $stmt = $conn->prepare("
        SELECT level
        FROM academic_levels
        WHERE section_id = ?
          AND specialization_id = ?
        ORDER BY level ASC
    ");

    if (!$stmt) {

        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "Unable to load levels."
        ]);

        exit;
    }

    $stmt->bind_param(
        "ii",
        $sectionId,
        $specializationId
    );

} else {

    $stmt = $conn->prepare("
        SELECT level
        FROM academic_levels
        WHERE section_id = ?
          AND specialization_id IS NULL
        ORDER BY level ASC
    ");

    if (!$stmt) {

        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "Unable to load levels."
        ]);

        exit;
    }

    $stmt->bind_param(
        "i",
        $sectionId
    );
}

if (!$stmt->execute()) {

    error_log(
        "Database execute error in auth/get_levels.php: " .
        $stmt->error
    );

    $stmt->close();

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Unable to load levels."
    ]);

    exit;
}

$result = $stmt->get_result();

$levels = [];

while ($row = $result->fetch_assoc()) {

    $levels[] = (int) $row["level"];
}

$stmt->close();

echo json_encode([
    "success" => true,
    "levels" => $levels
]);