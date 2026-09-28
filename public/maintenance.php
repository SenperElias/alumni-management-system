<?php

session_start();

require_once "../config/database.php";
require_once "../config/config.php";
require_once "../includes/functions.php";


/*
|--------------------------------------------------------------------------
| Default Maintenance Message
|--------------------------------------------------------------------------
*/

$maintenanceMessage =
    "The system is currently undergoing maintenance. Please try again later.";


/*
|--------------------------------------------------------------------------
| Load Maintenance Message
|--------------------------------------------------------------------------
*/

$settingsStmt = $conn->prepare("
    SELECT setting_value
    FROM system_settings
    WHERE setting_key = 'maintenance_message'
    LIMIT 1
");

if ($settingsStmt) {

    $settingsStmt->execute();

    $settingsResult = $settingsStmt->get_result();

    if ($setting = $settingsResult->fetch_assoc()) {

        if (trim($setting["setting_value"]) !== "") {

            $maintenanceMessage =
                $setting["setting_value"];
        }
    }

    $settingsStmt->close();
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
        System Maintenance |
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

<div class="login-container">

    <div class="login-card">

        <h1>
            System Maintenance
        </h1>

        <p>
            <?= nl2br(e($maintenanceMessage)) ?>
        </p>

        <p class="login-back">

            <a href="../auth/login.php">
                ← Back to Login
            </a>

        </p>

    </div>

</div>

</body>

</html>