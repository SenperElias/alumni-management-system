 <?php

session_start();

require_once "../../config/database.php";
require_once "../../includes/functions.php";


/*
|--------------------------------------------------------------------------
| Access Control
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] !== "admin"
) {
    http_response_code(403);
    exit("Access denied.");
}


/*
|--------------------------------------------------------------------------
| Validate Employment ID
|--------------------------------------------------------------------------
*/

$employmentId = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$employmentId || $employmentId <= 0) {
    http_response_code(400);
    exit("Invalid employment record.");
}


/*
|--------------------------------------------------------------------------
| Get Verification Document
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT verification_document
    FROM employment
    WHERE employment_id = ?
    LIMIT 1
");

if (!$stmt) {
    http_response_code(500);
    exit("Unable to load the document.");
}

$stmt->bind_param("i", $employmentId);
$stmt->execute();

$result = $stmt->get_result();
$employment = $result->fetch_assoc();

$stmt->close();


if (
    !$employment ||
    empty($employment["verification_document"])
) {
    http_response_code(404);
    exit("Verification document not found.");
}


/*
|--------------------------------------------------------------------------
| Secure Filename
|--------------------------------------------------------------------------
*/

$filename = basename(
    $employment["verification_document"]
);


/*
|--------------------------------------------------------------------------
| Employment Document Directory
|--------------------------------------------------------------------------
*/

$uploadDirectory = realpath(
    __DIR__ . "/../../uploads/employment_documents"
);

if ($uploadDirectory === false) {
    http_response_code(500);
    exit("Document storage is unavailable.");
}


/*
|--------------------------------------------------------------------------
| Build and Validate File Path
|--------------------------------------------------------------------------
*/

$filePath = realpath(
    $uploadDirectory . DIRECTORY_SEPARATOR . $filename
);

if (
    $filePath === false ||
    strpos(
        $filePath,
        $uploadDirectory . DIRECTORY_SEPARATOR
    ) !== 0
) {
    http_response_code(404);
    exit("Verification document not found.");
}


/*
|--------------------------------------------------------------------------
| Confirm File
|--------------------------------------------------------------------------
*/

if (!is_file($filePath) || !is_readable($filePath)) {
    http_response_code(404);
    exit("Verification document not found.");
}


/*
|--------------------------------------------------------------------------
| Validate MIME Type
|--------------------------------------------------------------------------
*/

$finfo = finfo_open(FILEINFO_MIME_TYPE);

if ($finfo === false) {
    http_response_code(500);
    exit("Unable to verify the document type.");
}

$mimeType = finfo_file(
    $finfo,
    $filePath
);

finfo_close($finfo);


$allowedMimeTypes = [
    "application/pdf",
    "image/jpeg",
    "image/png"
];


if (!in_array($mimeType, $allowedMimeTypes, true)) {
    http_response_code(403);
    exit("Invalid document type.");
}


/*
|--------------------------------------------------------------------------
| Prevent Browser Caching
|--------------------------------------------------------------------------
*/

header("Cache-Control: private, no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");


/*
|--------------------------------------------------------------------------
| Display Document
|--------------------------------------------------------------------------
*/
 header("Content-Type: " . $mimeType);
header("Content-Length: " . filesize($filePath));
header(
    'Content-Disposition: inline; filename="' .
    $filename .
    '"'
);

readfile($filePath);
exit;