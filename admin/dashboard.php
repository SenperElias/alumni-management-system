<?php

session_start();

require_once "../config/database.php";
require_once "../config/config.php";
require_once "../includes/functions.php";


/*
|--------------------------------------------------------------------------
| Admin Access
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "admin") {
    header("Location: ../index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/

$totalAlumni = 0;
$totalEmployed = 0;
$totalUnemployed = 0;
$totalOpportunities = 0;
$pendingOpportunities = 0;
$approvedOpportunities = 0;


/* Total Alumni */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM alumni
");

if ($result) {
    $row = $result->fetch_assoc();
    $totalAlumni = (int) $row["total"];
}


/* Employment Statistics */

$result = $conn->query("
    SELECT
        SUM(employment_status = 'Employed') AS employed,
        SUM(employment_status = 'Unemployed') AS unemployed
    FROM employment
");

if ($result) {

    $row = $result->fetch_assoc();

    $totalEmployed = (int) ($row["employed"] ?? 0);
    $totalUnemployed = (int) ($row["unemployed"] ?? 0);
}


/* Opportunity Statistics */

$result = $conn->query("
    SELECT
        COUNT(*) AS total,
        SUM(status = 'Pending') AS pending,
        SUM(status = 'Approved') AS approved
    FROM opportunities
");

if ($result) {

    $row = $result->fetch_assoc();

    $totalOpportunities = (int) ($row["total"] ?? 0);
    $pendingOpportunities = (int) ($row["pending"] ?? 0);
    $approvedOpportunities = (int) ($row["approved"] ?? 0);
}


/*
|--------------------------------------------------------------------------
| Recent Opportunities
|--------------------------------------------------------------------------
*/

$recentOpportunities = $conn->query("
    SELECT
        opportunity_id,
        title,
        company_name,
        type,
        status,
        created_at
    FROM opportunities
    ORDER BY created_at DESC
    LIMIT 5
");


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
        Admin Dashboard |
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


 <?php
    require_once __DIR__ . "/includes/sidebar.php";
    ?>



    <!-- =========================================================
         MAIN CONTENT
    ========================================================== -->

    <main class="admin-main">


        <!-- TOP BAR -->

        <header class="admin-topbar">


            <div>

                <h1>
                    Admin Dashboard
                </h1>

                <p>
                    Welcome back. Manage your alumni system from here.
                </p>

            </div>


        </header>



        <section class="dashboard-content">


            <!-- =================================================
                 STATISTICS
            ================================================== -->

            <div class="stats-grid">


                <!-- TOTAL ALUMNI -->

                <div class="stat-card">

                    <div class="stat-icon">
                        👥
                    </div>

                    <div>

                        <span>
                            Total Alumni
                        </span>

                        <strong>
                            <?= $totalAlumni ?>
                        </strong>

                    </div>

                </div>



                <!-- EMPLOYED -->

                <div class="stat-card">

                    <div class="stat-icon">
                        💼
                    </div>

                    <div>

                        <span>
                            Employed
                        </span>

                        <strong>
                            <?= $totalEmployed ?>
                        </strong>

                    </div>

                </div>



                <!-- UNEMPLOYED -->

                <div class="stat-card">

                    <div class="stat-icon">
                        📊
                    </div>

                    <div>

                        <span>
                            Unemployed
                        </span>

                        <strong>
                            <?= $totalUnemployed ?>
                        </strong>

                    </div>

                </div>



                <!-- OPPORTUNITIES -->

                <div class="stat-card">

                    <div class="stat-icon">
                        🎯
                    </div>

                    <div>

                        <span>
                            Opportunities
                        </span>

                        <strong>
                            <?= $totalOpportunities ?>
                        </strong>

                    </div>

                </div>


            </div>



            <!-- =================================================
                 OPPORTUNITY SUMMARY
            ================================================== -->

            <div class="dashboard-grid">


                <div class="dashboard-panel">


                    <div class="panel-header">

                        <div>

                            <h2>
                                Opportunity Status
                            </h2>

                            <p>
                                Current opportunity submissions.
                            </p>

                        </div>

                    </div>


                    <div class="quick-stats">


                        <div>

                            <span>
                                Pending
                            </span>

                            <strong>
                                <?= $pendingOpportunities ?>
                            </strong>
                            </div>


                        <div>

                            <span>
                                Approved
                            </span>

                            <strong>
                                <?= $approvedOpportunities ?>
                            </strong>

                        </div>


                    </div>


                </div>



                <!-- QUICK ACTIONS -->

                <div class="dashboard-panel">


                    <div class="panel-header">

                        <div>

                            <h2>
                                Quick Actions
                            </h2>

                            <p>
                                Common administration tasks.
                            </p>

                        </div>

                    </div>


                    <div class="quick-actions">


                        <a
                            href="alumni/add.php"
                            class="secondary-button"
                        >
                            + Add Alumni
                        </a>


                        <a
                            href="opportunities/add.php"
                            class="secondary-button"
                        >
                            + Add Opportunity
                        </a>


                        <a
                            href="alumni/index.php"
                            class="secondary-button"
                        >
                            Manage Alumni
                        </a>


                    </div>


                </div>


            </div>



            <!-- =================================================
                 RECENT OPPORTUNITIES
            ================================================== -->

            <div class="dashboard-panel">


                <div class="panel-header">


                    <div>

                        <h2>
                            Recent Opportunities
                        </h2>

                        <p>
                            Latest jobs, internships and training opportunities.
                        </p>

                    </div>


                    <a
                        href="opportunities/index.php"
                        class="secondary-button"
                    >
                        View All
                    </a>


                </div>



                <?php if (
                    $recentOpportunities &&
                    $recentOpportunities->num_rows > 0
                ): ?>


                    <div class="table-responsive">


                        <table class="admin-table">


                            <thead>

                                <tr>

                                    <th>
                                        Title
                                    </th>

                                    <th>
                                        Company
                                    </th>

                                    <th>
                                        Type
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                    <th>
                                        Date
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                                <?php while (
                                    $opportunity =
                                    $recentOpportunities->fetch_assoc()
                                ): ?>


                                    <tr>


                                        <td>

                                            <strong>
                                                <?= e(
                                                    $opportunity["title"]
                                                ) ?>

                                            </strong>

                                        </td>


                                        <td>

                                            <?= e(
                                                $opportunity["company_name"]
                                            ) ?>

                                        </td>


                                        <td>

                                            <?= e(
                                                $opportunity["type"]
                                            ) ?>

                                        </td>


                                        <td>

                                            <span
                                                class="status-badge <?= e(
                                                    strtolower(
                                                        $opportunity["status"]
                                                    )
                                                ) ?>"
                                            >

                                                <?= e(
                                                    $opportunity["status"]
                                                ) ?>

                                            </span>

                                        </td>


                                        <td>

                                            <?= e(
                                                date(
                                                    "M d, Y",
                                                    strtotime(
                                                        $opportunity["created_at"]
                                                    )
                                                )
                                            ) ?>

                                        </td>


                                    </tr>


                                <?php endwhile; ?>


                            </tbody>


                        </table>


                    </div>


                <?php else: ?>


                    <div class="empty-dashboard">


                        <div>
                            💼
                        </div>


                        <h3>
                            No opportunities yet
                        </h3>


                        <p>
                            Opportunities will appear here when they are created.
                        </p>


                    </div>


                <?php endif; ?>


            </div>


        </section>


    </main>


</div>


</body>

</html>