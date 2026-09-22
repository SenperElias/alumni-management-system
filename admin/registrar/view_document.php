<?php

session_start();

require_once "../../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../../auth/login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| REGISTRAR ONLY
|--------------------------------------------------------------------------
*/
if ($_SESSION["role"] !== "registrar") {
    header("Location: /almuni-management-system/public/index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| GET REGISTRATION ID
|--------------------------------------------------------------------------
*/
$registrationId = filter_input(
    INPUT_GET,
    "registration_id",
    FILTER_VALIDATE_INT
);

if (!$registrationId) {
    http_response_code(400);
    exit("Invalid registration ID.");
}

/*
|--------------------------------------------------------------------------
| GET DOCUMENT FROM DATABASE
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("
    SELECT verification_document
    FROM alumni_registrations
    WHERE registration_id = ?
    LIMIT 1
");

if (!$stmt) {
    http_response_code(500);
    exit("Unable to process the request.");
}

$stmt->bind_param("i", $registrationId);
$stmt->execute();

$result = $stmt->get_result();
$registration = $result->fetch_assoc();

$stmt->close();

if (!$registration) {
    http_response_code(404);
    exit("Registration not found.");
}

/*
|--------------------------------------------------------------------------
| CHECK DOCUMENT EXISTS
|--------------------------------------------------------------------------
*/
$fileName = $registration["verification_document"];

if (empty($fileName)) {
    http_response_code(404);
    exit("No verification document was uploaded.");
}

/*
|--------------------------------------------------------------------------
| PREVENT PATH TRAVERSAL
|--------------------------------------------------------------------------
*/
$fileName = basename($fileName);

/*
|--------------------------------------------------------------------------
| PRIVATE DOCUMENT PATH
|--------------------------------------------------------------------------
*/
$projectRoot = dirname(__DIR__, 2);

$uploadDirectory = realpath(
    $projectRoot . "/uploads/verification_documents"
);

$filePath = realpath(
    $uploadDirectory . DIRECTORY_SEPARATOR . $fileName
);

if (
    $filePath === false ||
    $uploadDirectory === false ||
    strpos($filePath, $uploadDirectory . DIRECTORY_SEPARATOR) !== 0
) {
    http_response_code(404);
    exit("Document not found.");
}

/*
|--------------------------------------------------------------------------
| CHECK FILE
|--------------------------------------------------------------------------
*/
if (!is_file($filePath) || !is_readable($filePath)) {
    http_response_code(404);
    exit("Document not found.");
}

/*
|--------------------------------------------------------------------------
| DETERMINE MIME TYPE
|--------------------------------------------------------------------------
*/
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($filePath);

$allowedTypes = [
    "application/pdf",
    "image/jpeg",
    "image/png"
];

if (!in_array($mimeType, $allowedTypes, true)) {
    http_response_code(403);
    exit("Unsupported document type.");
}

/*
|--------------------------------------------------------------------------
| SEND FILE
|--------------------------------------------------------------------------
*/
header("Content-Type: " . $mimeType);
header("Content-Length: " . filesize($filePath));
header("Content-Disposition: inline; filename=\"verification-document\"");
header("X-Content-Type-Options: nosniff");
header("Cache-Control: private, no-store, no-cache, must-revalidate");
header("Pragma: no-cache");

readfile($filePath);
exit;