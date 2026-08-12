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
| GET MENTORSHIP REQUESTS
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT

        mr.request_id,
        mr.mentor_id,
        mr.mentee_id,
        mr.message,
        mr.status,
        mr.created_at,
        mr.responded_at,

        mp.expertise,

        mentor_alumni.first_name AS mentor_first_name,
        mentor_alumni.last_name AS mentor_last_name,

        mentee_alumni.first_name AS mentee_first_name,
        mentee_alumni.last_name AS mentee_last_name

    FROM mentorship_requests mr

    INNER JOIN mentor_profiles mp
        ON mr.mentor_id = mp.mentor_profile_id

    INNER JOIN alumni mentor_alumni
        ON mp.alumni_id = mentor_alumni.alumni_id

    INNER JOIN alumni mentee_alumni
        ON mr.mentee_id = mentee_alumni.alumni_id

    ORDER BY mr.created_at DESC
";


$stmt = $conn->prepare($sql);


if (!$stmt) {

    die("Database error: " . $conn->error);

}


$stmt->execute();

$requests = $stmt->get_result();

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
        Mentorship Requests |
        <?= e(SITE_NAME) ?>
    </title>


    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >


    <style>

        .requests-wrapper {

            padding: 30px;

        }


        .requests-table-wrapper {

            background: #ffffff;

            border-radius: 14px;

            border: 1px solid #eeeeee;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.05);

            overflow-x: auto;

        }


        .requests-table {

            width: 100%;

            border-collapse: collapse;

            min-width: 1000px;

        }


        .requests-table th {

            text-align: left;

            padding: 15px;

            background: #f8f5f2;

            color: #4a2c1d;

            font-size: 13px;

            border-bottom:
                1px solid #eeeeee;

        }


        .requests-table td {

            padding: 15px;

            border-bottom:
                1px solid #eeeeee;

            color: #555555;

            vertical-align: top;

        }


        .requests-table tr:last-child td {

            border-bottom: none;

        }


        .person-name {

            color: #4a2c1d;

            font-weight: 700;

        }


        .person-role {

            color: #888888;

            font-size: 12px;

            margin-top: 3px;

        }


        .expertise {

            color: #7a4b2a;

            font-weight: 600;

        }


        .message {

            max-width: 280px;

            line-height: 1.5;

        }


        .status-badge {

            display: inline-block;

            padding: 6px 12px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 600;

        }


        .status-pending {

            background: #fff3cd;

            color: #856404;

        }


        .status-accepted {

            background: #e8f5e9;

            color: #2e7d32;

        }


        .status-rejected {

            background: #ffebee;

            color: #c62828;

        }


        .status-cancelled {

            background: #eeeeee;

            color: #666666;

        }


        .empty-requests {

            text-align: center;

            padding: 60px 20px;
            color: #777777;

        }


        .back-button {

            display: inline-block;

            margin-bottom: 20px;

            padding: 10px 16px;

            border-radius: 8px;

            border: 1px solid #8b5e3c;

            color: #8b5e3c;

            background: #ffffff;

            text-decoration: none;

        }


        .back-button:hover {

            background: #8b5e3c;

            color: #ffffff;

        }


        @media (max-width: 700px) {

            .requests-wrapper {

                padding: 15px;

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


            <a href="../Alumni/index.php">
                Alumni
            </a>


            <a href="../Employment/index.php">
                Employment
            </a>


            <a
                href="index.php"
                class="active"
            >
                Mentors
            </a>


            <a href="../Opportunities/index.php">
                Opportunities
            </a>


            <a href="../Events/index.php">
                Events
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
                    Mentorship Requests
                </h1>

                <p>
                    View mentorship requests submitted by alumni.
                </p>

            </div>


        </header>


        <section class="dashboard-content">


            <div class="requests-wrapper">


                <a
                    href="index.php"
                    class="back-button"
                >
                    ← Back to Mentors
                </a>


                <div class="dashboard-panel">


                    <div class="panel-header">


                        <div>

                            <h2>
                                All Mentorship Requests
                            </h2>

                            <p>
                                Monitor mentor and mentee requests.
                            </p>

                        </div>


                    </div>


                    <?php if ($requests->num_rows === 0): ?>


                        <div class="empty-requests">


                            <h3>
                                No Mentorship Requests
                            </h3>
                            <p>
                                No alumni have submitted mentorship requests yet.
                            </p>


                        </div>


                    <?php else: ?>


                        <div class="requests-table-wrapper">


                            <table class="requests-table">


                                <thead>

                                    <tr>

                                        <th>
                                            Mentor
                                        </th>

                                        <th>
                                            Mentee
                                        </th>

                                        <th>
                                            Expertise
                                        </th>

                                        <th>
                                            Message
                                        </th>

                                        <th>
                                            Status
                                        </th>

                                        <th>
                                            Requested
                                        </th>

                                        <th>
                                            Responded
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>


                                <?php while (
                                    $request =
                                        $requests->fetch_assoc()
                                ): ?>


                                    <?php

                                    $status =
                                        strtolower(
                                            trim(
                                                $request["status"]
                                            )
                                        );


                                    $statusClass =
                                        "status-pending";


                                    if (
                                        $status === "accepted"
                                    ) {

                                        $statusClass =
                                            "status-accepted";

                                    } elseif (
                                        $status === "rejected"
                                    ) {

                                        $statusClass =
                                            "status-rejected";

                                    } elseif (
                                        $status === "cancelled"
                                    ) {

                                        $statusClass =
                                            "status-cancelled";

                                    }

                                    ?>


                                    <tr>


                                        <td>


                                            <div class="person-name">

                                                <?= e(
                                                    $request[
                                                        "mentor_first_name"
                                                    ]
                                                ) ?>

                                                <?= e(
                                                    $request[
                                                        "mentor_last_name"
                                                    ]
                                                ) ?>

                                            </div>


                                            <div class="person-role">

                                                Mentor
                                                </div>


                                        </td>


                                        <td>


                                            <div class="person-name">

                                                <?= e(
                                                    $request[
                                                        "mentee_first_name"
                                                    ]
                                                ) ?>

                                                <?= e(
                                                    $request[
                                                        "mentee_last_name"
                                                    ]
                                                ) ?>

                                            </div>


                                            <div class="person-role">

                                                Mentee

                                            </div>


                                        </td>


                                        <td>

                                            <span class="expertise">

                                                <?= e(
                                                    $request[
                                                        "expertise"
                                                    ]
                                                ) ?>

                                            </span>

                                        </td>


                                        <td>


                                            <div class="message">

                                                <?= nl2br(
                                                    e(
                                                        $request[
                                                            "message"
                                                        ]
                                                    )
                                                ) ?>

                                            </div>


                                        </td>


                                        <td>


                                            <span
                                                class="status-badge <?= e($statusClass) ?>"
                                            >

                                                <?= e(
                                                    ucfirst(
                                                        $status
                                                    )
                                                ) ?>

                                            </span>


                                        </td>


                                        <td>

                                            <?= e(
                                                $request[
                                                    "created_at"
                                                ]
                                            ) ?>

                                        </td>


                                        <td>

                                            <?php if (
                                                !empty(
                                                    $request[
                                                        "responded_at"
                                                    ]
                                                )
                                            ): ?>

                                                <?= e(
                                                    $request[
                                                        "responded_at"
                                                    ]
                                                ) ?>

                                            <?php else: ?>

                                                —
                                                <?php endif; ?>

                                        </td>


                                    </tr>


                                <?php endwhile; ?>


                                </tbody>


                            </table>


                        </div>


                    <?php endif; ?>


                </div>


            </div>


        </section>


    </main>


</div>


</body>

</html>