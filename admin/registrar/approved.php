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

/*
|--------------------------------------------------------------------------
| Get Approved Registrations
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        r.registration_id,
        r.college_id_number,
        r.first_name,
        r.last_name,
        r.email,
        r.graduation_year,
        r.verified_at,
        d.department_name
    FROM alumni_registrations r
    LEFT JOIN departments d
        ON r.department_id = d.department_id
    WHERE r.status = 'approved'
    ORDER BY r.verified_at DESC
";

$result = $conn->query($sql);

if (!$result) {
    error_log(
        "Database error in registrar/approved.php: " .
        $conn->error
    );

    die(
        "Unable to load approved registrations. " .
        "Please try again later."
    );
}
?>

<?php
$activePage="approved";
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
        Approved Registrations |
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

</head>

<body class="admin-body">

<div class="admin-layout">

       <?php
 include "includes/sidebar.php"; 
?>


    <!-- Main -->

    <main class="admin-main">

        <!-- Topbar -->

        <header class="admin-topbar">

            <div>

                <h1>
                    Approved Registrations
                </h1>

                <p>
                    Alumni applications that have been
                    verified and approved.
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


        <!-- Content -->

        <section class="dashboard-content">

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Verified Alumni
                        </h2>

                        <p>
                            These applications have been successfully
                            verified by the Registrar.
                        </p>

                    </div>

                </div>


                <?php if ($result->num_rows === 0): ?>

                    <div class="form-alert success-message">

                        No approved registrations.

                    </div>

                <?php else: ?>
 <div style="overflow-x: auto;">

                        <table class="data-table">

                            <thead>

                                <tr>

                                    <th>Name</th>

                                    <th>Alumni ID</th>

                                    <th>Email</th>

                                    <th>Department</th>

                                    <th>Graduation Year</th>

                                    <th>Verified At</th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php while ($row = $result->fetch_assoc()): ?>

                                    <tr>

                                        <td>

                                            <?= e(
                                                $row["first_name"]
                                                . " "
                                                . $row["last_name"]
                                            ) ?>

                                        </td>

                                        <td>

                                            <?= e(
                                                $row["college_id_number"]
                                            ) ?>

                                        </td>

                                        <td>

                                            <?= e(
                                                $row["email"]
                                            ) ?>

                                        </td>

                                        <td>

                                            <?= e(
                                                $row["department_name"]
                                            ) ?>

                                        </td>

                                        <td>

                                            <?= e(
                                                $row["graduation_year"]
                                            ) ?>

                                        </td>

                                        <td>

                                            <?= e(
                                                $row["verified_at"]
                                            ) ?>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            </tbody>

                        </table>

                    </div>

                <?php endif; ?>

            </div>


            <!-- Back -->

            <div class="form-actions">

                <a
                    href="dashboard.php"
                    class="secondary-button"
                >
                    ← Back to Dashboard
                </a>

            </div>

        </section>

    </main>

</div>

</body>

</html>