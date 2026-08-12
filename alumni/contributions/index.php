<?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";


/*
|--------------------------------------------------------------------------
| ALUMNI ACCESS
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {

    header("Location: ../../auth/login.php");
    exit;

}

if ($_SESSION["role"] !== "alumni") {

    header("Location: ../../index.php");
    exit;

}


$user_id = (int) $_SESSION["user_id"];


/*
|--------------------------------------------------------------------------
| GET ALUMNI CONTRIBUTIONS
|--------------------------------------------------------------------------
*/

$sql = "

    SELECT

        contribution_id,
        alumni_id,
        contribution_type,
        description,
        amount,
        contribution_date,
        purpose,
        status,
        verified_by,
        verified_at,
        created_at

    FROM contributions

    WHERE alumni_id = ?

    ORDER BY created_at DESC

";


$stmt = $conn->prepare($sql);


if (!$stmt) {

    die(
        "Database error: "
        . $conn->error
    );

}


$stmt->bind_param(
    "i",
    $user_id
);


$stmt->execute();


$result =
    $stmt->get_result();


$stmt->close();

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

        My Contributions |

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

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;

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


        .add-button {

            display: inline-block;

            padding: 11px 18px;

            background: #7a4b2a;

            color: #ffffff;

            text-decoration: none;

            border-radius: 8px;

            font-weight: 600;

            white-space: nowrap;

        }


        .add-button:hover {

            background: #5f3921;

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

            min-width: 800px;

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


        .approved {

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

            margin-bottom: 8px;

        }


        @media (max-width: 650px) {

            .contributions-page {

                padding: 15px;

            }


            .page-header {

                flex-direction: column;

                align-items: flex-start;

            }

        }

    </style>

</head>


<body class="admin-body">


<div class="admin-layout">


    <!-- SIDEBAR -->

<?php
$currentPage = "contributions";
require_once __DIR__ . "/../includes/sidebar.php";
?>
    


        
    

    <!-- MAIN -->

    <main class="admin-main">


        <header class="admin-topbar">


            <div>

                <h1>
                    My Contributions
                </h1>

                <p>
                    View your contributions and donation records.
                </p>

            </div>


        </header>


        <section class="dashboard-content">


            <div class="contributions-page">


                <div class="page-header">


                    <div>

                        <h1>
                            My Contributions
                        </h1>

                        <p>
                            Track your submitted contributions and their verification status.
                        </p>

                    </div>


                    <a
                        href="add.php"
                        class="add-button"
                    >
                        + Add Contribution
                    </a>


                </div>


                <div class="table-wrapper">


                    <?php if (
                        $result->num_rows === 0
                    ): ?>


                        <div class="empty-state">


                            <h3>
                                No Contributions Yet
                            </h3>
                            <p>
                                You have not submitted any contributions.
                            </p>


                            <a
                                href="add.php"
                                class="add-button"
                            >
                                Add Your First Contribution
                            </a>


                        </div>


                    <?php else: ?>


                        <table class="contributions-table">


                            <thead>


                                <tr>

                                    <th>
                                        ID
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


                                <?php while (
                                    $contribution =
                                        $result->fetch_assoc()
                                ): ?>


                                    <?php

                                    $status =
                                        strtolower(
                                            trim(
                                                $contribution[
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
                                                $contribution[
                                                    "contribution_id"
                                                ]
                                            ?>

                                        </td>


                                        <td>

                                            <?= e(
                                                $contribution[
                                                    "contribution_type"
                                                ]
                                            ) ?>

                                        </td>


                                        <td>
                                         <?= e(
                                                $contribution[
                                                    "amount"
                                                ]
                                            ) ?>

                                        </td>


                                        <td>

                                            <?= e(
                                                $contribution[
                                                    "purpose"
                                                ]
                                            ) ?>

                                        </td>


                                        <td>

                                            <?= e(
                                                $contribution[
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
                                                    $contribution[
                                                        "status"
                                                    ]
                                                ) ?>

                                            </span>


                                        </td>


                                        <td>


                                            <a
                                                href="view.php?id=<?= (int) $contribution["contribution_id"] ?>"
                                                class="view-button"
                                            >
                                                View
                                            </a>


                                        </td>


                                    </tr>


                                <?php endwhile; ?>


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