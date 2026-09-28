<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load project configuration for BASE_URL
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';


/*
|--------------------------------------------------------------------------
| Session Timeout
|--------------------------------------------------------------------------
*/

// Default session timeout: 30 minutes
$sessionTimeoutMinutes = 30;

// Load configured session timeout
$settingsStmt = $conn->prepare("
    SELECT setting_value
    FROM system_settings
    WHERE setting_key = 'session_timeout'
    LIMIT 1
");

if ($settingsStmt) {

    $settingsStmt->execute();

    $settingsResult = $settingsStmt->get_result();

    if ($setting = $settingsResult->fetch_assoc()) {

        $configuredTimeout = (int) $setting["setting_value"];

        if (
            $configuredTimeout >= 5 &&
            $configuredTimeout <= 480
        ) {
            $sessionTimeoutMinutes = $configuredTimeout;
        }
    }

    $settingsStmt->close();
}

// Convert minutes to seconds
$session_timeout = $sessionTimeoutMinutes * 60;


/*
|--------------------------------------------------------------------------
| Check Login
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {

    header(
        "Location: "
        . BASE_URL
        . "auth/login.php"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| Maintenance Mode
|--------------------------------------------------------------------------
|
| System Administrators can always access the system.
| Other authenticated users are redirected to the
| maintenance page when maintenance mode is enabled.
|
*/

$maintenanceMode = false;

$maintenanceStmt = $conn->prepare("
    SELECT setting_value
    FROM system_settings
    WHERE setting_key = 'maintenance_mode'
    LIMIT 1
");

if ($maintenanceStmt) {

    $maintenanceStmt->execute();

    $maintenanceResult = $maintenanceStmt->get_result();

    if ($maintenanceSetting = $maintenanceResult->fetch_assoc()) {

        $maintenanceMode =
            $maintenanceSetting["setting_value"] === "1";
    }

    $maintenanceStmt->close();
}


/*
 * System Administrator bypass
 */

if (
    $maintenanceMode &&
    (
        !isset($_SESSION["role"]) ||
        $_SESSION["role"] !== "system_admin"
    )
) {

    header(
        "Location: "
        . BASE_URL
        . "public/maintenance.php"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| Inactivity Timeout
|--------------------------------------------------------------------------
*/

if (
    isset($_SESSION["last_activity"]) &&
    (
        time() -
        $_SESSION["last_activity"]
    ) > $session_timeout
) {

    // Clear and destroy the session
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {

        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();

    header(
        "Location: "
        . BASE_URL
        . "auth/login.php?timeout=1"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| Update Last Activity
|--------------------------------------------------------------------------
*/

$_SESSION["last_activity"] = time();