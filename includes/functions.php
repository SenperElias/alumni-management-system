<?php

function redirect($url)
{
    header("Location: " . $url);
    exit;
}

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

function e($value)
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}