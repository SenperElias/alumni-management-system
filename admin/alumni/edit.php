<?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";

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
| Get Alumni ID
|--------------------------------------------------------------------------
*/

$alumniId = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($alumniId <= 0) {
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Get Current Alumni
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT
        a.*,
        u.email
     FROM alumni a
     LEFT JOIN users u
        ON a.user_id = u.user_id
     WHERE a.alumni_id = ?
     LIMIT 1"
);

$stmt->bind_param("i", $alumniId);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit;
}

$alumni = $result->fetch_assoc();

$userId = $alumni["user_id"];

/*
|--------------------------------------------------------------------------
| Variables
|--------------------------------------------------------------------------
*/

$error = "";
$success = "";

$email = $alumni["email"] ?? "";
$alumniIdNumber = $alumni["college_id_number"] ?? "";
$firstName = $alumni["first_name"] ?? "";
$lastName = $alumni["last_name"] ?? "";
$gender = $alumni["gender"] ?? "";
$dateOfBirth = $alumni["date_of_birth"] ?? "";
$phone = $alumni["phone"] ?? "";
$address = $alumni["address"] ?? "";
$departmentId = $alumni["department_id"] ?? "";
$graduationYear = $alumni["graduation_year"] ?? "";
$bio = $alumni["bio"] ?? "";
$profilePhoto = $alumni["profile_photo"] ?? "";

$showProfile = (int) ($alumni["show_profile"] ?? 1);
$showProfession = (int) ($alumni["show_profession"] ?? 1);
$showSkills = (int) ($alumni["show_skills"] ?? 1);
$showEmail = (int) ($alumni["show_email"] ?? 1);
$showPhone = (int) ($alumni["show_phone"] ?? 1);

/*
|--------------------------------------------------------------------------
| Get Departments
|--------------------------------------------------------------------------
*/

$departments = [];

$departmentResult = $conn->query(
    "SELECT department_id, department_name
     FROM departments
     ORDER BY department_name ASC"
);

if ($departmentResult) {
    while ($row = $departmentResult->fetch_assoc()) {
        $departments[] = $row;
    }
}

/*
|--------------------------------------------------------------------------
| Process Form
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $alumniIdNumber = trim($_POST["college_id_number"] ?? "");
    $firstName = trim($_POST["first_name"] ?? "");
    $lastName = trim($_POST["last_name"] ?? "");
    $gender = trim($_POST["gender"] ?? "");
    $dateOfBirth = trim($_POST["date_of_birth"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $departmentId = $_POST["department_id"] ?? "";
    $graduationYear = $_POST["graduation_year"] ?? "";
    $bio = trim($_POST["bio"] ?? "");

    $showProfile = isset($_POST["show_profile"]) ? 1 : 0;
    $showProfession = isset($_POST["show_profession"]) ? 1 : 0;
    $showSkills = isset($_POST["show_skills"]) ? 1 : 0;
    $showEmail = isset($_POST["show_email"]) ? 1 : 0;
    $showPhone = isset($_POST["show_phone"]) ? 1 : 0;

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */
    if (
        $email === "" ||
        $alumniIdNumber === "" ||
        $firstName === "" ||
        $lastName === "" ||
        $gender === "" ||
        $departmentId === "" ||
        $graduationYear === ""
    ) {

        $error = "Please fill in all required fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Check Email
        |--------------------------------------------------------------------------
        */

        $emailCheck = $conn->prepare(
            "SELECT user_id
             FROM users
             WHERE email = ?
             AND user_id != ?
             LIMIT 1"
        );

        $emailCheck->bind_param(
            "si",
            $email,
            $userId
        );

        $emailCheck->execute();

        $emailResult = $emailCheck->get_result();

        if ($emailResult->num_rows > 0) {

            $error = "This email address is already being used.";

        } else {

            /*
            |--------------------------------------------------------------------------
            | Check Alumni ID
            |--------------------------------------------------------------------------
            */

            $idCheck = $conn->prepare(
                "SELECT alumni_id
                 FROM alumni
                 WHERE college_id_number = ?
                 AND alumni_id != ?
                 LIMIT 1"
            );

            $idCheck->bind_param(
                "si",
                $alumniIdNumber,
                $alumniId
            );

            $idCheck->execute();

            $idResult = $idCheck->get_result();

            if ($idResult->num_rows > 0) {

                $error = "This Alumni ID already exists.";

            } else {

                /*
                |--------------------------------------------------------------------------
                | Profile Photo
                |--------------------------------------------------------------------------
                */

                $newProfilePhoto = $profilePhoto;

                if (
                    isset($_FILES["profile_photo"]) &&
                    $_FILES["profile_photo"]["error"] !== UPLOAD_ERR_NO_FILE
                ) {

                    if (
                        $_FILES["profile_photo"]["error"] !==
                        UPLOAD_ERR_OK
                    ) {

                        $error =
                            "There was a problem uploading the photo.";

                    } else {

                        $file = $_FILES["profile_photo"];

                        $maxSize = 5 * 1024 * 1024;

                        if ($file["size"] > $maxSize) {

                            $error =
                                "Profile photo must be smaller than 5 MB.";

                        } else {

                            $allowedTypes = [
                                "image/jpeg" => "jpg",
                                "image/png" => "png",
                                "image/webp" => "webp"
                            ];

                            $mimeType =
                                mime_content_type(
                                    $file["tmp_name"]
                                );

                            if (!isset($allowedTypes[$mimeType])) {

                                $error =
                                    "Only JPG, PNG and WebP images are allowed.";

                            } else {

                                $extension =
                                    $allowedTypes[$mimeType];

                                $newProfilePhoto =
                                    bin2hex(
                                        random_bytes(16)
                                    )
                                    . "."
                                    . $extension;
                   $uploadDirectory =
                                    "../../uploads/";

                                if (!is_dir($uploadDirectory)) {
                                    mkdir(
                                        $uploadDirectory,
                                        0755,
                                        true
                                    );
                                }

                                $destination =
                                    $uploadDirectory
                                    . $newProfilePhoto;

                                if (
                                    !move_uploaded_file(
                                        $file["tmp_name"],
                                        $destination
                                    )
                                ) {

                                    $error =
                                        "Unable to save the new profile photo.";

                                }
                            }
                        }
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Update Database
                |--------------------------------------------------------------------------
                */

                if ($error === "") {

                    $conn->begin_transaction();

                    try {

                        /*
                        | Update user email
                        */

                        $userUpdate = $conn->prepare(
                            "UPDATE users
                             SET email = ?
                             WHERE user_id = ?"
                        );

                        $userUpdate->bind_param(
                            "si",
                            $email,
                            $userId
                        );

                        $userUpdate->execute();

                        /*
                        | Update alumni profile
                        */

                        $alumniUpdate = $conn->prepare(
                            "UPDATE alumni
                             SET
                                college_id_number = ?,
                                first_name = ?,
                                last_name = ?,
                                gender = ?,
                                date_of_birth = ?,
                                phone = ?,
                                address = ?,
                                department_id = ?,
                                graduation_year = ?,
                                bio = ?,
                                profile_photo = ?,
                                show_profile = ?,
                                show_profession = ?,
                                show_skills = ?,
                                show_email = ?,
                                show_phone = ?
                             WHERE alumni_id = ?"
                        );

                        $alumniUpdate->bind_param(
                            "sssssssisssiiiiii",
                            $alumniIdNumber,
                            $firstName,
                            $lastName,
                            $gender,
                            $dateOfBirth,
                            $phone,
                            $address,
                            $departmentId,
                            $graduationYear,
                            $bio,
                            $newProfilePhoto,
                            $showProfile,
                            $showProfession,
                            $showSkills,
                            $showEmail,
                            $showPhone,
                            $alumniId
                        );

                        $alumniUpdate->execute();

                        $conn->commit();
                     /*
                        | Delete old photo only after
                        | successful database update
                        */

                        if (
                            $newProfilePhoto !== $profilePhoto &&
                            !empty($profilePhoto)
                        ) {

                            $oldPhoto =
                                "../../uploads/"
                                . $profilePhoto;

                            if (file_exists($oldPhoto)) {
                                unlink($oldPhoto);
                            }
                        }

                        $profilePhoto = $newProfilePhoto;

                        $success =
                            "Alumni profile updated successfully.";

                    } catch (Exception $e) {

                        $conn->rollback();

                        /*
                        | Remove new photo if database update failed
                        */

                        if (
                            $newProfilePhoto !== $profilePhoto &&
                            file_exists(
                                "../../uploads/"
                                . $newProfilePhoto
                            )
                        ) {

                            unlink(
                                "../../uploads/"
                                . $newProfilePhoto
                            );
                        }

                        $error =
                            "Unable to update alumni profile. "
                            . $e->getMessage();
                    }
                }
            }

            $idCheck->close();
        }

        $emailCheck->close();
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Edit Alumni |
        <?= e(SITE_NAME) ?>
    </title>

    <link rel="stylesheet"
          href="../../assets/css/style.css">

</head>

<body class="admin-body">

<div class="admin-layout">

    <!-- Sidebar -->

    <aside class="admin-sidebar">

        <div class="admin-brand">

            <div class="brand-logo">
                TM
            </div>

            <div>
                <strong>Alumni System</strong>
                <small>Admin Panel</small>
            </div>

        </div>

        <nav class="admin-nav">

            <a href="../dashboard.php">
                Dashboard
            </a>

            <div class="nav-section">
                MANAGEMENT
            </div>

            <a href="index.php" class="active">
                Alumni
            </a>

            <a href="#">
                Employment
            </a>

            <a href="#">
                Opportunities
            </a>

            <a href="#">
                Mentorship
            </a>

            <a href="#">
                Projects
            </a>

            <a href="#">
                Events
            </a>

            <a href="#">
                Contributions
            </a>

            <div class="nav-section">
                CONTENT
            </div>

            <a href="#">
                Success Stories
            </a>

            <a href="#">
                Announcements
            </a>

            <a href="#">
                Gallery
            </a>

            <a href="#">
                Contact Messages
            </a>

            <div class="nav-section">
                SYSTEM
            </div>

            <a href="#">
                Reports
            </a>

            <a href="#">
                Notifications
            </a>

            <a href="#">
                Settings
            </a>

            <a href="../../auth/logout.php"
               class="logout-link">
                Logout
            </a>

        </nav>

    </aside>


    <!-- Main Content -->

    <main class="admin-main">

        <header class="admin-topbar">
             <div>

                <h1>Edit Alumni</h1>

                <p>
                    Update alumni information and profile settings.
                </p>

            </div>

            <div class="admin-user">

                <div class="admin-avatar">
                    A
                </div>

                <div>

                    <strong>Administrator</strong>

                    <small>
                        System Admin
                    </small>

                </div>

            </div>

        </header>


        <section class="dashboard-content">

            <?php if ($error !== ""): ?>

                <div class="form-alert error-message">
                    <?= e($error) ?>
                </div>

            <?php endif; ?>


            <?php if ($success !== ""): ?>

                <div class="form-alert success-message">
                    <?= e($success) ?>
                </div>

            <?php endif; ?>


            <form
                method="POST"
                enctype="multipart/form-data"
                class="alumni-form"
            >

                <!-- Account -->

                <div class="dashboard-panel">

                    <div class="panel-header">

                        <div>

                            <h2>Login Account</h2>

                            <p>
                                Update the alumni's email address.
                            </p>

                        </div>

                    </div>

                    <div class="form-grid">

                        <div class="form-field">

                            <label for="email">
                                Email Address *
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="<?= e($email) ?>"
                                required
                            >

                        </div>

                    </div>

                </div>


                <!-- Personal Information -->

                <div class="dashboard-panel">

                    <div class="panel-header">

                        <div>

                            <h2>Personal Information</h2>

                            <p>
                                Update the alumni's personal details.
                            </p>

                        </div>

                    </div>

                    <div class="form-grid">

                        <div class="form-field">

                            <label for="college_id_number">
                                College ID Number *
                            </label>

                            <input
                                type="text"
                                id="college_id_number"
                                name="college_id_number"
                                value="<?= e($alumniIdNumber) ?>"
                                required
                            >

                        </div>


                        <div class="form-field">

                            <label for="first_name">
                                First Name *
                            </label>

                            <input
                                type="text"
                                id="first_name"
                                name="first_name"
                                value="<?= e($firstName) ?>"
                                required
                            >

                        </div>


                        <div class="form-field">

                            <label for="last_name">
                                Last Name *
                            </label>
                             <input
                                type="text"
                                id="last_name"
                                name="last_name"
                                value="<?= e($lastName) ?>"
                                required
                            >

                        </div>


                        <div class="form-field">

                            <label for="gender">
                                Gender *
                            </label>

                            <select
                                id="gender"
                                name="gender"
                                required
                            >

                                <option value="">
                                    Select Gender
                                </option>

                                <option
                                    value="Male"
                                    <?= $gender === "Male"
                                        ? "selected"
                                        : "" ?>
                                >
                                    Male
                                </option>

                                <option
                                    value="Female"
                                    <?= $gender === "Female"
                                        ? "selected"
                                        : "" ?>
                                >
                                    Female
                                </option>

                            </select>

                        </div>


                        <div class="form-field">

                            <label for="date_of_birth">
                                Date of Birth
                            </label>

                            <input
                                type="date"
                                id="date_of_birth"
                                name="date_of_birth"
                                value="<?= e($dateOfBirth) ?>"
                            >

                        </div>


                        <div class="form-field">

                            <label for="phone">
                                Phone
                            </label>

                            <input
                                type="tel"
                                id="phone"
                                name="phone"
                                value="<?= e($phone) ?>"
                            >

                        </div>


                        <div class="form-field form-full">

                            <label for="address">
                                Address
                            </label>

                            <input
                                type="text"
                                id="address"
                                name="address"
                                value="<?= e($address) ?>"
                            >

                        </div>

                    </div>

                </div>


                <!-- Education -->

                <div class="dashboard-panel">

                    <div class="panel-header">

                        <div>

                            <h2>Education Information</h2>

                            <p>
                                Update department and graduation information.
                            </p>

                        </div>

                    </div>

                    <div class="form-grid">

                        <div class="form-field">

                            <label for="department_id">
                                Department *
                            </label>

                            <select
                                id="department_id"
                                name="department_id"
                                required
                            >
                              <option value="">
                                    Select Department
                                </option>

                                <?php foreach ($departments as $department): ?>

                                    <option
                                        value="<?= e($department["department_id"]) ?>"
                                        <?= $departmentId ==
                                            $department["department_id"]
                                            ? "selected"
                                            : "" ?>
                                    >
                                        <?= e($department["department_name"]) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <div class="form-field">

                            <label for="graduation_year">
                                Graduation Year *
                            </label>

                            <input
                                type="number"
                                id="graduation_year"
                                name="graduation_year"
                                min="1900"
                                max="2100"
                                value="<?= e($graduationYear) ?>"
                                required
                            >

                        </div>

                    </div>

                </div>


                <!-- Profile -->

                <div class="dashboard-panel">

                    <div class="panel-header">

                        <div>

                            <h2>Profile</h2>

                            <p>
                                Update biography and profile photo.
                            </p>

                        </div>

                    </div>

                    <div class="form-grid">

                        <div class="form-field">

                            <label>
                                Current Profile Photo
                            </label>

                            <?php if (!empty($profilePhoto)): ?>

                                <img
                                    src="../../uploads/<?= e($profilePhoto) ?>"
                                    alt="Current profile photo"
                                    class="edit-profile-photo"
                                >

                            <?php else: ?>

                                <p class="muted-text">
                                    No profile photo uploaded.
                                </p>

                            <?php endif; ?>

                        </div>


                        <div class="form-field">

                            <label for="profile_photo">
                                Replace Profile Photo
                            </label>

                            <input
                                type="file"
                                id="profile_photo"
                                name="profile_photo"
                                accept=".jpg,.jpeg,.png,.webp"
                            >

                            <small>
                                JPG, PNG or WebP. Maximum 5 MB.
                            </small>

                        </div>


                        <div class="form-field form-full">

                            <label for="bio">
                                Biography
                            </label>

                            <textarea
                                id="bio"
                                name="bio"
                                rows="5"
                                placeholder="Write a short biography..."
                            ><?= e($bio) ?></textarea>

                        </div>

                    </div>

                </div>


                <!-- Visibility -->
                  <div class="dashboard-panel">

                    <div class="panel-header">

                        <div>

                            <h2>Profile Visibility</h2>

                            <p>
                                Choose what information can be displayed
                                publicly.
                            </p>

                        </div>

                    </div>


                    <div class="visibility-form">

                        <label class="visibility-checkbox">

                            <input
                                type="checkbox"
                                name="show_profile"
                                <?= $showProfile ? "checked" : "" ?>
                            >

                            <span>
                                Show Profile
                            </span>

                        </label>


                        <label class="visibility-checkbox">

                            <input
                                type="checkbox"
                                name="show_profession"
                                <?= $showProfession ? "checked" : "" ?>
                            >

                            <span>
                                Show Profession
                            </span>

                        </label>


                        <label class="visibility-checkbox">

                            <input
                                type="checkbox"
                                name="show_skills"
                                <?= $showSkills ? "checked" : "" ?>
                            >

                            <span>
                                Show Skills
                            </span>

                        </label>


                        <label class="visibility-checkbox">

                            <input
                                type="checkbox"
                                name="show_email"
                                <?= $showEmail ? "checked" : "" ?>
                            >

                            <span>
                                Show Email
                            </span>

                        </label>


                        <label class="visibility-checkbox">

                            <input
                                type="checkbox"
                                name="show_phone"
                                <?= $showPhone ? "checked" : "" ?>
                            >

                            <span>
                                Show Phone
                            </span>

                        </label>

                    </div>

                </div>


                <!-- Actions -->

                <div class="form-actions">

                    <a
                        href="view.php?id=<?= e($alumniId) ?>"
                        class="secondary-button"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="primary-button"
                    >
                        Save Changes
                    </button>

                </div>

            </form>

        </section>

    </main>

</div>

</body>

</html>              