<?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";


/*
|--------------------------------------------------------------------------
| ADMIN ACCESS
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {

    header("Location: ../../auth/login.php");
    exit;

}

if ($_SESSION["role"] !== "admin") {

    header("Location: ../../index.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| TOTAL GRADUATES
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT COUNT(*) AS total
    FROM alumni
";

$result = $conn->query($sql);

$total_graduates = 0;

if ($result) {

    $row = $result->fetch_assoc();

    $total_graduates =
        (int) $row["total"];

}


/*
|--------------------------------------------------------------------------
| EMPLOYMENT COUNTS
|--------------------------------------------------------------------------
*/
$employed = 0;
$unemployed = 0;
$self_employed = 0;
$continuing_education = 0;

/*
|--------------------------------------------------------------------------
| EMPLOYMENT COUNTS
|--------------------------------------------------------------------------
| Count each alumnus only once using their latest employment record.
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        LOWER(TRIM(e.employment_status)) AS employment_status,
        COUNT(*) AS total
    FROM employment e
    INNER JOIN (
        SELECT
            alumni_id,
            MAX(employment_id) AS latest_employment_id
        FROM employment
        GROUP BY alumni_id
    ) latest
        ON latest.latest_employment_id = e.employment_id
    GROUP BY LOWER(TRIM(e.employment_status))
";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {

        $status = strtolower(trim($row["employment_status"]));
        $count = (int) $row["total"];

        if ($status === "employed") {
            $employed = $count;
        } elseif ($status === "unemployed") {
            $unemployed = $count;
        } elseif ($status === "self_employed") {
            $self_employed = $count;
        } elseif ($status === "continuing_education") {
            $continuing_education = $count;
        }
    }
}


/*
|--------------------------------------------------------------------------
| EMPLOYMENT RATE
|--------------------------------------------------------------------------
*/

$employment_rate = 0;


if ($total_graduates > 0) {

    $employment_rate =
        ($employed / $total_graduates)
        * 100;

}


/*|--------------------------------------------------------------------------
| DEPARTMENT EMPLOYMENT REPORT
|--------------------------------------------------------------------------
| Each alumnus is counted once.
| The latest employment record determines the current status.
|--------------------------------------------------------------------------
*/

$department_report = [];

$sql = "
    SELECT
        d.department_name,
        COUNT(a.alumni_id) AS total_graduates,

        SUM(
            CASE
                WHEN LOWER(TRIM(latest_employment.employment_status)) = 'employed'
                THEN 1
                ELSE 0
            END
        ) AS employed

    FROM departments d

    LEFT JOIN alumni a
        ON a.department_id = d.department_id

    LEFT JOIN (
        SELECT e1.*
        FROM employment e1
        INNER JOIN (
            SELECT
                alumni_id,
                MAX(COALESCE(Updated_at, created_at)) AS latest_date
            FROM employment
            GROUP BY alumni_id
        ) e2
            ON e1.alumni_id = e2.alumni_id
            AND COALESCE(e1.Updated_at, e1.created_at) = e2.latest_date
    ) latest_employment
        ON latest_employment.alumni_id = a.alumni_id

    GROUP BY
        d.department_id,
        d.department_name

    ORDER BY
        d.department_name
";

$result = $conn->query($sql);

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $department_total =
            (int) $row["total_graduates"];

        $department_employed =
            (int) $row["employed"];

        $department_rate = 0;

        if ($department_total > 0) {

            $department_rate =
                (
                    $department_employed
                    /
                    $department_total
                ) * 100;
        }

        $row["employment_rate"] =
            $department_rate;

        $department_report[] =
            $row;
    }
}
/*
/*|--------------------------------------------------------------------------
| GRADUATION YEAR REPORT
|--------------------------------------------------------------------------
| Each alumnus is counted once.
| The latest employment record determines the current status.
|--------------------------------------------------------------------------
*/

$year_report = [];

$sql = "
    SELECT
        a.graduation_year,

        COUNT(a.alumni_id) AS total_graduates,

        SUM(
            CASE
                WHEN LOWER(TRIM(latest_employment.employment_status)) = 'employed'
                THEN 1
                ELSE 0
            END
        ) AS employed

    FROM alumni a

    LEFT JOIN (
        SELECT e1.*
        FROM employment e1
        INNER JOIN (
            SELECT
                alumni_id,
                MAX(COALESCE(Updated_at, created_at)) AS latest_date
            FROM employment
            GROUP BY alumni_id
        ) e2
            ON e1.alumni_id = e2.alumni_id
            AND COALESCE(e1.Updated_at, e1.created_at) = e2.latest_date
    ) latest_employment
        ON latest_employment.alumni_id = a.alumni_id

    WHERE a.graduation_year IS NOT NULL

    GROUP BY
        a.graduation_year

    ORDER BY
        a.graduation_year DESC
";

$result = $conn->query($sql);

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $year_total =
            (int) $row["total_graduates"];

        $year_employed =
            (int) $row["employed"];

        $year_rate = 0;

        if ($year_total > 0) {

            $year_rate =
                (
                    $year_employed
                    /
                    $year_total
                ) * 100;
        }

        $row["employment_rate"] =
            $year_rate;

        $year_report[] =
            $row;
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

        Reports |

        <?= e(SITE_NAME) ?>

    </title>


    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >


    <style>

        .reports-page {

            padding: 30px;

        }


        .reports-header {

            margin-bottom: 25px;

        }


        .reports-header h1 {

            margin: 0 0 6px;

            color: #4a2c1d;

        }


        .reports-header p {

            margin: 0;

            color: #777;

        }


        .stats-grid {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 15px;

            margin-bottom: 30px;

        }


        .stat-card {

            background: #ffffff;

            border: 1px solid #eeeeee;

            border-radius: 12px;

            padding: 20px;

            box-shadow:
                0 4px 15px
                rgba(0,0,0,0.04);

        }


        .stat-card span {

            display: block;

            color: #777;

            font-size: 12px;

            margin-bottom: 8px;

        }


        .stat-card strong {

            color: #4a2c1d;

            font-size: 28px;

        }


        .report-section {

            background: #ffffff;

            border: 1px solid #eeeeee;

            border-radius: 14px;

            padding: 25px;

            margin-bottom: 25px;

            box-shadow:
                0 5px 20px
                rgba(0,0,0,0.04);

        }


        .report-section h2 {

            margin: 0 0 18px;

            color: #4a2c1d;

            font-size: 20px;

        }


        .table-wrapper {

            overflow-x: auto;

        }


        .report-table {

            width: 100%;

            border-collapse: collapse;

            min-width: 650px;

        }


        .report-table th {

            background: #f8f5f2;

            color: #4a2c1d;

            padding: 13px;

            text-align: left;

            font-size: 13px;

        }


        .report-table td {

            padding: 13px;

            border-top: 1px solid #eeeeee;

            color: #555;

        }


        .rate {

            font-weight: 700;

            color: #7a4b2a;

        }


        .empty {

            padding: 25px;

            text-align: center;

            color: #777;

        }


        @media (max-width: 900px) {

            .stats-grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }

        }


        @media (max-width: 600px) {
            .reports-page {

                padding: 15px;

            }

            .stats-grid {

                grid-template-columns: 1fr;

            }

        }

    </style>

</head>


<body class="admin-body">


<div class="admin-layout">
  <?php require_once __DIR__ ."/../includes/sidebar.php"; ?>
    <!-- MAIN -->

    <main class="admin-main">


        <header class="admin-topbar">

            <div>

                <h1>
                    Reports
                </h1>

                <p>
                    Alumni graduation and employment statistics.
                </p>

            </div>

        </header>


        <section class="dashboard-content">


            <div class="reports-page">


                <div class="reports-header">

                    <h1>
                        Employment Reports
                    </h1>

                    <p>
                        Overview of graduate employment and career outcomes.
                    </p>

                </div>


                <!-- SUMMARY -->

                <div class="stats-grid">


                    <div class="stat-card">

                        <span>
                            Total Graduates
                        </span>

                        <strong>
                            <?= $total_graduates ?>
                        </strong>

                    </div>


                    <div class="stat-card">

                        <span>
                            Employed
                        </span>

                        <strong>
                            <?= $employed ?>
                        </strong>

                    </div>


                    <div class="stat-card">

                        <span>
                            Unemployed
                        </span>

                        <strong>
                            <?= $unemployed ?>
                        </strong>

                    </div>

<div class="stat-card">
    <span>Continuing Education</span>
    <strong><?= $continuing_education ?></strong>
</div>

                    <div class="stat-card">

                        <span>
                            Employment Rate
                        </span>

                        <strong>
                            <?= number_format(
                                $employment_rate,
                                1
                            ) ?>%
                        </strong>

                    </div>


                </div>


                <!-- OTHER STATUS -->

                <div class="report-section">
                    <h2>
                        Employment Status Breakdown
                    </h2>


                    <div class="table-wrapper">


                        <table class="report-table">


                            <thead>

                                <tr>

                                    <th>
                                        Status
                                    </th>

                                    <th>
                                        Number
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                                <tr>

                                    <td>
                                        Employed
                                    </td>

                                    <td>
                                        <?= $employed ?>
                                    </td>

                                </tr>


                                <tr>

                                    <td>
                                        Unemployed
                                    </td>

                                    <td>
                                        <?= $unemployed ?>
                                    </td>

                                </tr>


                                <tr>

                                    <td>
                                        Self-employed
                                    </td>

                                    <td>
                                        <?= $self_employed ?>
                                    </td>

                                </tr>


                                <tr>

                                    <td>
                                        Continuing Education
                                    </td>

                                    <td>
                                        <?= $continuing_education ?>
                                    </td>

                                </tr>


                            </tbody>


                        </table>


                    </div>


                </div>


                <!-- DEPARTMENT -->

                <div class="report-section">


                    <h2>
                        Employment Rate by Department
                    </h2>


                    <div class="table-wrapper">


                        <?php if (
                            empty(
                                $department_report
                            )
                        ): ?>


                            <div class="empty">
                                No department data available.
                            </div>


                        <?php else: ?>


                            <table class="report-table">


                                <thead>

                                    <tr>

                                        <th>
                                            Department
                                        </th>

                                        <th>
                                            Graduates
                                        </th>

                                        <th>
                                            Employed
                                        </th>

                                        <th>
                                            Employment Rate
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>


                                    <?php foreach (
                                        $department_report
                                        as $department
                                    ): ?>


                                        <tr>
                              <td>
                                                <?= e(
                                                    $department[
                                                        "department_name"
                                                    ]
                                                ) ?>
                                            </td>


                                            <td>
                                                <?= (int)
                                                    $department[
                                                        "total_graduates"
                                                    ]
                                                ?>
                                            </td>


                                            <td>
                                                <?= (int)
                                                    $department[
                                                        "employed"
                                                    ]
                                                ?>
                                            </td>


                                            <td class="rate">

                                                <?= number_format(
                                                    $department[
                                                        "employment_rate"
                                                    ],
                                                    1
                                                ) ?>%

                                            </td>

                                        </tr>


                                    <?php endforeach; ?>


                                </tbody>


                            </table>


                        <?php endif; ?>


                    </div>


                </div>


                <!-- GRADUATION YEAR -->

                <div class="report-section">


                    <h2>
                        Employment Rate by Graduation Year
                    </h2>


                    <div class="table-wrapper">


                        <?php if (
                            empty(
                                $year_report
                            )
                        ): ?>


                            <div class="empty">
                                No graduation-year data available.
                            </div>


                        <?php else: ?>


                            <table class="report-table">


                                <thead>

                                    <tr>

                                        <th>
                                            Graduation Year
                                        </th>

                                        <th>
                                            Graduates
                                        </th>

                                        <th>
                                            Employed
                                        </th>

                                        <th>
                                            Employment Rate
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>


                                    <?php foreach (
                                        $year_report
                                        as $year
                                    ): ?>


                                        <tr>

                                            <td>
                                                <?= e(
                                                    $year[
                                                        "graduation_year"
                                                    ]
                                                ) ?>
                                            </td>
                                 <td>
                                                <?= (int)
                                                    $year[
                                                        "total_graduates"
                                                    ]
                                                ?>
                                            </td>


                                            <td>
                                                <?= (int)
                                                    $year[
                                                        "employed"
                                                    ]
                                                ?>
                                            </td>


                                            <td class="rate">

                                                <?= number_format(
                                                    $year[
                                                        "employment_rate"
                                                    ],
                                                    1
                                                ) ?>%

                                            </td>

                                        </tr>


                                    <?php endforeach; ?>


                                </tbody>


                            </table>


                        <?php endif; ?>


                    </div>


                </div>


            </div>


        </section>


    </main>


</div>


</body>


</html>                         