 <?php

session_start();

require_once "../../../config/database.php";
require_once "../../../config/config.php";
require_once "../../../includes/functions.php";

// System Admin access only
if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "system_admin"
) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];

$successMessage = "";
$errorMessage = "";

/*
|--------------------------------------------------------------------------
| Backup Directory
|--------------------------------------------------------------------------
*/

$backupDirectory = __DIR__ . "/../../../backups/";

/*
|--------------------------------------------------------------------------
| Create Backup Directory If Missing
|--------------------------------------------------------------------------
*/

if (!is_dir($backupDirectory)) {

    if (!mkdir($backupDirectory, 0755, true)) {
        $errorMessage = "Unable to create the backup directory.";
    }
}

/*
|--------------------------------------------------------------------------
| CSRF Token
|--------------------------------------------------------------------------
*/

if (empty($_SESSION["backup_csrf_token"])) {
    $_SESSION["backup_csrf_token"] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION["backup_csrf_token"];

/*
|--------------------------------------------------------------------------
| Create Database Backup
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["action"]) &&
    $_POST["action"] === "create_backup"
) {

    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($csrfToken, $_POST["csrf_token"])
    ) {

        $errorMessage =
            "Invalid security token. Please refresh the page and try again.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Database Configuration
        |--------------------------------------------------------------------------
        */

        $databaseHost = "localhost";
        $databaseUser = "root";
        $databasePassword = "";
        $databaseName = "alumni_management";

        /*
        |--------------------------------------------------------------------------
        | Backup Filename
        |--------------------------------------------------------------------------
        */

        $backupFilename =
            "alumni_management_backup_" .
            date("Y-m-d_H-i-s") .
            ".sql";

        $backupPath = $backupDirectory . $backupFilename;

        /*
        |--------------------------------------------------------------------------
        | mysqldump Path
        |--------------------------------------------------------------------------
        */

        $mysqldumpPath =
            "C:\\xaampp\\mysql\\bin\\mysqldump.exe";

        /*
        |--------------------------------------------------------------------------
        | Check mysqldump
        |--------------------------------------------------------------------------
        */

        if (!file_exists($mysqldumpPath)) {

            $errorMessage =
                "mysqldump was not found at: " .
                $mysqldumpPath;

        } else {

            /*
            |--------------------------------------------------------------------------
            | Build mysqldump Command
            |--------------------------------------------------------------------------
            */

            $command =
                '"' . $mysqldumpPath . '"' .
                ' --host=' . escapeshellarg($databaseHost) .
                ' --user=' . escapeshellarg($databaseUser) .
                ' --password=' . escapeshellarg($databasePassword) .
                ' ' . escapeshellarg($databaseName) .
                ' --result-file=' . escapeshellarg($backupPath) .
                ' 2>&1';
 /*
            |--------------------------------------------------------------------------
            | Execute Backup
            |--------------------------------------------------------------------------
            */

            $output = [];
            $returnCode = 0;

            exec(
                $command,
                $output,
                $returnCode
            );

            /*
            |--------------------------------------------------------------------------
            | Verify Backup
            |--------------------------------------------------------------------------
            */

            if (
                $returnCode === 0 &&
                file_exists($backupPath) &&
                filesize($backupPath) > 0
            ) {

                logAudit(
                    $conn,
                    $userId,
                    "CREATE",
                    "database_backup",
                    0,
                    "System Administrator created database backup: " .
                    $backupFilename
                );

                $successMessage =
                    "Database backup created successfully.";

            } else {

                if (file_exists($backupPath)) {
                    unlink($backupPath);
                }

                $errorDetails = !empty($output)
                    ? implode(" ", $output)
                    : "Unknown mysqldump error.";

                $errorMessage =
                    "Unable to create the database backup. " .
                    $errorDetails;
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| Delete Backup
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["action"]) &&
    $_POST["action"] === "delete_backup"
) {

    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($csrfToken, $_POST["csrf_token"])
    ) {

        $errorMessage =
            "Invalid security token. Please refresh the page and try again.";

    } else {

        $filename = basename($_POST["filename"] ?? "");

        if (
            $filename === "" ||
            !preg_match(
                '/^alumni_management_backup_[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{2}-[0-9]{2}-[0-9]{2}\.sql$/',
                $filename
            )
        ) {

            $errorMessage =
                "Invalid backup file.";

        } else {

            $backupPath = $backupDirectory . $filename;

            if (file_exists($backupPath)) {

                if (unlink($backupPath)) {

                    logAudit(
                        $conn,
                        $userId,
                        "DELETE",
                        "database_backup",
                        0,
                        "System Administrator deleted database backup: " .
                        $filename
                    );

                    $successMessage =
                        "Backup deleted successfully.";

                } else {

                    $errorMessage =
                        "Unable to delete the backup.";
                }

            } else {

                $errorMessage =
                    "Backup file not found.";
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| Get Backup Files
|--------------------------------------------------------------------------
*/

$backupFiles = [];

if (is_dir($backupDirectory)) {

    $files = scandir($backupDirectory);

    if ($files !== false) {

        foreach ($files as $file) {

            if (
                $file === "." ||
                $file === ".."
            ) {
                continue;
            }

            if (
                preg_match(
                    '/^alumni_management_backup_[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{2}-[0-9]{2}-[0-9]{2}\.sql$/',
                    $file
                )
            ) {
 $filePath = $backupDirectory . $file;

                if (is_file($filePath)) {

                    $backupFiles[] = [
                        "filename" => $file,
                        "size" => filesize($filePath),
                        "created" => filemtime($filePath)
                    ];
                }
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| Sort Newest Backup First
|--------------------------------------------------------------------------
*/

usort(
    $backupFiles,
    function ($a, $b) {
        return $b["created"] <=> $a["created"];
    }
);

/*
|--------------------------------------------------------------------------
| Format File Size
|--------------------------------------------------------------------------
*/

function formatBackupSize($bytes)
{
    if ($bytes < 1024) {
        return $bytes . " B";
    }

    if ($bytes < 1024 * 1024) {
        return round($bytes / 1024, 2) . " KB";
    }

    if ($bytes < 1024 * 1024 * 1024) {
        return round($bytes / (1024 * 1024), 2) . " MB";
    }

    return round(
        $bytes / (1024 * 1024 * 1024),
        2
    ) . " GB";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Backup & Recovery | Alumni Management System
    </title>

    <link
        rel="stylesheet"
        href="../../../assets/css/style.css"
    >

</head>

<body class="admin-body">

<div class="admin-layout">

    <?php include "../sidebar.php"; ?>

    <main class="admin-main">

        <header class="admin-topbar">

            <div>
                <h1>
                    Backup & Recovery
                </h1>

                <p>
                    Manage database backups for the Alumni Management System.
                </p>
            </div>

        </header>

        <section class="dashboard-content">

            <?php if ($successMessage !== ""): ?>

                <div class="success-message">
                    <?= e($successMessage) ?>
                </div>

            <?php endif; ?>

            <?php if ($errorMessage !== ""): ?>

                <div class="error-message">
                    <?= e($errorMessage) ?>
                </div>

            <?php endif; ?>

            <div class="dashboard-card">

                <div class="card-header">

                    <div>
                        <h2>
                            Database Backup
                        </h2>

                        <p>
                            Create a complete backup of the system database.
                        </p>
                    </div>

                </div>

                <form method="POST">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= e($csrfToken) ?>"
                    >

                    <input
                        type="hidden"
                        name="action"
                        value="create_backup"
                    >

                    <button
                        type="submit"
                        class="login-button"
                    >
                        Create Database Backup
                    </button>

                </form>

            </div>

            <div class="dashboard-card">

                <div class="card-header">

                    <div>
                        <h2>
                            Backup History
                        </h2>

                        <p>
                            Previously created database backups.
                        </p>
                    </div>

                </div>

                <?php if (empty($backupFiles)): ?>

                    <p>
                        No database backups have been created yet.
                    </p>

                <?php else: ?>
 <div class="table-responsive">

                        <table
                            style="
                                width: 100%;
                                border-collapse: collapse;
                                table-layout: fixed;
                            "
                        >

                            <colgroup>

                                <col style="width: 42%;">

                                <col style="width: 23%;">

                                <col style="width: 12%;">

                                <col style="width: 23%;">

                            </colgroup>

                            <thead>

                                <tr>

                                    <th
                                        style="
                                            text-align: left;
                                            padding: 12px;
                                        "
                                    >
                                        Backup File
                                    </th>

                                    <th
                                        style="
                                            text-align: left;
                                            padding: 12px;
                                        "
                                    >
                                        Date &amp; Time
                                    </th>

                                    <th
                                        style="
                                            text-align: left;
                                            padding: 12px;
                                        "
                                    >
                                        Size
                                    </th>

                                    <th
                                        style="
                                            text-align: left;
                                            padding: 12px;
                                        "
                                    >
                                        Actions
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach ($backupFiles as $backup): ?>

                                    <tr>

                                        <td
                                            style="
                                                padding: 12px;
                                                word-break: break-word;
                                                vertical-align: middle;
                                            "
                                        >
                                            <?= e($backup["filename"]) ?>
                                        </td>

                                        <td
                                            style="
                                                padding: 12px;
                                                vertical-align: middle;
                                                white-space: nowrap;
                                            "
                                        >
                                            <?= e(
                                                date(
                                                    "Y-m-d H:i:s",
                                                    $backup["created"]
                                                )
                                            ) ?>
                                        </td>

                                        <td

style="
                                                padding: 12px;
                                                vertical-align: middle;
                                                white-space: nowrap;
                                            "
                                        >
                                            <?= e(
                                                formatBackupSize(
                                                    $backup["size"]
                                                )
                                            ) ?>
                                        </td>

                                        <td
                                            style="
                                                padding: 12px;
                                                vertical-align: middle;
                                                white-space: nowrap;
                                            "
                                        >

                                            <a
                                                href="download.php?file=<?= urlencode($backup["filename"]) ?>"
                                                style="
                                                    display: inline-block;
                                                    margin-right: 10px;
                                                "
                                            >
                                                Download
                                            </a>

                                            <form
                                                method="POST"
                                                style="
                                                    display: inline-block;
                                                    margin: 0;
                                                "
                                            >

                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= e($csrfToken) ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="action"
                                                    value="delete_backup"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="filename"
                                                    value="<?= e($backup["filename"]) ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    onclick="return confirm('Are you sure you want to delete this backup?');"
                                                >
                                                    Delete
                                                </button>

                                            </form>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php endif; ?>

            </div>

        </section>

    </main>

</div>

</body>

</html>