<?php

session_start();

require_once "../config/database.php";
require_once "../config/config.php";
require_once "../includes/functions.php";

// Default maximum failed login attempts
$maxFailedLoginAttempts = 5;

// Load configured maximum failed login attempts
$settingsStmt = $conn->prepare("
    SELECT setting_value
    FROM system_settings
    WHERE setting_key = 'max_failed_login_attempts'
    LIMIT 1
");

if ($settingsStmt) {

    $settingsStmt->execute();

    $settingsResult = $settingsStmt->get_result();

    if ($setting = $settingsResult->fetch_assoc()) {

        $configuredAttempts = (int) $setting["setting_value"];

        if (
            $configuredAttempts >= 3 &&
            $configuredAttempts <= 20
        ) {
            $maxFailedLoginAttempts = $configuredAttempts;
        }
    }

    $settingsStmt->close();
}
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {

        $error = "Please enter your email and password.";

    } else {

        $stmt = $conn->prepare(
            "SELECT
                user_id,
                email,
                password_hash,
                role,
                account_status,
                must_change_password,
                failed_login_attempts,
                locked_until
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        if (!$stmt) {

            $error = "Unable to process login. Please try again.";

        } else {

            $stmt->bind_param("s", $email);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows === 1) {

                $user = $result->fetch_assoc();

                // Check temporary lockout
                if (
                    !empty($user["locked_until"]) &&
                    strtotime($user["locked_until"]) > time()
                ) {

                    $error = "Too many failed login attempts. Please try again later.";

                } elseif ($user["account_status"] !== "active") {

                    $error = "Your account is not active.";

                } elseif (password_verify($password, $user["password_hash"])) {

                    // Successful login: reset failed attempts and lockout
                    $resetStmt = $conn->prepare(
                        "UPDATE users
                         SET failed_login_attempts = 0,
                             locked_until = NULL
                         WHERE user_id = ?"
                    );

                    if ($resetStmt) {
                        $resetStmt->bind_param("i", $user["user_id"]);
                        $resetStmt->execute();
                        $resetStmt->close();
                    }

                    // Audit successful login
                    logAudit(
                        $conn,
                        (int) $user["user_id"],
                        "LOGIN_SUCCESS",
                        "users",
                        (int) $user["user_id"],
                        "User logged in successfully."
                    );

                    session_regenerate_id(true);

                    $_SESSION["user_id"] = $user["user_id"];
                    $_SESSION["email"] = $user["email"];
                    $_SESSION["role"] = $user["role"];
                    $_SESSION["must_change_password"] =
                        (int) $user["must_change_password"];

                    // Force password change when required
                    if ((int) $user["must_change_password"] === 1) {

                        $_SESSION["must_change_password"] = 1;

                        header("Location: change_password.php?required=1");
                        exit;
                    }

                    // Normal role-based redirects
                    if ($user["role"] === "admin") {

                        header("Location: ../admin/dashboard.php");
                        exit;

                    } elseif ($user["role"] === "alumni") {

                        header("Location: ../alumni/dashboard.php");
                        exit;

                    } elseif ($user["role"] === "registrar") {

                        header("Location: ../admin/registrar/dashboard.php");
                        exit;

                    } elseif ($user["role"] === "student_rep") {

                        header("Location: ../admin/student_rep/dashboard.php");
                        exit;

                    } elseif ($user["role"] === "system_admin") {

                        header("Location: ../admin/system_admin/dashboard.php");
                        exit;
 } else {

                        $error = "Invalid account role.";
                    }

                } else {

                    // Failed login attempt
                    $failedAttempts = (int) $user["failed_login_attempts"];
                    $failedAttempts++;

                    if ($failedAttempts >= $maxFailedLoginAttempts) {

                        $lockedUntil = date(
                            "Y-m-d H:i:s",
                            time() + (15 * 60)
                        );

                        $lockStmt = $conn->prepare(
                            "UPDATE users
                             SET failed_login_attempts = ?,
                                 locked_until = ?
                             WHERE user_id = ?"
                        );

                        if ($lockStmt) {
                            $lockStmt->bind_param(
                                "isi",
                                $failedAttempts,
                                $lockedUntil,
                                $user["user_id"]
                            );
                            $lockStmt->execute();
                            $lockStmt->close();
                        }

                        // Audit account lockout
                        logAudit(
                            $conn,
                            (int) $user["user_id"],
                            "ACCOUNT_LOCKED",
                            "users",
                            (int) $user["user_id"],
                            "Account locked after " . $maxFailedLoginAttempts. " consecutive failed login attempts."
                        );

                        $error = "Too many failed login attempts. Please try again later.";

                    } else {

                        $failStmt = $conn->prepare(
                            "UPDATE users
                             SET failed_login_attempts = ?
                             WHERE user_id = ?"
                        );

                        if ($failStmt) {
                            $failStmt->bind_param(
                                "ii",
                                $failedAttempts,
                                $user["user_id"]
                            );
                            $failStmt->execute();
                            $failStmt->close();
                        }

                        // Audit failed login
                        logAudit(
                            $conn,
                            (int) $user["user_id"],
                            "LOGIN_FAILED",
                            "users",
                            (int) $user["user_id"],
                            "Failed login attempt."
                        );

                        $error = "Invalid email or password.";
                    }
                }
            } else {

                $error = "Invalid email or password.";
            }

            $stmt->close();
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
        Login | Alumni Management System
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
            Welcome Back
        </h1>

        <p>
            Login to your Alumni Management System account.
        </p>

        <?php if ($error !== ""): ?>

            <div class="error-message">
                <?= e($error) ?>
            </div>

        <?php endif; ?>

        <form
            method="POST"
            action=""
        >

            <div class="form-group">

                <label for="email">
                    Email Address
                </label>
 <input
                    type="email"
                    id="email"
                    name="email"
                    required
                    autocomplete="email"
                >

            </div>

            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    autocomplete="current-password"
                >

            </div>

            <button
                type="submit"
                class="login-button"
            >
                Login
            </button>

        </form>

        <p class="login-back">

            <a href="register.php">
                Register as Alumni
            </a>

            <br><br>

            <a href="../public/index.php">
                ← Back to Website
            </a>

        </p>

    </div>

</div>

</body>

</html>