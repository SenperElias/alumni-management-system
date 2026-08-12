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


$adminId = (int) $_SESSION["user_id"];

$error = "";
$success = "";


/*
|--------------------------------------------------------------------------
| FILTER
|--------------------------------------------------------------------------
*/

$filter = trim($_GET["status"] ?? "all");


$allowedFilters = [
    "all",
    "pending",
    "approved",
    "rejected"
];


if (!in_array($filter, $allowedFilters, true)) {
    $filter = "all";
}


/*
|--------------------------------------------------------------------------
| HANDLE APPROVE / REJECT
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $employmentId = (int) (
        $_POST["employment_id"] ?? 0
    );

    $action = $_POST["action"] ?? "";


    if ($employmentId <= 0) {

        $error = "Invalid employment record.";

    } else {


        if ($action === "approve") {

            $verificationStatus = "approved";

            $verificationNotes =
                "Employment information approved by admin.";

        }

        elseif ($action === "reject") {

            $verificationStatus = "rejected";

            $verificationNotes =
                trim(
                    $_POST["verification_notes"] ?? ""
                );

            if ($verificationNotes === "") {

                $verificationNotes =
                    "Employment information rejected by admin.";

            }

        }

        else {

            $verificationStatus = "";

        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE RECORD
        |--------------------------------------------------------------------------
        */

        if ($verificationStatus !== "") {

            $stmt = $conn->prepare(
                "
                UPDATE employment

                SET
                    Verification_status = ?,
                    Verification_notes = ?,
                    Updated_by = ?,
                    Updated_at = NOW()

                WHERE employment_id = ?
                "
            );


            if (!$stmt) {

                $error =
                    "Database error: " .
                    $conn->error;

            } else {

                $stmt->bind_param(
                    "ssii",
                    $verificationStatus,
                    $verificationNotes,
                    $adminId,
                    $employmentId
                );


                if ($stmt->execute()) {

                    if (
                        $verificationStatus ===
                        "approved"
                    ) {

                        $success =
                            "Employment information approved successfully.";

                    } else {

                        $success =
                            "Employment information rejected.";

                    }

                } else {

                    $error =
                        "Unable to update employment verification.";

                }


                $stmt->close();

            }

        }

    }

}


/*
|--------------------------------------------------------------------------
| GET EMPLOYMENT RECORDS
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT

        e.employment_id,
        e.alumni_id,
        e.employment_status,
        e.company_name,
        e.job_position,
        e.Work_location,
        e.industry,
        e.employment_date,
        e.End_date,

        e.Verification_status,
        e.Verification_notes,

        e.Updated_by,
        e.created_at,
        e.Updated_at,

        a.first_name,
        a.last_name

    FROM employment e

    INNER JOIN alumni a
        ON e.alumni_id = a.alumni_id
";


if ($filter === "pending") {

    $sql .= "
        WHERE LOWER(
            TRIM(e.Verification_status)
        ) = 'pending'
    ";

}

elseif ($filter === "approved") {

    $sql .= "
        WHERE LOWER(
            TRIM(e.Verification_status)
        ) = 'approved'
    ";

}

elseif ($filter === "rejected") {

    $sql .= "
        WHERE LOWER(
            TRIM(e.Verification_status)
        ) = 'rejected'
    ";

}


$sql .= "
    ORDER BY e.created_at DESC
";


$stmt = $conn->prepare($sql);


if (!$stmt) {

    die(
        "Database error: " .
        $conn->error
    );

}


$stmt->execute();

$employmentResult =
    $stmt->get_result();


/*
|--------------------------------------------------------------------------
| COUNT RECORDS
|--------------------------------------------------------------------------
*/

$countSql = "
    SELECT

        COUNT(*) AS total,

        SUM(
            LOWER(
                TRIM(Verification_status)
            ) = 'pending'
        ) AS pending,

        SUM(
            LOWER(
                TRIM(Verification_status)
            ) = 'approved'
        ) AS approved,

        SUM(
            LOWER(
                TRIM(Verification_status)
            ) = 'rejected'
        ) AS rejected

    FROM employment
";


$countResult =
    $conn->query($countSql);


$counts =
    $countResult->fetch_assoc();


$totalCount =
    (int) (
        $counts["total"] ?? 0
    );


$pendingCount =
    (int) (
        $counts["pending"] ?? 0
    );


$approvedCount =
    (int) (
        $counts["approved"] ?? 0
    );


$rejectedCount =
    (int) (
        $counts["rejected"] ?? 0
    );

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
        Employment Verification |
        <?= e(SITE_NAME) ?>
    </title>


    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >


    <style>

        .verification-header {
            margin-bottom: 25px;
        }


        .verification-header h1 {
            margin-bottom: 6px;
        }


        .verification-header p {
            color: #777;
        }


        .verification-stats {

            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 15px;

            margin-bottom: 25px;
        }


        .verification-stat {

            background: #fff;

            border: 1px solid #eee;

            border-radius: 10px;

            padding: 20px;

        }


        .verification-stat span {

            display: block;

            color: #777;

            font-size: 13px;

            margin-bottom: 8px;

        }


        .verification-stat strong {

            font-size: 25px;

            color: #333;

        }


        .verification-filters {

            display: flex;

            gap: 10px;

            flex-wrap: wrap;

            margin-bottom: 20px;

        }


        .verification-filters a {

            text-decoration: none;

            padding: 9px 15px;

            border-radius: 7px;

            background: #eee;

            color: #444;

            font-size: 14px;

            font-weight: 600;

        }


        .verification-filters a.active {

            background: #7a4b2a;

            color: #fff;

        }


        .employment-verification-card {

            background: #fff;

            border: 1px solid #eee;

            border-radius: 10px;

            padding: 22px;

            margin-bottom: 18px;

        }
        .employment-card-top {

            display: flex;

            justify-content: space-between;

            align-items: flex-start;

            gap: 20px;

            margin-bottom: 20px;

        }


        .employment-card-top h3 {

            margin: 0 0 6px;

        }


        .employment-card-top p {

            margin: 0;

            color: #777;

        }


        .verification-badge {

            display: inline-block;

            padding: 6px 11px;

            border-radius: 6px;

            font-size: 12px;

            font-weight: 700;

        }


        .verification-badge.pending {

            background: #fff4d6;

            color: #7a5700;

        }


        .verification-badge.approved {

            background: #e8f5e9;

            color: #27632a;

        }


        .verification-badge.rejected {

            background: #fdecea;

            color: #a12622;

        }


        .employment-info-grid {

            display: grid;

            grid-template-columns:
                repeat(3, minmax(0, 1fr));

            gap: 15px;

        }


        .employment-info-item {

            padding: 13px;

            background: #faf8f6;

            border-radius: 7px;

        }


        .employment-info-item span {

            display: block;

            color: #777;

            font-size: 12px;

            margin-bottom: 5px;

        }


        .employment-info-item strong {

            color: #333;

            overflow-wrap: anywhere;

        }


        .verification-note {

            margin-top: 18px;

            padding: 13px;

            background: #faf8f6;

            border-left: 3px solid #7a4b2a;

        }


        .verification-actions {

            display: flex;

            gap: 10px;

            flex-wrap: wrap;

            margin-top: 20px;

        }


        .verification-actions form {

            margin: 0;

        }


        .approve-button,

        .reject-button {

            border: none;

            padding: 10px 17px;

            border-radius: 7px;

            font-weight: 600;

            cursor: pointer;

        }


        .approve-button {

            background: #2e7d32;

            color: #fff;

        }


        .reject-button {

            background: #c62828;

            color: #fff;

        }


        .reject-note {

            width: 100%;

            max-width: 450px;

            min-height: 80px;

            padding: 10px;

            border: 1px solid #ddd;

            border-radius: 7px;

            resize: vertical;

            box-sizing: border-box;

            margin-bottom: 8px;

        }


        .success-message {

            padding: 14px;

            margin-bottom: 20px;

            border-radius: 8px;

            background: #e8f5e9;

            color: #27632a;

        }


        .error-message {

            padding: 14px;

            margin-bottom: 20px;

            border-radius: 8px;

            background: #fdecea;

            color: #a12622;

        }


        .empty-state {

            text-align: center;

            padding: 50px 20px;

            color: #777;

        }


        @media (max-width: 900px) {

            .verification-stats {

                grid-template-columns:
                    repeat(2, 1fr);

            }


            .employment-info-grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }

        }


        @media (max-width: 600px) {

            .verification-stats {

                grid-template-columns: 1fr;

            }


            .employment-info-grid {

                grid-template-columns: 1fr;

            }


            .employment-card-top {

                flex-direction: column;

            }

        }

    </style>

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
                    Admin Portal
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


            <a href="../users.php">
                Users
            </a>


            <a href="../alumni.php">
                Alumni
            </a>


            <a
                href="index.php"
                class="active"
            >
                Employment
            </a>


            <a href="../opportunities/index.php">
                Opportunities
            </a>


            <a href="../mentors/index.php">
                Mentors
            </a>


            <div class="nav-section">
                REPORTS
            </div>


            <a href="../reports.php">
                Reports
            </a>


            <div class="nav-section">
                SYSTEM
            </div>


            <a href="../settings.php">
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



    <!-- =====================================================
         MAIN
    ====================================================== -->

    <main class="admin-main">


        <header class="admin-topbar">


            <div class="verification-header">

                <h1>
                    Employment Verification
                </h1>


                <p>
                    Review employment information submitted by alumni.
                </p>

            </div>


        </header>



        <section class="dashboard-content">


            <!-- =================================================
                 MESSAGES
            ================================================== -->

            <?php if ($success !== ""): ?>

                <div class="success-message">

                    <?= e($success) ?>

                </div>

            <?php endif; ?>


            <?php if ($error !== ""): ?>

                <div class="error-message">

                    <?= e($error) ?>

                </div>

            <?php endif; ?>



            <!-- =================================================
                 STATISTICS
            ================================================== -->

            <div class="verification-stats">


                <div class="verification-stat">

                    <span>
                        Total Records
                    </span>

                    <strong>
                        <?= $totalCount ?>
                    </strong>

                </div>



                <div class="verification-stat">

                    <span>
                        Pending
                    </span>

                    <strong>
                        <?= $pendingCount ?>
                    </strong>

                </div>



                <div class="verification-stat">

                    <span>
                        Approved
                    </span>

                    <strong>
                        <?= $approvedCount ?>
                    </strong>

                </div>



                <div class="verification-stat">

                    <span>
                        Rejected
                    </span>

                    <strong>
                        <?= $rejectedCount ?>
                    </strong>

                </div>


            </div>
            <!-- =================================================
                 FILTERS
            ================================================== -->

            <div class="verification-filters">


                <a
                    href="index.php?status=all"
                    class="<?= $filter === 'all'
                        ? 'active'
                        : '' ?>"
                >
                    All
                </a>


                <a
                    href="index.php?status=pending"
                    class="<?= $filter === 'pending'
                        ? 'active'
                        : '' ?>"
                >
                    Pending
                </a>


                <a
                    href="index.php?status=approved"
                    class="<?= $filter === 'approved'
                        ? 'active'
                        : '' ?>"
                >
                    Approved
                </a>


                <a
                    href="index.php?status=rejected"
                    class="<?= $filter === 'rejected'
                        ? 'active'
                        : '' ?>"
                >
                    Rejected
                </a>


            </div>



            <!-- =================================================
                 EMPLOYMENT RECORDS
            ================================================== -->

            <?php if (
                $employmentResult->num_rows > 0
            ): ?>


                <?php while (
                    $job =
                        $employmentResult->fetch_assoc()
                ): ?>


                    <?php

                    $verificationStatus =
                        strtolower(
                            trim(
                                $job[
                                    "Verification_status"
                                ] ?? "pending"
                            )
                        );

                    ?>


                    <div
                        class="employment-verification-card"
                    >


                        <!-- CARD HEADER -->

                        <div
                            class="employment-card-top"
                        >


                            <div>

                                <h3>

                                    <?= e(
                                        $job["first_name"] .
                                        " " .
                                        $job["last_name"]
                                    ) ?>

                                </h3>


                                <p>

                                    <?= e(
                                        $job[
                                            "job_position"
                                        ]
                                    ) ?>

                                    at

                                    <?= e(
                                        $job[
                                            "company_name"
                                        ]
                                    ) ?>

                                </p>

                            </div>


                            <span class="verification-badge <?= e($verificationStatus) ?>">
    <?= e(ucfirst($verificationStatus)) ?>
</span>


                        </div>



                        <!-- EMPLOYMENT INFORMATION -->

                        <div
                            class="employment-info-grid"
                        >
                    <div
                                class="employment-info-item"
                            >

                                <span>
                                    Employment Status
                                </span>


                                <strong>

                                    <?= e(
                                        $job[
                                            "employment_status"
                                        ]
                                    ) ?>

                                </strong>

                            </div>



                            <div
                                class="employment-info-item"
                            >

                                <span>
                                    Company
                                </span>


                                <strong>

                                    <?= e(
                                        $job[
                                            "company_name"
                                        ]
                                    ) ?>

                                </strong>

                            </div>



                            <div
                                class="employment-info-item"
                            >

                                <span>
                                    Job Position
                                </span>


                                <strong>

                                    <?= e(
                                        $job[
                                            "job_position"
                                        ]
                                    ) ?>

                                </strong>

                            </div>



                            <div
                                class="employment-info-item"
                            >

                                <span>
                                    Work Location
                                </span>


                                <strong>

                                    <?= e(
                                        $job[
                                            "Work_location"
                                        ]
                                        ?: "Not provided"
                                    ) ?>

                                </strong>

                            </div>



                            <div
                                class="employment-info-item"
                            >

                                <span>
                                    Industry
                                </span>


                                <strong>

                                    <?= e(
                                        $job[
                                            "industry"
                                        ]
                                        ?: "Not provided"
                                    ) ?>

                                </strong>

                            </div>



                            <div
                                class="employment-info-item"
                            >

                                <span>
                                    Start Date
                                </span>


                                <strong>

                                    <?= e(
                                        $job[
                                            "employment_date"
                                        ]
                                    ) ?>

                                </strong>

                            </div>



                            <div
                                class="employment-info-item"
                            >

                                <span>
                                    End Date
                                </span>


                                <strong>
                  <?php if (
                                        !empty(
                                            $job[
                                                "End_date"
                                            ]
                                        )
                                    ): ?>

                                        <?= e(
                                            $job[
                                                "End_date"
                                            ]
                                        ) ?>

                                    <?php else: ?>

                                        Present

                                    <?php endif; ?>

                                </strong>

                            </div>


                        </div>



                        <!-- EXISTING ADMIN NOTE -->

                        <?php if (
                            !empty(
                                $job[
                                    "Verification_notes"
                                ]
                            )
                        ): ?>


                            <div
                                class="verification-note"
                            >

                                <strong>
                                    Verification Note:
                                </strong>


                                <?= e(
                                    $job[
                                        "Verification_notes"
                                    ]
                                ) ?>

                            </div>


                        <?php endif; ?>



                        <!-- ACTIONS -->

                        <?php if (
                            $verificationStatus ===
                            "pending"
                        ): ?>


                            <div
                                class="verification-actions"
                            >


                                <!-- APPROVE -->

                                <form
                                    method="POST"
                                    onsubmit="
                                        return confirm(
                                            'Are you sure you want to approve this employment information?'
                                        );
                                    "
                                >

                                    <input
                                        type="hidden"
                                        name="employment_id"
                                        value="<?= (int) (
                                            $job[
                                                "employment_id"
                                            ]
                                        ) ?>"
                                    >


                                    <input
                                        type="hidden"
                                        name="action"
                                        value="approve"
                                    >


                                    <button
                                        type="submit"
                                        class="approve-button"
                                    >
                                        Approve
                                    </button>

                                </form>



                                <!-- REJECT -->

                                <form
                                    method="POST"
                                    style="
                                        display:flex;        
                                           flex-direction:column;
                                        gap:8px;
                                    "
                                    onsubmit="
                                        return confirm(
                                            'Are you sure you want to reject this employment information?'
                                        );
                                    
                                >

                                    <input
                                        type="hidden"
                                        name="employment_id"
                                        value="<?= (int) (
                                            $job[
                                                "employment_id"
                                            ]
                                        ) ?>"
                                    >


                                    <input
                                        type="hidden"
                                        name="action"
                                        value="reject"
                                    >


                                    <textarea
                                        name="verification_notes"
                                        class="reject-note"
                                        placeholder="Optional rejection reason..."
                                    ></textarea>


                                    <button
                                        type="submit"
                                        class="reject-button"
                                    >
                                        Reject
                                    </button>

                                </form>


                            </div>


                        <?php endif; ?>


                    </div>


                <?php endwhile; ?>


            <?php else: ?>


                <div
                    class="dashboard-panel empty-state"
                >

                    <div style="font-size:40px;">
                        💼
                    </div>


                    <h3>
                        No employment records found
                    </h3>


                    <p>
                        There are no employment records matching this filter.
                    </p>

                </div>


            <?php endif; ?>


        </section>


    </main>


</div>


</body>

</html>
