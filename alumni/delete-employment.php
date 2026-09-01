<?php

error_reporting(E_ALL);


session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    die("User is not logged in.");
}

if ($_SESSION["role"] !== "alumni") {
    die("Access denied.");
}

$userId = (int) $_SESSION["user_id"];

/* Get alumni ID */

$stmt = $conn->prepare(
    "SELECT alumni_id
     FROM alumni
     WHERE user_id = ?
     LIMIT 1"
);

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Alumni profile not found.");
}

$alumni = $result->fetch_assoc();

$alumniId = (int) $alumni["alumni_id"];

$stmt->close();


/* Get employment ID */

$employmentId = (int) ($_GET["id"] ?? 0);

if ($employmentId <= 0) {
    die("Invalid employment ID.");
}


/* Delete */

$stmt = $conn->prepare(
    "DELETE FROM employment
     WHERE employment_id = ?
     AND alumni_id = ?"
);

$stmt->bind_param(
    "ii",
    $employmentId,
    $alumniId
);

if (!$stmt->execute()) {
    die("Delete failed: " . $stmt->error);
}


/* Check whether anything was actually deleted */

if ($stmt->affected_rows === 0) {

    $stmt->close();

    die(
        "No employment record was deleted. 
        Employment ID: " . $employmentId .
        " | Alumni ID: " . $alumniId
    );
}

$stmt->close();

header("Location: employment.php?deleted=1");
exit;