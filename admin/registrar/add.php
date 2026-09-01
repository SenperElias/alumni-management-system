 <?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "registrar") {
    header("Location: ../../index.php");
    exit;
}

$error = "";
$success = "";

/*
|--------------------------------------------------------------------------
| Form Values
|--------------------------------------------------------------------------
*/

$email = "";
$alumniIdNumber = "";
$firstName = "";
$lastName = "";
$gender = "";
$dateOfBirth = "";
$phone = "";
$address = "";
$departmentId = "";
$graduationYear = "";
$bio = "";

/*
|--------------------------------------------------------------------------
| Profile Photo
|--------------------------------------------------------------------------
*/

$profilePhoto = null;

/*
|--------------------------------------------------------------------------
| Get Departments
|--------------------------------------------------------------------------
*/

$departments = [];

$result = $conn->query("
    SELECT
        department_id,
        department_name
    FROM departments
    WHERE status = 'active'
    ORDER BY department_name ASC
");

if ($result) {

    while ($row = $result->fetch_assoc()) {
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

    $password = $_POST["password"] ?? "";

    $alumniIdNumber = trim(
        $_POST["alumni_id_number"] ?? ""
    );

    $firstName = trim(
        $_POST["first_name"] ?? ""
    );

    $lastName = trim(
        $_POST["last_name"] ?? ""
    );

    $gender = trim(
        $_POST["gender"] ?? ""
    );

    $dateOfBirth = trim(
        $_POST["date_of_birth"] ?? ""
    );

    $phone = trim(
        $_POST["phone"] ?? ""
    );

    $address = trim(
        $_POST["address"] ?? ""
    );

    $departmentId = $_POST["department_id"] ?? "";

    $graduationYear = $_POST["graduation_year"] ?? "";

    $bio = trim(
        $_POST["bio"] ?? ""
    );


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (
        $email === "" ||
        $password === "" ||
        $alumniIdNumber === "" ||
        $firstName === "" ||
        $lastName === "" ||
        $gender === "" ||
        $departmentId === "" ||
        $graduationYear === ""
    ) {

        $error = "Please fill in all required fields.";

    } elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $error = "Please enter a valid email address.";

    } elseif (
        strlen($password) < 8
    ) {

        $error =
            "Password must contain at least 8 characters.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Check Existing Email
        |--------------------------------------------------------------------------
        */

        $check = $conn->prepare("
            SELECT user_id
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        if (!$check) {

            $error =
                "Database error: " .
                $conn->error;

        } else {

            $check->bind_param(
                "s",
                $email
            );

            $check->execute();

            $existingEmail =
                $check->get_result();

            if ($existingEmail->num_rows > 0) {

                $error =
                    "An account with this email already exists.";
            }

            $check->close();
        }
 /*
        |--------------------------------------------------------------------------
        | Check Alumni ID
        |--------------------------------------------------------------------------
        */

        if ($error === "") {

            $checkId = $conn->prepare("
                SELECT alumni_id
                FROM alumni
                WHERE alumni_id_number = ?
                LIMIT 1
            ");

            if (!$checkId) {

                $error =
                    "Database error: " .
                    $conn->error;

            } else {

                $checkId->bind_param(
                    "s",
                    $alumniIdNumber
                );

                $checkId->execute();

                $existingId =
                    $checkId->get_result();

                if ($existingId->num_rows > 0) {

                    $error =
                        "This Alumni ID already exists.";
                }

                $checkId->close();
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Profile Photo Upload
        |--------------------------------------------------------------------------
        */

        if (
            $error === "" &&
            isset($_FILES["profile_photo"]) &&
            $_FILES["profile_photo"]["error"] !== UPLOAD_ERR_NO_FILE
        ) {

            if (
                $_FILES["profile_photo"]["error"] !==
                UPLOAD_ERR_OK
            ) {

                $error =
                    "There was a problem uploading the profile photo.";

            } else {

                $file = $_FILES["profile_photo"];

                $maxSize = 5 * 1024 * 1024;

                if ($file["size"] > $maxSize) {

                    $error =
                        "Profile photo must be smaller than 5 MB.";

                } else {

                    $allowedTypes = [

                        "image/jpeg" => "jpg",
                        "image/png"  => "png",
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

                        $profilePhoto =
                            bin2hex(
                                random_bytes(16)
                            ) .
                            "." .
                            $extension;

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
                            $uploadDirectory .
                            $profilePhoto;

                        if (
                            !move_uploaded_file(
                                $file["tmp_name"],
                                $destination
                            )
                        ) {

                            $profilePhoto = null;

                            $error =
                                "Unable to save the profile photo.";
                        }
                    }
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Create User + Alumni
        |--------------------------------------------------------------------------
        */

        if ($error === "") {

            $conn->begin_transaction();
 try {

                /*
                | Create User Account
                */

                $passwordHash =
                    password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                $userStmt = $conn->prepare("
                    INSERT INTO users
                    (
                        email,
                        password_hash,
                        role,
                        account_status
                    )
                    VALUES
                    (
                        ?,
                        ?,
                        'alumni',
                        'active'
                    )
                ");

                if (!$userStmt) {
                    throw new Exception(
                        $conn->error
                    );
                }

                $userStmt->bind_param(
                    "ss",
                    $email,
                    $passwordHash
                );

                $userStmt->execute();

                $userId =
                    $conn->insert_id;

                $userStmt->close();


                /*
                | Create Alumni Profile
                */

                $alumniStmt = $conn->prepare("
                    INSERT INTO alumni
                    (
                        user_id,
                        alumni_id_number,
                        first_name,
                        last_name,
                        gender,
                        date_of_birth,
                        phone,
                        address,
                        department_id,
                        graduation_year,
                        bio,
                        profile_photo,
                        show_profile,
                        show_profession,
                        show_skills,
                        show_email,
                        show_phone
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
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        1,
                        1,
                        1,
                        1,
                        1
                    )
                ");

                if (!$alumniStmt) {
                    throw new Exception(
                        $conn->error
                    );
                }

                $alumniStmt->bind_param(
                    "isssssssiiss",
                    $userId,
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
                    $profilePhoto
                );

                $alumniStmt->execute();

                $alumniStmt->close();


                /*
                | Commit
                */

                $conn->commit();

                $success =
                    "Alumni account created successfully.";


                /*
                | Clear Form
                */

                $email = "";
                $alumniIdNumber = "";
                $firstName = "";
                $lastName = "";
                $gender = "";
                $dateOfBirth = "";
                $phone = "";
                $address = "";
                $departmentId = "";
                $graduationYear = "";
                $bio = "";

            } catch (Exception $e) {

                $conn->rollback();

                /*
                | Remove Photo If Database Failed
                */
  if (
                    $profilePhoto !== null &&
                    file_exists(
                        "../../uploads/" .
                        $profilePhoto
                    )
                ) {

                    unlink(
                        "../../uploads/" .
                        $profilePhoto
                    );
                }

                $error =
                    "Unable to create alumni account. " .
                    $e->getMessage();
            }
        }
    }
}

?>

<?php
$activePage = "add";
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
        Add Alumni |
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

</head>


<body class="admin-body">


<div class="admin-layout">


    <!-- =====================================================
         REUSABLE REGISTRAR SIDEBAR
    ====================================================== -->

    <?php include "includes/sidebar.php"; ?>


    <!-- =====================================================
         MAIN
    ====================================================== -->

    <main class="admin-main">


        <!-- TOPBAR -->

        <header class="admin-topbar">

            <div>

                <h1>
                    Add Alumni
                </h1>

                <p>
                    Create a new alumni account and profile.
                </p>

            </div>


            <div class="admin-user">

                <div class="admin-avatar">
                    R
                </div>

                <div>

                    <strong>
                        Registrar
                    </strong>

                    <small>
                        Registration Officer
                    </small>

                </div>

            </div>

        </header>


        <!-- CONTENT -->

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


                <!-- =================================================
                     LOGIN ACCOUNT
                ================================================== -->

                <div class="dashboard-panel">

                    <div class="panel-header">

                        <div>

                            <h2>
                                Login Account
                            </h2>

                            <p>
                                These details will be used by the
                                alumni to log in.
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


                        <div class="form-field">

                            <label for="password">
                                Temporary Password *
                            </label>
 <input
                                type="password"
                                id="password"
                                name="password"
                                minlength="8"
                                required
                            >

                            <small>
                                Minimum 8 characters.
                            </small>

                        </div>


                    </div>

                </div>


                <!-- =================================================
                     PERSONAL INFORMATION
                ================================================== -->

                <div class="dashboard-panel">

                    <div class="panel-header">

                        <div>

                            <h2>
                                Personal Information
                            </h2>

                            <p>
                                Basic information about the alumni.
                            </p>

                        </div>

                    </div>


                    <div class="form-grid">


                        <div class="form-field">

                            <label for="alumni_id_number">
                                Alumni ID Number *
                            </label>

                            <input
                                type="text"
                                id="alumni_id_number"
                                name="alumni_id_number"
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


                <!-- =================================================
                     EDUCATION
                ================================================== -->

                <div class="dashboard-panel">

                    <div class="panel-header">

                        <div>

                            <h2>
                                Education Information
                            </h2>

                            <p>
                                College and graduation information.
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
                                        value="<?= e(
                                            $department["department_id"]
                                        ) ?>"
                                        <?= $departmentId ==
                                            $department["department_id"]
                                            ? "selected"
                                            : "" ?>
                                    >

                                        <?= e(
                                            $department["department_name"]
                                        ) ?>

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


                <!-- =================================================
                     PROFILE
                ================================================== -->

                <div class="dashboard-panel">

                    <div class="panel-header">

                        <div>

                            <h2>
                                Profile
                            </h2>

                            <p>
                                Add a profile photo and biography.
                            </p>

                        </div>

                    </div>


                    <div class="form-grid">


                        <div class="form-field">

                            <label for="profile_photo">
                                Profile Photo
                            </label>

                            <input
                                type="file"
                                id="profile_photo"
                                name="profile_photo"
                                accept=".jpg,.jpeg,.png,.webp"
                            >

                            <small>
                                JPG, PNG or WebP.
                                Maximum 5 MB.
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


                <!-- =================================================
                     ACTIONS
                ================================================== -->

                <div class="form-actions">

                    <a
                        href="dashboard.php"
                        class="secondary-button"
                    >
                        Cancel
                    </a>


                    <button
                        type="submit"
                        class="primary-button"
                    >
                        Create Alumni
                    </button>

                </div>


            </form>


        </section>


    </main>


</div>


</body>

</html>