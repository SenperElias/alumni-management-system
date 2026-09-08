 <?php

session_start();

require_once "../../../config/database.php";
require_once "../../../config/config.php";
require_once "../../../includes/functions.php";

/* ---------------------------------------------------------
   SYSTEM ADMIN ACCESS
--------------------------------------------------------- */

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "system_admin") {
    header("Location: ../../../index.php");
    exit;
}

/* ---------------------------------------------------------
   CSRF TOKEN
--------------------------------------------------------- */

$csrf_token = csrf_token();

/* ---------------------------------------------------------
   FORM VARIABLES
--------------------------------------------------------- */

$email = "";
$role = "";
$error = "";

/* ---------------------------------------------------------
   CREATE USER
--------------------------------------------------------- */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!verify_csrf_token($_POST["csrf_token"] ?? "")) {
        $error = "Invalid security token. Please try again.";
    } else {

        $email = trim($_POST["email"] ?? "");
        $role = trim($_POST["role"] ?? "");

        /* Validate email */

        if ($email === "") {

            $error = "Email address is required.";

        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $error = "Please enter a valid email address.";

        }

        /* Validate role */

        elseif (!in_array(
            $role,
            [
                "admin",
                "registrar",
                "student_rep",
                "system_admin"
            ],
            true
        )) {

            $error = "Invalid user role.";

        } else {

            /* Check existing email */

            $stmt = $conn->prepare(
                "SELECT user_id FROM users WHERE email = ? LIMIT 1"
            );

            $stmt->bind_param("s", $email);

            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows > 0) {

                $error = "An account with this email already exists.";

            } else {

                /*
                 * Generate temporary password.
                 * The user will be required to change it after login.
                 */

                $temporary_password =
                    bin2hex(random_bytes(6));

                $password_hash =
                    password_hash(
                        $temporary_password,
                        PASSWORD_DEFAULT
                    );

                $account_status = "active";
                $must_change_password = 1;

                $stmt = $conn->prepare(
                    "
                    INSERT INTO users
                    (
                        email,
                        password_hash,
                        role,
                        account_status,
                        must_change_password
                    )
                    VALUES (?, ?, ?, ?, ?)
                    "
                );

                $stmt->bind_param(
                    "ssssi",
                    $email,
                    $password_hash,
                    $role,
                    $account_status,
                    $must_change_password
                );

                if ($stmt->execute()) {

                    $new_user_id =
                        $stmt->insert_id;

                    /*
                     * Audit the account creation.
                     */

                    logAudit(
                        $conn,
                        (int) $_SESSION["user_id"],
                        "CREATE",
                        "users",
                        (int) $new_user_id,
                        "Created user account: " . $email
                    );
 /*
                     * Store temporary password in session
                     * so it can be displayed once.
                     */

                    $_SESSION["created_user"] = [
                        "email" => $email,
                        "role" => $role,
                        "temporary_password" =>
                            $temporary_password
                    ];

                    header("Location: created.php");
                    exit;

                } else {

                    $error =
                        "Unable to create the user account.";
                }
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
        Add User | <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../../assets/css/style.css"
    >

    <style>

        .users-page {
            padding: 30px;
        }

        .form-card {
            max-width: 650px;
            background: #ffffff;
            border: 1px solid #eeeeee;
            border-radius: 14px;
            padding: 25px;
            box-shadow:
                0 5px 20px
                rgba(0,0,0,0.04);
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
            padding: 11px 13px;
            border: 1px solid #dddddd;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .primary-button,
        .secondary-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 18px;
            border-radius: 8px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }

        .primary-button {
            background: #7a4b2a;
            color: #ffffff;
        }

        .secondary-button {
            background: #eeeeee;
            color: #444444;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
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
                    Add User
                </h1>

                <p>
                    Create a system account and assign its access role.
                </p>

            </div>

        </header>

        <section class="dashboard-content">

            <div class="users-page">

                <div class="form-card">

                    <?php if ($error !== ""): ?>

                        <div class="error-message">
                            <?= e($error) ?>
                        </div>

                    <?php endif; ?>

                    <form
                        method="POST"
                        autocomplete="off"
                    >

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= e($csrf_token) ?>"
                        >

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

                        <div class="form-group">

                            <label for="role">
                                User Role
                            </label>

                            <select
                                id="role"
                                name="role"
                                required
                            >

                                <option value="">
                                    Select role
                                </option>

                                <option
                                    value="admin"
                                    <?= $role === "admin"
                                        ? "selected"
                                        : "" ?>
                                >
                                    Alumni President


                                <option
                                    value="registrar"
                                    <?= $role === "registrar"
                                        ? "selected"
                                        : "" ?>
                                >
                                    Registrar
                                </option>

                                <option
                                    value="student_rep"
                                    <?= $role === "student_rep"
                                        ? "selected"
                                        : "" ?>
                                >
                                    Alumni Admin
                                </option>

                                <option
                                    value="system_admin"
                                    <?= $role === "system_admin"
                                        ? "selected"
                                        : "" ?>
                                >
                                    System Administrator
                                </option>

                            </select>

                        </div>

                        <div class="form-actions">

                            <button
                                type="submit"
                                class="primary-button"
                            >
                                Create User
                            </button>

                            <a
                                href="index.php"
                                class="secondary-button"
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