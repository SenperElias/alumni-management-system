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
| GET MENTOR PROFILES
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

        a.first_name,
        a.last_name,
        a.alumni_id_number,
        a.phone,

        u.email

    FROM mentor_profiles mp

    INNER JOIN alumni a
        ON mp.alumni_id = a.alumni_id

    LEFT JOIN users u
        ON a.user_id = u.user_id

    ORDER BY mp.created_at DESC
";


$stmt = $conn->prepare($sql);


if (!$stmt) {

    die("Database error: " . $conn->error);

}


$stmt->execute();

$mentors = $stmt->get_result();

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
        Mentor Management |
        <?= e(SITE_NAME) ?>
    </title>


    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >


    <style>

        .mentors-wrapper {

            padding: 30px;

        }


        .mentor-table-wrapper {

            background: #ffffff;

            border-radius: 14px;

            border: 1px solid #eeeeee;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.05);

            overflow-x: auto;

        }


        .mentor-table {

            width: 100%;

            border-collapse: collapse;

            min-width: 850px;

        }


        .mentor-table th {

            text-align: left;

            padding: 15px;

            background: #f8f5f2;

            color: #4a2c1d;

            font-size: 13px;

            border-bottom:
                1px solid #eeeeee;

        }


        .mentor-table td {

            padding: 15px;

            border-bottom:
                1px solid #eeeeee;

            color: #555555;

            vertical-align: middle;

        }


        .mentor-table tr:last-child td {

            border-bottom: none;

        }


        .mentor-name {

            color: #4a2c1d;

            font-weight: 700;

        }


        .mentor-email {

            color: #888888;

            font-size: 13px;

            margin-top: 3px;

        }


        .expertise {

            color: #7a4b2a;

            font-weight: 600;

        }


        .status-badge {

            display: inline-block;

            padding: 6px 12px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 600;

        }


        .status-active {

            background: #e8f5e9;

            color: #2e7d32;

        }


        .status-inactive {

            background: #eeeeee;

            color: #666666;

        }


        .status-pending {

            background: #fff3cd;

            color: #856404;

        }


        .view-button {

            display: inline-block;

            padding: 8px 13px;

            border-radius: 7px;

            background: #7a4b2a;

            color: #ffffff;

            text-decoration: none;

            font-size: 13px;

            font-weight: 600;

        }


        .view-button:hover {

            background: #5f3921;

        }


        .empty-mentors {

            text-align: center;

            padding: 60px 20px;

            color: #777777;

        }
        .mentor-count {

            display: inline-block;

            margin-top: 10px;

            padding: 8px 14px;

            border-radius: 8px;

            background: #f8f5f2;

            color: #7a4b2a;

            font-weight: 600;

        }


        @media (max-width: 700px) {

            .mentors-wrapper {

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

  <?php require_once __DIR__ ."/../includes/sidebar.php"; ?>
    
    


    <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->


    <main class="admin-main">


        <header class="admin-topbar">


            <div>

                <h1>
                    Mentor Management
                </h1>

                <p>
                    Manage alumni who are available to provide mentorship.
                </p>

            </div>


        </header>


        <section class="dashboard-content">


            <div class="mentors-wrapper">


                <div class="dashboard-panel">


                    <div class="panel-header">


                        <div>

                            <h2>
                                Mentor Profiles
                            </h2>

                            <p>
                                View and manage registered mentors.
                            </p>


                            <span class="mentor-count">

                                Total Mentors:

                                <?= $mentors->num_rows ?>

                            </span>

                        </div>


                    </div>


                    <?php if ($mentors->num_rows === 0): ?>


                        <div class="empty-mentors">


                            <h3>
                                No Mentor Profiles
                            </h3>


                            <p>
                                No alumni have created a mentor profile yet.
                            </p>


                        </div>


                    <?php else: ?>


                        <div class="mentor-table-wrapper">
                            <table class="mentor-table">


                                <thead>

                                    <tr>

                                        <th>
                                            Mentor
                                        </th>

                                        <th>
                                            Alumni ID
                                        </th>

                                        <th>
                                            Expertise
                                        </th>

                                        <th>
                                            Experience
                                        </th>

                                        <th>
                                            Availability
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
                                    $mentor = $mentors->fetch_assoc()
                                ): ?>


                                    <?php

                                    $status =
                                        strtolower(
                                            trim(
                                                $mentor["status"]
                                            )
                                        );


                                    $statusClass =
                                        "status-inactive";


                                    if (
                                        $status === "active"
                                    ) {

                                        $statusClass =
                                            "status-active";

                                    } elseif (
                                        $status === "pending"
                                    ) {

                                        $statusClass =
                                            "status-pending";

                                    }

                                    ?>


                                    <tr>


                                        <td>


                                            <div class="mentor-name">

                                                <?= e(
                                                    $mentor["first_name"]
                                                ) ?>

                                                <?= e(
                                                    $mentor["last_name"]
                                                ) ?>

                                            </div>


                                            <div class="mentor-email">

                                                <?= e(
                                                    $mentor["email"]
                                                    ?: "No email"
                                                ) ?>

                                            </div>


                                        </td>


                                        <td>

                                            <?= e(
                                                $mentor["alumni_id_number"]
                                                ?: $mentor["alumni_id"]
                                            ) ?>

                                        </td>


                                        <td>

                                            <span class="expertise">
                                                <?= e(
                                                    $mentor["expertise"]
                                                ) ?>

                                            </span>

                                        </td>


                                        <td>

                                            <?= (int) $mentor[
                                                "experience_years"
                                            ] ?>

                                            years

                                        </td>


                                        <td>

                                            <?= e(
                                                $mentor["availability"]
                                            ) ?>

                                        </td>


                                        <td>


                                            <span
                                                class="status-badge <?= e($statusClass) ?>"
                                            >

                                                <?= e(
                                                    ucfirst($status)
                                                ) ?>

                                            </span>


                                        </td>


                                        <td>


                                            <a
                                                href="view.php?id=<?= (int) $mentor["mentor_profile_id"] ?>"
                                                class="view-button"
                                            >

                                                View

                                            </a>


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