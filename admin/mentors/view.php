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
| GET MENTOR ID
|--------------------------------------------------------------------------
*/

$mentor_profile_id = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;


if ($mentor_profile_id <= 0) {
    die("Invalid mentor profile ID.");
}


$message = "";
$message_type = "";


/*
|--------------------------------------------------------------------------
| HANDLE ADMIN ACTION
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";

    /*
    |--------------------------------------------------------------------------
    | APPROVE
    |--------------------------------------------------------------------------
    */

    if ($action === "approve") {

        $new_status = "Active";

    }

    /*
    |--------------------------------------------------------------------------
    | REJECT
    |--------------------------------------------------------------------------
    */

    elseif ($action === "reject") {

        $new_status = "Inactive";

    }

    /*
    |--------------------------------------------------------------------------
    | DEACTIVATE
    |--------------------------------------------------------------------------
    */

    elseif ($action === "deactivate") {

        $new_status = "Inactive";

    }

    /*
    |--------------------------------------------------------------------------
    | REACTIVATE
    |--------------------------------------------------------------------------
    */

    elseif ($action === "reactivate") {

        $new_status = "Active";

    }

    else {

        $new_status = "";

    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE STATUS
    |--------------------------------------------------------------------------
    */

    if ($new_status !== "") {

        $sql = "
            UPDATE mentor_profiles
            SET
                status = ?,
                updated_at = NOW()
            WHERE mentor_profile_id = ?
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die("Database error: " . $conn->error);
        }

        $stmt->bind_param(
            "si",
            $new_status,
            $mentor_profile_id
        );

        if ($stmt->execute()) {

            header(
                "Location: view.php?id=" .
                $mentor_profile_id .
                "&success=" .
                strtolower($new_status)
            );

            exit;

        } else {

            $message =
                "Unable to update mentor status.";

            $message_type = "error";
        }
    }
}


/*
|--------------------------------------------------------------------------
| SUCCESS MESSAGE
|--------------------------------------------------------------------------
*/

$success = $_GET["success"] ?? "";


/*
|--------------------------------------------------------------------------
| GET MENTOR PROFILE
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        mp.mentor_profile_id,
        mp.alumni_id,
        mp.expertise,
        mp.skills,
        mp.experience_years,
        mp.biography,
        mp.availability,
        mp.status,
        mp.created_at,
        mp.updated_at,

        a.user_id

    FROM mentor_profiles mp

    INNER JOIN alumni a
        ON mp.alumni_id = a.alumni_id
        WHERE mp.mentor_profile_id = ?

    LIMIT 1
";


$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}


$stmt->bind_param(
    "i",
    $mentor_profile_id
);


$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows === 0) {
    die("Mentor profile not found.");
}


$mentor = $result->fetch_assoc();


/*
|--------------------------------------------------------------------------
| NORMALIZE STATUS
|--------------------------------------------------------------------------
*/

$status = strtolower(
    trim(
        $mentor["status"] ?? ""
    )
);


/*
|--------------------------------------------------------------------------
| STATUS CLASS
|--------------------------------------------------------------------------
*/

if ($status === "pending") {

    $status_class = "status-pending";

}
elseif ($status === "active") {

    $status_class = "status-active";

}
elseif ($status === "inactive") {

    $status_class = "status-inactive";

}
else {

    $status_class = "status-unknown";

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
        Mentor Details |
        <?= e(SITE_NAME) ?>
    </title>


    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >


    <style>

        .mentor-status-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;

            padding: 20px;

            background: #faf8f6;

            border-radius: 10px;

            margin-bottom: 25px;
        }


        .status-badge {
            display: inline-block;

            padding: 7px 12px;

            border-radius: 6px;

            font-size: 13px;

            font-weight: 600;
        }


        .status-pending {
            background: #fff4d6;
            color: #7a5700;
        }


        .status-active {
            background: #e8f5e9;
            color: #27632a;
        }


        .status-inactive {
            background: #fdecea;
            color: #a12622;
        }


        .status-unknown {
            background: #eeeeee;
            color: #555555;
        }


        .mentor-detail-grid {
            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 20px;
        }


        .mentor-detail-item {
            padding: 18px;

            border: 1px solid #eee;

            border-radius: 8px;
        }


        .mentor-detail-item.full-width {
            grid-column: 1 / -1;
        }


        .mentor-detail-item span {
            display: block;

            color: #777;

            font-size: 13px;

            margin-bottom: 8px;
        }


        .mentor-detail-item strong {
            color: #333;
        }


        .mentor-description {
            line-height: 1.7;

            color: #444;

            white-space: normal;

            overflow-wrap: anywhere;
        }


        .mentor-actions {
            display: flex;

            gap: 12px;

            flex-wrap: wrap;

            margin-top: 25px;
        }


        .mentor-actions form {
            margin: 0;
        }


        .approve-button,
        .reject-button,
        .deactivate-button,
        .reactivate-button {
            border: none;

            padding: 11px 20px;

            border-radius: 7px;

            cursor: pointer;

            font-size: 14px;

            font-weight: 600;
        }


        .approve-button {
            background: #2e7d32;
            color: #fff;
        }


        .reject-button,
        .deactivate-button {
            background: #c62828;
            color: #fff;
        }


        .reactivate-button {
            background: #2e7d32;
            color: #fff;
        }


        .back-button {
            display: inline-block;

            margin-bottom: 20px;
        }
        .success-message {
            padding: 15px;

            margin-bottom: 20px;

            border-radius: 8px;

            background: #e8f5e9;

            color: #27632a;
        }


        .error-message {
            padding: 15px;

            margin-bottom: 20px;

            border-radius: 8px;

            background: #fdecea;

            color: #a12622;
        }


        @media (max-width: 700px) {

            .mentor-detail-grid {
                grid-template-columns: 1fr;
            }

            .mentor-detail-item.full-width {
                grid-column: auto;
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


            <a href="../opportunities/index.php">
                Opportunities
            </a>


            <a
                href="index.php"
                class="active"
            >
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
         MAIN CONTENT
    ====================================================== -->

    <main class="admin-main">


        <header class="admin-topbar">


            <div>

                <h1>
                    Mentor Details
                </h1>


                <p>
                    Review and manage this alumni mentor profile.
                </p>

            </div>


        </header>



        <section class="dashboard-content">


            <a
                href="index.php"
                class="secondary-button back-button"
            >
                ← Back to Mentors
            </a>



            <!-- =================================================
                 SUCCESS MESSAGE
            ================================================== -->

            <?php if ($success === "active"): ?>

                <div class="success-message">
                    Mentor profile is now Active.
                </div>

            <?php elseif ($success === "inactive"): ?>

                <div class="success-message">
                    Mentor profile is now Inactive.
                </div>

            <?php endif; ?>


            <!-- =================================================
                 ERROR MESSAGE
            ================================================== -->

            <?php if ($message !== ""): ?>

                <div class="error-message">
                    <?= e($message) ?>
                </div>

            <?php endif; ?>



            <!-- =================================================
                 PROFILE PANEL
            ================================================== -->
            <div class="dashboard-panel">


                <!-- STATUS -->

                <div class="mentor-status-box">


                    <div>

                        <strong>
                            Current Mentor Status
                        </strong>


                        <p>
                            This mentor profile is currently
                            <strong>
                                <?= e(
                                    $mentor["status"]
                                ) ?>
                            </strong>.
                        </p>

                    </div>


                    <span
                        class="status-badge
                        <?= e($status_class) ?>"
                    >

                        <?= e(
                            $mentor["status"]
                        ) ?>

                    </span>


                </div>



                <!-- DETAILS -->

                <div class="mentor-detail-grid">


                    <div class="mentor-detail-item">

                        <span>
                            Mentor Profile ID
                        </span>


                        <strong>
                            <?= (int) (
                                $mentor[
                                    "mentor_profile_id"
                                ]
                            ) ?>
                        </strong>

                    </div>



                    <div class="mentor-detail-item">

                        <span>
                            Alumni ID
                        </span>


                        <strong>
                            <?= (int) (
                                $mentor["alumni_id"]
                            ) ?>
                        </strong>

                    </div>



                    <div class="mentor-detail-item">

                        <span>
                            Expertise
                        </span>


                        <strong>
                            <?= e(
                                $mentor["expertise"]
                            ) ?>
                        </strong>

                    </div>



                    <div class="mentor-detail-item">

                        <span>
                            Experience
                        </span>


                        <strong>

                            <?= (int) (
                                $mentor[
                                    "experience_years"
                                ]
                            ) ?>

                            years

                        </strong>

                    </div>



                    <div class="mentor-detail-item">

                        <span>
                            Availability
                        </span>


                        <strong>
                            <?= e(
                                $mentor["availability"]
                            ) ?>
                        </strong>

                    </div>



                    <div class="mentor-detail-item">

                        <span>
                            Created At
                        </span>


                        <strong>
                            <?= e(
                                $mentor["created_at"]
                            ) ?>
                        </strong>

                    </div>



                    <div
                        class="mentor-detail-item full-width"
                    >

                        <span>
                            Skills
                        </span>


                        <div class="mentor-description">

                            <?= nl2br(
                                e(
                                    $mentor["skills"]
                                )
                            ) ?>

                        </div>

                    </div>
                    <div
                        class="mentor-detail-item full-width"
                    >

                        <span>
                            Biography
                        </span>


                        <div class="mentor-description">

                            <?= nl2br(
                                e(
                                    $mentor["biography"]
                                )
                            ) ?>

                        </div>

                    </div>


                </div>



                <!-- =================================================
                     ACTIONS
                ================================================== -->

                <div class="mentor-actions">


                    <?php if ($status === "pending"): ?>


                        <!-- APPROVE -->

                        <form
                            method="POST"
                            onsubmit="
                                return confirm(
                                    'Are you sure you want to approve this mentor?'
                                );
                            "
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
                                Approve Mentor
                            </button>

                        </form>



                        <!-- REJECT -->

                        <form
                            method="POST"
                            onsubmit="
                                return confirm(
                                    'Are you sure you want to reject this mentor?'
                                );
                            "
                        >

                            <input
                                type="hidden"
                                name="action"
                                value="reject"
                            >


                            <button
                                type="submit"
                                class="reject-button"
                            >
                                Reject Mentor
                            </button>

                        </form>


                    <?php elseif ($status === "active"): ?>


                        <!-- DEACTIVATE -->

                        <form
                            method="POST"
                            onsubmit="
                                return confirm(
                                    'Are you sure you want to deactivate this mentor?'
                                );
                            "
                        >

                            <input
                                type="hidden"
                                name="action"
                                value="deactivate"
                            >


                            <button
                                type="submit"
                                class="deactivate-button"
                            >
                                Deactivate Mentor
                            </button>

                        </form>


                    <?php elseif ($status === "inactive"): ?>


                        <!-- REACTIVATE -->

                        <form
                            method="POST"
                            onsubmit="
                                return confirm(
                                    'Are you sure you want to reactivate this mentor?'
                                );
                            "
                        >
                        <input
                                type="hidden"
                                name="action"
                                value="reactivate"
                            >


                            <button
                                type="submit"
                                class="reactivate-button"
                            >
                                Reactivate Mentor
                            </button>

                        </form>


                    <?php endif; ?>


                </div>


            </div>


        </section>


    </main>


</div>


</body>

</html>