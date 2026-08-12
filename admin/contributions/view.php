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


$admin_id = (int) $_SESSION["user_id"];


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
| HANDLE VERIFY / REJECT
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $action =
        $_POST["action"] ?? "";


    if (
        $action === "verify"
        || $action === "reject"
    ) {


        if ($action === "verify") {

            $new_status = "Verified";

        } else {

            $new_status = "Rejected";

        }


        $update_sql = "

            UPDATE contributions

            SET

                status = ?,

                verified_by = ?,

                verified_at = NOW()

               

            WHERE contribution_id = ?

        ";


        $update_stmt =
            $conn->prepare($update_sql);


        if (!$update_stmt) {

            die(
                "Database error: "
                . $conn->error
            );

        }


        $update_stmt->bind_param(

            "sii",

            $new_status,

            $admin_id,

            $contribution_id

        );


        if (
            !$update_stmt->execute()
        ) {

            die(
                "Unable to update contribution: "
                . $update_stmt->error
            );

        }


        $update_stmt->close();


        header(
            "Location: view.php?id="
            . $contribution_id
            . "&updated=1"
        );

        exit;

    }

}


/*
|--------------------------------------------------------------------------
| GET CONTRIBUTION
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
        a.alumni_id_number,
        a.first_name,
        a.last_name,
        a.phone
        

    FROM contributions c

    INNER JOIN alumni a

        ON c.alumni_id = a.alumni_id

    WHERE c.contribution_id = ?

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
    "i",
    $contribution_id
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


/*
|--------------------------------------------------------------------------
| STATUS
|--------------------------------------------------------------------------
*/

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

            max-width: 1000px;

            margin: 30px auto;

            padding: 20px;

        }


        .back-link {

            display: inline-block;

            margin-bottom: 20px;

            color: #7a4b2a;

            text-decoration: none;

            font-weight: 600;

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


        .page-title {

            display: flex;

            justify-content: space-between;

            align-items: flex-start;

            gap: 20px;

            padding-bottom: 20px;

            margin-bottom: 25px;

            border-bottom: 1px solid #eeeeee;

        }


        .page-title h1 {

            margin: 0 0 8px;

            color: #4a2c1d;

        }


        .page-title p {

            margin: 0;

            color: #777;

        }


        .status-badge {

            display: inline-block;

            padding: 8px 14px;

            border-radius: 20px;

            font-size: 12px;

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


        .section {

            margin-top: 25px;

            padding-top: 25px;

            border-top: 1px solid #eeeeee;

        }


        .section h2 {

            margin: 0 0 15px;

            color: #4a2c1d;

            font-size: 19px;

        }


        .details-grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 15px;

        }


        .detail-box {

            background: #f8f5f2;

            border-radius: 10px;

            padding: 16px;

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


        .description {

            background: #fafafa;

            border: 1px solid #eeeeee;

            border-radius: 10px;

            padding: 18px;

            color: #555;

            line-height: 1.7;

            white-space: pre-line;

        }


        .action-area {

            display: flex;

            gap: 12px;

            flex-wrap: wrap;

            margin-top: 25px;

        }


        .action-button {

            border: none;

            border-radius: 8px;

            padding: 11px 20px;

            cursor: pointer;

            font-weight: 600;

            font-size: 14px;

        }


        .verify-button {

            background: #2e7d32;

            color: #ffffff;

        }


        .verify-button:hover {

            background: #1b5e20;

        }


        .reject-button {

            background: #c62828;

            color: #ffffff;

        }


        .reject-button:hover {

            background: #8e0000;

        }


        .back-button {

            display: inline-block;

            padding: 11px 18px;

            background: #eeeeee;

            color: #444444;

            text-decoration: none;

            border-radius: 8px;

            font-weight: 600;

        }


        .success-message {

            background: #d4edda;

            color: #155724;

            border: 1px solid #c3e6cb;

            padding: 13px 15px;

            border-radius: 8px;
            margin-bottom: 20px;

        }


        @media (max-width: 650px) {

            .contribution-view-page {

                padding: 12px;

            }


            .contribution-card {

                padding: 20px;

            }


            .page-title {

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
                    Administration
                </small>

            </div>


        </div>


        <nav class="admin-nav">


            <a href="../dashboard.php">
                Dashboard
            </a>


            <div class="nav-section">
                MANAGEMENT
            </div>


            <a href="../alumni/index.php">
                Alumni
            </a>


            <a href="../employment/index.php">
                Employment
            </a>


            <a href="../events/index.php">
                Events
            </a>


            <a href="../opportunities/index.php">
                Opportunities
            </a>


            <a href="../projects/index.php">
                Projects
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


            <a href="../users/index.php">
                Users
            </a>


            <a href="#">
                Reports
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
                    Review and verify this alumni contribution.
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


                <?php if (
                    isset(
                        $_GET["updated"]
                    )
                ): ?>


                    <div class="success-message">

                        Contribution status updated successfully.

                    </div>


                <?php endif; ?>


                <div class="contribution-card">


                    <!-- HEADER -->


                    <div class="page-title">


                        <div>

                            <h1>

                                Contribution #

                                <?= (int)
                                    $contribution[
                                        "contribution_id"
                                    ]
                                ?>

                            </h1>


                            <p>

                                <?= e(
                                    $contribution[
                                        "contribution_type"
                                    ]
                                ) ?>

                            </p>

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


                    <!-- ALUMNI INFORMATION -->


                    <div class="section">


                        <h2>
                            Alumni Information
                        </h2>


                        <div class="details-grid">


                            <div class="detail-box">


                                <span>
                                    Name
                                </span>


                                <strong>

                                    <?= e(
                                        $contribution[
                                            "first_name"
                                        ]
                                    ) ?>

                                    <?= e(
                                        $contribution[
                                            "last_name"
                                        ]
                                    ) ?>

                                </strong>


                            </div>


                            <div class="detail-box">


                                <span>
                                    Alumni ID
                                </span>


                                <strong>

                                    <?= e(
                                        $contribution[
                                            "alumni_id_number"
                                        ]
                                    ) ?>

                                </strong>


                            </div>


                            <div class="detail-box">


                                <span>
                                    Phone
                                </span>


                                <strong>

                                    <?= e(
                                        $contribution[
                                            "phone"
                                        ]
                                    ) ?>

                                </strong>


                            </div>


                            <div class="detail-box">


                                <span>
                                    Alumni Record ID
                                </span>


                                <strong>

                                    <?= (int)
                                        $contribution[
                                            "alumni_id"
                                        ]
                                    ?>

                                </strong>


                            </div>


                        </div>


                    </div>


                    <!-- CONTRIBUTION INFORMATION -->


                    <div class="section">


                        <h2>
                            Contribution Information
                        </h2>


                        <div class="details-grid">


                            <div class="detail-box">


                                <span>
                                    Contribution Type
                                </span>


                                <strong>

                                    <?= e(
                                        $contribution[
                                            "contribution_type"
                                        ]
                                    ) ?>

                                </strong>


                            </div>


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


                        </div>


                    </div>


                    <!-- DESCRIPTION -->


                    <?php if (
                        trim(
                            $contribution[
                                "description"
                            ]
                        ) !== ""
                    ): ?>


                        <div class="section">


                            <h2>
                                Description
                            </h2>


                            <div class="description">

                                <?= e(
                                    $contribution[
                                        "description"
                                    ]
                                ) ?>

                            </div>


                        </div>


                    <?php endif; ?>


                    <!-- VERIFICATION -->


                    <div class="section">


                        <h2>
                            Verification
                        </h2>


                        <?php if (
                            $status === "verified"
                        ): ?>


                            <p>

                                This contribution is waiting for admin verification.

                            </p>


                        <?php elseif (
                            $status === "verified"
                            || $status === "approved"
                        ): ?>


                            <p>

                                This contribution has been verified.

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

                                This contribution has been rejected.

                            </p>


                        <?php endif; ?>


                    </div>


                    <!-- ACTIONS -->


                    <div class="action-area">


                        <?php if (
                            $status === "pending"
                        ): ?>


                            <form
                                method="POST"
                                action="view.php?id=<?= (int) $contribution["contribution_id"] ?>">

                                <input
                                    type="hidden"
                                    name="action"
                                    value="verify"
                                >


                                <button
                                    type="submit"
                                    class="action-button verify-button"
                                >

                                    ✓ Verify Contribution

                                </button>


                            </form>


                            <form
                                method="POST"
                                action=""
                                onsubmit="return confirm('Are you sure you want to reject this contribution?');"
                            >


                                <input
                                    type="hidden"
                                    name="action"
                                    value="reject"
                                >


                                <button
                                    type="submit"
                                    class="action-button reject-button"
                                >

                                    ✕ Reject Contribution

                                </button>


                            </form>


                        <?php endif; ?>


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