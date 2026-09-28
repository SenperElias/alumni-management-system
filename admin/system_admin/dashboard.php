 <?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "system_admin") {
    header("Location: ../../index.php");
    exit;
}

requirePasswordChange();

$error = "";

$totalUsers = 0;
$totalAlumni = 0;
$totalAdmins = 0;

/*
|--------------------------------------------------------------------------
| Existing System Statistics
|--------------------------------------------------------------------------
*/

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM users
");

if ($result) {

    $row = $result->fetch_assoc();

    $totalUsers = (int) $row["total"];

} else {

    $error = "Unable to load system statistics.";
}


$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM users
    WHERE role = 'alumni'
");

if ($result) {

    $row = $result->fetch_assoc();

    $totalAlumni = (int) $row["total"];
}


$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM users
    WHERE role IN ('admin', 'system_admin')
");

if ($result) {

    $row = $result->fetch_assoc();

    $totalAdmins = (int) $row["total"];
}

/*
|--------------------------------------------------------------------------
| 4E-1 SYSTEM HEALTH
|--------------------------------------------------------------------------
*/

/*
 * Database Health
 *
 * The dashboard already has an active database connection.
 * mysqli->ping() confirms that the connection is still available.
 */

$databaseStatus = "Unavailable";

if ($conn && $conn->ping()) {
    $databaseStatus = "Connected";
}


/*
 * Backup Directory
 *
 * The dashboard is located at:
 *
 * admin/system_admin/dashboard.php
 *
 * Therefore:
 *
 * ../../backups/
 *
 * points to the existing project backup directory.
 */

$backupDirectory = __DIR__ . "/../../backups/";

$backupSystemStatus = "Unavailable";
$backupStorageStatus = "Not accessible";
$backupFileCount = 0;
$latestBackupName = "";
$latestBackupTime = null;

if (is_dir($backupDirectory)) {

    $backupSystemStatus = "Available";

    if (is_writable($backupDirectory)) {

        $backupStorageStatus = "Writable";

    } else {

        $backupStorageStatus = "Not writable";
    }


    /*
     * Find valid database backup files.
     *
     * This matches the same filename pattern used
     * by the Backup & Recovery page.
     */

    $backupFiles = scandir($backupDirectory);

    if ($backupFiles !== false) {

        foreach ($backupFiles as $file) {

            if (
                $file === "." ||
                $file === ".."
            ) {
                continue;
            }


            if (
                !preg_match(
                    '/^alumni_management_backup_[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{2}-[0-9]{2}-[0-9]{2}\.sql$/',
                    $file
                )
            ) {
                continue;
            }


            $filePath = $backupDirectory . $file;


            if (
                !is_file($filePath) ||
                filesize($filePath) <= 0
            ) {
                continue;
            }


            $backupFileCount++;

            $backupTime = filemtime($filePath);

            if (
                $backupTime !== false &&
                (
                    $latestBackupTime === null ||
                    $backupTime > $latestBackupTime
                )
            ) {

                $latestBackupTime = $backupTime;
                $latestBackupName = $file;
            }
        }
    }
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
        System Administrator Dashboard |
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

</head>

<body class="admin-body">

<div class="admin-layout">
 <?php require_once __DIR__ . "/sidebar.php"; ?>

    <main class="admin-main">

        <header class="admin-topbar">

            <div>

                <h1>
                    System Administrator Dashboard
                </h1>

                <p>
                    Manage the technical operation, security, and configuration of the alumni management system.
                </p>

            </div>

        </header>


        <section class="dashboard-content">

            <?php if ($error !== ""): ?>

                <div class="error-message">

                    <?= e($error) ?>

                </div>

            <?php endif; ?>


            <!-- Existing System Statistics -->

            <div class="stats-grid">

                <div class="stat-card">

                    <span>
                        Total Users
                    </span>

                    <strong>
                        <?= $totalUsers ?>
                    </strong>

                </div>


                <div class="stat-card">

                    <span>
                        Alumni Accounts
                    </span>

                    <strong>
                        <?= $totalAlumni ?>
                    </strong>

                </div>


                <div class="stat-card">

                    <span>
                        Administrative Accounts
                    </span>

                    <strong>
                        <?= $totalAdmins ?>
                    </strong>

                </div>

            </div>


            <!-- System Health -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            System Health
                        </h2>

                        <p>
                            Monitor the basic technical status of the system.
                        </p>

                    </div>

                </div>


                <div class="quick-stats">

                    <div>

                        <span>
                            Database
                        </span>

                        <strong>
                            <?= e($databaseStatus) ?>
                        </strong>

                    </div>


                    <div>

                        <span>
                            Backup System
                        </span>

                        <strong>
                            <?= e($backupSystemStatus) ?>
                        </strong>

                    </div>


                    <div>

                        <span>
                            Backup Files
                        </span>

                        <strong>
                            <?= $backupFileCount ?>
                        </strong>

                    </div>


                    <div>

                        <span>
                            Backup Storage
                        </span>

                        <strong>
                            <?= e($backupStorageStatus) ?>
                        </strong>

                    </div>


                    <div>

                        <span>
                            Last Backup
                        </span>

                        <strong>

                            <?php if ($latestBackupTime !== null): ?>

                                <?= e(
                                    date(
                                        "Y-m-d H:i:s",
                                        $latestBackupTime
                                    )
                                ) ?>

                            <?php else: ?>

                                No valid backup found

                            <?php endif; ?>

                        </strong>

                    </div>

                </div>


                <?php if ($latestBackupName !== ""): ?>

                    <p style="margin-top: 15px;">
 <strong>
                            Latest Backup:
                        </strong>

                        <?= e($latestBackupName) ?>

                    </p>

                <?php endif; ?>

            </div>


            <!-- System Management -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            System Management
                        </h2>

                        <p>
                            Technical administration of the system.
                        </p>

                    </div>

                </div>


                <div class="quick-actions">

                    <a
                        href="users/index.php"
                        class="secondary-button"
                    >
                        Manage Users
                    </a>


                    <a
                        href="settings/index.php"
                        class="secondary-button"
                    >
                        System Settings
                    </a>

                </div>

            </div>


            <!-- Security -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Security
                        </h2>

                        <p>
                            Monitor important system activity and security records.
                        </p>

                    </div>

                </div>


                <div class="quick-actions">

                    <a
                        href="../audit/index.php"
                        class="secondary-button"
                    >
                        View Audit Logs
                    </a>

                </div>

            </div>

        </section>

    </main>

</div>

</body>

</html>