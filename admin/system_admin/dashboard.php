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

$error = "";
$totalUsers = 0;
$totalAlumni = 0;
$totalAdmins = 0;

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