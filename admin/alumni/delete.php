<?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "admin") {
    header("Location: ../../index.php");
    exit;
}

$alumniId = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($alumniId <= 0) {
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Get Alumni
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT alumni_id, user_id, profile_photo
     FROM alumni
     WHERE alumni_id = ?
     LIMIT 1"
);

$stmt->bind_param("i", $alumniId);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit;
}

$alumni = $result->fetch_assoc();

$userId = (int) $alumni["user_id"];
$profilePhoto = $alumni["profile_photo"];

/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $conn->begin_transaction();

    try {

        /*
        | Delete alumni profile
        */

        $deleteAlumni = $conn->prepare(
            "DELETE FROM alumni
             WHERE alumni_id = ?"
        );

        $deleteAlumni->bind_param(
            "i",
            $alumniId
        );

        $deleteAlumni->execute();

        /*
        | Delete associated user account
        */

        $deleteUser = $conn->prepare(
            "DELETE FROM users
             WHERE user_id = ?"
        );

        $deleteUser->bind_param(
            "i",
            $userId
        );

        $deleteUser->execute();

        $conn->commit();

        /*
        | Delete profile photo after
        | successful database deletion
        */

        if (!empty($profilePhoto)) {

            $photoPath =
                "../../uploads/" . $profilePhoto;

            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
        }

        header(
            "Location: index.php?deleted=1"
        );

        exit;

    } catch (Exception $e) {

        $conn->rollback();

        die(
            "Unable to delete alumni: "
            . e($e->getMessage())
        );
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Delete Alumni |
        <?= e(SITE_NAME) ?>
    </title>

    <link rel="stylesheet"
          href="../../assets/css/style.css">

</head>

<body class="admin-body">

<div class="admin-layout">

    <?php require_once __DIR__ ."/../includes/sidebar.php"; ?>

    <main class="admin-main">

        <header class="admin-topbar">

            <div>

                <h1>Delete Alumni</h1>

                <p>
                    Confirm removal of this alumni account.
                </p>

            </div>

        </header>


        <section class="dashboard-content">

            <div class="delete-confirmation">

                <div class="delete-icon">
                    !
                </div>

                <h2>
                    Are you sure?
                </h2>

                <p>
                    You are about to permanently delete this
                    alumni profile and its associated login account.
                </p>

                <p class="delete-warning">
                    This action cannot be undone.
                </p>


                <form method="POST">

                    <a
                        href="index.php"
                        class="secondary-button"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="danger-button"
                    >
                        Yes, Delete Alumni
                    </button>

                </form>

            </div>

        </section>

    </main>

</div>

</body>

</html>