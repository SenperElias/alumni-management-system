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
| GET CONTRIBUTIONS
|--------------------------------------------------------------------------
*/

$sql = "

    SELECT

        c.contribution_id,
        c.alumni_id,
        c.contribution_type,
        c.description,
        c.amount,
        c.contribution_date,
        c.purpose,
        c.status,
        c.verified_by,
        c.verified_at,
        c.created_at,

        a.first_name,
        a.last_name,
        a.alumni_id_number

    FROM contributions c

    INNER JOIN alumni a
        ON c.alumni_id = a.alumni_id

    ORDER BY c.created_at DESC

";


$result =
    $conn->query($sql);


if (!$result) {

    die(
        "Database error: "
        . $conn->error
    );

}


/*
|--------------------------------------------------------------------------
| COUNTS
|--------------------------------------------------------------------------
*/

$total_count = 0;

$pending_count = 0;

$verified_count = 0;

$rejected_count = 0;


$rows = [];


while (
    $row = $result->fetch_assoc()
) {

    $rows[] = $row;


    $total_count++;


    $status =
        strtolower(
            trim(
                $row["status"]
            )
        );


    if (
        $status === "pending"
    ) {

        $pending_count++;

    }

    elseif (
        $status === "verified"
        || $status === "approved"
    ) {

        $verified_count++;

    }

    elseif (
        $status === "rejected"
    ) {

        $rejected_count++;

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

        Contributions Management |

        <?= e(SITE_NAME) ?>

    </title>


    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >


    <style>

        .contributions-page {

            padding: 30px;

        }


        .page-header {

            margin-bottom: 25px;

        }


        .page-header h1 {

            margin: 0 0 6px;

            color: #4a2c1d;

        }


        .page-header p {

            margin: 0;

            color: #777;

        }


        .stats-grid {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 15px;

            margin-bottom: 25px;

        }


        .stat-card {

            background: #ffffff;

            border: 1px solid #eeeeee;

            border-radius: 12px;

            padding: 20px;

            box-shadow:
                0 4px 15px
                rgba(0, 0, 0, 0.04);

        }


        .stat-card span {

            display: block;

            color: #777;

            font-size: 12px;

            margin-bottom: 8px;

        }


        .stat-card strong {

            font-size: 28px;

            color: #4a2c1d;

        }


        .table-wrapper {

            background: #ffffff;

            border: 1px solid #eeeeee;

            border-radius: 14px;

            overflow-x: auto;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.05);

        }


        .contributions-table {

            width: 100%;

            min-width: 950px;

            border-collapse: collapse;

        }


        .contributions-table th {

            background: #f8f5f2;

            color: #4a2c1d;

            padding: 14px;

            text-align: left;

            font-size: 13px;

            white-space: nowrap;

        }
        .contributions-table td {

            padding: 14px;

            border-top: 1px solid #eeeeee;

            color: #555;

            vertical-align: middle;

        }


        .contributions-table tr:hover {

            background: #fcfbfa;

        }


        .status-badge {

            display: inline-block;

            padding: 6px 10px;

            border-radius: 20px;

            font-size: 11px;

            font-weight: 700;

        }


        .pending {

            background: #fff3cd;

            color: #856404;

        }


        .verified {

            background: #d4edda;

            color: #155724;

        }


        .rejected {

            background: #f8d7da;

            color: #721c24;

        }


        .view-button {

            display: inline-block;

            padding: 8px 12px;

            background: #7a4b2a;

            color: #ffffff;

            text-decoration: none;

            border-radius: 7px;

            font-size: 12px;

            font-weight: 600;

        }


        .view-button:hover {

            background: #5f3921;

        }


        .empty-state {

            text-align: center;

            padding: 60px 20px;

            color: #777;

        }


        .empty-state h3 {

            color: #4a2c1d;

        }


        @media (max-width: 900px) {

            .stats-grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }

        }


        @media (max-width: 600px) {

            .contributions-page {

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


    <!-- SIDEBAR -->

  <?php require_once __DIR__ ."/../includes/sidebar.php"; ?>

    <!-- MAIN -->


    <main class="admin-main">


        <header class="admin-topbar">


            <div>

                <h1>
                    Contributions Management
                </h1>

                <p>
                    Review and verify alumni contributions.
                </p>

            </div>


        </header>


        <section class="dashboard-content">


            <div class="contributions-page">


                <div class="page-header">


                    <h1>
                        Contributions
                    </h1>
                 <p>
                        Manage submitted donations and other alumni contributions.
                    </p>


                </div>


                <!-- STATISTICS -->


                <div class="stats-grid">


                    <div class="stat-card">

                        <span>
                            Total Contributions
                        </span>

                        <strong>
                            <?= $total_count ?>
                        </strong>

                    </div>


                    <div class="stat-card">

                        <span>
                            Pending
                        </span>

                        <strong>
                            <?= $pending_count ?>
                        </strong>

                    </div>


                    <div class="stat-card">

                        <span>
                            Verified
                        </span>

                        <strong>
                            <?= $verified_count ?>
                        </strong>

                    </div>


                    <div class="stat-card">

                        <span>
                            Rejected
                        </span>

                        <strong>
                            <?= $rejected_count ?>
                        </strong>

                    </div>


                </div>


                <!-- TABLE -->


                <div class="table-wrapper">


                    <?php if (
                        empty($rows)
                    ): ?>


                        <div class="empty-state">


                            <h3>
                                No Contributions Found
                            </h3>


                            <p>
                                There are currently no alumni contributions to review.
                            </p>


                        </div>


                    <?php else: ?>


                        <table class="contributions-table">


                            <thead>


                                <tr>

                                    <th>
                                        ID
                                    </th>

                                    <th>
                                        Alumni
                                    </th>

                                    <th>
                                        Alumni ID
                                    </th>

                                    <th>
                                        Type
                                    </th>

                                    <th>
                                        Amount
                                    </th>

                                    <th>
                                        Purpose
                                    </th>

                                    <th>
                                        Date
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                    <th>
                                        Action
                                    </th>

                                </tr>


                            </thead>


                            <tbody>


                                <?php foreach (
                                    $rows
                                    as $row
                                ): ?>


                                    <?php

                                    $status =
                                        strtolower(
                                            trim(
                                                $row[
                                                    "status"
                                                ]
                                            )
                                        );
             $status_class =
                                        "pending";


                                    if (
                                        $status === "verified"
                                        || $status === "approved"
                                    ) {

                                        $status_class =
                                            "verified";

                                    }

                                    elseif (
                                        $status === "rejected"
                                    ) {

                                        $status_class =
                                            "rejected";

                                    }

                                    ?>


                                    <tr>


                                        <td>

                                            #<?= (int)
                                                $row[
                                                    "contribution_id"
                                                ]
                                            ?>

                                        </td>


                                        <td>

                                            <?= e(
                                                $row[
                                                    "first_name"
                                                ]
                                            ) ?>

                                            <?= e(
                                                $row[
                                                    "last_name"
                                                ]
                                            ) ?>

                                        </td>


                                        <td>

                                            <?= e(
                                                $row[
                                                    "alumni_id_number"
                                                ]
                                            ) ?>

                                        </td>


                                        <td>

                                            <?= e(
                                                $row[
                                                    "contribution_type"
                                                ]
                                            ) ?>

                                        </td>


                                        <td>

                                            <?= e(
                                                $row[
                                                    "amount"
                                                ]
                                            ) ?>

                                        </td>


                                        <td>

                                            <?= e(
                                                $row[
                                                    "purpose"
                                                ]
                                            ) ?>

                                        </td>


                                        <td>

                                            <?= e(
                                                $row[
                                                    "contribution_date"
                                                ]
                                            ) ?>

                                        </td>


                                        <td>


                                            <span
                                                class="status-badge
                                                <?= e(
                                                    $status_class
                                                ) ?>"
                                            >
                           <?= e(
                                                    $row[
                                                        "status"
                                                    ]
                                                ) ?>

                                            </span>


                                        </td>


                                        <td>


                                            <a
                                                href="view.php?id=<?= (int) $row["contribution_id"] ?>"
                                                class="view-button"
                                            >
                                                View
                                            </a>


                                        </td>


                                    </tr>


                                <?php endforeach; ?>


                            </tbody>


                        </table>


                    <?php endif; ?>


                </div>


            </div>


        </section>


    </main>


</div>


</body>


</html>                                               