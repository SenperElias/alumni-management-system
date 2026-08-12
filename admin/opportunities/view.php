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
| GET OPPORTUNITY ID
|--------------------------------------------------------------------------
*/

$opportunity_id = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;

if ($opportunity_id <= 0) {
    die("Invalid opportunity ID.");
}


/*
|--------------------------------------------------------------------------
| APPROVE / REJECT
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";

    if ($action === "approve") {

        $new_status = "Approved";

    } elseif ($action === "reject") {

        $new_status = "Rejected";

    } else {

        $new_status = "";
    }


    if ($new_status !== "") {

        $reviewed_by = (int) $_SESSION["user_id"];


        $sql = "
            UPDATE opportunities
            SET
                status = ?,
                reviewed_by = ?,
                reviewed_at = NOW()
            WHERE opportunity_id = ?
        ";


        $stmt = $conn->prepare($sql);


        if (!$stmt) {
            die("Database error: " . $conn->error);
        }


        $stmt->bind_param(
            "sii",
            $new_status,
            $reviewed_by,
            $opportunity_id
        );


        if ($stmt->execute()) {

            header(
                "Location: view.php?id=" .
                $opportunity_id .
                "&success=" .
                strtolower($new_status)
            );

            exit;

        } else {

            die(
                "Failed to update opportunity: " .
                $stmt->error
            );
        }
    }
}


/*
|--------------------------------------------------------------------------
| GET OPPORTUNITY
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        o.*
    FROM opportunities o
    WHERE o.opportunity_id = ?
    LIMIT 1
";


$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}


$stmt->bind_param(
    "i",
    $opportunity_id
);


$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows === 0) {
    die("Opportunity not found.");
}


$opportunity = $result->fetch_assoc();


/*
|--------------------------------------------------------------------------
| SUCCESS MESSAGE
|--------------------------------------------------------------------------
*/

$success = $_GET["success"] ?? "";

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
        Opportunity Details |
        <?= e(SITE_NAME) ?>
    </title>


    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >


    <style>

        .opportunity-view-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 25px;
        }


        .opportunity-view-header h2 {
            margin: 8px 0;
        }


        .opportunity-view-header p {
            margin: 0;
        }


        .opportunity-type {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            background: #f1e7df;
            color: #7a4b2a;
        }
        .opportunity-info-grid {
            display: grid;
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
            gap: 20px;
            margin-top: 20px;
        }


        .opportunity-info-item {
            padding: 15px;
            background: #faf8f6;
            border-radius: 8px;
        }


        .opportunity-info-item span {
            display: block;
            font-size: 13px;
            color: #777;
            margin-bottom: 5px;
        }


        .opportunity-info-item strong {
            color: #333;
        }


        .opportunity-description {
            line-height: 1.7;
            color: #444;
        }


        /* =====================================================
           REVIEW SECTION
        ===================================================== */

        .review-panel {
            margin-top: 25px;
            border: 2px solid #d8c5b5;
        }


        .review-panel h2 {
            margin-top: 0;
            margin-bottom: 8px;
        }


        .review-panel p {
            color: #666;
        }


        .review-status {
            display: inline-block;
            margin: 10px 0 20px;
            padding: 7px 14px;
            border-radius: 6px;
            font-weight: 600;
        }


        .review-buttons {
            display: flex;
            gap: 15px;
            align-items: center;
            margin-top: 20px;
        }


        .review-buttons button {
            min-width: 130px;
            padding: 13px 22px;
            border: none;
            border-radius: 7px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }


        .approve-button {
            background: #7a4b2a;
            color: white;
        }


        .approve-button:hover {
            background: #60391f;
        }


        .reject-button {
            background: #b3261e;
            color: white;
        }


        .reject-button:hover {
            background: #8f1e18;
        }


        .success-message {
            padding: 15px 18px;
            margin-bottom: 20px;
            border-radius: 8px;
            background: #e8f5e9;
            color: #27632a;
            border: 1px solid #b7dfba;
        }


        .warning-message {
            padding: 15px 18px;
            margin-bottom: 20px;
            border-radius: 8px;
            background: #fff7e6;
            color: #805b00;
            border: 1px solid #ead49a;
        }


        @media (max-width: 700px) {

            .opportunity-view-header {
                flex-direction: column;
            }


            .opportunity-info-grid {
                grid-template-columns: 1fr;
            }


            .review-buttons {
                flex-direction: column;
                align-items: stretch;
            }


            .review-buttons button {
                width: 100%;
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


            <a href="../alumni/index.php">
                Alumni
            </a>


            <a
                href="index.php"
                class="active"
            >
                Opportunities
            </a>


            <a href="#">
                Events
            </a>
            <a href="#">
                Mentorship
            </a>


            <a href="#">
                Projects
            </a>


            <div class="nav-section">
                REPORTS
            </div>


            <a href="#">
                Employment Reports
            </a>


            <a href="#">
                Alumni Reports
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



    <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->

    <main class="admin-main">


        <header class="admin-topbar">


            <div>

                <h1>
                    Opportunity Details
                </h1>


                <p>
                    Review and manage this opportunity.
                </p>

            </div>


            <a
                href="index.php"
                class="secondary-button"
            >
                ← Back
            </a>


        </header>



        <section class="dashboard-content">


            <!-- =================================================
                 SUCCESS MESSAGE
            ================================================== -->

            <?php if ($success === "approved"): ?>

                <div class="success-message">

                    Opportunity has been
                    <strong>approved</strong>
                    successfully.

                </div>

            <?php endif; ?>


            <?php if ($success === "rejected"): ?>

                <div class="warning-message">

                    Opportunity has been
                    <strong>rejected</strong>
                    successfully.

                </div>

            <?php endif; ?>



            <!-- =================================================
                 BASIC INFORMATION
            ================================================== -->

            <div class="dashboard-panel">


                <div class="opportunity-view-header">


                    <div>


                        <span class="opportunity-type">

                            <?= e(
                                $opportunity["type"]
                            ) ?>

                        </span>


                        <h2>

                            <?= e(
                                $opportunity["title"]
                            ) ?>

                        </h2>


                        <p>

                            <strong>

                                <?= e(
                                    $opportunity["company_name"]
                                    ?: "Company not specified"
                                ) ?>

                            </strong>

                        </p>


                    </div>


                    <div>


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


                    </div>


                </div>



                <!-- INFORMATION -->

                <div class="opportunity-info-grid">


                    <div class="opportunity-info-item">

                        <span>
                            Location
                        </span>


                        <strong>
                         <?= e(
                                !empty(
                                    $opportunity["location"]
                                )
                                ? $opportunity["location"]
                                : "Not specified"
                            ) ?>

                        </strong>

                    </div>



                    <div class="opportunity-info-item">

                        <span>
                            Deadline
                        </span>


                        <strong>

                            <?php

                            $deadline =
                                $opportunity["deadline"]
                                ?? "";

                            if (
                                empty($deadline) ||
                                $deadline === "0000-00-00"
                            ) {

                                echo "Not specified";

                            } else {

                                echo e($deadline);

                            }

                            ?>

                        </strong>

                    </div>



                    <div class="opportunity-info-item">

                        <span>
                            Created By
                        </span>


                        <strong>

                            <?= e(
                                $opportunity["created_by"]
                                ?? "Unknown"
                            ) ?>

                        </strong>

                    </div>



                    <div class="opportunity-info-item">

                        <span>
                            Created At
                        </span>


                        <strong>

                            <?= e(
                                $opportunity["created_at"]
                                ?? "Unknown"
                            ) ?>

                        </strong>

                    </div>


                </div>


            </div>



            <!-- =================================================
                 DESCRIPTION
            ================================================== -->

            <div class="dashboard-panel">


                <h2>
                    Description
                </h2>


                <div class="opportunity-description">

                    <?php

                    if (
                        !empty(
                            $opportunity["description"]
                        )
                    ) {

                        echo nl2br(
                            e(
                                $opportunity["description"]
                            )
                        );

                    } else {

                        echo "No description provided.";

                    }

                    ?>

                </div>


            </div>



            <!-- =================================================
                 REQUIREMENTS
            ================================================== -->

            <div class="dashboard-panel">


                <h2>
                    Requirements
                </h2>


                <div class="opportunity-description">

                    <?php

                    if (
                        !empty(
                            $opportunity["requirements"]
                        )
                    ) {

                        echo nl2br(
                            e(
                                $opportunity["requirements"]
                            )
                        );

                    } else {

                        echo "No requirements provided.";

                    }

                    ?>

                </div>


            </div>



            <!-- =================================================
                 CONTACT INFORMATION
            ================================================== -->
             <div class="dashboard-panel">


                <h2>
                    Contact Information
                </h2>


                <div class="opportunity-description">

                    <?php

                    if (
                        !empty(
                            $opportunity["contact_info"]
                        )
                    ) {

                        echo nl2br(
                            e(
                                $opportunity["contact_info"]
                            )
                        );

                    } else {

                        echo "No contact information provided.";

                    }

                    ?>

                </div>


            </div>



            <!-- =================================================
                 REVIEW OPPORTUNITY
            ================================================== -->

            <div class="dashboard-panel review-panel">


                <h2>
                    Review Opportunity
                </h2>


                <p>
                    Current opportunity status:
                </p>


                <span
                    class="review-status"
                >

                    <?= e(
                        $opportunity["status"]
                    ) ?>

                </span>

<?php if (
    strtolower(trim($opportunity["status"])) === "pending"
): ?>




                    <p>
                        Review this opportunity and choose
                        whether it should be available to alumni.
                    </p>


                    <form
                        method="POST"
                    >


                        <div class="review-buttons">


                            <!-- REJECT -->

                            <button
                                type="submit"
                                name="action"
                                value="reject"
                                class="reject-button"
                                onclick="return confirm('Are you sure you want to reject this opportunity?');"
                            >

                                Reject

                            </button>



                            <!-- APPROVE -->

                            <button
                                type="submit"
                                name="action"
                                value="approve"
                                class="approve-button"
                                onclick="return confirm('Are you sure you want to approve this opportunity?');"
                            >

                                Approve

                            </button>


                        </div>


                    </form>


                <?php else: ?>


                    <p>

                        This opportunity has already been reviewed.

                    </p>


                    <div class="opportunity-info-grid">


                        <div class="opportunity-info-item">

                            <span>
                                Reviewed By
                            </span>


                            <strong>

                                <?= e(
                                    $opportunity["reviewed_by"]
                                    ?? "Not available"
                                ) ?>

                            </strong>

                        </div>


                        <div class="opportunity-info-item">

                            <span>
                                Reviewed At
                            </span>


                            <strong>

                                <?= e(
                                    $opportunity["reviewed_at"]
                                    ?? "Not available"
                                ) ?>

                            </strong>

                        </div>


                    </div>


                <?php endif; ?>  
                </div>


        </section>


    </main>


</div>


</body>

</html>