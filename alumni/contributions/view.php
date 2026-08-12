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
| GET CONTRIBUTION ID
|--------------------------------------------------------------------------
*/

$contribution_id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);


if (!$contribution_id) {

    die("Invalid contribution ID.");

}


/*
|--------------------------------------------------------------------------
| GET CONTRIBUTION
|--------------------------------------------------------------------------
|
| The alumni can only view their own contribution.
|
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

    WHERE contribution_id = ?

      AND alumni_id = ?

    LIMIT 1

";


$stmt =
    $conn->prepare($sql);


if (!$stmt) {

    die(
        "Database error: "
        . $conn->error
    );

}


$stmt->bind_param(
    "ii",
    $contribution_id,
    $user_id
);


$stmt->execute();


$result =
    $stmt->get_result();


$contribution =
    $result->fetch_assoc();


$stmt->close();


if (!$contribution) {

    die(
        "Contribution not found."
    );

}


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


<!DOCTYPE html>

<html lang="en">


<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >


    <title>

        Contribution Details |

        <?= e(SITE_NAME) ?>

    </title>


    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >


    <style>

        .contribution-view-page {

            max-width: 900px;

            margin: 35px auto;

            padding: 20px;

        }


        .back-link {

            display: inline-block;

            margin-bottom: 20px;

            color: #7a4b2a;

            text-decoration: none;

            font-weight: 600;

        }


        .back-link:hover {

            text-decoration: underline;

        }


        .contribution-card {

            background: #ffffff;

            border: 1px solid #eeeeee;

            border-radius: 15px;

            padding: 30px;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.05);

        }


        .contribution-header {

            display: flex;

            justify-content: space-between;

            align-items: flex-start;

            gap: 20px;

            padding-bottom: 20px;

            margin-bottom: 25px;

            border-bottom: 1px solid #eeeeee;

        }


        .contribution-header h1 {

            margin: 0 0 8px;

            color: #4a2c1d;

        }


        .contribution-type {

            color: #7a4b2a;

            font-weight: 600;

        }


        .status-badge {

            display: inline-block;

            padding: 7px 12px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 700;

            white-space: nowrap;

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


        .details-grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 15px;

            margin-bottom: 25px;

        }


        .detail-box {

            background: #f8f5f2;

            padding: 16px;

            border-radius: 10px;

        }


        .detail-box span {

            display: block;

            color: #777;

            font-size: 12px;

            margin-bottom: 6px;

        }


        .detail-box strong {

            color: #4a2c1d;

        }


        .description-section {

            margin-top: 25px;

            padding-top: 25px;

            border-top: 1px solid #eeeeee;

        }


        .description-section h2 {

            margin-bottom: 10px;

            color: #4a2c1d;

            font-size: 20px;

        }


        .description-section p {

            margin: 0;

            color: #555;

            line-height: 1.8;

            white-space: pre-line;

        }


        .verification-section {

            margin-top: 25px;

            padding-top: 25px;

            border-top: 1px solid #eeeeee;

        }


        .verification-section h2 {

            color: #4a2c1d;

            font-size: 20px;

            margin-bottom: 10px;

        }


        .verification-section p {

            color: #666;

            line-height: 1.6;

        }


        .action-area {

            margin-top: 30px;

            padding-top: 20px;

            border-top: 1px solid #eeeeee;

        }


        .back-button {

            display: inline-block;

            padding: 11px 18px;

            background: #7a4b2a;

            color: #ffffff;

            text-decoration: none;

            border-radius: 8px;

            font-weight: 600;

        }


        .back-button:hover {

            background: #5f3921;

        }


        @media (max-width: 650px) {

            .contribution-view-page {

                padding: 12px;

            }


            .contribution-card {

                padding: 20px;

            }


            .contribution-header {

                flex-direction: column;

            }


            .details-grid {

                grid-template-columns: 1fr;

            }

        }

    </style>

</head>


<body class="admin-body">


<div class="admin-layout">


    <!-- SIDEBAR -->


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
                    Alumni Portal
                </small>

            </div>


        </div>


        <nav class="admin-nav">


            <a href="../dashboard.php">
                Dashboard
            </a>


            <div class="nav-section">
                MY ACCOUNT
            </div>


            <a href="../profile.php">
                My Profile
            </a>


            <a href="../employment.php">
                Employment
            </a>


            <div class="nav-section">
                OPPORTUNITIES
            </div>


            <a href="../jobs.php">
                Jobs & Internships
            </a>


            <a href="../mentorship/index.php">
                Mentorship
            </a>


            <div class="nav-section">
                ACTIVITIES
            </div>


            <a href="../projects/browse.php">
                Projects
            </a>


            <a href="../events/events.php">
                Events
            </a>


            <a
                href="index.php"
                class="active"
            >
                Contributions
            </a>
            <div class="nav-section">
                SYSTEM
            </div>


            <a href="#">
                Notifications
            </a>


            <a href="#">
                Settings
            </a>


            <a
                href="../../auth/logout.php"
                class="logout-link"
            >
                Logout
            </a>


        </nav>


    </aside>


    <!-- MAIN -->


    <main class="admin-main">


        <header class="admin-topbar">


            <div>

                <h1>
                    Contribution Details
                </h1>

                <p>
                    View your contribution record and verification status.
                </p>

            </div>


        </header>


        <section class="dashboard-content">


            <div class="contribution-view-page">


                <a
                    href="index.php"
                    class="back-link"
                >
                    ← Back to Contributions
                </a>


                <div class="contribution-card">


                    <div class="contribution-header">


                        <div>


                            <h1>
                                Contribution #<?= (int)
                                    $contribution[
                                        "contribution_id"
                                    ]
                                ?>
                            </h1>


                            <div class="contribution-type">

                                <?= e(
                                    $contribution[
                                        "contribution_type"
                                    ]
                                ) ?>

                            </div>


                        </div>


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


                    </div>


                    <div class="details-grid">


                        <div class="detail-box">


                            <span>
                                Amount
                            </span>


                            <strong>

                                <?= e(
                                    $contribution[
                                        "amount"
                                    ]
                                ) ?>

                            </strong>


                        </div>


                        <div class="detail-box">


                            <span>
                                Contribution Date
                            </span>


                            <strong>

                                <?= e(
                                    $contribution[
                                        "contribution_date"
                                    ]
                                ) ?>

                            </strong>


                        </div>


                        <div class="detail-box">


                            <span>
                                Purpose
                            </span>


                            <strong>

                                <?= e(
                                    $contribution[
                                        "purpose"
                                    ]
                                ) ?>

                            </strong>


                        </div>


                        <div class="detail-box">


                            <span>
                                Submitted
                            </span>


                            <strong>
                                <?= e(
                                    date(
                                        "M d, Y H:i",
                                        strtotime(
                                            $contribution[
                                                "created_at"
                                            ]
                                        )
                                    )
                                ) ?>

                            </strong>


                        </div>


                    </div>


                    <?php if (
                        trim(
                            $contribution[
                                "description"
                            ]
                        ) !== ""
                    ): ?>


                        <div class="description-section">


                            <h2>
                                Description
                            </h2>


                            <p>

                                <?= e(
                                    $contribution[
                                        "description"
                                    ]
                                ) ?>

                            </p>


                        </div>


                    <?php endif; ?>


                    <div class="verification-section">


                        <h2>
                            Verification
                        </h2>


                        <?php if (
                            $status === "pending"
                        ): ?>


                            <p>
                                Your contribution has been submitted and is waiting for admin verification.
                            </p>


                        <?php elseif (
                            $status === "verified"
                            || $status === "approved"
                        ): ?>


                            <p>
                                Your contribution has been verified by the administrator.
                            </p>


                            <?php if (
                                !empty(
                                    $contribution[
                                        "verified_at"
                                    ]
                                )
                            ): ?>


                                <p>

                                    Verified on:

                                    <?= e(
                                        date(
                                            "M d, Y H:i",
                                            strtotime(
                                                $contribution[
                                                    "verified_at"
                                                ]
                                            )
                                        )
                                    ) ?>

                                </p>


                            <?php endif; ?>


                        <?php elseif (
                            $status === "rejected"
                        ): ?>


                            <p>
                                Your contribution was rejected by the administrator.
                            </p>


                        <?php else: ?>


                            <p>

                                Current status:

                                <?= e(
                                    $contribution[
                                        "status"
                                    ]
                                ) ?>

                            </p>


                        <?php endif; ?>


                    </div>


                    <div class="action-area">


                        <a
                            href="index.php"
                            class="back-button"
                        >
                            Back to Contributions
                        </a>
                        </div>


                </div>


            </div>


        </section>


    </main>


</div>


</body>


</html>
