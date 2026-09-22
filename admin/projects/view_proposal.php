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


/*
|--------------------------------------------------------------------------
| GET PROJECT ID
|--------------------------------------------------------------------------
*/

$project_id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$project_id) {

    http_response_code(400);
    exit("Invalid project ID.");

}


/*
|--------------------------------------------------------------------------
| GET PROPOSAL DOCUMENT
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT proposal_document
    FROM projects
    WHERE project_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    http_response_code(500);
    exit("Database error.");

}

$stmt->bind_param(
    "i",
    $project_id
);

$stmt->execute();

$result = $stmt->get_result();

$project = $result->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| CHECK PROJECT
|--------------------------------------------------------------------------
*/

if (!$project) {

    http_response_code(404);
    exit("Project not found.");

}


/*
|--------------------------------------------------------------------------
| CHECK PROPOSAL
|--------------------------------------------------------------------------
*/

if (empty($project["proposal_document"])) {

    http_response_code(404);
    exit("No proposal document is available for this project.");

}


/*
|--------------------------------------------------------------------------
| BUILD SAFE FILE PATH
|--------------------------------------------------------------------------
*/

$file_name = basename(
    $project["proposal_document"]
);

$file_path =
    __DIR__
    . "/../../uploads/project_proposals/"
    . $file_name;


/*
|--------------------------------------------------------------------------
| CHECK FILE
|--------------------------------------------------------------------------
*/

if (!is_file($file_path)) {

    http_response_code(404);
    exit("Proposal document not found.");

}

if (!is_readable($file_path)) {

    http_response_code(403);
    exit("Proposal document cannot be accessed.");

}


/*
|--------------------------------------------------------------------------
| VERIFY PDF MIME TYPE
|--------------------------------------------------------------------------
*/

$finfo = finfo_open(
    FILEINFO_MIME_TYPE
);

$mime_type = finfo_file(
    $finfo,
    $file_path
);

finfo_close($finfo);


if ($mime_type !== "application/pdf") {

    http_response_code(403);
    exit("Invalid proposal document.");

}


/*
|--------------------------------------------------------------------------
| SERVE PDF
|--------------------------------------------------------------------------
*/

header("Content-Type: application/pdf");

header(
    "Content-Length: " . filesize($file_path)
);

header(
    'Content-Disposition: inline; filename="' .
    $file_name .
    '"'
);

header(
    "X-Content-Type-Options: nosniff"
);

readfile($file_path);

exit;

?>