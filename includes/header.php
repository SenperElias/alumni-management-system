<?php
require_once DIR . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= isset($pageTitle)
            ? e($pageTitle) . ' | ' . SITE_NAME
            : SITE_NAME;
        ?>
    </title>

    <link rel="stylesheet"
          href="<?= BASE_URL ?>assets/css/style.css">
</head>

<body>