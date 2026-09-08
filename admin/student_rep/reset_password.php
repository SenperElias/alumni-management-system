<?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";

/*
|--------------------------------------------------------------------------
| Student Representative Access
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "student_rep") {
    header("Location: ../../index.php");
    exit;
}

$activePage = "manage_alumni";

$error = "";
$success = "";
$tempPassword = "";

$userId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$userId) {
    header("Location: manage_alumni.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Load Approved Alumni Account
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        u.user_id,
        u.email,
        u.account_status,
        a.college_id_number,
        a.first_name,
        a.last_name
    FROM users u
    INNER JOIN alumni a
        ON a.user_id = u.user_id
    WHERE u.user_id = ?
      AND u.role = 'alumni'
      AND u.account_status = 'active'
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    $error = "Unable to load the alumni account.";

} else {

    $stmt->bind_param("i", $userId);
    $stmt->execute();

    $result = $stmt->get_result();
    $alumnus = $result->fetch_assoc();

    $stmt->close();

    if (!$alumnus) {
        $error = "Approved alumni account not found.";
    }
}

/*
|--------------------------------------------------------------------------
| Generate Temporary Password
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && !$error) {

    if (!verify_csrf_token()){
        die("Invalid security token.");
    }

    $tempPassword = bin2hex(random_bytes(6));

    $passwordHash = password_hash(
        $tempPassword,
        PASSWORD_DEFAULT
    );

    $updateSql = "
        UPDATE users
        SET
            password_hash = ?,
            must_change_password = 1,
            updated_at = NOW()
        WHERE user_id = ?
          AND role = 'alumni'
          AND account_status = 'active'
    ";

    $updateStmt = $conn->prepare($updateSql);

    if (!$updateStmt) {

        $error = "Unable to reset the password.";
        $tempPassword = "";

    } else {

        $updateStmt->bind_param(
            "si",
            $passwordHash,
            $userId
        );

        if ($updateStmt->execute()) {

            /*
             * Record the password reset in the audit log.
             */
            logAudit(
                $conn,
                (int) $_SESSION["user_id"],
                "RESET_PASSWORD",
                "users",
                $userId,
                "Student Representative reset the password for alumni account: "
                . $alumnus["first_name"] . " "
                . $alumnus["last_name"]
            );

            $success = "Temporary password generated successfully.";

        } else {

            $error = "The password could not be reset.";
            $tempPassword = "";
        }

        $updateStmt->close();
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
        Reset Alumni Password |
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

</head>

<body class="admin-body">

<div class="admin-layout">

    <!-- SIDEBAR -->

    <?php include "includes/sidebar.php"; ?>


    <!-- MAIN CONTENT -->

    <main class="admin-main">

        <!-- TOPBAR -->

        <header class="admin-topbar">

            <div>

                <h1>
                    Reset Alumni Password
                </h1>

                <p>
                    Assist an approved alumnus with account recovery.
                </p>
 </div>

            <div class="admin-user">

                <div class="admin-avatar">
                    S
                </div>

                <div>

                    <strong>
                        Student Representative
                    </strong>

                    <small>
                        Alumni Registration Officer
                    </small>

                </div>

            </div>

        </header>


        <!-- CONTENT -->

        <section class="dashboard-content">

            <div class="dashboard-panel">

                <?php if ($error): ?>

                    <div class="alert alert-error">
                        <?= e($error) ?>
                    </div>

                <?php endif; ?>


                <?php if ($success && $tempPassword): ?>

                    <div class="alert alert-success">
                        <?= e($success) ?>
                    </div>


                    <div class="panel-header">

                        <div>

                            <h2>
                                Temporary Password
                            </h2>

                            <p>
                                Give this temporary password to the alumnus.
                                It will not be displayed again.
                            </p>

                        </div>

                    </div>


                    <div class="temporary-password-box">

                        <strong>
                            <?= e($tempPassword) ?>
                        </strong>

                    </div>


                    <p>

                        <strong>Alumnus:</strong>

                        <?= e(
                            $alumnus["first_name"]
                            . " "
                            . $alumnus["last_name"]
                        ) ?>

                    </p>


                    <p>

                        <strong>College ID Number:</strong>

                        <?= e(
                            $alumnus["college_id_number"]
                        ) ?>

                    </p>


                    <p>

                        <strong>Email:</strong>

                        <?= e(
                            $alumnus["email"]
                        ) ?>

                    </p>


                    <p>

                        The alumnus will be required to change this password
                        after logging in.

                    </p>


                    <div class="form-actions">

                        <a
                            href="manage_alumni.php"
                            class="primary-button"
                        >
                            Back to Manage Alumni
                        </a>

                    </div>


                <?php elseif (!$error && $alumnus): ?>


                    <div class="panel-header">

                        <div>

                            <h2>
                                Confirm Password Reset
                            </h2>

                            <p>
                                You are about to reset the password for this
                                approved alumni account.
                            </p>

                        </div>

                    </div>


                    <div class="account-summary">

                        <p>

                            <strong>Name:</strong>

                            <?= e(
                                $alumnus["first_name"]
                                . " "
                                . $alumnus["last_name"]
                            ) ?>

                        </p>


                        <p>

                            <strong>College ID Number:</strong>

                            <?= e(
                                $alumnus["college_id_number"]
                            ) ?>

                        </p>


                        <p>

                            <strong>Email:</strong>
<?= e(
                                $alumnus["email"]
                            ) ?>

                        </p>

                    </div>


                    <div class="alert alert-warning">

                        <strong>Important:</strong>

                        The current password cannot be viewed or recovered.
                        A new temporary password will replace it.

                    </div>


                    <form
                        method="POST"
                        action="reset_password.php?id=<?= (int) $userId ?>"
                    >

                        <?= csrf_field() ?>


                        <div class="form-actions">

                            <button
                                type="submit"
                                class="primary-button"
                                onclick="return confirm('Reset this alumni password?')"
                            >
                                Generate Temporary Password
                            </button>


                            <a
                                href="manage_alumni.php"
                                class="secondary-button"
                            >
                                Cancel
                            </a>

                        </div>

                    </form>


                <?php endif; ?>

            </div>

        </section>

    </main>

</div>

</body>

</html>