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
| GET LOGGED-IN ALUMNI ID
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        alumni_id
    FROM alumni
    WHERE user_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$alumni = $result->fetch_assoc();

$stmt->close();

if (!$alumni) {
    die("Alumni profile not found.");
}

$alumni_id = (int) $alumni["alumni_id"];

/*
|--------------------------------------------------------------------------
| GET ACTIVE MENTORS
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
        a.first_name,
        a.last_name,
        a.college_id_number,
        a.profile_photo
    FROM mentor_profiles mp
    INNER JOIN alumni a
        ON mp.alumni_id = a.alumni_id
    WHERE LOWER(TRIM(mp.status)) = 'active'
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
        Mentorship |
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

    <style>

        /*
        |--------------------------------------------------------------------------
        | MENTOR GRID
        |--------------------------------------------------------------------------
        */

        .mentor-grid {
            display: grid;
            grid-template-columns:
                repeat(3, minmax(0, 1fr));
            gap: 20px;
        }

        /*
        |--------------------------------------------------------------------------
        | MENTOR CARD
        |--------------------------------------------------------------------------
        */

        .mentor-card {
            background: #ffffff;
            border: 1px solid #eeeeee;
            border-radius: 12px;
            padding: 22px;
            box-shadow:
                0 5px 18px
                rgba(0, 0, 0, 0.05);
            transition: 0.2s ease;
        }

        .mentor-card:hover {
            transform: translateY(-3px);
            box-shadow:
                0 8px 25px
                rgba(0, 0, 0, 0.08);
        }

        /*
        |--------------------------------------------------------------------------
        | PROFILE PHOTO
        |--------------------------------------------------------------------------
        */

        .mentor-photo {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid #f1e7df;
        }

        .mentor-photo-placeholder {
 width: 70px;
            height: 70px;
            border-radius: 50%;
            background: #f1e7df;
            color: #7a4b2a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 25px;
            font-weight: 700;
            margin-bottom: 15px;
        }

        /*
        |--------------------------------------------------------------------------
        | MENTOR NAME
        |--------------------------------------------------------------------------
        */

        .mentor-card h3 {
            margin-top: 0;
            margin-bottom: 6px;
            color: #4a2c1d;
        }

        /*
        |--------------------------------------------------------------------------
        | EXPERTISE
        |--------------------------------------------------------------------------
        */

        .mentor-expertise {
            color: #7a4b2a;
            font-weight: 600;
            margin-bottom: 18px;
        }

        /*
        |--------------------------------------------------------------------------
        | CARD LABELS
        |--------------------------------------------------------------------------
        */

        .mentor-card-label {
            font-size: 12px;
            color: #777777;
            display: block;
            margin-bottom: 5px;
        }

        .mentor-card-value {
            color: #444444;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        /*
        |--------------------------------------------------------------------------
        | BUTTONS
        |--------------------------------------------------------------------------
        */

        .mentor-button {
            display: inline-block;
            padding: 10px 15px;
            border-radius: 7px;
            background: #7a4b2a;
            color: #ffffff;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
        }

        .mentor-button:hover {
            background: #5f3921;
        }

        /*
        |--------------------------------------------------------------------------
        | MY REQUESTS / BECOME MENTOR BUTTON
        |--------------------------------------------------------------------------
        */

        .my-requests-button {
            display: inline-block;
            padding: 10px 16px;
            border-radius: 8px;
            background: #ffffff;
            color: #7a4b2a;
            border: 1px solid #7a4b2a;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
        }

        .my-requests-button:hover {
            background: #7a4b2a;
            color: #ffffff;
        }

        /*
        |--------------------------------------------------------------------------
        | EMPTY STATE
        |--------------------------------------------------------------------------
        */

        .no-mentors {
            text-align: center;
            padding: 50px 20px;
            color: #777777;
        }

        /*
        |--------------------------------------------------------------------------
        | RESPONSIVE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 1000px) {

            .mentor-grid {
                grid-template-columns: 1fr 1fr;
            }

        }

        @media (max-width: 650px) {

            .mentor-grid {
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

    <?php

    $currentPage = "mentorship";

    require_once __DIR__ . "/../includes/sidebar.php";

    ?>
 <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->

    <main class="admin-main">

        <!-- =================================================
             TOP BAR
        ================================================== -->

        <header class="admin-topbar">

            <div>

                <h1>
                    Mentorship
                </h1>

                <p>
                    Connect with experienced alumni and learn
                    from their expertise.
                </p>

            </div>

        </header>

        <!-- =================================================
             CONTENT
        ================================================== -->

        <section class="dashboard-content">

            <div class="dashboard-panel">

                <!-- =================================================
                     PANEL HEADER
                ================================================== -->

                <div
                    class="panel-header"
                    style="
                        display:flex;
                        justify-content:space-between;
                        align-items:center;
                        gap:15px;
                        flex-wrap:wrap;
                    "
                >

                    <div>

                        <h2>
                            Available Mentors
                        </h2>

                        <p>
                            Choose a mentor whose experience
                            matches your goals.
                        </p>

                    </div>

                    <!-- =================================================
                         ACTION BUTTONS
                    ================================================== -->

                    <div
                        style="
                            display:flex;
                            gap:10px;
                            flex-wrap:wrap;
                        "
                    >

                        <!-- MY REQUESTS -->

                        <a
                            href="my-requests.php"
                            class="my-requests-button"
                        >
                            My Requests
                        </a>

                        <!-- RECEIVED REQUESTS -->

                        <a
                            href="received-requests.php"
                            class="my-requests-button"
                        >
                            Received Requests
                        </a>

                        <!-- BECOME A MENTOR -->

                        <a
                            href="become.php"
                            class="my-requests-button"
                        >
                            🎓 Become a Mentor
                        </a>

                    </div>

                </div>

                <!-- =================================================
                     CHECK IF MENTORS EXIST
                ================================================== -->

                <?php if ($mentors->num_rows === 0): ?>

                    <div class="no-mentors">

                        <h3>
                            No Mentors Available
                        </h3>

                        <p>
                            There are currently no alumni
                            available for mentorship.
                        </p>

                    </div>

                <?php else: ?>

                    <!-- =================================================
                         MENTOR GRID
                    ================================================== -->

                    <div class="mentor-grid">

                        <?php while ($mentor = $mentors->fetch_assoc()): ?>

                            <div class="mentor-card">
 <!-- =====================================
                                     PROFILE PHOTO
                                ====================================== -->

                                <?php if (
                                    !empty($mentor["profile_photo"])
                                ): ?>

                                    <img
                                        src="../../uploads/<?= e($mentor["profile_photo"]) ?>"
                                        alt="Mentor"
                                        class="mentor-photo"
                                    >

                                <?php else: ?>

                                    <div class="mentor-photo-placeholder">

                                        <?= e(
                                            strtoupper(
                                                substr(
                                                    $mentor["first_name"],
                                                    0,
                                                    1
                                                )
                                            )
                                        ) ?>

                                    </div>

                                <?php endif; ?>

                                <!-- =====================================
                                     NAME
                                ====================================== -->

                                <h3>

                                    <?= e(
                                        $mentor["first_name"]
                                    ) ?>

                                    <?= e(
                                        $mentor["last_name"]
                                    ) ?>

                                </h3>

                                <!-- =====================================
                                     EXPERTISE
                                ====================================== -->

                                <div class="mentor-expertise">

                                    <?= e(
                                        $mentor["expertise"]
                                    ) ?>

                                </div>

                                <!-- =====================================
                                     EXPERIENCE
                                ====================================== -->

                                <span class="mentor-card-label">

                                    Experience

                                </span>

                                <div class="mentor-card-value">

                                    <?= (int) $mentor[
                                        "experience_years"
                                    ] ?>

                                    years

                                </div>

                                <!-- =====================================
                                     SKILLS
                                ====================================== -->

                                <span class="mentor-card-label">

                                    Skills

                                </span>

                                <div class="mentor-card-value">

                                    <?= e(
                                        $mentor["skills"]
                                    ) ?>

                                </div>

                                <!-- =====================================
                                     AVAILABILITY
                                ====================================== -->

                                <span class="mentor-card-label">

                                    Availability

                                </span>

                                <div class="mentor-card-value">
 <?= e(
                                        $mentor["availability"]
                                    ) ?>

                                </div>

                                <!-- =====================================
                                     REQUEST MENTORSHIP BUTTON
                                ====================================== -->

                                <a
                                    href="request.php?mentor_id=<?= (int)
                                        $mentor["mentor_profile_id"] ?>"
                                    class="mentor-button"
                                >
                                    Request Mentorship
                                </a>

                            </div>

                        <?php endwhile; ?>

                    </div>

                <?php endif; ?>

            </div>

        </section>

    </main>

</div>

</body>

</html>