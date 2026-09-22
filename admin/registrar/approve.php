<?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";

/*
|--------------------------------------------------------------------------
| Registrar Access
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "registrar") {
    header("Location: ../../index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Only POST Requests Are Allowed
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: pending.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| CSRF Protection
|--------------------------------------------------------------------------
*/

if (!verify_csrf_token()) {
    die("Invalid security token.");
}

/*
|--------------------------------------------------------------------------
| Get Registration ID
|--------------------------------------------------------------------------
*/

$registration_id = (int) ($_POST["registration_id"] ?? 0);

if ($registration_id <= 0) {
    die("Invalid registration.");
}

/*
|--------------------------------------------------------------------------
| Get Registration
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT *
    FROM alumni_registrations
    WHERE registration_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log(
        "Database prepare error in registrar/approve.php: " .
        $conn->error
    );

    die(
        "Unable to process the registration. " .
        "Please try again later."
    );
}

$stmt->bind_param("i", $registration_id);
$stmt->execute();

$result = $stmt->get_result();
$registration = $result->fetch_assoc();

$stmt->close();

if (!$registration) {
    die("Registration not found.");
}

/*
|--------------------------------------------------------------------------
| Make Sure Registration Is Still Pending
|--------------------------------------------------------------------------
*/

if ($registration["status"] !== "pending") {
    die("This registration has already been processed.");
}

/*
|--------------------------------------------------------------------------
| Approve Registration
|--------------------------------------------------------------------------
*/

$conn->begin_transaction();

try {

    /*
    |--------------------------------------------------------------------------
    | Create User Account
    |--------------------------------------------------------------------------
    */

    $userStmt = $conn->prepare("
        INSERT INTO users
        (
            email,
            password_hash,
            role,
            account_status
        )
        VALUES
        (?, ?, 'alumni', 'active')
    ");

    if (!$userStmt) {
        throw new Exception(
            "Unable to prepare user account."
        );
    }

    $passwordHash = $registration["password_hash"];

    $userStmt->bind_param(
        "ss",
        $registration["email"],
        $passwordHash
    );

    if (!$userStmt->execute()) {
        throw new Exception(
            "Unable to create user account."
        );
    }

    $user_id = $conn->insert_id;

    $userStmt->close();

    /*
    |--------------------------------------------------------------------------
    | Create Alumni Profile
    |--------------------------------------------------------------------------
    */

    $alumniStmt = $conn->prepare("
        INSERT INTO alumni
        (
            user_id,
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
            show_profile,
            show_profession,
            show_skills,
            show_email,
            show_phone
        )
        VALUES
        (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
            1, 1, 1, 1, 1
        )
    ");

    if (!$alumniStmt) {
        throw new Exception(
            "Unable to prepare alumni profile."
        );
    }

    $alumniStmt->bind_param(
        "isssssssiiiiiss",
        $user_id,
        $registration["college_id_number"],
        $registration["first_name"],
        $registration["last_name"],
        $registration["gender"],
        $registration["date_of_birth"],
        $registration["phone"],
        $registration["address"],
        $registration["department_id"],
        $registration["section_id"],
        $registration["specialization_id"],
        $registration["level"],
        $registration["graduation_year"],
        $registration["bio"],
        $registration["profile_photo"]
    );

    if (!$alumniStmt->execute()) {
        throw new Exception(
            "Unable to create alumni profile."
        );
    }

    $alumniStmt->close();

    /*
    |--------------------------------------------------------------------------
    | Update Registration
    |--------------------------------------------------------------------------
    */

    $updateStmt = $conn->prepare("
        UPDATE alumni_registrations
        SET
            status = 'approved',
            verified_by = ?,
            verified_at = NOW()
        WHERE registration_id = ?
          AND status = 'pending'
    ");

    if (!$updateStmt) {
        throw new Exception(
            "Unable to prepare registration update."
        );
    }

    $registrarId = (int) $_SESSION["user_id"];

    $updateStmt->bind_param(
        "ii",
        $registrarId,
        $registration_id
    );

    if (!$updateStmt->execute()) {
        throw new Exception(
            "Unable to update registration."
        );
    }

    if ($updateStmt->affected_rows !== 1) {
        throw new Exception(
            "Registration could not be approved."
        );
    }

    $updateStmt->close();

    /*
    |--------------------------------------------------------------------------
    | Audit Log
    |--------------------------------------------------------------------------
    */

    logAudit(
        $conn,
        $registrarId,
        "APPROVE",
        "alumni_registrations",
        $registration_id,
        "Registrar approved alumni registration."
    );

    /*
    |--------------------------------------------------------------------------
    | Create Notification
    |--------------------------------------------------------------------------
    */

    $notificationStmt = $conn->prepare("
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
            ?, ?, ?, ?, 0, NOW()
        )
    ");

    if (!$notificationStmt) {
        throw new Exception(
            "Unable to prepare notification."
        );
    }

    $notificationTitle = "Registration Approved";

    $notificationMessage =
        "Your alumni registration has been approved. " .
        "You can now log in to your alumni account.";

    $notificationType = "system";

    $notificationStmt->bind_param(
        "isss",
        $user_id,
        $notificationTitle,
        $notificationMessage,
        $notificationType
    );

    if (!$notificationStmt->execute()) {
        throw new Exception(
            "Unable to create notification."
        );
    }

    $notificationStmt->close();

    /*
    |--------------------------------------------------------------------------
    | Commit Everything
    |--------------------------------------------------------------------------
    */

    $conn->commit();
 header(
        "Location: pending.php?success=" .
        urlencode(
            "Registration approved successfully."
        )
    );

    exit;

} catch (Exception $e) {

    $conn->rollback();

    error_log(
        "Approval error in registrar/approve.php: " .
        $e->getMessage()
    );

    die(
        "Unable to approve registration. " .
        "Please try again later."
    );
}