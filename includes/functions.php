<?php

/* =========================================================
   GENERAL FUNCTIONS
========================================================= */

function redirect($url)
{
    header("Location: " . $url);
    exit;
}


/* =========================================================
   AUTHENTICATION HELPERS
========================================================= */

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isAdmin()
{
    return isset($_SESSION['role']) &&
           $_SESSION['role'] === 'admin';
}

function isAlumni()
{
    return isset($_SESSION['role']) &&
           $_SESSION['role'] === 'alumni';
}


/* =========================================================
   OUTPUT ESCAPING
========================================================= */

function e($value)
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}


/* =========================================================
   CSRF PROTECTION
========================================================= */

/**
 * Generate and return the CSRF token.
 */
function csrf_token()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(
            random_bytes(32)
        );
    }

    return $_SESSION['csrf_token'];
}


/**
 * Verify the submitted CSRF token.
 */
function verify_csrf_token()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $submitted_token = $_POST['csrf_token'] ?? '';

    if (
        empty($submitted_token) ||
        empty($_SESSION['csrf_token']) ||
        !hash_equals(
            $_SESSION['csrf_token'],
            $submitted_token
        )
    ) {
        die("Invalid security token. Please go back and try again.");
    }

    return true;
}
function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' .
           e($_SESSION['csrf_token'] ?? '') .
           '">';
}