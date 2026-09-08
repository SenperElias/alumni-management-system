 <?php

session_start();

require_once "../config/database.php";
require_once "../config/config.php";
require_once "../includes/functions.php";

/*--------------------------------------------------------------------------*/
/* LOGIN CHECK */
/*--------------------------------------------------------------------------*/

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

/*--------------------------------------------------------------------------*/
/* LOAD CURRENT USER */
/*--------------------------------------------------------------------------*/

$userId = (int) $_SESSION["user_id"];

$stmt = $conn->prepare("
    SELECT
        user_id,
        email,
        password_hash,
        role,
        account_status,
        must_change_password
    FROM users
    WHERE user_id = ?
    LIMIT 1
");

if (!$stmt) {
    die("Unable to load your account.");
}

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

if (!$user) {
    session_unset();
    session_destroy();

    header("Location: login.php");
    exit;
}

/*--------------------------------------------------------------------------*/
/* ACCOUNT STATUS */
/*--------------------------------------------------------------------------*/

if ($user["account_status"] !== "active") {

    session_unset();
    session_destroy();

    header("Location: login.php");
    exit;
}

/*--------------------------------------------------------------------------*/
/* FORM */
/*--------------------------------------------------------------------------*/

$error = "";
$success = "";

/*--------------------------------------------------------------------------*/
/* PROCESS PASSWORD CHANGE */
/*--------------------------------------------------------------------------*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!verify_csrf_token($_POST["csrf_token"] ?? "")) {

        $error =
            "Invalid security token. Please try again.";

    } else {

        $currentPassword =
            $_POST["current_password"] ?? "";

        $newPassword =
            $_POST["new_password"] ?? "";

        $confirmPassword =
            $_POST["confirm_password"] ?? "";

        if (
            $currentPassword === ""
            || $newPassword === ""
            || $confirmPassword === ""
        ) {

            $error =
                "Please fill in all password fields.";

        } elseif (
            !password_verify(
                $currentPassword,
                $user["password_hash"]
            )
        ) {

            $error =
                "Current password is incorrect.";

        } elseif (
            strlen($newPassword) < 8
        ) {

            $error =
                "New password must be at least 8 characters.";

        } elseif (
            $newPassword !== $confirmPassword
        ) {

            $error =
                "New passwords do not match.";

        } elseif (
            password_verify(
                $newPassword,
                $user["password_hash"]
            )
        ) {

            $error =
                "Your new password must be different from your current password.";

        } else {

            $newPasswordHash =
                password_hash(
                    $newPassword,
                    PASSWORD_DEFAULT
                );

            $updateStmt = $conn->prepare("
                UPDATE users
                SET
                    password_hash = ?,
                    must_change_password = 0,
                    updated_at = NOW()
                WHERE user_id = ?
                AND account_status = 'active'
            ");

            if (!$updateStmt) {

                $error =
                    "Unable to change your password. Please try again.";

            } else {

                $updateStmt->bind_param(
                    "si",
                    $newPasswordHash,
                    $userId
                );

                if ($updateStmt->execute()) {
 $success =
                        "Your password has been changed successfully.";

                    $_SESSION["must_change_password"] = 0;

                } else {

                    $error =
                        "Unable to change your password. Please try again.";
                }

                $updateStmt->close();
            }
        }
    }
}

/*--------------------------------------------------------------------------*/
/* DASHBOARD REDIRECT */
/*--------------------------------------------------------------------------*/

$dashboardUrl = "../index.php";

switch ($user["role"]) {

    case "admin":

        $dashboardUrl =
            "../admin/dashboard.php";

        break;

    case "alumni":

        $dashboardUrl =
            "../alumni/dashboard.php";

        break;

    case "registrar":

        $dashboardUrl =
            "../admin/registrar/dashboard.php";

        break;

    case "student_rep":

        $dashboardUrl =
            "../admin/student_rep/dashboard.php";

        break;

    case "system_admin":

        $dashboardUrl =
            "../admin/system_admin/dashboard.php";

        break;
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

        Change Password |

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

        <?php if ($success !== ""): ?>

            <h1>
                Password Changed
            </h1>

            <div class="success-message">

                <?= e($success) ?>

            </div>

            <p>

                Your temporary password has been replaced.

                You can now use your new password normally.

            </p>

            <p class="login-back">

                <a href="<?= e($dashboardUrl) ?>">

                    Continue to Dashboard

                </a>

            </p>

        <?php else: ?>

            <h1>
                Change Your Password
            </h1>

            <p>

                For your security, you must create a new password
                before continuing.

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

                <?= csrf_field() ?>


                <div class="form-group">

                    <label for="current_password">

                        Temporary Password

                    </label>

                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        required
                        autocomplete="current-password"
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
                        autocomplete="new-password"
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
                        autocomplete="new-password"
                    >
 </div>


                <button
                    type="submit"
                    class="login-button"
                >

                    Change Password

                </button>

            </form>


            <p class="login-back">

                <a href="../public/index.php">

                    ← Back to Website

                </a>

            </p>

        <?php endif; ?>

    </div>

</div>

</body>

</html>