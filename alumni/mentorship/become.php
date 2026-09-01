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

$userId = (int) $_SESSION["user_id"];

$error = "";
$success = "";


/*
|--------------------------------------------------------------------------
| GET LOGGED-IN ALUMNI
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        alumni_id,
        first_name,
        last_name
    FROM alumni
    WHERE user_id = ?
    LIMIT 1
");

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();
$alumni = $result->fetch_assoc();

$stmt->close();

if (!$alumni) {
    die("Alumni profile not found.");
}

$alumniId = (int) $alumni["alumni_id"];


/*
|--------------------------------------------------------------------------
| GET EXISTING MENTOR PROFILE
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        mentor_profile_id,
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
");

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $alumniId);
$stmt->execute();

$existingResult = $stmt->get_result();
$existingMentor = $existingResult->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| EDIT MODE
|--------------------------------------------------------------------------
|
| Editing happens inside this same page:
|
| become-mentor.php?edit=1
|
*/

$editMode = false;

if (
    isset($_GET["edit"]) &&
    $_GET["edit"] === "1" &&
    $existingMentor
) {
    $editMode = true;
}


/*
|--------------------------------------------------------------------------
| HANDLE FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $expertise = trim($_POST["expertise"] ?? "");
    $skills = trim($_POST["skills"] ?? "");
    $experienceYears = (int) ($_POST["experience_years"] ?? 0);
    $biography = trim($_POST["biography"] ?? "");
    $availability = trim($_POST["availability"] ?? "");


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($expertise === "") {

        $error = "Please enter your area of expertise.";

    } elseif ($skills === "") {

        $error = "Please enter your skills.";

    } elseif (
        $experienceYears < 0 ||
        $experienceYears > 100
    ) {

        $error = "Experience years must be between 0 and 100.";

    } elseif ($biography === "") {

        $error = "Please enter a short biography.";

    } elseif ($availability === "") {

        $error = "Please enter your availability.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | EXISTING MENTOR PROFILE
        |--------------------------------------------------------------------------
        */

        if ($existingMentor) {

            $mentorProfileId =
                (int) $existingMentor["mentor_profile_id"];

            $currentStatus = strtolower(
                trim($existingMentor["status"] ?? "")
            );
 /*
            |--------------------------------------------------------------------------
            | ACTIVE MENTOR EDIT
            |--------------------------------------------------------------------------
            |
            | When an active mentor edits their information,
            | the profile becomes pending again.
            |
            */

            if ($currentStatus === "active") {

                $newStatus = "pending";

                $stmt = $conn->prepare("
                    UPDATE mentor_profiles
                    SET
                        expertise = ?,
                        skills = ?,
                        experience_years = ?,
                        biography = ?,
                        availability = ?,
                        status = ?,
                        updated_at = NOW()
                    WHERE mentor_profile_id = ?
                ");

                if (!$stmt) {

                    $error =
                        "Unable to update your mentor profile.";

                } else {

                    $stmt->bind_param(
                        "ssisssi",
                        $expertise,
                        $skills,
                        $experienceYears,
                        $biography,
                        $availability,
                        $newStatus,
                        $mentorProfileId
                    );

                    if ($stmt->execute()) {

                        /*
                        |--------------------------------------------------------------------------
                        | NOTIFY ALL ADMINS
                        |--------------------------------------------------------------------------
                        */

                        $notificationTitle =
                            "Mentor Profile Updated";

                        $notificationMessage =
                            $alumni["first_name"] .
                            " " .
                            $alumni["last_name"] .
                            " updated their mentor profile. " .
                            "The changes require administrator review.";

                        $notificationType = "mentorship";


                        $adminStmt = $conn->prepare("
                            SELECT user_id
                            FROM users
                            WHERE role = 'admin'
                        ");

                        if ($adminStmt) {

                            $adminStmt->execute();

                            $adminResult =
                                $adminStmt->get_result();

                            while (
                                $admin = $adminResult->fetch_assoc()
                            ) {

                                $adminUserId =
                                    (int) $admin["user_id"];


                                $notificationStmt =
                                    $conn->prepare("
                                        INSERT INTO notifications
                                        (
                                            user_id,
                                            title,
                                            message,
                                            type,
                                            is_read,
                                            created_at
                                        )
                                        VALUES
                                        (
                                            ?,
                                            ?,
                                            ?,
                                            ?,
                                            0,
                                            NOW()
                                        )
                                    ");

                                if ($notificationStmt) {
$notificationStmt->bind_param(
                                        "isss",
                                        $adminUserId,
                                        $notificationTitle,
                                        $notificationMessage,
                                        $notificationType
                                    );

                                    $notificationStmt->execute();

                                    $notificationStmt->close();
                                }
                            }

                            $adminStmt->close();
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | SUCCESS
                        |--------------------------------------------------------------------------
                        */

                        $success =
                            "Your mentor information has been updated successfully. " .
                            "Your changes have been submitted for administrator review.";


                        /*
                        |--------------------------------------------------------------------------
                        | REFRESH PROFILE
                        |--------------------------------------------------------------------------
                        */

                        $existingMentor = [
                            "mentor_profile_id" =>
                                $mentorProfileId,

                            "expertise" =>
                                $expertise,

                            "skills" =>
                                $skills,

                            "experience_years" =>
                                $experienceYears,

                            "biography" =>
                                $biography,

                            "availability" =>
                                $availability,

                            "status" =>
                                "pending",

                            "created_at" =>
                                $existingMentor["created_at"] ?? null,

                            "updated_at" =>
                                date("Y-m-d H:i:s")
                        ];

                        $editMode = false;

                    } else {

                        $error =
                            "Unable to update your mentor profile.";
                    }

                    $stmt->close();
                }


            /*
            |--------------------------------------------------------------------------
            | PENDING APPLICATION
            |--------------------------------------------------------------------------
            */

            } elseif ($currentStatus === "pending") {

                $error =
                    "Your mentor application is already pending administrator review.";


            /*
            |--------------------------------------------------------------------------
            | REJECTED APPLICATION
            |--------------------------------------------------------------------------
            |
            | Rejected mentors can edit and resubmit.
            |
            */

            } elseif ($currentStatus === "rejected") {

                $newStatus = "pending";

                $stmt = $conn->prepare("
                    UPDATE mentor_profiles
                    SET
                        expertise = ?,
                        skills = ?,
                        experience_years = ?,
                        biography = ?,
                        availability = ?,
                        status = ?,
                        updated_at = NOW()
                    WHERE mentor_profile_id = ?
                ");

                if (!$stmt) {

                    $error =
                        "Unable to resubmit your mentor application.";

                } else {
 $stmt->bind_param(
                        "ssisssi",
                        $expertise,
                        $skills,
                        $experienceYears,
                        $biography,
                        $availability,
                        $newStatus,
                        $mentorProfileId
                    );

                    if ($stmt->execute()) {

                        /*
                        |--------------------------------------------------------------------------
                        | NOTIFY ALL ADMINS
                        |--------------------------------------------------------------------------
                        */

                        $notificationTitle =
                            "Mentor Application Resubmitted";

                        $notificationMessage =
                            $alumni["first_name"] .
                            " " .
                            $alumni["last_name"] .
                            " has resubmitted their mentor application for review.";

                        $notificationType = "mentorship";


                        $adminStmt = $conn->prepare("
                            SELECT user_id
                            FROM users
                            WHERE role = 'admin'
                        ");

                        if ($adminStmt) {

                            $adminStmt->execute();

                            $adminResult =
                                $adminStmt->get_result();

                            while (
                                $admin = $adminResult->fetch_assoc()
                            ) {

                                $adminUserId =
                                    (int) $admin["user_id"];


                                $notificationStmt =
                                    $conn->prepare("
                                        INSERT INTO notifications
                                        (
                                            user_id,
                                            title,
                                            message,
                                            type,
                                            is_read,
                                            created_at
                                        )
                                        VALUES
                                        (
                                            ?,
                                            ?,
                                            ?,
                                            ?,
                                            0,
                                            NOW()
                                        )
                                    ");

                                if ($notificationStmt) {

                                    $notificationStmt->bind_param(
                                        "isss",
                                        $adminUserId,
                                        $notificationTitle,
                                        $notificationMessage,
                                        $notificationType
                                    );

                                    $notificationStmt->execute();

                                    $notificationStmt->close();
                                }
                            }

                            $adminStmt->close();
                        }


                        $success =
                            "Your mentor application has been resubmitted successfully.";


                        $existingMentor = [
                            "mentor_profile_id" =>
                                $mentorProfileId,

                            "expertise" =>
                                $expertise,

                            "skills" =>
                                $skills,

                            "experience_years" =>
                                $experienceYears,
 "biography" =>
                                $biography,

                            "availability" =>
                                $availability,

                            "status" =>
                                "pending",

                            "created_at" =>
                                $existingMentor["created_at"] ?? null,

                            "updated_at" =>
                                date("Y-m-d H:i:s")
                        ];

                        $editMode = false;

                    } else {

                        $error =
                            "Unable to resubmit your mentor application.";
                    }

                    $stmt->close();
                }


            /*
            |--------------------------------------------------------------------------
            | OTHER EXISTING STATUS
            |--------------------------------------------------------------------------
            */

            } else {

                $error =
                    "You already have a mentor profile.";
            }


        /*
        |--------------------------------------------------------------------------
        | CREATE NEW MENTOR APPLICATION
        |--------------------------------------------------------------------------
        */

        } else {

            $status = "pending";

            $stmt = $conn->prepare("
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
            ");

            if (!$stmt) {

                $error =
                    "Unable to submit your mentor application.";

            } else {

                $stmt->bind_param(
                    "ississs",
                    $alumniId,
                    $expertise,
                    $skills,
                    $experienceYears,
                    $biography,
                    $availability,
                    $status
                );

                if ($stmt->execute()) {

                    $mentorProfileId =
                        $stmt->insert_id;


                    /*
                    |--------------------------------------------------------------------------
                    | NOTIFY ALL ADMINS
                    |--------------------------------------------------------------------------
                    */

                    $notificationTitle =
                        "New Mentor Application";

                    $notificationMessage =
                        $alumni["first_name"] .
                        " " .
                        $alumni["last_name"] .
                        " has submitted an application to become a mentor.";

                    $notificationType = "mentorship";


                    $adminStmt = $conn->prepare("
                        SELECT user_id
                        FROM users
                        WHERE role = 'admin'
                    ");

                    if ($adminStmt) {

                        $adminStmt->execute();

                        $adminResult =
                            $adminStmt->get_result();

                        while (
                            $admin = $adminResult->fetch_assoc()
                        ) {

                            $adminUserId =
                                (int) $admin["user_id"];
 $notificationStmt =
                                $conn->prepare("
                                    INSERT INTO notifications
                                    (
                                        user_id,
                                        title,
                                        message,
                                        type,
                                        is_read,
                                        created_at
                                    )
                                    VALUES
                                    (
                                        ?,
                                        ?,
                                        ?,
                                        ?,
                                        0,
                                        NOW()
                                    )
                                ");

                            if ($notificationStmt) {

                                $notificationStmt->bind_param(
                                    "isss",
                                    $adminUserId,
                                    $notificationTitle,
                                    $notificationMessage,
                                    $notificationType
                                );

                                $notificationStmt->execute();

                                $notificationStmt->close();
                            }
                        }

                        $adminStmt->close();
                    }


                    $success =
                        "Your mentor application has been submitted successfully.";


                    /*
                    |--------------------------------------------------------------------------
                    | DISPLAY SUBMITTED INFORMATION
                    |--------------------------------------------------------------------------
                    */

                    $existingMentor = [
                        "mentor_profile_id" =>
                            $mentorProfileId,

                        "expertise" =>
                            $expertise,

                        "skills" =>
                            $skills,

                        "experience_years" =>
                            $experienceYears,

                        "biography" =>
                            $biography,

                        "availability" =>
                            $availability,

                        "status" =>
                            "pending",

                        "created_at" =>
                            date("Y-m-d H:i:s"),

                        "updated_at" =>
                            date("Y-m-d H:i:s")
                    ];

                    $editMode = false;

                } else {

                    $error =
                        "Unable to submit your mentor application.";
                }

                $stmt->close();
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| FORM VALUES
|--------------------------------------------------------------------------
*/

$formExpertise =
    $existingMentor["expertise"] ?? "";

$formSkills =
    $existingMentor["skills"] ?? "";

$formExperience =
    $existingMentor["experience_years"] ?? 0;

$formBiography =
    $existingMentor["biography"] ?? "";

$formAvailability =
    $existingMentor["availability"] ?? "";

$mentorStatus =
    strtolower(
        trim(
            $existingMentor["status"] ?? ""
        )
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
        Become a Mentor |
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

    <style>
 /*
        |--------------------------------------------------------------------------
        | PAGE
        |--------------------------------------------------------------------------
        */

        .mentor-application-panel {
            max-width: 850px;
            margin: 0 auto;
        }

        .mentor-application-intro {
            margin-bottom: 25px;
        }

        .mentor-application-intro h2 {
            margin-bottom: 8px;
            color: #4a2c1d;
        }

        .mentor-application-intro p {
            color: #777;
            line-height: 1.6;
        }


        /*
        |--------------------------------------------------------------------------
        | MESSAGES
        |--------------------------------------------------------------------------
        */

        .mentor-success,
        .mentor-error {
            padding: 14px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .mentor-success {
            background: #e8f5e9;
            color: #27632a;
        }

        .mentor-error {
            background: #fdecea;
            color: #a12622;
        }


        /*
        |--------------------------------------------------------------------------
        | STATUS BOX
        |--------------------------------------------------------------------------
        */

        .mentor-status-box {
            padding: 18px;
            margin-top: 25px;
            margin-bottom: 25px;
            border-radius: 9px;
            background: #fff8e1;
            border: 1px solid #f0dfad;
        }

        .mentor-status-box strong {
            color: #7a5700;
        }

        .mentor-status-box p {
            margin: 7px 0 0;
            color: #6d5b2b;
            line-height: 1.5;
        }


        /*
        |--------------------------------------------------------------------------
        | PROFILE DISPLAY
        |--------------------------------------------------------------------------
        */

        .mentor-profile-display {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .mentor-info-row {
            padding-bottom: 15px;
            border-bottom: 1px solid #eeeeee;
        }

        .mentor-info-row:last-child {
            border-bottom: none;
        }

        .mentor-info-label {
            display: block;
            font-size: 12px;
            color: #777777;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .mentor-info-value {
            color: #444444;
            line-height: 1.6;
            white-space: pre-wrap;
        }


        /*
        |--------------------------------------------------------------------------
        | STATUS BADGES
        |--------------------------------------------------------------------------
        */

        .mentor-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .mentor-badge-pending {
            background: #fff3cd;
            color: #856404;
        }

        .mentor-badge-active {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .mentor-badge-rejected {
            background: #fdecea;
            color: #a12622;
        }


        /*
        |--------------------------------------------------------------------------
        | FORM
        |--------------------------------------------------------------------------
        */

        .mentor-form {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .form-group label {
            font-weight: 600;
            color: #444;
        }
 .form-group input,
        .form-group textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 12px 13px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            background: #fff;
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #7a4b2a;
        }

        .form-help {
            font-size: 12px;
            color: #888;
        }


        /*
        |--------------------------------------------------------------------------
        | BUTTONS
        |--------------------------------------------------------------------------
        */

        .mentor-form-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .submit-mentor-button,
        .edit-mentor-button,
        .back-mentor-button,
        .cancel-mentor-button {

            display: inline-block;
            padding: 11px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .submit-mentor-button {
            border: none;
            background: #7a4b2a;
            color: #fff;
        }

        .submit-mentor-button:hover {
            background: #5f3921;
        }

        .edit-mentor-button {
            background: #7a4b2a;
            color: #fff;
        }

        .edit-mentor-button:hover {
            background: #5f3921;
        }

        .back-mentor-button,
        .cancel-mentor-button {
            background: #fff;
            color: #7a4b2a;
            border: 1px solid #7a4b2a;
        }

        .back-mentor-button:hover,
        .cancel-mentor-button:hover {
            background: #7a4b2a;
            color: #fff;
        }


        /*
        |--------------------------------------------------------------------------
        | REJECTED
        |--------------------------------------------------------------------------
        */

        .rejected-box {
            padding: 18px;
            margin-bottom: 20px;
            border-radius: 9px;
            background: #fdecea;
            border: 1px solid #f5c6c6;
            color: #8b2626;
        }

        .rejected-box strong {
            display: block;
            margin-bottom: 6px;
        }


        /*
        |--------------------------------------------------------------------------
        | EDIT NOTICE
        |--------------------------------------------------------------------------
        */

        .edit-notice {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            background: #f8f5f2;
            border: 1px solid #eadfd6;
            color: #5f3921;
            line-height: 1.5;
        }


        /*
        |--------------------------------------------------------------------------
        | RESPONSIVE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 650px) {

            .mentor-application-panel {
                width: 100%;
            }

            .mentor-form-actions {
                flex-direction: column;
            }

            .submit-mentor-button,
            .edit-mentor-button,
            .back-mentor-button,
            .cancel-mentor-button {
                text-align: center;
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
                    Become a Mentor
                </h1>

                <p>
                    Share your experience and help other alumni
                    grow in their careers.
                </p>

            </div>

        </header>


        <!-- =================================================
             CONTENT
        ================================================== -->

        <section class="dashboard-content">

            <div class="dashboard-panel mentor-application-panel">


                <!-- =================================================
                     INTRO
                ================================================== -->

                <div class="mentor-application-intro">

                    <h2>
                        Mentor Application
                    </h2>

                    <p>
                        Complete the form below to apply to become
                        a mentor. Your application will be reviewed
                        by the administration before you become
                        an active mentor.
                    </p>

                </div>


                <!-- =================================================
                     SUCCESS MESSAGE
                ================================================== -->

                <?php if ($success !== ""): ?>

                    <div class="mentor-success">
                        <?= e($success) ?>
                    </div>

                <?php endif; ?>


                <!-- =================================================
                     ERROR MESSAGE
                ================================================== -->

                <?php if ($error !== ""): ?>

                    <div class="mentor-error">
                        <?= e($error) ?>
                    </div>

                <?php endif; ?>


                <!-- =================================================
                     EDIT MODE
                ================================================== -->

                <?php if ($editMode): ?>

                    <div class="edit-notice">

                        <strong>
                            Edit Mentor Information
                        </strong>

                        <br>

                        Update your mentor information below.

                        Because your current profile is active,
                        submitting changes will send your profile
                        back to the administrator for review.

                    </div>


                    <!-- =================================================
                         EDIT FORM
                    ================================================== -->

                    <form
                        method="POST"
                        action="become.php"
                        class="mentor-form"
                    >


                        <!-- EXPERTISE -->

                        <div class="form-group">

                            <label for="expertise">
                                Area of Expertise
                            </label>

                            <input
                                type="text"
                                id="expertise"
                                name="expertise"
                                value="<?= e($formExpertise) ?>"
                                placeholder="Example: Web Development"
                                required
                            >
 <span class="form-help">
                                Enter the main area where you can
                                provide mentorship.
                            </span>

                        </div>


                        <!-- SKILLS -->

                        <div class="form-group">

                            <label for="skills">
                                Skills
                            </label>

                            <input
                                type="text"
                                id="skills"
                                name="skills"
                                value="<?= e($formSkills) ?>"
                                placeholder="Example: PHP, MySQL, JavaScript"
                                required
                            >

                            <span class="form-help">
                                Separate multiple skills with commas.
                            </span>

                        </div>


                        <!-- EXPERIENCE -->

                        <div class="form-group">

                            <label for="experience_years">
                                Years of Experience
                            </label>

                            <input
                                type="number"
                                id="experience_years"
                                name="experience_years"
                                value="<?= (int) $formExperience ?>"
                                min="0"
                                max="100"
                                required
                            >

                        </div>


                        <!-- BIOGRAPHY -->

                        <div class="form-group">

                            <label for="biography">
                                Biography
                            </label>

                            <textarea
                                id="biography"
                                name="biography"
                                placeholder="Tell alumni about your professional background and experience..."
                                required
                            ><?= e($formBiography) ?></textarea>

                        </div>


                        <!-- AVAILABILITY -->

                        <div class="form-group">

                            <label for="availability">
                                Availability
                            </label>

                            <textarea
                                id="availability"
                                name="availability"
                                placeholder="Example: Saturdays 9:00 AM - 12:00 PM"
                                required
                            ><?= e($formAvailability) ?></textarea>

                            <span class="form-help">
                                Explain when you are normally available
                                to mentor other alumni.
                            </span>

                        </div>


                        <!-- ACTIONS -->

                        <div class="mentor-form-actions">

                            <button
                                type="submit"
                                class="submit-mentor-button"
                            >
                                Submit Changes for Review
                            </button>

                            <a
                                href="become.php"
                                class="cancel-mentor-button"
                            >
                                Cancel
                            </a>

                        </div>

                    </form>


                <!-- =================================================
                     EXISTING PROFILE VIEW
                ================================================== -->

                <?php elseif ($existingMentor): ?>
 <div class="mentor-profile-display">


                        <!-- STATUS -->

                        <div class="mentor-info-row">

                            <span class="mentor-info-label">
                                Application Status
                            </span>


                            <?php if ($mentorStatus === "pending"): ?>

                                <span class="mentor-badge mentor-badge-pending">
                                    Pending Review
                                </span>

                            <?php elseif ($mentorStatus === "active"): ?>

                                <span class="mentor-badge mentor-badge-active">
                                    Active Mentor
                                </span>

                            <?php elseif ($mentorStatus === "rejected"): ?>

                                <span class="mentor-badge mentor-badge-rejected">
                                    Rejected
                                </span>

                            <?php else: ?>

                                <span class="mentor-badge mentor-badge-pending">
                                    <?= e(ucfirst($mentorStatus)) ?>
                                </span>

                            <?php endif; ?>

                        </div>


                        <!-- EXPERTISE -->

                        <div class="mentor-info-row">

                            <span class="mentor-info-label">
                                Area of Expertise
                            </span>

                            <div class="mentor-info-value">
                                <?= e($existingMentor["expertise"]) ?>
                            </div>

                        </div>


                        <!-- SKILLS -->

                        <div class="mentor-info-row">

                            <span class="mentor-info-label">
                                Skills
                            </span>

                            <div class="mentor-info-value">
                                <?= e($existingMentor["skills"]) ?>
                            </div>

                        </div>


                        <!-- EXPERIENCE -->

                        <div class="mentor-info-row">

                            <span class="mentor-info-label">
                                Years of Experience
                            </span>

                            <div class="mentor-info-value">

                                <?= (int) $existingMentor["experience_years"] ?>

                                years

                            </div>

                        </div>


                        <!-- BIOGRAPHY -->

                        <div class="mentor-info-row">

                            <span class="mentor-info-label">
                                Biography
                            </span>

                            <div class="mentor-info-value">
                                <?= e($existingMentor["biography"]) ?>
                            </div>

                        </div>


                        <!-- AVAILABILITY -->

                        <div class="mentor-info-row">

                            <span class="mentor-info-label">
                                Availability
                            </span>

                            <div class="mentor-info-value">
                                <?= e($existingMentor["availability"]) ?>
                            </div>

                        </div>


                        <!-- CREATED -->

                        <?php if (!empty($existingMentor["created_at"])): ?>

                            <div class="mentor-info-row">

                                <span class="mentor-info-label">
                                    Application Date
                                </span>

                                <div class="mentor-info-value">
 <?= e(
                                        date(
                                            "M d, Y",
                                            strtotime(
                                                $existingMentor["created_at"]
                                            )
                                        )
                                    ) ?>

                                </div>

                            </div>

                        <?php endif; ?>


                    </div>


                    <!-- =================================================
                         PENDING MESSAGE
                    ================================================== -->

                    <?php if ($mentorStatus === "pending"): ?>

                        <div class="mentor-status-box">

                            <strong>
                                Application Pending
                            </strong>

                            <p>
                                Your mentor application is currently
                                waiting for administrator review.
                                You cannot edit the application while
                                it is under review.
                            </p>

                        </div>

                    <?php endif; ?>


                    <!-- =================================================
                         ACTIVE MESSAGE
                    ================================================== -->

                    <?php if ($mentorStatus === "active"): ?>

                        <div class="mentor-status-box">

                            <strong>
                                You are an active mentor.
                            </strong>

                            <p>
                                Your mentor profile is currently
                                visible to other alumni. You can edit
                                your information if necessary.
                                Changes will require administrator
                                review before becoming active again.
                            </p>

                        </div>

                    <?php endif; ?>


                    <!-- =================================================
                         REJECTED MESSAGE
                    ================================================== -->

                    <?php if ($mentorStatus === "rejected"): ?>

                        <div class="rejected-box">

                            <strong>
                                Mentor Application Rejected
                            </strong>

                            Your application was not approved.
                            You can edit your information and
                            resubmit your application for review.

                        </div>

                    <?php endif; ?>


                    <!-- =================================================
                         ACTIONS
                    ================================================== -->

                    <div class="mentor-form-actions">


                        <!-- ACTIVE CAN EDIT -->

                        <?php if ($mentorStatus === "active"): ?>

                            <a
                                href="become.php?edit=1"
                                class="edit-mentor-button"
                            >
                                Edit Mentor Information
                            </a>

                        <?php endif; ?>


                        <!-- REJECTED CAN EDIT/RESUBMIT -->

                        <?php if ($mentorStatus === "rejected"): ?>

                            <a
                                href="become.php?edit=1"
                                class="edit-mentor-button"
                            >
                                Edit & Resubmit
                            </a>
 <?php endif; ?>


                        <a
                            href="index.php"
                            class="back-mentor-button"
                        >
                            ← Back to Mentorship
                        </a>

                    </div>


                <!-- =================================================
                     NO PROFILE YET
                ================================================== -->

                <?php else: ?>


                    <!-- =================================================
                         APPLICATION FORM
                    ================================================== -->

                    <form
                        method="POST"
                        action="become.php"
                        class="mentor-form"
                    >


                        <!-- EXPERTISE -->

                        <div class="form-group">

                            <label for="expertise">
                                Area of Expertise
                            </label>

                            <input
                                type="text"
                                id="expertise"
                                name="expertise"
                                value="<?= e($formExpertise) ?>"
                                placeholder="Example: Web Development"
                                required
                            >

                            <span class="form-help">
                                Enter the main area where you can
                                provide mentorship.
                            </span>

                        </div>


                        <!-- SKILLS -->

                        <div class="form-group">

                            <label for="skills">
                                Skills
                            </label>

                            <input
                                type="text"
                                id="skills"
                                name="skills"
                                value="<?= e($formSkills) ?>"
                                placeholder="Example: PHP, MySQL, JavaScript"
                                required
                            >

                            <span class="form-help">
                                Separate multiple skills with commas.
                            </span>

                        </div>


                        <!-- EXPERIENCE -->

                        <div class="form-group">

                            <label for="experience_years">
                                Years of Experience
                            </label>

                            <input
                                type="number"
                                id="experience_years"
                                name="experience_years"
                                value="<?= (int) $formExperience ?>"
                                min="0"
                                max="100"
                                required
                            >

                        </div>


                        <!-- BIOGRAPHY -->

                        <div class="form-group">

                            <label for="biography">
                                Biography
                            </label>

                            <textarea
                                id="biography"
                                name="biography"
                                placeholder="Tell alumni about your professional background and experience..."
                                required
                            ><?= e($formBiography) ?></textarea>

                        </div>


                        <!-- AVAILABILITY -->

                        <div class="form-group">

                            <label for="availability">
                                Availability
                            </label>
 <textarea
                                id="availability"
                                name="availability"
                                placeholder="Example: Saturdays 9:00 AM - 12:00 PM"
                                required
                            ><?= e($formAvailability) ?></textarea>

                            <span class="form-help">
                                Explain when you are normally available
                                to mentor other alumni.
                            </span>

                        </div>


                        <!-- ACTIONS -->

                        <div class="mentor-form-actions">

                            <button
                                type="submit"
                                class="submit-mentor-button"
                            >
                                Submit Mentor Application
                            </button>

                            <a
                                href="index.php"
                                class="back-mentor-button"
                            >
                                Back to Mentorship
                            </a>

                        </div>


                    </form>

                <?php endif; ?>


            </div>

        </section>

    </main>

</div>

</body>

</html>