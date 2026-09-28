<?php

session_start();

require_once "../../../config/config.php";

// System Admin access only
if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "system_admin"
) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Backup Directory
|--------------------------------------------------------------------------
*/

$backupDirectory = __DIR__ . "/../../../backups/";

/*
|--------------------------------------------------------------------------
| Get Requested File
|--------------------------------------------------------------------------
*/

$filename = basename($_GET["file"] ?? "");

/*
|--------------------------------------------------------------------------
| Validate Backup Filename
|--------------------------------------------------------------------------
*/

if (
    $filename === "" ||
    !preg_match(
        '/^alumni_management_backup_[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{2}-[0-9]{2}-[0-9]{2}\.sql$/',
        $filename
    )
) {
    http_response_code(400);
    exit("Invalid backup file.");
}

/*
|--------------------------------------------------------------------------
| Build Safe File Path
|--------------------------------------------------------------------------
*/

$backupPath = $backupDirectory . $filename;

/*
|--------------------------------------------------------------------------
| Verify File Exists
|--------------------------------------------------------------------------
*/

if (!is_file($backupPath)) {
    http_response_code(404);
    exit("Backup file not found.");
}

/*
|--------------------------------------------------------------------------
| Download Backup
|--------------------------------------------------------------------------
*/

header("Content-Description: File Transfer");
header("Content-Type: application/sql");
header(
    'Content-Disposition: attachment; filename="' .
    $filename .
    '"'
);
header("Content-Length: " . filesize($backupPath));
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: public");

readfile($backupPath);
exit;