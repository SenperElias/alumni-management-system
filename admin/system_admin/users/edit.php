 <?php

session_start();

require_once "../../../config/database.php";
require_once "../../../config/config.php";
require_once "../../../includes/functions.php";

/*--------------------------------------------------------------------------*/
/* SYSTEM ADMIN ACCESS */
/*--------------------------------------------------------------------------*/

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "system_admin") {
    header("Location: ../../../index.php");
    exit;
}

/*--------------------------------------------------------------------------*/
/* GET USER ID */
/*--------------------------------------------------------------------------*/

$user_id = (int) ($_GET["user_id"] ?? $_POST["user_id"] ?? 0);

if ($user_id <= 0) {
    header("Location: index.php");
    exit;
}

/*--------------------------------------------------------------------------*/
/* ROLE LABELS */
/*--------------------------------------------------------------------------*/

$role_labels = [

    "admin" =>
        "Alumni President",

    "registrar" =>
        "Registrar",

    "student_rep" =>
        "Alumni Admin",

    "system_admin" =>
        "System Administrator"

];

/*--------------------------------------------------------------------------*/
/* ALLOWED ROLES */
/*--------------------------------------------------------------------------*/

$allowed_roles = array_keys($role_labels);

/*--------------------------------------------------------------------------*/
/* GET USER */
/*--------------------------------------------------------------------------*/

$stmt = $conn->prepare("
    SELECT
        user_id,
        email,
        role,
        account_status,
        must_change_password
    FROM users
    WHERE user_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

if (!$user) {
    header("Location: index.php");
    exit;
}

/*--------------------------------------------------------------------------*/
/* FORM VALUES */
/*--------------------------------------------------------------------------*/

$email = $user["email"];
$role = $user["role"];
$error = "";

/*--------------------------------------------------------------------------*/
/* UPDATE USER */
/*--------------------------------------------------------------------------*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /*------------------------------------------------------------------*/
    /* CSRF */
    /*------------------------------------------------------------------*/

    if (!verify_csrf_token($_POST["csrf_token"] ?? "")) {

        $error = "Invalid security token. Please try again.";

    } else {

        $email = trim($_POST["email"] ?? "");
        $role = trim($_POST["role"] ?? "");

        /*--------------------------------------------------------------*/
        /* EMAIL VALIDATION */
        /*--------------------------------------------------------------*/

        if ($email === "") {

            $error = "Email address is required.";

        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $error = "Please enter a valid email address.";

        }

        /*--------------------------------------------------------------*/
        /* ROLE VALIDATION */
        /*--------------------------------------------------------------*/

        elseif (!in_array($role, $allowed_roles, true)) {

            $error = "Invalid user role.";

        }

        /*--------------------------------------------------------------*/
        /* PREVENT SELF ROLE CHANGE */
        /*--------------------------------------------------------------*/

        elseif (
            $user_id === (int) $_SESSION["user_id"]
            && $role !== "system_admin"
        ) {

            $error =
                "You cannot change your own role from System Administrator.";

        }
 /*--------------------------------------------------------------*/
        /* CHECK EMAIL DUPLICATE */
        /*--------------------------------------------------------------*/

        else {

            $stmt = $conn->prepare("
                SELECT user_id
                FROM users
                WHERE email = ?
                AND user_id != ?
                LIMIT 1
            ");

            $stmt->bind_param(
                "si",
                $email,
                $user_id
            );

            $stmt->execute();

            $duplicate_result = $stmt->get_result();

            $duplicate_user =
                $duplicate_result->fetch_assoc();

            $stmt->close();

            if ($duplicate_user) {

                $error =
                    "An account with this email already exists.";

            }

        }

        /*--------------------------------------------------------------*/
        /* UPDATE */
        /*--------------------------------------------------------------*/

        if ($error === "") {

            $old_email = $user["email"];
            $old_role = $user["role"];

            $stmt = $conn->prepare("
                UPDATE users
                SET
                    email = ?,
                    role = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE user_id = ?
            ");

            $stmt->bind_param(
                "ssi",
                $email,
                $role,
                $user_id
            );

            if (!$stmt->execute()) {

                $error =
                    "Unable to update the user account.";

            }

            $stmt->close();


            /*----------------------------------------------------------*/
            /* AUDIT LOG */
            /*----------------------------------------------------------*/

            if ($error === "") {

                if ($old_email !== $email) {

                    logAudit(
                        $conn,
                        (int) $_SESSION["user_id"],
                        "UPDATE",
                        "users",
                        $user_id,
                        "User email changed from "
                        . $old_email
                        . " to "
                        . $email
                        . "."
                    );

                }


                if ($old_role !== $role) {

                    logAudit(
                        $conn,
                        (int) $_SESSION["user_id"],
                        "UPDATE",
                        "users",
                        $user_id,
                        "User role changed from "
                        . ($role_labels[$old_role] ?? $old_role)
                        . " to "
                        . ($role_labels[$role] ?? $role)
                        . "."
                    );

                }


                header("Location: index.php");
                exit;
            }

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
        Edit User |
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../../assets/css/style.css"
    >

    <style>

        .edit-user-page {
            padding: 30px;
        }

        .edit-user-card {
            max-width: 700px;
            background: #ffffff;
            border: 1px solid #eeeeee;
            border-radius: 14px;
            padding: 25px;
            box-shadow:
                0 5px 20px
                rgba(0,0,0,0.04);
        }

        .edit-user-card h2 {
            margin-top: 0;
            color: #4a2c1d;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-weight: 600;
            color: #4a2c1d;
        }
.form-group input,
        .form-group select {
            width: 100%;
            box-sizing: border-box;
            padding: 11px 13px;
            border: 1px solid #dddddd;
            border-radius: 8px;
            font-size: 14px;
        }

        .form-help {
            margin-top: 6px;
            color: #777777;
            font-size: 12px;
        }

        .readonly-info {
            background: #f8f5f2;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 20px;
            color: #555555;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .cancel-button {
            display: inline-flex;
            align-items: center;
            padding: 11px 18px;
            background: #eeeeee;
            color: #444444;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
        }

    </style>

</head>

<body class="admin-body">

<div class="admin-layout">

    <?php require_once "../sidebar.php"; ?>


    <main class="admin-main">

        <header class="admin-topbar">

            <div>

                <h1>
                    Edit User
                </h1>

                <p>
                    Manage this system user's account information.
                </p>

            </div>

        </header>


        <section class="dashboard-content">

            <div class="edit-user-page">

                <div class="edit-user-card">

                    <h2>
                        Edit User Account
                    </h2>


                    <?php if ($error !== ""): ?>

                        <div class="error-message">

                            <?= e($error) ?>

                        </div>

                    <?php endif; ?>


                    <div class="readonly-info">

                        <strong>
                            User ID:
                        </strong>

                        <?= (int) $user["user_id"] ?>

                        <br><br>

                        <strong>
                            Account Status:
                        </strong>

                        <?= e(
                            ucfirst(
                                $user["account_status"]
                            )
                        ) ?>

                    </div>


                    <form
                        method="POST"
                        action=""
                    >

                        <input
                            type="hidden"
                            name="user_id"
                            value="<?= (int) $user_id ?>"
                        >

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= e(csrf_token()) ?>"
                        >


                        <!-- EMAIL -->

                        <div class="form-group">

                            <label for="email">
                                Email Address
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="<?= e($email) ?>"
                                required
                            >

                        </div>


                        <!-- ROLE -->

                        <div class="form-group">

                            <label for="role">
                                User Role
                            </label>
 <select
                                id="role"
                                name="role"
                                required
                            >

                                <?php foreach (
                                    $role_labels
                                    as $role_value =>
                                    $role_label
                                ): ?>

                                    <option
                                        value="<?= e($role_value) ?>"
                                        <?= (
                                            $role === $role_value
                                        )
                                            ? "selected"
                                            : ""
                                        ?>
                                    >

                                        <?= e($role_label) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                            <div class="form-help">

                                Select the role that determines
                                this user's system access.

                            </div>

                        </div>


                        <!-- ACTIONS -->

                        <div class="form-actions">

                            <button
                                type="submit"
                                class="search-button"
                            >
                                Save Changes
                            </button>


                            <a
                                href="index.php"
                                class="cancel-button"
                            >
                                Cancel
                            </a>

                        </div>

                    </form>

                </div>

            </div>

        </section>

    </main>

</div>

</body>

</html>