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
| GET LOGGED-IN USER
|--------------------------------------------------------------------------
*/

$user_id = (int) $_SESSION["user_id"];


/*
|--------------------------------------------------------------------------
| GET REAL ALUMNI ID
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT alumni_id
    FROM alumni
    WHERE user_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

$alumni = $result->fetch_assoc();


if (!$alumni) {
    die("Alumni profile not found.");
}


$alumni_id = (int) $alumni["alumni_id"];


$message = "";
$message_type = "";


/*
|--------------------------------------------------------------------------
| GET EXISTING MENTOR PROFILE
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        mentor_profile_id,
        alumni_id,
        expertise,
        skills,
        experience_years,
        biography,
        availability,
        status,
        created_at,
        updated_at
    FROM mentor_profiles
    WHERE alumni_id = ?
    LIMIT 1
";


$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}


$stmt->bind_param(
    "i",
    $alumni_id
);


$stmt->execute();

$result = $stmt->get_result();

$mentor = $result->fetch_assoc();



/*
|--------------------------------------------------------------------------
| FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $expertise = trim(
        $_POST["expertise"] ?? ""
    );


    $skills = trim(
        $_POST["skills"] ?? ""
    );


    $experience_years = trim(
        $_POST["experience_years"] ?? ""
    );


    $biography = trim(
        $_POST["biography"] ?? ""
    );


    $availability = trim(
        $_POST["availability"] ?? ""
    );



    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($expertise === "") {

        $message =
            "Please enter your area of expertise.";

        $message_type = "error";


    } elseif ($skills === "") {

        $message =
            "Please enter your skills.";

        $message_type = "error";


    } elseif (
        $experience_years === "" ||
        !is_numeric($experience_years) ||
        $experience_years < 0
    ) {

        $message =
            "Please enter a valid number of experience years.";

        $message_type = "error";


    } elseif ($biography === "") {

        $message =
            "Please enter your biography.";

        $message_type = "error";


    } elseif ($availability === "") {

        $message =
            "Please enter your availability.";

        $message_type = "error";


    } else {


        /*
        |--------------------------------------------------------------------------
        | CREATE MENTOR PROFILE
        |--------------------------------------------------------------------------
        */

        if (!$mentor) {


            $status = "Pending";

            $experience_years =
                (int) $experience_years;
                $sql = "
                INSERT INTO mentor_profiles
                (
                    alumni_id,
                    expertise,
                    skills,
                    experience_years,
                    biography,
                    availability,
                    status,
                    created_at,
                    updated_at
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    NOW(),
                    NOW()
                )
            ";


            $stmt = $conn->prepare($sql);


            if (!$stmt) {

                die(
                    "Database error: " .
                    $conn->error
                );

            }


            $stmt->bind_param(
                "ississs",
                $alumni_id,
                $expertise,
                $skills,
                $experience_years,
                $biography,
                $availability,
                $status
            );


            if ($stmt->execute()) {

                header(
                    "Location: mentor-profile.php?success=created"
                );

                exit;

            } else {

                $message =
                    "Unable to create mentor profile.";

                $message_type = "error";

            }


        } else {


            /*
            |--------------------------------------------------------------------------
            | UPDATE EXISTING PROFILE
            |--------------------------------------------------------------------------
            */

            $mentor_profile_id =
                (int) $mentor["mentor_profile_id"];


            $experience_years =
                (int) $experience_years;


            $sql = "
                UPDATE mentor_profiles
                SET
                    expertise = ?,
                    skills = ?,
                    experience_years = ?,
                    biography = ?,
                    availability = ?,
                    updated_at = NOW()
                WHERE mentor_profile_id = ?
                  AND alumni_id = ?
            ";


            $stmt = $conn->prepare($sql);


            if (!$stmt) {

                die(
                    "Database error: " .
                    $conn->error
                );

            }


            $stmt->bind_param(
                "ssissii",
                $expertise,
                $skills,
                $experience_years,
                $biography,
                $availability,
                $mentor_profile_id,
                $alumni_id
            );


            if ($stmt->execute()) {

                header(
                    "Location: mentor-profile.php?success=updated"
                );

                exit;

            } else {

                $message =
                    "Unable to update mentor profile.";

                $message_type = "error";

            }

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
| LOAD PROFILE AGAIN
|--------------------------------------------------------------------------
*/

if (
    $success === "created" ||
    $success === "updated"
) {


    $sql = "
        SELECT
            mentor_profile_id,
            alumni_id,
            expertise,
            skills,
            experience_years,
            biography,
            availability,
            status,
            created_at,
            updated_at
        FROM mentor_profiles
        WHERE alumni_id = ?
        LIMIT 1
    ";


    $stmt = $conn->prepare($sql);


    if (!$stmt) {
        die("Database error: " . $conn->error);
    }
    $stmt->bind_param(
        "i",
        $alumni_id
    );


    $stmt->execute();

    $result = $stmt->get_result();

    $mentor = $result->fetch_assoc();

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
        Mentor Profile |
        <?= e(SITE_NAME) ?>
    </title>


    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

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


            <a href="jobs.php">
                Jobs & Internships
            </a>


            <a
                href="mentor-profile.php"
                class="active"
            >
                Mentorship
            </a>


            <div class="nav-section">
                ACTIVITIES
            </div>


            <a href="#">
                Projects
            </a>


            <a href="#">
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
                    Mentor Profile
                </h1>


                <p>
                    Share your experience and skills with fellow alumni.
                </p>

            </div>


        </header>



        <section class="dashboard-content">


            <!-- =================================================
                 SUCCESS / ERROR
            ================================================== -->


            <?php if ($success === "created"): ?>

                <div class="dashboard-panel">

                    <p>
                        Your mentor profile has been created
                        successfully and is waiting for admin approval.
                    </p>

                </div>

            <?php endif; ?>


            <?php if ($success === "updated"): ?>

                <div class="dashboard-panel">

                    <p>
                        Your mentor profile has been updated successfully.
                    </p>

                </div>

            <?php endif; ?>


            <?php if ($message !== ""): ?>

                <div class="dashboard-panel">

                    <p>
                        <?= e($message) ?>
                    </p>

                </div>

            <?php endif; ?>
            <!-- =================================================
                 PROFILE STATUS
            ================================================== -->


            <?php if ($mentor): ?>


                <div class="dashboard-panel">


                    <div class="panel-header">


                        <div>

                            <h2>
                                Profile Status
                            </h2>


                            <p>
                                Current status of your mentor profile.
                            </p>

                        </div>


                    </div>


                    <div class="opportunity-info-grid">


                        <div class="opportunity-info-item">


                            <span>
                                Status
                            </span>


                            <strong>

                                <?= e(
                                    $mentor["status"]
                                ) ?>

                            </strong>


                        </div>


                        <div class="opportunity-info-item">


                            <span>
                                Created At
                            </span>


                            <strong>

                                <?= e(
                                    $mentor["created_at"]
                                ) ?>

                            </strong>


                        </div>


                    </div>


                    <?php if (
                        strtolower(
                            trim(
                                $mentor["status"]
                            )
                        ) === "pending"
                    ): ?>


                        <p>
                            Your mentor profile is waiting for
                            admin approval.
                        </p>


                    <?php elseif (
                        strtolower(
                            trim(
                                $mentor["status"]
                            )
                        ) === "approved"
                    ): ?>


                        <p>
                            Your mentor profile is approved and
                            can be viewed by other alumni.
                        </p>


                    <?php elseif (
                        strtolower(
                            trim(
                                $mentor["status"]
                            )
                        ) === "rejected"
                    ): ?>


                        <p>
                            Your mentor profile was rejected.
                            You can update the information below.
                        </p>


                    <?php endif; ?>


                </div>


            <?php endif; ?>



            <!-- =================================================
                 FORM
            ================================================== -->


            <div class="dashboard-panel">


                <div class="panel-header">


                    <div>

                        <h2>

                            <?= $mentor
                                ? "Update Mentor Profile"
                                : "Become a Mentor" ?>

                        </h2>


                        <p>
                            Provide your professional information
                            so other alumni can learn from your experience.
                        </p>

                    </div>


                </div>



                <form
                    method="POST"
                    class="search-filter-form"
                >


                    <div class="form-group">


                        <label for="expertise">
                            Area of Expertise
                        </label>
                      <input
                            type="text"
                            id="expertise"
                            name="expertise"
                            value="<?= e(
                                $mentor["expertise"]
                                ?? ""
                            ) ?>"
                            placeholder="Example: Web Development"
                            required
                        >


                    </div>



                    <div class="form-group">


                        <label for="skills">
                            Skills
                        </label>


                        <textarea
                            id="skills"
                            name="skills"
                            rows="4"
                            placeholder="Example: PHP, MySQL, JavaScript, HTML, CSS"
                            required
                        ><?= e(
                            $mentor["skills"]
                            ?? ""
                        ) ?></textarea>


                    </div>



                    <div class="form-group">


                        <label for="experience_years">
                            Years of Experience
                        </label>


                        <input
                            type="number"
                            id="experience_years"
                            name="experience_years"
                            min="0"
                            value="<?= e(
                                $mentor["experience_years"]
                                ?? ""
                            ) ?>"
                            placeholder="Example: 5"
                            required
                        >


                    </div>



                    <div class="form-group">


                        <label for="biography">
                            Biography
                        </label>


                        <textarea
                            id="biography"
                            name="biography"
                            rows="6"
                            placeholder="Tell alumni about your professional background and experience..."
                            required
                        ><?= e(
                            $mentor["biography"]
                            ?? ""
                        ) ?></textarea>


                    </div>



                    <div class="form-group">


                        <label for="availability">
                            Availability
                        </label>


                        <input
                            type="text"
                            id="availability"
                            name="availability"
                            value="<?= e(
                                $mentor["availability"]
                                ?? ""
                            ) ?>"
                            placeholder="Example: Weekends, 2 hours per week"
                            required
                        >


                    </div>



                    <div class="form-actions">


                        <button
                            type="submit"
                            class="primary-button"
                        >

                            <?= $mentor
                                ? "Update Profile"
                                : "Submit Mentor Profile" ?>

                        </button>


                    </div>


                </form>


            </div>


        </section>


    </main>


</div>


</body>

</html>  