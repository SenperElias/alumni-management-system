<?php

session_start();

require_once "../config/database.php";
require_once "../config/config.php";
require_once "../includes/functions.php";

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "alumni") {
    header("Location: ../index.php");
    exit;
}

requirePasswordChange();

$userId = (int) $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| Only POST Requests Allowed
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: employment.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| CSRF Protection
|--------------------------------------------------------------------------
*/

if (!verify_csrf_token()) {
    die("Invalid security token.");
}

/*
|--------------------------------------------------------------------------
| Get Employment ID
|--------------------------------------------------------------------------
*/

$employmentId = (int) ($_POST["employment_id"] ?? 0);

if ($employmentId <= 0) {
    die("Invalid employment ID.");
}

/*
|--------------------------------------------------------------------------
| Get Alumni ID
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT alumni_id
    FROM alumni
    WHERE user_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();
    die("Alumni profile not found.");
}

$alumni = $result->fetch_assoc();
$alumniId = (int) $alumni["alumni_id"];

$stmt->close();

/*
|--------------------------------------------------------------------------
| Delete Employment Record
|--------------------------------------------------------------------------
|
| IMPORTANT:
| employment_id AND alumni_id are checked together.
|
| This prevents an alumni from deleting another alumni's
| employment record.
|
*/

$stmt = $conn->prepare("
    DELETE FROM employment
    WHERE employment_id = ?
      AND alumni_id = ?
");

$stmt->bind_param(
    "ii",
    $employmentId,
    $alumniId
);

if (!$stmt->execute()) {
    $stmt->close();
    die("Unable to delete employment record.");
}

/*
|--------------------------------------------------------------------------
| Check Whether Record Was Deleted
|--------------------------------------------------------------------------
*/

if ($stmt->affected_rows !== 1) {
    $stmt->close();
    die("Employment record not found or you are not authorized to delete it.");
}

$stmt->close();

/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

header("Location: employment.php?deleted=1");
exit;