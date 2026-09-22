<?php
error_reporting(E_ALL);

session_start();

require_once "../config/database.php";
require_once "../config/config.php";
require_once "../includes/functions.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "alumni") {
    header("Location: ../index.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];
$error = "";
$success = "";

/*
|--------------------------------------------------------------------------
| Get Alumni
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT
        a.*,
        u.email,
        d.department_name
     FROM alumni a
     LEFT JOIN users u
        ON a.user_id = u.user_id
     LEFT JOIN departments d
        ON a.department_id = d.department_id
     WHERE a.user_id = ?
     LIMIT 1"
);

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Alumni profile not found.");
}

$alumni = $result->fetch_assoc();

$stmt->close();

/*
|--------------------------------------------------------------------------
| Update Profile
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf_token();

    $phone = trim($_POST["phone"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $bio = trim($_POST["bio"] ?? "");

    $showProfile = isset($_POST["show_profile"]) ? 1 : 0;
    $showEmail = isset($_POST["show_email"]) ? 1 : 0;
    $showPhone = isset($_POST["show_phone"]) ? 1 : 0;
    $showProfession = isset($_POST["show_profession"]) ? 1 : 0;
    $showSkills = isset($_POST["show_skills"]) ? 1 : 0;

    $profilePhoto = $alumni["profile_photo"];

    /*
    |--------------------------------------------------------------------------
    | Profile Photo
    |--------------------------------------------------------------------------
    */

    if (
        isset($_FILES["profile_photo"]) &&
        $_FILES["profile_photo"]["error"] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES["profile_photo"]["error"] !== UPLOAD_ERR_OK) {

            $error = "There was a problem uploading the profile photo.";

        } else {

            $allowedTypes = [
                "image/jpeg",
                "image/png",
                "image/webp"
            ];

            $fileType = $_FILES["profile_photo"]["type"];

            if (!in_array($fileType, $allowedTypes, true)) {

                $error =
                    "Only JPG, PNG, and WEBP images are allowed.";

            } elseif ($_FILES["profile_photo"]["size"] > 5 * 1024 * 1024) {

                $error =
                    "The profile photo must be less than 5 MB.";

            } else {

                $extension =
                    strtolower(
                        pathinfo(
                            $_FILES["profile_photo"]["name"],
                            PATHINFO_EXTENSION
                        )
                    );

                $newFileName =
                    "alumni_" .
                    $userId .
                    "_" .
                    time() .
                    "." .
                    $extension;

                $uploadDirectory = "../uploads/";

                if (!is_dir($uploadDirectory)) {
                    mkdir($uploadDirectory, 0755, true);
                }

                $destination =
                    $uploadDirectory . $newFileName;

                if (
                    move_uploaded_file(
                        $_FILES["profile_photo"]["tmp_name"],
                        $destination
                    )
                ) {

                    /*
                    | Delete old photo
                    */

                    if (!empty($profilePhoto)) {

                        $oldPhoto =
                            $uploadDirectory . $profilePhoto;

                        if (file_exists($oldPhoto)) {
                            unlink($oldPhoto);
                        }
                    }
                    $profilePhoto = $newFileName;

                } else {

                    $error =
                        "Unable to save the profile photo.";
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

        $update = $conn->prepare(
            "UPDATE alumni
             SET
                phone = ?,
                address = ?,
                bio = ?,
                profile_photo = ?,
                show_profile = ?,
                show_email = ?,
                show_phone = ?,
                show_profession = ?,
                show_skills = ?,
                updated_at = NOW()
             WHERE user_id = ?"
        );

        $update->bind_param(
            "ssssiiiiii",
            $phone,
            $address,
            $bio,
            $profilePhoto,
            $showProfile,
            $showEmail,
            $showPhone,
            $showProfession,
            $showSkills,
            $userId
        );

        if ($update->execute()) {

            $success =
                "Your profile has been updated successfully.";

            /*
            | Reload current data
            */

            $stmt = $conn->prepare(
                "SELECT
                    a.*,
                    u.email,
                    d.department_name
                 FROM alumni a
                 LEFT JOIN users u
                    ON a.user_id = u.user_id
                 LEFT JOIN departments d
                    ON a.department_id = d.department_id
                 WHERE a.user_id = ?
                 LIMIT 1"
            );

            $stmt->bind_param("i", $userId);
            $stmt->execute();

            $result = $stmt->get_result();

            $alumni = $result->fetch_assoc();

            $stmt->close();

        } else {

            $error =
                "Unable to update your profile.";
        }

        $update->close();
    }
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
        Edit Profile |
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body class="admin-body">

<div class="admin-layout">

    <!-- SIDEBAR -->

    <?php
    require_once __DIR__ . "/includes/sidebar.php";
    ?>

       



    <!-- MAIN -->

    <main class="admin-main">

        <header class="admin-topbar">

            <div>

                <h1>
                    Edit Profile
                </h1>

                <p>
                    Update your personal information and privacy settings.
                </p>

            </div>

        </header>


        <section class="dashboard-content">


            <?php if ($error !== ""): ?>

                <div class="error-message">
                    <?= e($error) ?>
                </div>

            <?php endif; ?>


            <?php if ($success !== ""): ?>

                <div class="success-message">
                    <?= e($success) ?>
                </div>

            <?php endif; ?>


            <form
                method="POST"
                enctype="multipart/form-data"
                class="profile-edit-form"
            >
<?= csrf_field() ?>

                <!-- PROFILE PHOTO -->

                <div class="dashboard-panel">

                    <div class="panel-header">

                        <div>

                            <h2>
                                Profile Photo
                            </h2>

                            <p>
                                Upload a professional profile photo.
                            </p>

                        </div>

                    </div>


                    <div class="edit-photo-section">

                        <?php if (!empty($alumni["profile_photo"])): ?>

                            <img
                                src="../uploads/<?= e($alumni["profile_photo"]) ?>"
                                alt="Profile photo"
                                class="edit-profile-photo"
                            >

                        <?php else: ?>

                            <div class="edit-profile-initial">

                                <?= strtoupper(
                                    substr(
                                        $alumni["first_name"],
                                        0,
                                        1
                                    )
                                ) ?>

                            </div>

                        <?php endif; ?>


                        <div>

                            <input
                                type="file"
                                name="profile_photo"
                                accept="image/jpeg,image/png,image/webp"
                            >

                            <small>
                                JPG, PNG or WEBP. Maximum 5 MB.
                            </small>

                        </div>

                    </div>

                </div>


                <!-- BASIC INFORMATION -->

                <div class="dashboard-panel">

                    <div class="panel-header">

                        <div>

                            <h2>
                                Basic Information
                            </h2>

                            <p>
                                Some information is managed by the college.
                            </p>

                        </div>

                    </div>


                    <div class="profile-info-grid">


                        <div class="profile-info-item">

                            <span>
                                First Name
                            </span>

                            <strong>
                                <?= e($alumni["first_name"]) ?>
                            </strong>

                        </div>


                        <div class="profile-info-item">

                            <span>
                                Last Name
                            </span>
                            <strong>
                                <?= e($alumni["last_name"]) ?>
                            </strong>

                        </div>


                        <div class="profile-info-item">

                            <span>
                                Email
                            </span>

                            <strong>
                                <?= e($alumni["email"]) ?>
                            </strong>

                        </div>


                        <div class="profile-info-item">

                            <span>
                                Alumni ID
                            </span>

                            <strong>
                                <?= e($alumni["college_id_number"]) ?>
                            </strong>

                        </div>


                        <div class="profile-info-item">

                            <span>
                                Department
                            </span>

                            <strong>
                                <?= e(
                                    $alumni["department_name"]
                                    ?? "Not assigned"
                                ) ?>
                            </strong>

                        </div>


                        <div class="profile-info-item">

                            <span>
                                Graduation Year
                            </span>

                            <strong>
                                <?= e(
                                    $alumni["graduation_year"]
                                ) ?>
                            </strong>

                        </div>

                    </div>

                </div>


                <!-- CONTACT INFORMATION -->

                <div class="dashboard-panel">

                    <div class="panel-header">

                        <div>

                            <h2>
                                Contact Information
                            </h2>

                        </div>

                    </div>


                    <div class="form-grid">


                        <div class="form-group">

                            <label for="phone">
                                Phone
                            </label>

                            <input
                                type="text"
                                id="phone"
                                name="phone"
                                value="<?= e(
                                    $alumni["phone"] ?? ""
                                ) ?>"
                            >

                        </div>


                        <div class="form-group">

                            <label for="address">
                                Address
                            </label>

                            <input
                                type="text"
                                id="address"
                                name="address"
                                value="<?= e(
                                    $alumni["address"] ?? ""
                                ) ?>"
                            >

                        </div>

                    </div>

                </div>


                <!-- BIO -->

                <div class="dashboard-panel">

                    <div class="panel-header">

                        <div>

                            <h2>
                                About Me
                            </h2>

                        </div>

                    </div>


                    <div class="form-group">

                        <label for="bio">
                            Biography
                        </label>
                        <textarea
                            id="bio"
                            name="bio"
                            rows="6"
                            placeholder="Tell us about yourself..."
                        ><?= e(
                            $alumni["bio"] ?? ""
                        ) ?></textarea>

                    </div>

                </div>


                <!-- PRIVACY -->

                <div class="dashboard-panel">

                    <div class="panel-header">

                        <div>

                            <h2>
                                Privacy Settings
                            </h2>

                            <p>
                                Choose which information can be displayed publicly.
                            </p>

                        </div>

                    </div>


                    <div class="privacy-options">


                        <label class="checkbox-option">

                            <input
                                type="checkbox"
                                name="show_profile"
                                <?= $alumni["show_profile"]
                                    ? "checked"
                                    : "" ?>
                            >

                            <span>
                                Show my profile publicly
                            </span>

                        </label>


                        <label class="checkbox-option">

                            <input
                                type="checkbox"
                                name="show_email"
                                <?= $alumni["show_email"]
                                    ? "checked"
                                    : "" ?>
                            >

                            <span>
                                Show my email
                            </span>

                        </label>


                        <label class="checkbox-option">

                            <input
                                type="checkbox"
                                name="show_phone"
                                <?= $alumni["show_phone"]
                                    ? "checked"
                                    : "" ?>
                            >

                            <span>
                                Show my phone number
                            </span>

                        </label>


                        <label class="checkbox-option">

                            <input
                                type="checkbox"
                                name="show_profession"
                                <?= $alumni["show_profession"]
                                    ? "checked"
                                    : "" ?>
                            >

                            <span>
                                Show my profession
                            </span>

                        </label>


                        <label class="checkbox-option">

                            <input
                                type="checkbox"
                                name="show_skills"
                                <?= $alumni["show_skills"]
                                    ? "checked"
                                    : "" ?>
                            >

                            <span>
                                Show my skills
                            </span>

                        </label>

                    </div>

                </div>


                <!-- BUTTONS -->

                <div class="profile-form-actions">

                    <a
                        href="profile.php"
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