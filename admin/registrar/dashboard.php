 <?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";

/*
|--------------------------------------------------------------------------
| REGISTRAR ACCESS
|--------------------------------------------------------------------------
*/

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
| OVERALL REGISTRATION COUNTS
|--------------------------------------------------------------------------
*/

$pendingCount = 0;
$approvedCount = 0;
$rejectedCount = 0;
$totalCount = 0;

$countResult = $conn->query("
    SELECT
        SUM(status = 'pending') AS pending_count,
        SUM(status = 'approved') AS approved_count,
        SUM(status = 'rejected') AS rejected_count,
        COUNT(*) AS total_count
    FROM alumni_registrations
");

if (!$countResult) {

    error_log(
        "Database error in registrar/dashboard.php " .
        "(registration counts): " .
        $conn->error
    );

    die(
        "Unable to load registration statistics. " .
        "Please try again later."
    );
}

$counts = $countResult->fetch_assoc();

$pendingCount = (int) ($counts["pending_count"] ?? 0);
$approvedCount = (int) ($counts["approved_count"] ?? 0);
$rejectedCount = (int) ($counts["rejected_count"] ?? 0);
$totalCount = (int) ($counts["total_count"] ?? 0);

/*
|--------------------------------------------------------------------------
| DEPARTMENT REGISTRATION STATISTICS
|--------------------------------------------------------------------------
|
| Every active department is displayed, even if it has zero registrations.
|
*/

$departmentStats = [];

$departmentSql = "
    SELECT
        d.department_id,
        d.department_name,

        COALESCE(
            SUM(r.status = 'pending'),
            0
        ) AS pending_count,

        COALESCE(
            SUM(r.status = 'approved'),
            0
        ) AS approved_count,

        COALESCE(
            SUM(r.status = 'rejected'),
            0
        ) AS rejected_count,

        COUNT(r.registration_id) AS total_count

    FROM departments d

    LEFT JOIN alumni_registrations r
        ON d.department_id = r.department_id

    WHERE d.status = 'active'

    GROUP BY
        d.department_id,
        d.department_name

    ORDER BY
        d.department_name ASC
";

$departmentResult = $conn->query($departmentSql);

if (!$departmentResult) {

    error_log(
        "Database error in registrar/dashboard.php " .
        "(department statistics): " .
        $conn->error
    );

    die(
        "Unable to load department statistics. " .
        "Please try again later."
    );
}

while ($row = $departmentResult->fetch_assoc()) {

    $departmentStats[] = $row;

}

?>
<?php
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
        Registrar Dashboard |
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
    <!-- =========================================================
         MAIN CONTENT
    ========================================================== -->

    <main class="admin-main">


        <!-- =====================================================
             TOPBAR
        ====================================================== -->

        <header class="admin-topbar">

            <div>

                <h1>
                    Registrar Dashboard
                </h1>

                <p>
                    Review and verify alumni registration applications.
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


        <!-- =====================================================
             DASHBOARD CONTENT
        ====================================================== -->

        <section class="dashboard-content">


            <!-- =================================================
                 OVERALL STATISTICS
            ================================================== -->

            <div class="stats-grid">


                <!-- Pending -->

                <div class="stat-card">

                    <div class="stat-card-icon">
                        P
                    </div>

                    <div>

                        <span>
                            Pending Registrations
                        </span>

                        <strong>
                            <?= $pendingCount ?>
                        </strong>

                    </div>

                </div>


                <!-- Approved -->

                <div class="stat-card">

                    <div class="stat-card-icon">
                        A
                    </div>

                    <div>

                        <span>
                            Approved Registrations
                        </span>

                        <strong>
                            <?= $approvedCount ?>
                        </strong>

                    </div>

                </div>


                <!-- Rejected -->

                <div class="stat-card">

                    <div class="stat-card-icon">
                        R
                    </div>

                    <div>

                        <span>
                            Rejected Registrations
                        </span>

                        <strong>
                            <?= $rejectedCount ?>
                        </strong>

                    </div>

                </div>


                <!-- Total -->

                <div class="stat-card">

                    <div class="stat-card-icon">
                        T
                    </div>

                    <div>

                        <span>
                            Total Registrations
                        </span>

                        <strong>
                            <?= $totalCount ?>
                        </strong>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 DEPARTMENT REGISTRATION OVERVIEW
            ================================================== -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>
 <h2>
                            Department Registration Overview
                        </h2>

                        <p>
                            View registration statistics for each department.
                            Click a department to see its registrations.
                        </p>

                    </div>

                </div>


                <?php if (empty($departmentStats)) { ?>

                    <div class="form-alert">

                        No active departments found.

                    </div>

                <?php } else { ?>


                    <div style="overflow-x: auto;">

                        <table class="data-table">

                            <thead>

                                <tr>

                                    <th>
                                        Department
                                    </th>

                                    <th>
                                        Pending
                                    </th>

                                    <th>
                                        Approved
                                    </th>

                                    <th>
                                        Rejected
                                    </th>

                                    <th>
                                        Total
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                                <?php

                                foreach ($departmentStats as $department) {

                                ?>

                                    <tr>


                                        <!-- Department -->

                                        <td>

                                            <a
                                                href="department.php?id=<?= (int) $department["department_id"] ?>"
                                                style="
                                                    color: #6b4f3a;
                                                    font-weight: 600;
                                                    text-decoration: underline;
                                                "
                                            >

                                                <?= e(
                                                    $department["department_name"]
                                                ) ?>

                                            </a>

                                        </td>


                                        <!-- Pending -->

                                        <td>

                                            <?= (int) $department["pending_count"] ?>

                                        </td>


                                        <!-- Approved -->

                                        <td>

                                            <?= (int) $department["approved_count"] ?>

                                        </td>


                                        <!-- Rejected -->

                                        <td>

                                            <?= (int) $department["rejected_count"] ?>

                                        </td>


                                        <!-- Total -->

                                        <td>

                                            <strong>

                                                <?= (int) $department["total_count"] ?>

                                            </strong>

                                        </td>


                                    </tr>


                                <?php

                                }

                                ?>


                            </tbody>

                        </table>

                    </div>


                <?php } ?>

            </div>
 <!-- =================================================
                 REGISTRATION MANAGEMENT
            ================================================== -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Registration Management
                        </h2>

                        <p>
                            Review alumni information and verify it
                            against official college records.
                        </p>

                    </div>

                </div>


                <div class="quick-actions">


                    <a
                        href="pending.php"
                        class="secondary-button"
                    >
                        Pending Registrations
                    </a>


                    <a
                        href="approved.php"
                        class="secondary-button"
                    >
                        View Approved
                    </a>


                    <a
                        href="rejected.php"
                        class="secondary-button"
                    >
                        View Rejected
                    </a>


                    

                </div>

            </div>


            <!-- =================================================
                 REGISTRAR RESPONSIBILITY
            ================================================== -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Registrar Responsibility
                        </h2>

                        <p>
                            The Registrar verifies the information
                            submitted by alumni before approving their
                            registration.
                        </p>

                    </div>

                </div>


                <ul>

                    <li>
                        Review submitted alumni information.
                    </li>

                    <li>
                        Cross-check information with official
                        college records.
                    </li>

                    <li>
                        Approve valid registrations.
                    </li>

                    <li>
                        Reject incorrect or unverifiable registrations.
                    </li>

                    <li>
                        Monitor registration statistics by department.
                    </li>

                </ul>

            </div>


        </section>

    </main>

</div>

</body>

</html>
                