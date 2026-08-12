<?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";

/*
|--------------------------------------------------------------------------
| Alumni Access
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "alumni") {
    header("Location: ../../index.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];

$error = "";
$success = "";

/*
|--------------------------------------------------------------------------
| Get Alumni Account
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        user_id,
        email,
        password_hash,
        role,
        account_status,
        created_at,
        updated_at
    FROM users
    WHERE user_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();
    header("Location: ../dashboard.php");
    exit;
}

$alumni = $result->fetch_assoc();

$stmt->close();

/*
|--------------------------------------------------------------------------
| Change Password
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $currentPassword = $_POST["current_password"] ?? "";
    $newPassword = $_POST["new_password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";

    if (
        $currentPassword === "" ||
        $newPassword === "" ||
        $confirmPassword === ""
    ) {

        $error = "Please fill in all password fields.";

    } elseif (
        !password_verify(
            $currentPassword,
            $alumni["password_hash"]
        )
    ) {

        $error = "Current password is incorrect.";

    } elseif (strlen($newPassword) < 8) {

        $error = "New password must be at least 8 characters.";

    } elseif ($newPassword !== $confirmPassword) {

        $error = "New passwords do not match.";

    } else {

        $newPasswordHash = password_hash(
            $newPassword,
            PASSWORD_DEFAULT
        );

        $update = $conn->prepare("
            UPDATE users
            SET
                password_hash = ?,
                updated_at = NOW()
            WHERE user_id = ?
        ");

        $update->bind_param(
            "si",
            $newPasswordHash,
            $userId
        );

        if ($update->execute()) {

            $success = "Password changed successfully.";

        } else {

            $error = "Unable to change password.";

        }

        $update->close();
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
        Settings | <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

</head>

<body class="admin-body">

<div class="admin-layout">

    <!-- =========================================================
         SIDEBAR
    ========================================================== -->

    
<?php
$currentPage = "settings";
require_once __DIR__ . "/../includes/sidebar.php";
?>

        


    <!-- =========================================================
         MAIN CONTENT
    ========================================================== -->

    <main class="admin-main">

        <header class="admin-topbar">

            <div>

                <h1>
                    Settings
                </h1>

                <p>
                    Manage your alumni account settings.
                </p>

            </div>

        </header>


        <section class="dashboard-content">

            <?php if ($error !== ""): ?>

                <div class="error-message">
                    <?= e($error) ?>
                </div>

            <?php endif; ?>


            <?php if ($success !== ""): ?>

                <div class="success-message">
                    <?= e($success) ?>
                </div>

            <?php endif; ?>


            <!-- =================================================
                 ACCOUNT INFORMATION
            ================================================== -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Account Information
                        </h2>

                        <p>
                            Your alumni account information.
                        </p>

                    </div>

                </div>


                <div class="quick-stats">

                    <div>

                        <span>
                            Email
                        </span>

                        <strong>
                            <?= e($alumni["email"]) ?>
                        </strong>

                    </div>


                    <div>

                        <span>
                            Account Type
                        </span>

                        <strong>
                            <?= e($alumni["role"]) ?>
                        </strong>

                    </div>


                    <div>

                        <span>
                            Account Status
                        </span>

                        <strong>
                            <?= e($alumni["account_status"]) ?>
                        </strong>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 CHANGE PASSWORD
            ================================================== -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Change Password
                        </h2>

                        <p>
                            Update your account password.
                        </p>

                    </div>

                </div>


                <form
                    method="POST"
                    action=""
                >

                    <div class="form-group">
                    <label for="current_password">
                            Current Password
                        </label>

                        <input
                            type="password"
                            id="current_password"
                            name="current_password"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="new_password">
                            New Password
                        </label>

                        <input
                            type="password"
                            id="new_password"
                            name="new_password"
                            minlength="8"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="confirm_password">
                            Confirm New Password
                        </label>

                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            minlength="8"
                            required
                        >

                    </div>


                    <div class="quick-actions">

                        <button
                            type="submit"
                            class="secondary-button"
                        >
                            Change Password
                        </button>

                    </div>

                </form>

            </div>


            <!-- =================================================
                 ACCOUNT DATES
            ================================================== -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Account Information
                        </h2>

                        <p>
                            Your account history.
                        </p>

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
                                        $alumni["created_at"]
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
                                        $alumni["updated_at"]
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