<?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "registrar") {
    header("Location: ../../index.php");
    exit;
}

$alumni_id = (int) ($_GET["id"] ?? 0);

if ($alumni_id <= 0) {
    die("Invalid alumni ID.");
}


/*
|--------------------------------------------------------------------------
| Get Alumni Profile
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        a.*,
        u.email,
        u.account_status,
        d.department_name
    FROM alumni a

    LEFT JOIN users u
        ON a.user_id = u.user_id

    LEFT JOIN departments d
        ON a.department_id = d.department_id

    WHERE a.alumni_id = ?

    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $alumni_id);

$stmt->execute();

$result = $stmt->get_result();

$alumni = $result->fetch_assoc();

$stmt->close();


if (!$alumni) {
    die("Alumni record not found.");
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
        View Alumni |
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

</head>


<body class="admin-body">


<div class="admin-layout">


    <!-- =====================================================
         SIDEBAR
    ====================================================== -->

    <aside class="admin-sidebar">


        <div class="admin-brand">

            <div class="brand-logo">
                TM
            </div>

            <div>

                <strong>
                    Alumni System
                </strong>

                <small>
                    Registrar Panel
                </small>

            </div>

        </div>



        <nav class="admin-nav">


            <a href="dashboard.php">
                Dashboard
            </a>


            <div class="nav-section">
                REGISTRATION
            </div>


            <a href="pending.php">
                Pending Registrations
            </a>


            <a href="approved.php">
                Approved Registrations
            </a>


            <a href="rejected.php">
                Rejected Registrations
            </a>


            <div class="nav-section">
                ALUMNI
            </div>


            <a href="add.php">
                Add Alumni
            </a>


            <a
                href="alumni.php"
                class="active"
            >
                Alumni Directory
            </a>


            <div class="nav-section">
                SYSTEM
            </div>


            <a
                href="../../auth/logout.php"
                class="logout-link"
            >
                Logout
            </a>


        </nav>


    </aside>



    <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->

    <main class="admin-main">


        <!-- TOPBAR -->

        <header class="admin-topbar">


            <div>

                <h1>
                    Alumni Profile
                </h1>

                <p>
                    View registered alumni information.
                </p>

            </div>


            <div class="admin-user">


                <div class="admin-avatar">
                    R
                </div>


                <div>

                    <strong>
                        Registrar
                    </strong>

                    <small>
                        Registration Officer
                    </small>

                </div>


            </div>


        </header>



        <!-- CONTENT -->
 <section class="dashboard-content">


            <!-- =================================================
                 PROFILE HEADER
            ================================================== -->

            <div class="dashboard-panel">


                <div
                    style="
                        display:flex;
                        align-items:center;
                        gap:20px;
                        flex-wrap:wrap;
                    "
                >


                    <?php if (
                        !empty($alumni["profile_photo"])
                    ): ?>


                        <img
                            src="../../uploads/<?= e($alumni["profile_photo"]) ?>"
                            alt="Profile Photo"
                            style="
                                width:120px;
                                height:120px;
                                object-fit:cover;
                                border-radius:50%;
                            "
                        >


                    <?php else: ?>


                        <div
                            style="
                                width:120px;
                                height:120px;
                                border-radius:50%;
                                display:flex;
                                align-items:center;
                                justify-content:center;
                                background:#eee;
                                font-size:40px;
                                font-weight:bold;
                            "
                        >

                            <?= e(
                                strtoupper(
                                    substr(
                                        $alumni["first_name"],
                                        0,
                                        1
                                    )
                                )
                            ) ?>

                        </div>


                    <?php endif; ?>


                    <div>

                        <h2>

                            <?= e(
                                $alumni["first_name"]
                                . " "
                                . $alumni["last_name"]
                            ) ?>

                        </h2>


                        <p>

                            <strong>
                                Alumni ID:
                            </strong>

                            <?= e(
                                $alumni["alumni_id_number"]
                            ) ?>

                        </p>


                        <p>

                            <strong>
                                Department:
                            </strong>

                            <?= e(
                                $alumni["department_name"]
                            ) ?>

                        </p>


                    </div>


                </div>


            </div>



            <!-- =================================================
                 PERSONAL INFORMATION
            ================================================== -->

            <div class="dashboard-panel">


                <div class="panel-header">

                    <div>

                        <h2>
                            Personal Information
                        </h2>

                    </div>

                </div>


                <div class="form-grid">


                    <div class="form-field">

                        <label>
                            First Name
                        </label>

                        <p>
                            <?= e(
                                $alumni["first_name"]
                            ) ?>
                        </p>

                    </div>


                    <div class="form-field">
 <label>
                            Last Name
                        </label>

                        <p>
                            <?= e(
                                $alumni["last_name"]
                            ) ?>
                        </p>

                    </div>


                    <div class="form-field">

                        <label>
                            Gender
                        </label>

                        <p>
                            <?= e(
                                $alumni["gender"]
                            ) ?>
                        </p>

                    </div>


                    <div class="form-field">

                        <label>
                            Date of Birth
                        </label>

                        <p>
                            <?= e(
                                $alumni["date_of_birth"]
                            ) ?>
                        </p>

                    </div>


                    <div class="form-field">

                        <label>
                            Phone
                        </label>

                        <p>
                            <?= e(
                                $alumni["phone"]
                            ) ?>
                        </p>

                    </div>


                    <div class="form-field">

                        <label>
                            Email
                        </label>

                        <p>
                            <?= e(
                                $alumni["email"]
                            ) ?>
                        </p>

                    </div>


                    <div class="form-field form-full">

                        <label>
                            Address
                        </label>

                        <p>
                            <?= e(
                                $alumni["address"]
                            ) ?>
                        </p>

                    </div>


                </div>


            </div>



            <!-- =================================================
                 EDUCATION
            ================================================== -->

            <div class="dashboard-panel">


                <div class="panel-header">

                    <div>

                        <h2>
                            Education Information
                        </h2>

                    </div>

                </div>


                <div class="form-grid">


                    <div class="form-field">

                        <label>
                            Department
                        </label>

                        <p>
                            <?= e(
                                $alumni["department_name"]
                            ) ?>
                        </p>

                    </div>


                    <div class="form-field">

                        <label>
                            Graduation Year
                        </label>

                        <p>
                            <?= e(
                                $alumni["graduation_year"]
                            ) ?>
                        </p>

                    </div>


                </div>


            </div>



            <!-- =================================================
                 BIOGRAPHY
            ================================================== -->

            <div class="dashboard-panel">


                <div class="panel-header">

                    <div>

                        <h2>
                            Biography
                        </h2>

                    </div>

                </div>


                <?php if (
                    !empty($alumni["bio"])
                ): ?>


                    <p>
                        <?= nl2br(
                            e($alumni["bio"])
                        ) ?>
                    </p>
 <?php else: ?>


                    <p>
                        No biography provided.
                    </p>


                <?php endif; ?>


            </div>



            <!-- =================================================
                 ACCOUNT INFORMATION
            ================================================== -->

            <div class="dashboard-panel">


                <div class="panel-header">

                    <div>

                        <h2>
                            Account Information
                        </h2>

                    </div>

                </div>


                <div class="form-grid">


                    <div class="form-field">

                        <label>
                            Account Status
                        </label>

                        <p>
                            <?= e(
                                $alumni["account_status"]
                            ) ?>
                        </p>

                    </div>


                    <div class="form-field">

                        <label>
                            Alumni Record ID
                        </label>

                        <p>
                            <?= e(
                                $alumni["alumni_id"]
                            ) ?>
                        </p>

                    </div>


                </div>


            </div>



            <!-- =================================================
                 ACTIONS
            ================================================== -->

            <div class="form-actions">


                <a
                    href="alumni.php"
                    class="secondary-button"
                >
                    ← Back to Alumni Directory
                </a>


            </div>


        </section>


    </main>


</div>


</body>

</html>