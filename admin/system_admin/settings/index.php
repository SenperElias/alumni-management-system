 <?php

session_start();

require_once "../../../config/database.php";
require_once "../../../config/config.php";
require_once "../../../includes/functions.php";

/*
|--------------------------------------------------------------------------
| System Administrator Access
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "system_admin") {
    header("Location: ../../../index.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];


/*
|--------------------------------------------------------------------------
| CSRF Token
|--------------------------------------------------------------------------
*/

if (empty($_SESSION["settings_csrf_token"])) {
    $_SESSION["settings_csrf_token"] = bin2hex(
        random_bytes(32)
    );
}

$csrfToken = $_SESSION["settings_csrf_token"];


/*
|--------------------------------------------------------------------------
| Messages
|--------------------------------------------------------------------------
*/

$successMessage = "";
$errorMessage = "";


/*
|--------------------------------------------------------------------------
| Default System Settings
|--------------------------------------------------------------------------
*/

$settings = [
    "site_name" => defined("SITE_NAME") ? SITE_NAME : "",
    "institution_name" => "Taferi Mekonnen Polytechnic Technical College",
    "site_email" => defined("SITE_EMAIL") ? SITE_EMAIL : "",
    "session_timeout" => "30",
    "max_failed_login_attempts" => "5",
    "lockout_duration" => "15",
    "maintenance_mode" => "0",
    "maintenance_message" =>
        "The system is currently undergoing maintenance. Please try again later."
];


/*
|--------------------------------------------------------------------------
| Save Settings
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals(
            $csrfToken,
            $_POST["csrf_token"]
        )
    ) {

        $errorMessage =
            "Invalid security token. Please refresh the page and try again.";

    } else {

        $section = $_POST["settings_section"] ?? "";


        /*
        |--------------------------------------------------------------------------
        | System Configuration
        |--------------------------------------------------------------------------
        */

        if ($section === "system") {

            $systemName =
                trim($_POST["system_name"] ?? "");

            $institutionName =
                trim($_POST["institution_name"] ?? "");

            $siteEmail =
                trim($_POST["site_email"] ?? "");


            if (
                $systemName === "" ||
                $institutionName === "" ||
                $siteEmail === ""
            ) {

                $errorMessage =
                    "System name, institution name, and site email are required.";

            } elseif (
                !filter_var(
                    $siteEmail,
                    FILTER_VALIDATE_EMAIL
                )
            ) {

                $errorMessage =
                    "Please enter a valid site email address.";

            } else {

                $settingsToSave = [
                    "site_name" => $systemName,
                    "institution_name" => $institutionName,
                    "site_email" => $siteEmail
                ];

                $saveStmt = $conn->prepare("
                    INSERT INTO system_settings
                        (
                            setting_key,
                            setting_value,
 updated_by
                        )
                    VALUES
                        (?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        setting_value = VALUES(setting_value),
                        updated_by = VALUES(updated_by)
                ");

                if ($saveStmt) {

                    $saveSuccessful = true;

                    foreach (
                        $settingsToSave
                        as $key => $value
                    ) {

                        $saveStmt->bind_param(
                            "ssi",
                            $key,
                            $value,
                            $userId
                        );

                        if (!$saveStmt->execute()) {

                            $saveSuccessful = false;

                            break;
                        }
                    }

                    $saveStmt->close();


                    if ($saveSuccessful) {

                        logAudit(
                            $conn,
                            $userId,
                            "UPDATE",
                            "system_settings",
                            0,
                            "System Administrator updated system configuration."
                        );

                        $successMessage =
                            "System configuration updated successfully.";

                    } else {

                        $errorMessage =
                            "Unable to save the system configuration.";
                    }

                } else {

                    $errorMessage =
                        "Unable to prepare the settings update.";
                }
            }


        /*
        |--------------------------------------------------------------------------
        | Security Policies
        |--------------------------------------------------------------------------
        */

        } elseif ($section === "security") {

            $sessionTimeout =
                (int) (
                    $_POST["session_timeout"] ?? 30
                );

            $maxFailedAttempts =
                (int) (
                    $_POST["max_failed_login_attempts"] ?? 5
                );

            $lockoutDuration =
                (int) (
                    $_POST["lockout_duration"] ?? 15
                );


            if (
                $sessionTimeout < 5 ||
                $sessionTimeout > 480
            ) {

                $errorMessage =
                    "Session timeout must be between 5 and 480 minutes.";

            } elseif (
                $maxFailedAttempts < 3 ||
                $maxFailedAttempts > 20
            ) {

                $errorMessage =
                    "Maximum failed login attempts must be between 3 and 20.";

            } elseif (
                $lockoutDuration < 1 ||
                $lockoutDuration > 1440
            ) {

                $errorMessage =
                    "Account lockout duration must be between 1 and 1440 minutes.";

            } else {

                $settingsToSave = [

                    "session_timeout" =>
                        (string) $sessionTimeout,

                    "max_failed_login_attempts" =>
                        (string) $maxFailedAttempts,

                    "lockout_duration" =>
                        (string) $lockoutDuration
                ];


                $saveStmt = $conn->prepare("
                    INSERT INTO system_settings
                        (
                            setting_key,
                            setting_value,
                            updated_by
                        )
                    VALUES
                        (?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        setting_value = VALUES(setting_value),
                        updated_by = VALUES(updated_by)
                ");


                if ($saveStmt) {
 $saveSuccessful = true;

                    foreach (
                        $settingsToSave
                        as $key => $value
                    ) {

                        $saveStmt->bind_param(
                            "ssi",
                            $key,
                            $value,
                            $userId
                        );

                        if (!$saveStmt->execute()) {

                            $saveSuccessful = false;

                            break;
                        }
                    }

                    $saveStmt->close();


                    if ($saveSuccessful) {

                        logAudit(
                            $conn,
                            $userId,
                            "UPDATE",
                            "system_settings",
                            0,
                            "System Administrator updated security policies."
                        );

                        $successMessage =
                            "Security policies updated successfully.";

                    } else {

                        $errorMessage =
                            "Unable to save the security policies.";
                    }

                } else {

                    $errorMessage =
                        "Unable to prepare the security policy update.";
                }
            }


        /*
        |--------------------------------------------------------------------------
        | Maintenance Mode
        |--------------------------------------------------------------------------
        */

        } elseif ($section === "maintenance") {

            $maintenanceMode =
                ($_POST["maintenance_mode"] ?? "0") === "1"
                    ? "1"
                    : "0";

            $maintenanceMessage =
                trim(
                    $_POST["maintenance_message"] ?? ""
                );


            if ($maintenanceMessage === "") {

                $errorMessage =
                    "Maintenance message is required.";

            } elseif (mb_strlen($maintenanceMessage) > 500) {

                $errorMessage =
                    "Maintenance message must not exceed 500 characters.";

            } else {

                $settingsToSave = [

                    "maintenance_mode" =>
                        $maintenanceMode,

                    "maintenance_message" =>
                        $maintenanceMessage
                ];


                $saveStmt = $conn->prepare("
                    INSERT INTO system_settings
                        (
                            setting_key,
                            setting_value,
                            updated_by
                        )
                    VALUES
                        (?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        setting_value = VALUES(setting_value),
                        updated_by = VALUES(updated_by)
                ");


                if ($saveStmt) {

                    $saveSuccessful = true;

                    foreach (
                        $settingsToSave
                        as $key => $value
                    ) {

                        $saveStmt->bind_param(
                            "ssi",
                            $key,
                            $value,
                            $userId
                        );

                        if (!$saveStmt->execute()) {

                            $saveSuccessful = false;

                            break;
                        }
                    }

                    $saveStmt->close();


                    if ($saveSuccessful) {

                        $modeDescription =
                            $maintenanceMode === "1"
                                ? "enabled"
                                : "disabled";
 logAudit(
                            $conn,
                            $userId,
                            "UPDATE",
                            "system_settings",
                            0,
                            "System Administrator "
                            . $modeDescription
                            . " maintenance mode."
                        );

                        $successMessage =
                            "Maintenance settings updated successfully.";

                    } else {

                        $errorMessage =
                            "Unable to save the maintenance settings.";
                    }

                } else {

                    $errorMessage =
                        "Unable to prepare the maintenance settings update.";
                }
            }


        } else {

            $errorMessage =
                "Invalid settings section.";
        }
    }
}


/*
|--------------------------------------------------------------------------
| Load Current Settings From Database
|--------------------------------------------------------------------------
*/

$settingsStmt = $conn->prepare("
    SELECT
        setting_key,
        setting_value
    FROM system_settings
    WHERE setting_key IN (
        'site_name',
        'institution_name',
        'site_email',
        'session_timeout',
        'max_failed_login_attempts',
        'lockout_duration',
        'maintenance_mode',
        'maintenance_message'
    )
");


if ($settingsStmt) {

    $settingsStmt->execute();

    $settingsResult =
        $settingsStmt->get_result();

    while (
        $setting =
        $settingsResult->fetch_assoc()
    ) {

        if (
            array_key_exists(
                $setting["setting_key"],
                $settings
            )
        ) {

            $settings[
                $setting["setting_key"]
            ] =
                $setting["setting_value"];
        }
    }

    $settingsStmt->close();
}


/*
|--------------------------------------------------------------------------
| Academic Departments
|--------------------------------------------------------------------------
*/

$totalDepartments = 0;

$departmentStmt = $conn->prepare("
    SELECT COUNT(*) AS total_departments
    FROM departments
    WHERE status = 'active'
");

if ($departmentStmt) {

    $departmentStmt->execute();

    $departmentResult =
        $departmentStmt->get_result();

    if (
        $departmentRow =
        $departmentResult->fetch_assoc()
    ) {

        $totalDepartments =
            (int) $departmentRow["total_departments"];
    }

    $departmentStmt->close();
}


/*
|--------------------------------------------------------------------------
| Get Administrator Information
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        user_id,
        email,
        role,
        account_status,
        created_at,
        updated_at
    FROM users
    WHERE user_id = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $userId
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    $stmt->close();

    header("Location: ../dashboard.php");

    exit;
}

$admin = $result->fetch_assoc();

$stmt->close();

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
        System Settings |
        <?= e($settings["site_name"]) ?>
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
                    System Settings
                </h1>

                <p>
                    Manage system-level information and your administrator account.
                </p>
 </div>

        </header>


        <section class="dashboard-content">
            <?php if ($successMessage !== ""): ?>

    <p>
        <?= e($successMessage) ?>
    </p>

<?php endif; ?>


<?php if ($errorMessage !== ""): ?>

    <p>
        <?= e($errorMessage) ?>
    </p>

<?php endif; ?>


            <!-- System Administrator Profile -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            System Administrator Profile
                        </h2>

                        <p>
                            Your technical administrator account information.
                        </p>

                    </div>

                </div>


                <div class="quick-stats">

                    <div>

                        <span>
                            Email
                        </span>

                        <strong>
                            <?= e($admin["email"]) ?>
                        </strong>

                    </div>


                    <div>

                        <span>
                            Role
                        </span>

                        <strong>
                            System Administrator
                        </strong>

                    </div>


                    <div>

                        <span>
                            Account Status
                        </span>

                        <strong>
                            <?= e($admin["account_status"]) ?>
                        </strong>

                    </div>

                </div>

            </div>


            <!-- System Information -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            System Information
                        </h2>

                        <p>
                            Basic information about the alumni management system.
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
                        name="settings_section"
                        value="system"
                    >


                    <div class="quick-stats">

                        <div>

                            <span>
                                System Name
                            </span>

                            <input
                                type="text"
                                name="system_name"
                                value="<?= e($settings["site_name"]) ?>"
                                style="width: 100%; box-sizing: border-box;"
                                required
                            >

                        </div>


                        <div>

                            <span>
                                Institution
                            </span>

                            <input
                                type="text"
                                name="institution_name"
                                value="<?= e($settings["institution_name"]) ?>"
                                style="width: 100%; box-sizing: border-box;"
                                required
                            >

                        </div>


                        <div>

                            <span>
                                Site Email
                            </span>
 <input
                                type="email"
                                name="site_email"
                                value="<?= e($settings["site_email"]) ?>"
                                style="width: 100%; box-sizing: border-box;"
                                required
                            >

                        </div>


                        <div>

                            <span>
                                Academic Departments
                            </span>

                            <strong>
                                <?= $totalDepartments ?>
                            </strong>

                        </div>

                    </div>


                    <button type="submit">
                        Save System Configuration
                    </button>

                </form>

            </div>


            <!-- Security -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Security & Administration
                        </h2>

                        <p>
                            Configure login protection and session security policies.
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
                        name="settings_section"
                        value="security"
                    >


                    <div class="quick-stats">

                        <div>

                            <span>
                                Session Inactivity Timeout
                            </span>

                            <input
                                type="number"
                                name="session_timeout"
                                value="<?= e($settings["session_timeout"]) ?>"
                                min="5"
                                max="480"
                                style="width: 100%; box-sizing: border-box;"
                                required
                            >

                            <small>
                                Minutes
                            </small>

                        </div>


                        <div>

                            <span>
                                Maximum Failed Login Attempts
                            </span>

                            <input
                                type="number"
                                name="max_failed_login_attempts"
                                value="<?= e($settings["max_failed_login_attempts"]) ?>"
                                min="3"
                                max="20"
                                style="width: 100%; box-sizing: border-box;"
                                required
                            >

                            <small>
                                Attempts before lockout
                            </small>

                        </div>


                        <div>

                            <span>
                                Account Lockout Duration
                            </span>

                            <input
                                type="number"
                                name="lockout_duration"
                                value="<?= e($settings["lockout_duration"]) ?>"
                                min="1"
                                max="1440"
                                style="width: 100%; box-sizing: border-box;"
                                required
                            >

                            <small>
                                Minutes
                            </small>
 </div>


                        <div>

                            <span>
                                Password Minimum Length
                            </span>

                            <strong>
                                8 characters
                            </strong>

                        </div>

                    </div>


                    <button type="submit">
                        Save Security Policies
                    </button>

                </form>

            </div>


            <!-- Maintenance Mode -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Maintenance Mode
                        </h2>

                        <p>
                            Temporarily restrict system access while technical maintenance is being performed.
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
                        name="settings_section"
                        value="maintenance"
                    >


                    <div class="quick-stats">

                        <div>

                            <span>
                                Maintenance Status
                            </span>

                            <select
                                name="maintenance_mode"
                                style="width: 100%; box-sizing: border-box;"
                                required
                            >

                                <option
                                    value="0"
                                    <?= $settings["maintenance_mode"] === "0"
                                        ? "selected"
                                        : "" ?>
                                >
                                    Disabled
                                </option>

                                <option
                                    value="1"
                                    <?= $settings["maintenance_mode"] === "1"
                                        ? "selected"
                                        : "" ?>
                                >
                                    Enabled
                                </option>

                            </select>

                            <small>
                                System Administrators can access the system while maintenance mode is enabled.
                            </small>

                        </div>


                        <div>

                            <span>
                                Maintenance Message
                            </span>

                            <textarea
                                name="maintenance_message"
                                rows="4"
                                maxlength="500"
                                style="width: 100%; box-sizing: border-box;"
                                required
                            ><?= e($settings["maintenance_message"]) ?></textarea>

                            <small>
                                Maximum 500 characters.
                            </small>

                        </div>

                    </div>


                    <button type="submit">
                        Save Maintenance Settings
                    </button>

                </form>

            </div>


            <!-- Account Information -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Account Information
                        </h2>

                    </div>
 </div>


                <div class="quick-stats">

                    <div>

                        <span>
                            Account Created
                        </span>

                        <strong>
                            <?= e(
                                date(
                                    "M d, Y",
                                    strtotime(
                                        $admin["created_at"]
                                    )
                                )
                            ) ?>
                        </strong>

                    </div>


                    <div>

                        <span>
                            Last Updated
                        </span>

                        <strong>
                            <?= e(
                                date(
                                    "M d, Y",
                                    strtotime(
                                        $admin["updated_at"]
                                    )
                                )
                            ) ?>
                        </strong>

                    </div>

                </div>

            </div>


        </section>

    </main>

</div>

</body>

</html>