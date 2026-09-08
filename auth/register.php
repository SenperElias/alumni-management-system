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
        $graduationYear = $_POST["graduation_year"] ?? "";
        $bio = trim($_POST["bio"] ?? "");


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
                                    graduation_year,
                                    bio,
                                    status,
                                    created_at
                                )
                                VALUES
                                (
                                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
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
                                    "sssssssssiis",
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
                                    $graduationYear,
                                    $bio
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


        <form method="POST">


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


</body>

</html>