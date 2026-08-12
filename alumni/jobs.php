<?php

session_start();

require_once "../config/database.php";
require_once "../config/config.php";
require_once "../includes/functions.php";


/*
|--------------------------------------------------------------------------
| ALUMNI ACCESS
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {

    header("Location: ../auth/login.php");
    exit;

}

if ($_SESSION["role"] !== "alumni") {

    header("Location: ../index.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| SEARCH AND FILTER
|--------------------------------------------------------------------------
*/

$search = trim($_GET["search"] ?? "");

$type = trim($_GET["type"] ?? "");

$location = trim($_GET["location"] ?? "");


/*
|--------------------------------------------------------------------------
| GET JOBS / INTERNSHIPS
|--------------------------------------------------------------------------
|
| This query expects an opportunities table with:
|
| opportunity_id
| title
| description
| type
| company_name
| location
| deadline
| status
| created_at
|
*/

$sql = "
    SELECT
        opportunity_id,
        title,
        description,
        type,
        company_name,
        location,
        deadline,
        status,
        created_at
    FROM opportunities
    WHERE LOWER(TRIM(status)) = 'approved'
";


$params = [];

$types = "";


/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

if ($search !== "") {

    $sql .= "
        AND (
            title LIKE ?
            OR company_name LIKE ?
            OR description LIKE ?
        )
    ";

    $searchValue = "%" . $search . "%";

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;

    $types .= "sss";

}


/*
|--------------------------------------------------------------------------
| OPPORTUNITY TYPE
|--------------------------------------------------------------------------
*/

if ($type !== "") {

    $sql .= "
        AND type = ?
    ";

    $params[] = $type;

    $types .= "s";

}


/*
|--------------------------------------------------------------------------
| LOCATION
|--------------------------------------------------------------------------
*/

if ($location !== "") {

    $sql .= "
        AND location LIKE ?
    ";

    $locationValue =
        "%" . $location . "%";

    $params[] = $locationValue;

    $types .= "s";

}


/*
|--------------------------------------------------------------------------
| ORDER
|--------------------------------------------------------------------------
*/

$sql .= "
    ORDER BY created_at DESC
";


$stmt = $conn->prepare($sql);


if (!$stmt) {

    die(
        "Database error: "
        . $conn->error
    );

}


if (!empty($params)) {

    $stmt->bind_param(
        $types,
        ...$params
    );

}


$stmt->execute();

$opportunities =
    $stmt->get_result();


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
        Jobs & Internships |
        <?= e(SITE_NAME) ?>
    </title>


    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >


    <style>

        .jobs-wrapper {

            max-width: 1200px;

            margin: 40px auto;

            padding: 20px;

        }


        .jobs-header {

            margin-bottom: 25px;

        }


        .jobs-header h1 {

            color: #4a2c1d;

            margin-bottom: 8px;

        }


        .jobs-header p {

            color: #777;

            margin: 0;

        }


        .filter-panel {

            background: #ffffff;

            border: 1px solid #eeeeee;

            border-radius: 14px;

            padding: 20px;

            margin-bottom: 25px;
            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.05);

        }


        .filter-form {

            display: grid;

            grid-template-columns:
                2fr 1fr 1fr auto;

            gap: 12px;

        }


        .filter-form input,

        .filter-form select {

            width: 100%;

            box-sizing: border-box;

            padding: 11px 12px;

            border: 1px solid #dddddd;

            border-radius: 8px;

            font-family: inherit;

            background: #ffffff;

        }


        .filter-form input:focus,

        .filter-form select:focus {

            outline: none;

            border-color: #8b5e3c;

        }


        .filter-button {

            border: none;

            border-radius: 8px;

            padding: 11px 18px;

            background: #7a4b2a;

            color: #ffffff;

            cursor: pointer;

            font-weight: 600;

        }


        .filter-button:hover {

            background: #5f3921;

        }


        .clear-button {

            display: inline-block;

            padding: 11px 16px;

            border-radius: 8px;

            border: 1px solid #8b5e3c;

            color: #8b5e3c;

            background: #ffffff;

            text-decoration: none;

            font-weight: 600;

        }


        .clear-button:hover {

            background: #8b5e3c;

            color: #ffffff;

        }


        .jobs-grid {

            display: grid;

            grid-template-columns:
                repeat(3, minmax(0, 1fr));

            gap: 20px;

        }


        .job-card {

            background: #ffffff;

            border: 1px solid #eeeeee;

            border-radius: 14px;

            padding: 22px;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.04);

            display: flex;

            flex-direction: column;

        }


        .job-type {

            display: inline-block;

            width: fit-content;

            padding: 6px 10px;

            border-radius: 20px;

            background: #f8f5f2;

            color: #7a4b2a;

            font-size: 12px;

            font-weight: 700;

            margin-bottom: 12px;

        }


        .job-card h2 {

            color: #4a2c1d;

            font-size: 20px;

            margin: 0 0 8px;

        }


        .company-name {

            color: #7a4b2a;

            font-weight: 600;

            margin-bottom: 10px;

        }


        .job-description {

            color: #666666;

            line-height: 1.6;

            margin-bottom: 18px;

            flex: 1;

        }


        .job-info {

            display: grid;

            gap: 8px;

            margin-bottom: 18px;

        }


        .job-info-row {

            color: #666666;

            font-size: 14px;

        }


        .job-info-row strong {

            color: #4a2c1d;

        }


        .view-job-button {

            display: block;

            text-align: center;

            padding: 10px 15px;

            border-radius: 8px;

            background: #7a4b2a;

            color: #ffffff;

            text-decoration: none;

            font-weight: 600;

        }


        .view-job-button:hover {

            background: #5f3921;

        }


        .empty-jobs {

            grid-column: 1 / -1;

            text-align: center;

            padding: 60px 20px;

            background: #ffffff;

            border: 1px solid #eeeeee;

            border-radius: 14px;

            color: #777777;

        }


        .empty-jobs h3 {

            color: #4a2c1d;

        }


        @media (max-width: 1000px) {

            .jobs-grid {

                grid-template-columns:
                    1fr 1fr;

            }


            .filter-form {

                grid-template-columns:
                    1fr 1fr;

            }

        }


        @media (max-width: 650px) {

            .jobs-grid {

                grid-template-columns: 1fr;

            }
            .filter-form {

                grid-template-columns: 1fr;

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
                    Alumni Portal
                </small>

            </div>


        </div>


        <nav class="admin-nav">


            <a href="dashboard.php">
                Dashboard
            </a>


            <div class="nav-section">
                MY ACCOUNT
            </div>


            <a href="profile.php">
                My Profile
            </a>


            <a href="employment.php">
                Employment
            </a>


            <div class="nav-section">
                OPPORTUNITIES
            </div>


            <a
                href="jobs.php"
                class="active"
            >
                Jobs & Internships
            </a>


            <a href="mentorship/index.php">
                Mentorship
            </a>


            <div class="nav-section">
                ACTIVITIES
            </div>


            <a href="#">
                Projects
            </a>


            <a href="events/events.php">
                Events
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
                href="../auth/logout.php"
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
                    Jobs & Internships
                </h1>

                <p>
                    Find employment and internship opportunities shared with alumni.
                </p>

            </div>


        </header>


        <section class="dashboard-content">


            <div class="jobs-wrapper">


                <div class="jobs-header">

                    <h1>
                        Opportunities
                    </h1>

                    <p>
                        Explore available jobs and internships.
                    </p>

                </div>


                <!-- =================================================
                     FILTERS
                ================================================== -->


                <div class="filter-panel">


                    <form
                        method="GET"
                        action=""
                        class="filter-form"
                    >


                        <input
                            type="text"
                            name="search"
                            placeholder="Search jobs, companies..."
                            value="<?= e($search) ?>"
                        >


                        <select name="type">

                            <option value="">
                                All Types
                            </option>

                            <option
                                value="Job"
                                <?= $type === "Job"
                                    ? "selected"
                                    : "" ?>
                            >
                                Jobs
                            </option>
                            <option
                                value="Internship"
                                <?= $type === "Internship"
                                    ? "selected"
                                    : "" ?>
                            >
                                Internships
                            </option>

                        </select>


                        <input
                            type="text"
                            name="location"
                            placeholder="Location"
                            value="<?= e($location) ?>"
                        >


                        <button
                            type="submit"
                            class="filter-button"
                        >
                            Search
                        </button>


                    </form>


                    <?php if (
                        $search !== ""
                        || $type !== ""
                        || $location !== ""
                    ): ?>


                        <div style="margin-top: 12px;">

                            <a
                                href="jobs.php"
                                class="clear-button"
                            >
                                Clear Filters
                            </a>

                        </div>


                    <?php endif; ?>


                </div>


                <!-- =================================================
                     JOBS
                ================================================== -->


                <div class="jobs-grid">


                    <?php if (
                        $opportunities->num_rows === 0
                    ): ?>


                        <div class="empty-jobs">


                            <h3>
                                No Opportunities Found
                            </h3>


                            <p>
                                There are currently no published jobs or internships matching your search.
                            </p>


                        </div>


                    <?php else: ?>


                        <?php while (
                            $job =
                                $opportunities->fetch_assoc()
                        ): ?>


                            <article class="job-card">


                                <span class="job-type">

                                    <?= e(
                                        $job[
                                            "type"
                                        ]
                                    ) ?>

                                </span>


                                <h2>

                                    <?= e(
                                        $job["title"]
                                    ) ?>

                                </h2>


                                <div class="company-name">

                                    <?= e(
                                        $job[
                                            "company_name"
                                        ]
                                    ) ?>

                                </div>


                                <div class="job-description">

                                    <?= e(
                                        mb_strimwidth(
                                            strip_tags(
                                                $job[
                                                    "description"
                                                ]
                                            ),
                                            0,
                                            180,
                                            "..."
                                        )
                                    ) ?>

                                </div>


                                <div class="job-info">
                                   <div class="job-info-row">

                                        <strong>
                                            Location:
                                        </strong>

                                        <?= e(
                                            $job[
                                                "location"
                                            ]
                                        ) ?>

                                    </div>


                                    <div class="job-info-row">

                                        <strong>
                                            Deadline:
                                        </strong>

                                        <?= e(
                                            $job[
                                                "deadline"
                                            ]
                                        ) ?>

                                    </div>


                                </div>


                                <a
                                    href="opportunity.php?id=<?= (int) $job["opportunity_id"] ?>"
                                    class="view-job-button"
                                >
                                    View Opportunity
                                </a>


                            </article>


                        <?php endwhile; ?>


                    <?php endif; ?>


                </div>


            </div>


        </section>


    </main>


</div>


</body>

</html> 