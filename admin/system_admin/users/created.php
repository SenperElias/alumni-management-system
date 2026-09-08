 <?php

session_start();

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
/* CHECK CREATED USER */
/*--------------------------------------------------------------------------*/

if (!isset($_SESSION["created_user"])) {
    header("Location: index.php");
    exit;
}

$created_user = $_SESSION["created_user"];

/*--------------------------------------------------------------------------*/
/* REMOVE FROM SESSION AFTER READING */
/*--------------------------------------------------------------------------*/

unset($_SESSION["created_user"]);

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
        User Created |
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../../assets/css/style.css"
    >

    <style>

        .created-page {
            padding: 30px;
        }

        .created-card {
            max-width: 650px;
            background: #ffffff;
            border: 1px solid #eeeeee;
            border-radius: 14px;
            padding: 30px;
            box-shadow:
                0 5px 20px
                rgba(0,0,0,0.04);
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 600;
        }

        .user-info {
            background: #f8f5f2;
            padding: 18px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .user-info p {
            margin: 8px 0;
        }

        .temporary-password {
            background: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 18px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .temporary-password strong {
            display: block;
            margin-bottom: 8px;
        }

        .password-value {
            display: inline-block;
            background: #ffffff;
            border: 1px solid #dddddd;
            padding: 10px 14px;
            border-radius: 7px;
            font-family: monospace;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .warning {
            margin-top: 12px;
            font-size: 13px;
        }

        .actions {
            margin-top: 25px;
            display: flex;
            gap: 10px;
        }

        .back-button {
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
                    User Created
                </h1>

                <p>
                    The new system account has been created successfully.
                </p>

            </div>

        </header>


        <section class="dashboard-content">

            <div class="created-page">

                <div class="created-card">

                    <div class="success-message">

                        User account created successfully.

                    </div>
 <div class="user-info">

                        <p>

                            <strong>
                                Email:
                            </strong>

                            <?= e(
                                $created_user["email"]
                            ) ?>

                        </p>


                        <p>

                            <strong>
                                Role:
                            </strong>

                            <?php

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

                            ?>

                            <?= e(
                                $role_labels[
                                    $created_user["role"]
                                ]
                                ?? "Unknown Role"
                            ) ?>

                        </p>

                    </div>


                    <div class="temporary-password">

                        <strong>
                            Temporary Password
                        </strong>


                        <div class="password-value">

                            <?= e(
                                $created_user[
                                    "temporary_password"
                                ]
                            ) ?>

                        </div>


                        <div class="warning">

                            ⚠️ Give this temporary password
                            securely to the user.

                            The user should change it
                            immediately after logging in.

                        </div>

                    </div>


                    <div class="actions">

                        <a
                            href="index.php"
                            class="search-button"
                            style="text-decoration: none;"
                        >
                            Back to Users
                        </a>

                        <a
                            href="add.php"
                            class="back-button"
                        >
                            + Create Another User
                        </a>

                    </div>

                </div>

            </div>

        </section>

    </main>

</div>

</body>

</html>