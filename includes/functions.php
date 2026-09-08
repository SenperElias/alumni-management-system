<?php

/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

function redirect($url)
{
    header("Location: " . $url);
    exit;
}


/*
|--------------------------------------------------------------------------
| Authentication Helpers
|--------------------------------------------------------------------------
*/

function isLoggedIn()
{
    return isset($_SESSION["user_id"]);
}

function isAlumniPresident()
{
    return isset($_SESSION["role"])
        && $_SESSION["role"] === "admin";
}

function isSystemAdministrator(){
    return isset($_SESSION["role"])
        && $_SESSION["role"] === "system_admin";
}

function isAlumni()
{
    return isset($_SESSION["role"])
        && $_SESSION["role"] === "alumni";
}


/*
|--------------------------------------------------------------------------
| Temporary Password Helpers
|--------------------------------------------------------------------------
*/

function mustChangePassword()
{
    return 
 isset($_SESSION["must_change_password"])
        && (int) $_SESSION["must_change_password"] === 1;
}

function requirePasswordChange()
{
    if (mustChangePassword()) {
        header("Location: ../auth/change_password.php?required=1");
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| Security / Output Escaping
|--------------------------------------------------------------------------
*/

function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        "UTF-8"
    );
}


/*
|--------------------------------------------------------------------------
| CSRF Protection
|--------------------------------------------------------------------------
*/

function csrf_token()
{
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(
            random_bytes(32)
        );
    }

    return $_SESSION["csrf_token"];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="'
        . e(csrf_token())
        . '">';
}

function verify_csrf_token()
{
    if (
        !isset($_POST["csrf_token"]) ||
        !isset($_SESSION["csrf_token"]) ||
        !hash_equals(
            $_SESSION["csrf_token"],
            $_POST["csrf_token"]
        )
    ) {
        http_response_code(403);
        die("Invalid security token.");
    }

    return true;
}


/*
|--------------------------------------------------------------------------
| Audit Logging
|--------------------------------------------------------------------------
*/

function logAudit(
    mysqli $conn,
    int $userId,
    string $action,
    string $tableName,
    int $recordId,
    string $description
) {
    $stmt = $conn->prepare("
        INSERT INTO audit_logs
        (
            user_id,
            action,
            table_name,
            record_id,
            description
        )
        VALUES (?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        die(
            "AUDIT PREPARE ERROR: "
            . $conn->error
        );
    }

    $stmt->bind_param(
        "issis",
        $userId,
        $action,
        $tableName,
        $recordId,
        $description
    );

    if (!$stmt->execute()) {
        die(
            "AUDIT EXECUTE ERROR: "
            . $stmt->error
        );
    }

   

    $stmt->close();

    return true;
}