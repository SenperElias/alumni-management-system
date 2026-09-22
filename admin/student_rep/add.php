 <?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";

/*
|--------------------------------------------------------------------------
| Authorization
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "student_rep") {
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

$firstName = "";
$lastName = "";
$gender = "";
$dateOfBirth = "";
$phone = "";
$address = "";
$alumniIdNumber = "";
$departmentId = "";
$sectionId = "";
$specializationId = "";
$level = "";

$graduationYear = "";
$bio = "";

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

    /*
    |--------------------------------------------------------------------------
    | CSRF Protection
    |--------------------------------------------------------------------------
    */

    if (!verify_csrf_token($_POST["csrf_token"] ?? "")) {

        $error =
            "Invalid security token. Please refresh the page and try again.";
    }

    /*
    |--------------------------------------------------------------------------
    | Get Form Values
    |--------------------------------------------------------------------------
    */

    $email = trim($_POST["email"] ?? "");

    $password = $_POST["password"] ?? "";

    $alumniIdNumber = trim(
        $_POST["college_id_number"] ?? ""
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

    $sectionId = $_POST["section_id"] ?? "";

    $specializationId =
        $_POST["specialization_id"] ?? "";

    $level = $_POST["level"] ?? "";

    $graduationYear =
        $_POST["graduation_year"] ?? "";

    $bio = trim(
        $_POST["bio"] ?? ""
    );

    /*
    |--------------------------------------------------------------------------
    | Basic Validation
    |--------------------------------------------------------------------------
    */

    if ($error === "") {

        if (
            $email === "" ||
            $password === "" ||
            
            $firstName === "" ||
            $lastName === "" ||
            $gender === "" ||
            $departmentId === "" ||
            $sectionId === "" ||
            $level === "" ||
            $graduationYear === ""
        ) {

            $error =
                "Please fill in all required fields.";

        } elseif (
            !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {

            $error =
                "Please enter a valid email address.";

        } elseif (
            strlen($password) < 8
        ) {
 $error =
                "Password must contain at least 8 characters.";

        } elseif (
            !in_array(
                $gender,
                ["Male", "Female"],
                true
            )
        ) {

            $error =
                "Please select a valid gender.";

        } elseif (
            !filter_var(
                $departmentId,
                FILTER_VALIDATE_INT
            )
        ) {

            $error =
                "Please select a valid department.";

        } elseif (
            !filter_var(
                $sectionId,
                FILTER_VALIDATE_INT
            )
        ) {

            $error =
                "Please select a valid section / program.";

        } elseif (
            !filter_var(
                $level,
                FILTER_VALIDATE_INT
            )
        ) {

            $error =
                "Please select a valid level.";

        } elseif (
            !filter_var(
                $graduationYear,
                FILTER_VALIDATE_INT
            ) ||
            (int)$graduationYear < 1900 ||
            (int)$graduationYear > 2100
        ) {

            $error =
                "Please enter a valid graduation year.";
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Specialization
    |--------------------------------------------------------------------------
    */

    if ($error === "") {

        if ($specializationId !== "") {

            if (
                !filter_var(
                    $specializationId,
                    FILTER_VALIDATE_INT
                )
            ) {

                $error =
                    "Please select a valid specialization.";
            }

        } else {

            $specializationId = null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Academic Selection
    |--------------------------------------------------------------------------
    */

    if ($error === "") {

        $sectionId = (int)$sectionId;
        $level = (int)$level;

        if ($specializationId !== null) {

            $specializationId =
                (int)$specializationId;

        }

        $academicValid = false;

        if ($specializationId !== null) {

            $academicCheck = $conn->prepare("
                SELECT level_id
                FROM academic_levels
                WHERE section_id = ?
                  AND specialization_id = ?
                  AND level = ?
                LIMIT 1
            ");

            if ($academicCheck) {

                $academicCheck->bind_param(
                    "iii",
                    $sectionId,
                    $specializationId,
                    $level
                );

                if ($academicCheck->execute()) {

                    $academicResult =
                        $academicCheck->get_result();

                    $academicValid =
                        $academicResult->num_rows > 0;
                }

                $academicCheck->close();
            }

        } else {

            $academicCheck = $conn->prepare("
                SELECT level_id
                FROM academic_levels
                WHERE section_id = ?
                  AND specialization_id IS NULL
                  AND level = ?
                LIMIT 1
            ");

            if ($academicCheck) {

                $academicCheck->bind_param(
                    "ii",
                    $sectionId,
                    $level
                );

                if ($academicCheck->execute()) {

                    $academicResult =
                        $academicCheck->get_result();

                    $academicValid =
                        $academicResult->num_rows > 0;
                }

                $academicCheck->close();
            }
        }

        if (!$academicValid) {
 $error =
                "Invalid department, section, specialization, or level selection.";
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Check Existing Email
    |--------------------------------------------------------------------------
    */

    if ($error === "") {

        $check = $conn->prepare("
            SELECT user_id
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        if (!$check) {

            $error =
                "Unable to validate the registration.";

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
    }

    /*
    |--------------------------------------------------------------------------
    | Check Existing Alumni ID
    |--------------------------------------------------------------------------
    */

    if ($error === "" && $alumniIdNumber !== "") {

        $checkId = $conn->prepare("
            SELECT alumni_id
            FROM alumni
            WHERE college_id_number = ?
            LIMIT 1
        ");

        if (!$checkId) {

            $error =
                "Unable to validate the Alumni ID.";

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
    | Check Pending Registration Email
    |--------------------------------------------------------------------------
    */

    if ($error === "" && $alumniIdNumber !== "") {

        $pendingEmail = $conn->prepare("
            SELECT registration_id
            FROM alumni_registrations
            WHERE email = ?
              AND status = 'pending'
            LIMIT 1
        ");

        if (!$pendingEmail) {

            $error =
                "Unable to validate the registration.";

        } else {

            $pendingEmail->bind_param(
                "s",
                $email
            );

            $pendingEmail->execute();

            $pendingEmailResult =
                $pendingEmail->get_result();

            if ($pendingEmailResult->num_rows > 0) {

                $error =
                    "A pending registration already exists for this email.";
            }

            $pendingEmail->close();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Check Pending Registration Alumni ID
    |--------------------------------------------------------------------------
    */

    if ($error === "") {

        $pendingId = $conn->prepare("
            SELECT registration_id
            FROM alumni_registrations
            WHERE college_id_number = ?
              AND status = 'pending'
            LIMIT 1
        ");

        if (!$pendingId) {

            $error =
                "Unable to validate the Alumni ID.";

        } else {

            $pendingId->bind_param(
                "s",
                $alumniIdNumber
            );

            $pendingId->execute();

            $pendingIdResult =
                $pendingId->get_result();

            if ($pendingIdResult->num_rows > 0) {

                $error =
                    "A pending registration already exists for this Alumni ID.";
            }

            $pendingId->close();
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
        $_FILES["profile_photo"]["error"] !==
        UPLOAD_ERR_NO_FILE
    ) {

        if (
            $_FILES["profile_photo"]["error"] !==
            UPLOAD_ERR_OK
        ) {

            $error =
                "There was a problem uploading the profile photo.";

        } else {

            $file = $_FILES["profile_photo"];

            $maxSize =
                5 * 1024 * 1024;

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

                if (
                    !isset(
                        $allowedTypes[$mimeType]
                    )
                ) {

                    $error =
                        "Only JPG, PNG and WebP images are allowed.";

                } else {

                    $extension =
                        $allowedTypes[$mimeType];

                    $profilePhoto =
                        bin2hex(
                            random_bytes(16)
                        ) . "." . $extension;

                    $uploadDirectory =
                        "../../uploads/";

                    if (
                        !is_dir(
                            $uploadDirectory
                        )
                    ) {

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
    | Create Pending Registration
    |--------------------------------------------------------------------------
    */

    if ($error === "") {

        $passwordHash =
            password_hash(
                $password,
                PASSWORD_DEFAULT
            );

        if ($passwordHash === false) {

            $error =
                "Unable to secure the password.";

        } else {

            $dateOfBirthValue =
                $dateOfBirth !== ""
                    ? $dateOfBirth
                    : null;

            $conn->begin_transaction();

            try {

                $registrationStmt = $conn->prepare("
                    INSERT INTO alumni_registrations
                    (
                        email,
                        password_hash,
                        college_id_number,
                        first_name,
                        last_name,
                        gender,
                        date_of_birth,
                        phone,
                        address,
                        department_id,
 section_id,
                        specialization_id,
                        level,
                        graduation_year,
                        bio,
                        profile_photo,
                        status,
                        submitted_by
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
                        ?,
                        ?,
                        ?,
                        ?,
                        'pending',
                        ?
                    )
                ");

                if (!$registrationStmt) {

                    throw new Exception(
                        "Unable to prepare registration."
                    );
                }

                $submittedBy =
                    (int)$_SESSION["user_id"];

                $registrationStmt->bind_param(
                    "sssssssssiiiisssi",
                    $email,
                    $passwordHash,
                    $alumniIdNumber,
                    $firstName,
                    $lastName,
                    $gender,
                    $dateOfBirthValue,
                    $phone,
                    $address,
                    $departmentId,
                    $sectionId,
                    $specializationId,
                    $level,
                    $graduationYear,
                    $bio,
                    $profilePhoto,
                    $submittedBy
                );

                if (!$registrationStmt->execute()) {

                    throw new Exception(
                        "Unable to save registration."
                    );
                }

                $registrationStmt->close();

                $conn->commit();

                $success =
                    "Alumni registration submitted successfully. " .
                    "It is now waiting for Registrar verification.";

                /*
                |--------------------------------------------------------------------------
                | Clear Form
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
                $sectionId = "";
                $specializationId = "";
                $level = "";
                $graduationYear = "";
                $bio = "";
                $profilePhoto = null;
            }

            catch (Exception $e) {

                $conn->rollback();

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
                    "Unable to submit the alumni registration.";
            }
        }
    }
}

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
         STUDENT REPRESENTATIVE SIDEBAR
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
                    Register a new alumni for Registrar verification
                </p>

            </div>

            <div class="admin-user">

                <div class="admin-avatar">
                    S
                </div>

                <div>

                    <strong>
                        Student Representative
                    </strong>

                    <small>
                        Alumni Registration Officer
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

                <?= csrf_field() ?>


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
                                These details will be used by the alumni
                                to log in after approval.
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

                            <label for="college_id_number">
                                College ID Number 
                            </label>

                            <input
                                type="text"
                                id="college_id_number"
                                name="college_id_number"
                                value="<?= e($alumniIdNumber) ?>"
                                
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
                                College, program, specialization and
                                graduation information.
                            </p>

                        </div>

                    </div>


                    <div class="form-grid">


                        <!-- DEPARTMENT -->

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

                                <?php foreach (
                                    $departments
                                    as $department
                                ): ?>

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


                        <!-- SECTION -->

                        <div class="form-field">

                            <label for="section_id">
                                Section / Program *
                            </label>

                            <select
                                id="section_id"
                                name="section_id"
                                required
                                disabled
                            >

                                <option value="">
                                    Select Department First
                                </option>

                            </select>

                        </div>


                        <!-- SPECIALIZATION -->

                        <div class="form-field">
 <label for="specialization_id">
                                Specialization
                            </label>

                            <select
                                id="specialization_id"
                                name="specialization_id"
                                disabled
                            >

                                <option value="">
                                    Select Section / Program First
                                </option>

                            </select>

                        </div>


                        <!-- LEVEL -->

                        <div class="form-field">

                            <label for="level">
                                Level *
                            </label>

                            <select
                                id="level"
                                name="level"
                                required
                                disabled
                            >

                                <option value="">
                                    Select Section / Program First
                                </option>

                            </select>

                        </div>


                        <!-- GRADUATION YEAR -->

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
                        Submit for Approval
                    </button>

                </div>

            </form>

        </section>

    </main>

</div>


<!-- =========================================================
     ACADEMIC DROPDOWNS JAVASCRIPT
     Department → Section → Specialization → Level
========================================================= -->

<script>

const departmentSelect =
    document.getElementById("department_id");

const sectionSelect =
    document.getElementById("section_id");

const specializationSelect =
    document.getElementById("specialization_id");

const levelSelect =
    document.getElementById("level");


/*
|--------------------------------------------------------------------------
| Department → Section
|--------------------------------------------------------------------------
*/

departmentSelect.addEventListener(
    "change",
    function () {

        const departmentId =
            this.value;


        sectionSelect.innerHTML =
            '<option value="">Loading sections...</option>';

        sectionSelect.disabled = true;


        specializationSelect.innerHTML =
            '<option value="">Select Section / Program First</option>';

        specializationSelect.disabled = true;


        levelSelect.innerHTML =
            '<option value="">Select Section / Program First</option>';

        levelSelect.disabled = true;


        if (!departmentId) {

            sectionSelect.innerHTML =
                '<option value="">Select Department First</option>';

            return;
        }


        fetch(
            "../../auth/get_sections.php?department_id=" +
            encodeURIComponent(departmentId)
        )

        .then(function (response) {

            if (!response.ok) {

                throw new Error(
                    "Failed to load sections."
                );
            }

            return response.json();

        })

        .then(function (data) {

            sectionSelect.innerHTML =
                '<option value="">Select Section / Program</option>';


            if (
                data.success &&
                Array.isArray(data.sections) &&
                data.sections.length > 0
            ) {

                data.sections.forEach(
                    function (section) {

                        const option =
                            document.createElement(
                                "option"
                            );

                        option.value =
                            section.section_id;

                        option.textContent =
                            section.section_name;

                        sectionSelect.appendChild(
                            option
                        );

                    }
                );

                sectionSelect.disabled = false;

            } else {

                sectionSelect.innerHTML =
                    '<option value="">No sections available</option>';

                sectionSelect.disabled = true;
            }

        })

        .catch(function (error) {

            console.error(
                "Section loading error:",
                error
            );

            sectionSelect.innerHTML =
                '<option value="">Unable to load sections</option>';

            sectionSelect.disabled = true;
        });

    }
);


/*
|--------------------------------------------------------------------------
| Section → Specialization
|--------------------------------------------------------------------------
*/

sectionSelect.addEventListener(
    "change",
    function () {

        const sectionId =
            this.value;
 specializationSelect.innerHTML =
            '<option value="">Loading specializations...</option>';

        specializationSelect.disabled = true;


        levelSelect.innerHTML =
            '<option value="">Select Section / Program First</option>';

        levelSelect.disabled = true;


        if (!sectionId) {

            specializationSelect.innerHTML =
                '<option value="">Select Section / Program First</option>';

            return;
        }


        fetch(
            "../../auth/get_specializations.php?section_id=" +
            encodeURIComponent(sectionId)
        )

        .then(function (response) {

            if (!response.ok) {

                throw new Error(
                    "Failed to load specializations."
                );
            }

            return response.json();

        })

        .then(function (data) {

            if (
                data.success &&
                Array.isArray(data.specializations) &&
                data.specializations.length > 0
            ) {

                specializationSelect.innerHTML =
                    '<option value="">Select Specialization</option>';


                data.specializations.forEach(
                    function (specialization) {

                        const option =
                            document.createElement(
                                "option"
                            );

                        option.value =
                            specialization.specialization_id;

                        option.textContent =
                            specialization.specialization_name;

                        specializationSelect.appendChild(
                            option
                        );

                    }
                );

                specializationSelect.disabled = false;

            } else {

                specializationSelect.innerHTML =
                    '<option value="">No specialization required</option>';

                specializationSelect.disabled = true;

                loadLevels();
            }

        })

        .catch(function (error) {

            console.error(
                "Specialization loading error:",
                error
            );

            specializationSelect.innerHTML =
                '<option value="">Unable to load specializations</option>';

            specializationSelect.disabled = true;
        });

    }
);


/*
|--------------------------------------------------------------------------
| Specialization → Level
|--------------------------------------------------------------------------
*/

specializationSelect.addEventListener(
    "change",
    function () {

        loadLevels();

    }
);


/*
|--------------------------------------------------------------------------
| Load Levels
|--------------------------------------------------------------------------
*/

function loadLevels() {

    const sectionId =
        sectionSelect.value;

    const specializationId =
        specializationSelect.value;


    levelSelect.innerHTML =
        '<option value="">Loading levels...</option>';

    levelSelect.disabled = true;


    if (!sectionId) {

        levelSelect.innerHTML =
            '<option value="">Select Section / Program First</option>';

        return;
    }


    let url =
        "../../auth/get_levels.php?section_id=" +
        encodeURIComponent(sectionId);


    if (specializationId) {

        url +=
            "&specialization_id=" +
            encodeURIComponent(specializationId);
    }


    fetch(url)

    .then(function (response) {

        if (!response.ok) {

            throw new Error(
                "Failed to load levels."
            );
        }

        return response.json();

    })

    .then(function (data) {

        levelSelect.innerHTML =
            '<option value="">Select Level</option>';


        if (
            data.success &&
            Array.isArray(data.levels) &&
            data.levels.length > 0
        ) {
 data.levels.forEach(
                function (level) {

                    const option =
                        document.createElement(
                            "option"
                        );

                    option.value =
                        level;

                    option.textContent =
                        "Level " + level;

                    levelSelect.appendChild(
                        option
                    );

                }
            );

            levelSelect.disabled = false;

        } else {

            levelSelect.innerHTML =
                '<option value="">No levels available</option>';

            levelSelect.disabled = true;
        }

    })

    .catch(function (error) {

        console.error(
            "Level loading error:",
            error
        );

        levelSelect.innerHTML =
            '<option value="">Unable to load levels</option>';

        levelSelect.disabled = true;
    });

}

</script>

</body>

</html>