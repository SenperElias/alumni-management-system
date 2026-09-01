<?php
 session_start();

require_once "../config/database.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $alumniIdNumber = trim($_POST["alumni_id_number"] ?? "");
    $firstName = trim($_POST["first_name"] ?? "");
    $lastName = trim($_POST["last_name"] ?? "");
    $gender = trim($_POST["gender"] ?? "");
    $dateOfBirth = trim($_POST["date_of_birth"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $departmentId = $_POST["department_id"] ?? "";
    $graduationYear = $_POST["graduation_year"] ?? "";
    $bio = trim($_POST["bio"] ?? "");

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
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $error = "Password must contain at least 8 characters.";
    } else {

        $check = $conn->prepare(
            "SELECT user_id
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        $check->bind_param("s", $email);
        $check->execute();

        $existingEmail = $check->get_result();

        if ($existingEmail->num_rows > 0) {

            $error = "An account with this email already exists.";

        } else {

            $checkId = $conn->prepare(
                "SELECT alumni_id
                 FROM alumni
                 WHERE alumni_id_number = ?
                 LIMIT 1"
            );

            $checkId->bind_param("s", $alumniIdNumber);
            $checkId->execute();

            $existingId = $checkId->get_result();

            if ($existingId->num_rows > 0) {

                $error = "This Alumni ID is already registered.";

            } else {

                $checkPending = $conn->prepare(
                    "SELECT registration_id
                     FROM alumni_registrations
                     WHERE email = ?
                     AND status = 'pending'
                     LIMIT 1"
                );

                $checkPending->bind_param("s", $email);
                $checkPending->execute();

                $pendingResult = $checkPending->get_result();

                if ($pendingResult->num_rows > 0) {

                    $error = "A registration with this email is already pending.";

                } else {

                    $passwordHash = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    $stmt = $conn->prepare(
                        "INSERT INTO alumni_registrations
                        (
                            email,
                            password_hash,
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
                            status,
                            created_at
                        )
                        VALUES
                        (
                            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW()
                        )"
                    );

                    $stmt->bind_param(
                        "sssssssssiis",
                        $email,
                        $passwordHash,
                        $alumniIdNumber,
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
                            "Please wait for the Registrar to verify your information.";

                    } else {

                        $error =
                            "Unable to submit registration: " .
                            $stmt->error;
                    }

                    $stmt->close();
                }

                $checkPending->close();
            }

            $checkId->close();
        }

        $check->close();
    }
}
$departments = [];

$result = $conn->query(
    "SELECT department_id, department_name
     FROM departments
     ORDER BY department_name ASC"
);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }
}
 ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Registration</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="login-container">

    <div class="login-card">

        <h1>Alumni Registration</h1>

        <p>
            Register your information for Registrar verification.
        </p>

        <?php if ($error !== ""): ?>
            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success !== ""): ?>
            <div class="success-message">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($success === ""): ?>

        <form method="POST">

            <div class="form-group">
                <label>First Name *</label>
                <input
                    type="text"
                    name="first_name"
                    required
                >
            </div>

            <div class="form-group">
                <label>Last Name *</label>
                <input
                    type="text"
                    name="last_name"
                    required
                >
            </div>

            <div class="form-group">
                <label>Alumni ID Number *</label>
                <input
                    type="text"
                    name="alumni_id_number"
                    required
                >
            </div>

            <div class="form-group">
                <label>Email Address *</label>
                <input
                    type="email"
                    name="email"
                    required
                >
            </div>

            <div class="form-group">
                <label>Password *</label>
                <input
                    type="password"
                    name="password"
                    minlength="8"
                    required
                >
                <small>Minimum 8 characters.</small>
            </div>

            <div class="form-group">
                <label>Gender *</label>
                <select name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>

            <div class="form-group">
                <label>Date of Birth</label>
                <input
                    type="date"
                    name="date_of_birth"
                >
            </div>

            <div class="form-group">
                <label>Phone</label>
                <input
                    type="tel"
                    name="phone"
                >
            </div>

            <div class="form-group">
                <label>Address</label>
                <input
                    type="text"
                    name="address"
                >
            </div>

            <div class="form-group">
                <label>Department *</label>

                <select name="department_id" required>

                    <option value="">
                        Select Department
                    </option>

                    <?php foreach ($departments as $department): ?>

                        <option value="<?= htmlspecialchars($department["department_id"]) ?>">
                            <?= htmlspecialchars($department["department_name"]) ?>
                        </option>

                    <?php endforeach; ?>

                </select>
            </div>

            <div class="form-group">
                <label>Graduation Year *</label>

                <input
                    type="number"
                    name="graduation_year"
                    min="1900"
                    max="2100"
                    required
                >
            </div>
 <div class="form-group">
                <label>Biography</label>

                <textarea
                    name="bio"
                    rows="4"
                    placeholder="Write a short biography..."
                ></textarea>
            </div>

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