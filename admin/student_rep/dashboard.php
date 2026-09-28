<?php
session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";
require_once "../../includes/auth-check.php";

/* -------------------------------------------------------------
   Student Representative Access
------------------------------------------------------------- */

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "student_rep") {
    header("Location: ../../index.php");
    exit;
}

$activePage = "dashboard";
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
        Student Representative Dashboard |
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
                    Student Representative Dashboard
                </h1>

                <p>
                    Register and manage alumni accounts.
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

                <div class="panel-header">

                    <div>

                        <h2>
                            Alumni Registration
                        </h2>

                        <p>
                            Register alumni members and submit
                            their accounts for Registrar verification.
                        </p>

                    </div>

                </div>

                <div class="form-actions">

                    <a
                        href="add.php"
                        class="primary-button"
                    >
                        Add Alumni
                    </a>

                </div>

            </div>

        </section>

    </main>

</div>

</body>
</html>