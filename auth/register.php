 <?php

session_start();

require_once "../config/database.php";
require_once "../config/config.php";
require_once "../includes/functions.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /*
    |--------------------------------------------------------------------------
    | CSRF PROTECTION
    |--------------------------------------------------------------------------
    */

    if (!verify_csrf_token()) {
        $error = "Invalid security token. Please try again.";
    } else {

        /*
        |--------------------------------------------------------------------------
        | GET FORM DATA
        |--------------------------------------------------------------------------
        */

        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";

        // Optional college/alumni ID
        $alumniIdNumber = trim(
            $_POST["college_id_number"] ?? ""
        );

        $firstName = trim($_POST["first_name"] ?? "");
        $lastName = trim($_POST["last_name"] ?? "");
        $gender = trim($_POST["gender"] ?? "");
        $dateOfBirth = trim($_POST["date_of_birth"] ?? "");
        $phone = trim($_POST["phone"] ?? "");
        $address = trim($_POST["address"] ?? "");
        $departmentId = $_POST["department_id"] ?? "";
$sectionId = $_POST["section_id"] ?? "";
$specializationId = $_POST["specialization_id"] ?? "";
$level = $_POST["level"] ?? "";
$graduationYear = $_POST["graduation_year"] ?? "";
$bio = trim($_POST["bio"] ?? "");
        $bio = trim($_POST["bio"] ?? "");
        $verification_document = $_FILES["verification_document"] ?? null;
        $documentFileName = null;


        /*
        |--------------------------------------------------------------------------
        | BASIC VALIDATION
        |--------------------------------------------------------------------------
        */

        if (
            $email === "" ||
            $password === "" ||
            $firstName === "" ||
            $lastName === "" ||
            $gender === "" ||
            $departmentId === "" ||
            $sectionId === "" ||
            $graduationYear === ""
        ) {

            $error = "Please fill in all required fields.";

        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $error = "Please enter a valid email address.";

        } elseif (strlen($password) < 8) {

            $error = "Password must contain at least 8 characters.";

        
} else {
/*
        |--------------------------------------------------------------------------
        | VALIDATE ACADEMIC STRUCTURE
        |--------------------------------------------------------------------------
        */

        $sectionId = (int) $sectionId;
        $level = (int) $level;

        $specializationId =
            $specializationId !== ""
                ? (int) $specializationId
                : null;

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
    if ($verification_document && $verification_document["error"] !== UPLOAD_ERR_NO_FILE) {

        if ($verification_document["error"] !== UPLOAD_ERR_OK) {
            $error = "There was a problem uploading the verification document.";
        } elseif ($verification_document["size"] > 5 * 1024 * 1024) {
            $error = "The verification document must be 5 MB or smaller.";
        }
        else {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($verification_document["tmp_name"]);

    $allowedTypes = [
        "application/pdf",
        "image/jpeg",
        "image/png"
    ];

    if (!in_array($mimeType, $allowedTypes, true)) {
        $error = "Only PDF, JPG, and PNG files are allowed.";
    }
    else {
    $documentExtension = match ($mimeType) {
        "application/pdf" => "pdf",
        "image/jpeg"      => "jpg",
        "image/png"       => "png",
        default           => null
    };

    if ($documentExtension === null) {
        $error = "Invalid verification document type.";
    } else {
        $documentFileName = bin2hex(random_bytes(16)) . "." . $documentExtension;
        $documentUploadDir = dirname(__DIR__) . "/uploads/verification_documents/";
        
$documentUploadPath = $documentUploadDir . $documentFileName;
    }
}
        }
    }


    
    
            /*
            |--------------------------------------------------------------------------
            | CHECK EMAIL
            |--------------------------------------------------------------------------
            */

            $check = $conn->prepare("
                SELECT user_id
                FROM users
                WHERE email = ?
                LIMIT 1
            ");

            if (!$check) {

                error_log(
                    "Database prepare error in auth/register.php " .
                    "(email check): " .
                    $conn->error
                );

                $error =
                    "Unable to process registration. " .
                    "Please try again later.";

            } else {

                $check->bind_param(
                    "s",
                    $email
                );

                if (!$check->execute()) {

                    error_log(
                        "Database execute error in auth/register.php " .
                        "(email check): " .
                        $check->error
                    );

                    $error =
                        "Unable to process registration. " .
                        "Please try again later.";

                } else {

                    $existingEmail =
                        $check->get_result();


                    if ($existingEmail->num_rows > 0) {

                        $error =
                            "An account with this email already exists.";

                    } else {
 /*
                        |--------------------------------------------------------------------------
                        | CHECK ALUMNI ID ONLY IF PROVIDED
                        |--------------------------------------------------------------------------
                        */

                        $idAvailable = true;

                        if ($alumniIdNumber !== "") {

                            $checkId = $conn->prepare("
                                SELECT alumni_id
                                FROM alumni
                                WHERE college_id_number = ?
                                LIMIT 1
                            ");

                            if (!$checkId) {

                                error_log(
                                    "Database prepare error in auth/register.php " .
                                    "(alumni ID check): " .
                                    $conn->error
                                );

                                $error =
                                    "Unable to process registration. " .
                                    "Please try again later.";

                                $idAvailable = false;

                            } else {

                                $checkId->bind_param(
                                    "s",
                                    $alumniIdNumber
                                );

                                if (!$checkId->execute()) {

                                    error_log(
                                        "Database execute error in auth/register.php " .
                                        "(alumni ID check): " .
                                        $checkId->error
                                    );

                                    $error =
                                        "Unable to process registration. " .
                                        "Please try again later.";

                                    $idAvailable = false;

                                } else {

                                    $existingId =
                                        $checkId->get_result();

                                    if (
                                        $existingId->num_rows > 0
                                    ) {

                                        $error =
                                            "This Alumni ID is already registered.";

                                        $idAvailable = false;
                                    }
                                }

                                $checkId->close();
                            }
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | CHECK WHETHER EMAIL HAS A PENDING REGISTRATION
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $error === "" &&
                            $idAvailable
                        ) {

                            $checkPending = $conn->prepare("
                                SELECT registration_id
                                FROM alumni_registrations
                                WHERE email = ?
                                AND status = 'pending'
                                LIMIT 1
                            ");

                            if (!$checkPending) {

                                error_log(
                                    "Database prepare error in auth/register.php " .
                                    "(pending check): " .
                                    $conn->error
                                );

                                $error =
                                    "Unable to process registration. " .
                                    "Please try again later.";
} else {

                                $checkPending->bind_param(
                                    "s",
                                    $email
                                );

                                if (!$checkPending->execute()) {

                                    error_log(
                                        "Database execute error in auth/register.php " .
                                        "(pending check): " .
                                        $checkPending->error
                                    );

                                    $error =
                                        "Unable to process registration. " .
                                        "Please try again later.";

                                } else {

                                    $pendingResult =
                                        $checkPending->get_result();

                                    if (
                                        $pendingResult->num_rows > 0
                                    ) {

                                        $error =
                                            "A registration with this email " .
                                            "is already pending.";

                                    }
                                }

                                $checkPending->close();
                            }
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | INSERT REGISTRATION
                        |--------------------------------------------------------------------------
                        */

                        if ($error === "") {

                            $passwordHash = password_hash(
                                $password,
                                PASSWORD_DEFAULT
                            );

                            /*
                            | Store NULL when the optional Alumni ID
                            | is not provided.
                            */

                            $alumniIdValue =
                                $alumniIdNumber !== ""
                                    ? $alumniIdNumber
                                    : null;

if ($error === "" && $verification_document && $verification_document["error"] !== UPLOAD_ERR_NO_FILE) {

    if (!move_uploaded_file(
        $verification_document["tmp_name"],
        $documentUploadPath
    )) {
        $error = "Unable to save the verification document.";
    }
}
                            $stmt = $conn->prepare("
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
                                    verification_document,
                                    status,
                                    created_at
                                )
                                VALUES
                                (
                                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                                    'pending',
                                    NOW()
                                )
                            ");


                            if (!$stmt) {

                                error_log(
                                    "Database prepare error in auth/register.php " .
                                    "(registration insert): " .
                                    $conn->error
                                );

                                $error =
                                    "Unable to submit registration. " .
                                    "Please try again later.";

                            } else {
 $stmt->bind_param(
                                    "sssssssssiiiisss",
                                    $email,
                                    $passwordHash,
                                    $alumniIdValue,
                                    $firstName,
                                    $lastName,
                                    $gender,
                                    $dateOfBirth,
                                    $phone,
                                    $address,
                                    $departmentId,
                                    $sectionId,
                                    $specializationId,
                                    $level,
                                    $graduationYear,
                                    $bio,
                                    $documentFileName
                                );


                                if ($stmt->execute()) {

                                    $success =
                                        "Registration submitted successfully. " .
                                        "Please wait for the Registrar to " .
                                        "verify your information.";

                                } else {

                                    error_log(
                                        "Database execute error in auth/register.php " .
                                        "(registration insert): " .
                                        $stmt->error
                                    );

                                    $error =
                                        "Unable to submit registration. " .
                                        "Please try again later.";
                                }


                                $stmt->close();
                            }
                        }
                    }
                }

                $check->close();
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| GET DEPARTMENTS
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

if (!$result) {

    error_log(
        "Database error in auth/register.php " .
        "(departments): " .
        $conn->error
    );

    $departments = [];

} else {

    while ($row = $result->fetch_assoc()) {

        $departments[] = $row;

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
        Alumni Registration
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body>


<div class="login-container">


    <div class="login-card">


        <h1>
            Alumni Registration
        </h1>


        <p>
            Register your information for Registrar verification.
        </p>


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


        <?php if ($success === ""): ?>


        <form method="POST"
        enctype="multipart/form-data">


            <?= csrf_field() ?>


            <!-- =================================================
                 FIRST NAME
            ================================================== -->

            <div class="form-group">

                <label for="first_name">
                    First Name *
                </label>

                <input
                    type="text"
                    id="first_name"
                    name="first_name"
                    required
                    maxlength="100"
                    value="<?= e($_POST["first_name"] ?? "") ?>"
                >

            </div>
 <!-- =================================================
                 LAST NAME
            ================================================== -->

            <div class="form-group">

                <label for="last_name">
                    Last Name *
                </label>

                <input
                    type="text"
                    id="last_name"
                    name="last_name"
                    required
                    maxlength="100"
                    value="<?= e($_POST["last_name"] ?? "") ?>"
                >

            </div>


            <!-- =================================================
                 OPTIONAL ALUMNI ID
            ================================================== -->

            <div class="form-group">

                <label for="college_id_number">
                    College ID Number
                </label>

                <input
                    type="text"
                    id="college_id_number"
                    name="college_id_number"
                    maxlength="100"
                    value="<?= e($_POST["college_id_number"] ?? "") ?>"
                >

                <small>
                    Optional. Leave blank if you do not remember your
                    college ID.
                </small>

            </div>


            <!-- =================================================
                 EMAIL
            ================================================== -->

            <div class="form-group">

                <label for="email">
                    Email Address *
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                    maxlength="255"
                    value="<?= e($_POST["email"] ?? "") ?>"
                >

            </div>


            <!-- =================================================
                 PASSWORD
            ================================================== -->

            <div class="form-group">

                <label for="password">
                    Password *
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


            <!-- =================================================
                 GENDER
            ================================================== -->

            <div class="form-group">

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
                        <?= (
                            ($_POST["gender"] ?? "") === "Male"
                        ) ? "selected" : "" ?>
                    >
                        Male
                    </option>

                    <option
                        value="Female"
                        <?= (
                            ($_POST["gender"] ?? "") === "Female"
                        ) ? "selected" : "" ?>
                    >
                        Female
                    </option>

                </select>

            </div>


            <!-- =================================================
                 DATE OF BIRTH
            ================================================== -->

            <div class="form-group">

                <label for="date_of_birth">
                    Date of Birth
                </label>
 <input
                    type="date"
                    id="date_of_birth"
                    name="date_of_birth"
                    value="<?= e($_POST["date_of_birth"] ?? "") ?>"
                >

            </div>


            <!-- =================================================
                 PHONE
            ================================================== -->

            <div class="form-group">

                <label for="phone">
                    Phone
                </label>

                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    maxlength="50"
                    value="<?= e($_POST["phone"] ?? "") ?>"
                >

            </div>


            <!-- =================================================
                 ADDRESS
            ================================================== -->

            <div class="form-group">

                <label for="address">
                    Address
                </label>

                <input
                    type="text"
                    id="address"
                    name="address"
                    maxlength="255"
                    value="<?= e($_POST["address"] ?? "") ?>"
                >

            </div>


            <!-- =================================================
                 DEPARTMENT
            ================================================== -->

            <div class="form-group">

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
                            value="<?= (int) $department["department_id"] ?>"
                            <?= (
                                (string) (
                                    $_POST["department_id"] ?? ""
                                ) ===
                                (string) $department["department_id"]
                            )
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
<!-- =================================================
     SECTION / PROGRAM
================================================== -->

<div class="form-group">

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
<!-- =================================================
     SPECIALIZATION
================================================== -->

<div class="form-group">

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

<!-- =================================================
     LEVEL
================================================== -->

<div class="form-group">

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
            <!-- =================================================
                 GRADUATION YEAR
            ================================================== -->

            <div class="form-group">

                <label for="graduation_year">
                    Graduation Year *
                </label>

                <input
                    type="number"
                    id="graduation_year"
                    name="graduation_year"
                    min="1900"
                    max="2100"
                    required
                    value="<?= e($_POST["graduation_year"] ?? "") ?>"
                >

            </div>


            <!-- =================================================
                 BIOGRAPHY
            ================================================== -->

            <div class="form-group">

                <label for="bio">
                    Biography
                </label>

                <textarea
                    id="bio"
                    name="bio"
                    rows="4"
                    maxlength="2000"
                    placeholder="Write a short biography..."
                ><?= e($_POST["bio"] ?? "") ?></textarea>

            </div>
            <div class="form-group">
    <label for="verification_document">
        Supporting Identity/Alumni Verification Document
    </label>

    <input
        type="file"
        id="verification_document"
        name="verification_document"
        accept=".pdf,.jpg,.jpeg,.png"
    >

    <small>
        Upload a college ID card, graduation certificate, academic transcript,
        or another official college-issued document. PDF, JPG, or PNG only.
    </small>
</div>
 <!-- =================================================
                 SUBMIT
            ================================================== -->

            <button
                type="submit"
                class="login-button"
            >
                Submit Registration
            </button>


        </form>


        <?php endif; ?>


        <p class="login-back">

            <a href="login.php">

                ← Back to Login

            </a>

        </p>


    </div>


</div>

 <script>

// =================================================
// ACADEMIC DROPDOWNS
// Department → Section → Specialization → Level
// =================================================


// =================================================
// ELEMENTS
// =================================================

const departmentSelect =
    document.getElementById("department_id");

const sectionSelect =
    document.getElementById("section_id");

const specializationSelect =
    document.getElementById("specialization_id");

const levelSelect =
    document.getElementById("level");


// =================================================
// DEPARTMENT → SECTION
// =================================================

if (departmentSelect && sectionSelect) {

    departmentSelect.addEventListener("change", function () {

        const departmentId = this.value;


        // Reset Section
        sectionSelect.innerHTML =
            '<option value="">Loading sections...</option>';

        sectionSelect.disabled = true;


        // Reset Specialization
        specializationSelect.innerHTML =
            '<option value="">Select Section / Program First</option>';

        specializationSelect.disabled = true;


        // Reset Level
        levelSelect.innerHTML =
            '<option value="">Select Section / Program First</option>';

        levelSelect.disabled = true;


        // No department selected
        if (!departmentId) {

            sectionSelect.innerHTML =
                '<option value="">Select Department First</option>';

            return;
        }


        // Load sections
        fetch(
            "get_sections.php?department_id=" +
            encodeURIComponent(departmentId)
        )

            .then(response => {

                if (!response.ok) {
                    throw new Error("Failed to load sections.");
                }

                return response.json();
            })

            .then(data => {

                sectionSelect.innerHTML =
                    '<option value="">Select Section / Program</option>';


                if (
                    !data.success ||
                    !Array.isArray(data.sections)||
                    data.sections.length === 0
                ) {

                    sectionSelect.innerHTML =
                        '<option value="">Unable to load sections</option>';

                    return;
                }


                if (
                    data.sections.length === 0
                ) {

                    sectionSelect.innerHTML =
                        '<option value="">No sections available</option>';

                    sectionSelect.disabled = true;

                    return;
                }


                data.sections.forEach(section => {

                    const option =
                        document.createElement("option");

                    option.value =
                        section.section_id;

                    option.textContent =
                        section.section_name;

                    sectionSelect.appendChild(option);

                });


                sectionSelect.disabled = false;

            })

            .catch(error => {

                console.error(error);

                sectionSelect.innerHTML =
                    '<option value="">Unable to load sections</option>';

                sectionSelect.disabled = true;
            });

    });

}


// =================================================
// LOAD LEVELS
// =================================================

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
        "get_levels.php?section_id=" +
        encodeURIComponent(sectionId);


    if (specializationId) { url +=
            "&specialization_id=" +
            encodeURIComponent(specializationId);
    }


    fetch(url)

        .then(response => {

            if (!response.ok) {
                throw new Error("Failed to load levels.");
            }

            return response.json();
        })

        .then(data => {

            levelSelect.innerHTML =
                '<option value="">Select Level</option>';


            if (
                !data.success ||
                !Array.isArray(data.levels) ||
                data.levels.length === 0
            ) {

                levelSelect.innerHTML =
                    '<option value="">No levels available</option>';

                levelSelect.disabled = true;

                return;
            }


            data.levels.forEach(level => {

                const option =
                    document.createElement("option");

                option.value =
                    level;

                option.textContent =
                    "Level " + level;

                levelSelect.appendChild(option);

            });


            levelSelect.disabled = false;

        })

        .catch(error => {

            console.error(error);

            levelSelect.innerHTML =
                '<option value="">Unable to load levels</option>';

            levelSelect.disabled = true;
        });

}


// =================================================
// SECTION → SPECIALIZATION
// =================================================

if (sectionSelect && specializationSelect) {

    sectionSelect.addEventListener("change", function () {

        const sectionId = this.value;


        // Reset specialization
        specializationSelect.innerHTML =
            '<option value="">Loading specializations...</option>';

        specializationSelect.disabled = true;


        // Reset level
        levelSelect.innerHTML =
            '<option value="">Select Section / Program First</option>';

        levelSelect.disabled = true;


        if (!sectionId) {

            specializationSelect.innerHTML =
                '<option value="">Select Section / Program First</option>';

            return;
        }


        fetch(
            "get_specializations.php?section_id=" +
            encodeURIComponent(sectionId)
        )

            .then(response => {

                if (!response.ok) {
                    throw new Error(
                        "Failed to load specializations."
                    );
                }

                return response.json();
            })

            .then(data => {

                specializationSelect.innerHTML =
                    '<option value="">Select Specialization</option>';


                // No specialization
                if (
                    !data.success ||
                    !Array.isArray(data.specializations) ||
                    data.specializations.length === 0
                ) {

                    specializationSelect.innerHTML =
                        '<option value="">No specialization required</option>';

                    specializationSelect.disabled = true;


                    // Load levels directly
                    loadLevels();

                    return;
                }


                // Add specializations
                data.specializations.forEach(
                    specialization => {

                        const option =
                            document.createElement("option");

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

            })

            .catch(error => {

                console.error(error);
 specializationSelect.innerHTML =
                    '<option value="">Unable to load specializations</option>';

                specializationSelect.disabled = true;
            });

    });

}


// =================================================
// SPECIALIZATION → LEVEL
// =================================================

if (specializationSelect && levelSelect) {

    specializationSelect.addEventListener(
        "change",
        function () {

            loadLevels();

        }
    );

}

</script>
</body>

</html>