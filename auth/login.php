<?php

session_start();

require_once "../config/database.php";
require_once "../config/config.php";
require_once "../includes/functions.php";

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
                must_change_password
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

                if ($user["account_status"] !== "active") {

                    $error = "Your account is not active.";

                } elseif (
    $user["account_status"] !== "active"
) {

    $error = "Your account has been deactivated. Please contact the administrator.";

} elseif (
    password_verify(
        $password,
        $user["password_hash"]
    )
) {

                    session_regenerate_id(true);

                    $_SESSION["user_id"] = $user["user_id"];
                    $_SESSION["email"] = $user["email"];
                    $_SESSION["role"] = $user["role"];
                    $_SESSION["must_change_password"] =
                        (int) $user["must_change_password"];

                    /*
                     * Temporary password handling.
                     *
                     * Only alumni accounts can be forced
                     * to change a temporary password.
                     */
                    if ((int) $user["must_change_password"] === 1) {

    $_SESSION["must_change_password"] = 1;

    header("Location: change_password.php?required=1");
    exit;
}

                    /*
                     * Normal role-based redirects.
                     */
                    if ($user["role"] === "admin") {

                        // Alumni President
                        header(
                            "Location: ../admin/dashboard.php"
                        );
                        exit;

                    } elseif ($user["role"] === "alumni") {

                        header(
                            "Location: ../alumni/dashboard.php"
                        );
                        exit;

                    } elseif ($user["role"] === "registrar") {

                        header(
                            "Location: ../admin/registrar/dashboard.php"
                        );
                        exit;

                    } elseif ($user["role"] === "student_rep") {

                        header(
                            "Location: ../admin/student_rep/dashboard.php"
                        );
                        exit;

                    } elseif ($user["role"] === "system_admin") {

                        header(
                            "Location: ../admin/system_admin/dashboard.php"
                        );
                        exit;

                    } else {

                        $error = "Invalid account role.";
                    }

                } else {

                    $error = "Invalid email or password.";
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