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

if (($_SESSION["role"] ?? "") !== "system_admin") {
    header("Location: ../../../index.php");
    exit;
}

/*--------------------------------------------------------------------------*/
/* GET USER ID */
/*--------------------------------------------------------------------------*/

$user_id = (int) (
    $_GET["user_id"]
    ?? $_POST["user_id"]
    ?? 0
);

if ($user_id <= 0) {
    header("Location: index.php");
    exit;
}

/*--------------------------------------------------------------------------*/
/* LOAD USER */
/*--------------------------------------------------------------------------*/

$stmt = $conn->prepare("
    SELECT
        user_id,
        email,
        role,
        account_status
    FROM users
    WHERE user_id = ?
    LIMIT 1
");

if (!$stmt) {
    die("Unable to process the request.");
}

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
/* ROLE LABELS */
/*--------------------------------------------------------------------------*/

$role_labels = [

    "admin" =>
        "Alumni President",

    "alumni" =>
        "Alumni",

    "registrar" =>
        "Registrar",

    "student_rep" =>
        "Alumni Admin",

    "system_admin" =>
        "System Administrator"

];

$role_label =
    $role_labels[$user["role"]]
    ?? "Unknown Role";

/*--------------------------------------------------------------------------*/
/* FORM PROCESSING */
/*--------------------------------------------------------------------------*/

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /*------------------------------------------------------------------*/
    /* CSRF CHECK */
    /*------------------------------------------------------------------*/

    if (!verify_csrf_token($_POST["csrf_token"] ?? "")) {

        $error =
            "Invalid security token. Please try again.";

    } else {

        /*--------------------------------------------------------------*/
        /* PREVENT SELF PASSWORD RESET */
        /*--------------------------------------------------------------*/

        if ($user_id === (int) $_SESSION["user_id"]) {

            $error =
                "You cannot reset your own password from User Management.";

        } elseif ($user["account_status"] !== "active") {

            $error =
                "You cannot reset the password of an inactive account.";

        } else {

            /*----------------------------------------------------------*/
            /* GENERATE RANDOM TEMPORARY PASSWORD */
            /*----------------------------------------------------------*/

            try {

                $temporary_password =
                    bin2hex(random_bytes(8));

            } catch (Exception $e) {

                $error =
                    "Unable to generate a secure temporary password.";

            }


            /*----------------------------------------------------------*/
            /* UPDATE PASSWORD */
            /*----------------------------------------------------------*/

            if ($error === "") {

                $password_hash =
                    password_hash(
                        $temporary_password,
                        PASSWORD_DEFAULT
                    );

                if ($password_hash === false) {

                    $error =
                        "Unable to secure the new password.";

                } else {
$updateStmt = $conn->prepare("
                        UPDATE users
                        SET
                            password_hash = ?,
                            must_change_password = 1,
                            updated_at = NOW()
                        WHERE user_id = ?
                        AND account_status = 'active'
                    ");

                    if (!$updateStmt) {

                        $error =
                            "Unable to reset the password.";

                    } else {

                        $updateStmt->bind_param(
                            "si",
                            $password_hash,
                            $user_id
                        );

                        if ($updateStmt->execute()) {

                            /*--------------------------------------------------*/
                            /* AUDIT LOG */
                            /*--------------------------------------------------*/

                            logAudit(
                                $conn,
                                (int) $_SESSION["user_id"],
                                "UPDATE",
                                "users",
                                $user_id,
                                "System Administrator reset the user's password. "
                                . "A temporary password was generated and the "
                                . "user is required to change it at next login."
                            );

                            /*--------------------------------------------------*/
                            /* STORE TEMPORARY PASSWORD FOR ONE-TIME DISPLAY */
                            /*--------------------------------------------------*/

                            $_SESSION["reset_password_user"] = [

                                "user_id" =>
                                    $user["user_id"],

                                "email" =>
                                    $user["email"],

                                "role" =>
                                    $user["role"],

                                "temporary_password" =>
                                    $temporary_password

                            ];

                            $updateStmt->close();

                            header(
                                "Location: reset_password_success.php"
                            );

                            exit;

                        } else {

                            $error =
                                "Unable to reset the password.";

                        }

                        $updateStmt->close();
                    }
                }
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
        Reset Password |
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../../assets/css/style.css"
    >

    <style>

        .reset-page {
            padding: 30px;
        }

        .reset-card {
            max-width: 650px;
            background: #ffffff;
            border: 1px solid #eeeeee;
            border-radius: 14px;
            padding: 30px;
            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.04);
        }

        .reset-card h2 {
            margin-top: 0;
            color: #4a2c1d;
        }

        .user-info {
            background: #f8f5f2;
            padding: 18px;
            border-radius: 10px;
            margin: 20px 0;
        }

        .user-info p {
            margin: 8px 0;
        }

        .warning-message {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
 .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .reset-actions {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .cancel-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 18px;
            background: #eeeeee;
            color: #444444;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
        }

        .reset-button {
            border: none;
            background: #7a4b2a;
            color: #ffffff;
            padding: 11px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .reset-button:hover {
            background: #5f3920;
        }

        @media (max-width: 650px) {

            .reset-page {
                padding: 15px;
            }

            .reset-actions {
                flex-direction: column;
            }

            .reset-button,
            .cancel-button {
                width: 100%;
                box-sizing: border-box;
            }

        }

    </style>

</head>

<body class="admin-body">

<div class="admin-layout">

    <!-- SIDEBAR -->

    <?php require_once "../sidebar.php"; ?>


    <!-- MAIN -->

    <main class="admin-main">

        <header class="admin-topbar">

            <div>

                <h1>
                    Reset Password
                </h1>

                <p>
                    Generate a new temporary password for a user.
                </p>

            </div>

        </header>


        <section class="dashboard-content">

            <div class="reset-page">

                <div class="reset-card">

                    <h2>
                        Reset User Password
                    </h2>


                    <?php if ($error !== ""): ?>

                        <div class="error-message">

                            <?= e($error) ?>

                        </div>

                    <?php endif; ?>


                    <div class="user-info">

                        <p>

                            <strong>
                                User ID:
                            </strong>

                            <?= (int) $user["user_id"] ?>

                        </p>


                        <p>

                            <strong>
                                Email:
                            </strong>

                            <?= e($user["email"]) ?>

                        </p>


                        <p>

                            <strong>
                                Role:
                            </strong>

                            <?= e($role_label) ?>

                        </p>


                        <p>

                            <strong>
                                Account Status:
                            </strong>

                            <?= e(
                                ucfirst(
                                    $user["account_status"]
                                )
                            ) ?>

                        </p>

                    </div>


                    <div class="warning-message">

                        <strong>
                            ⚠️ Important
                        </strong>

                        <p>

                            A new random temporary password will
                            be generated.

                        </p>

                        <p>

                            The user's current password will stop
                            working immediately.

                        </p>

                        <p>

                            The user will be required to create a
                            new password when they log in.

                        </p>

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


                        <div class="reset-actions">

                            <button
                                type="submit"
                                class="reset-button"
                            >

                                Reset Password

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