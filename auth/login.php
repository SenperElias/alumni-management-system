<?php

session_start();

require_once "../config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $error = "Please enter your email and password.";
    } else {

        $stmt = $conn->prepare(
            "SELECT user_id, email, password_hash, role, account_status
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            if ($user["account_status"] !== "active") {

                $error = "Your account is not active.";

            } elseif (password_verify($password, $user["password_hash"])) {

                session_regenerate_id(true);

                $_SESSION["user_id"] = $user["user_id"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["role"] = $user["role"];

                if ($user["role"] === "admin") {

                    header("Location: ../admin/dashboard.php");
                    exit;

                } elseif ($user["role"] === "alumni") {

                    header("Location: ../alumni/dashboard.php");
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
?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login | Alumni Management System</title>

    <link rel="stylesheet"
          href="../assets/css/style.css">

</head>

<body>

<div class="login-container">

    <div class="login-card">

        <h1>Welcome Back</h1>

        <p>Login to your Alumni Management System account.</p>

        <?php if ($error !== ""): ?>

            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>

        <form method="POST" action="">

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

            <button type="submit" class="login-button">
                Login
            </button>

        </form>

        <p class="login-back">

            <a href="../public/index.php">
                ← Back to Website
            </a>

        </p>

    </div>

</div>

</body>
</html>