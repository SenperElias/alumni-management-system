<?php

session_start();

require_once "../config/database.php";
require_once "../config/config.php";
require_once "../includes/functions.php";

/*
|--------------------------------------------------------------------------
| Security
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "alumni") {
    header("Location: ../index.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| Get Logged-in Alumni
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT
        a.alumni_id,
        a.college_id_number,
        a.first_name,
        a.last_name,
        a.gender,
        a.date_of_birth,
        a.phone,
        a.address,
        a.department_id,
        a.graduation_year,
        a.bio,
        a.profile_photo,
        a.show_profile,
        a.show_profession,
        a.show_skills,
        a.show_email,
        a.show_phone,
        a.created_at,
        a.updated_at,
        u.email,
        d.department_name
     FROM alumni a
     LEFT JOIN users u
        ON a.user_id = u.user_id
     LEFT JOIN departments d
        ON a.department_id = d.department_id
     WHERE a.user_id = ?
     LIMIT 1"
);

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Alumni profile not found.");
}

$alumni = $result->fetch_assoc();

$stmt->close();

$fullName =
    $alumni["first_name"] . " " . $alumni["last_name"];

$department =
    $alumni["department_name"] ?? "Not assigned";

$profilePhoto =
    $alumni["profile_photo"] ?? "";

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
        My Profile |
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body class="admin-body">

<div class="admin-layout">

    <!-- SIDEBAR -->

      <?php
      $currentPage = "profile";
require_once __DIR__ . "/includes/sidebar.php";
?>


    <!-- MAIN -->

    <main class="admin-main">

        <header class="admin-topbar">

            <div>

                <h1>
                    My Profile
                </h1>
                <p>
                    View your alumni information.
                </p>

            </div>

        </header>


        <section class="dashboard-content">

            <!-- PROFILE HEADER -->

            <div class="profile-header-card">

                <div class="profile-photo-large">

                    <?php if (!empty($profilePhoto)): ?>

                        <img
                            src="../uploads/<?= e($profilePhoto) ?>"
                            alt="Profile photo"
                        >

                    <?php else: ?>

                        <div class="profile-initial-large">

                            <?= strtoupper(
                                substr(
                                    $alumni["first_name"],
                                    0,
                                    1
                                )
                            ) ?>

                        </div>

                    <?php endif; ?>

                </div>


                <div class="profile-header-info">

                    <h2>
                        <?= e($fullName) ?>
                    </h2>

                    <p>
                        <?= e($department) ?>
                    </p>

                    <span>
                        Alumni ID:
                        <?= e($alumni["college_id_number"]) ?>
                    </span>

                </div>


                <div class="profile-header-action">

                    <a
                        href="edit-profile.php"
                        class="primary-button"
                    >
                        Edit Profile
                    </a>

                </div>

            </div>


            <!-- PERSONAL INFORMATION -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Personal Information
                        </h2>

                        <p>
                            Your basic personal details.
                        </p>

                    </div>

                </div>


                <div class="profile-info-grid">

                    <div class="profile-info-item">

                        <span>
                            First Name
                        </span>

                        <strong>
                            <?= e($alumni["first_name"]) ?>
                        </strong>

                    </div>


                    <div class="profile-info-item">

                        <span>
                            Last Name
                        </span>

                        <strong>
                            <?= e($alumni["last_name"]) ?>
                        </strong>

                    </div>


                    <div class="profile-info-item">

                        <span>
                            Gender
                        </span>

                        <strong>
                            <?= e(
                                $alumni["gender"] ?? "Not provided"
                            ) ?>
                        </strong>

                    </div>


                    <div class="profile-info-item">

                        <span>
                            Date of Birth
                        </span>

                        <strong>
                            <?= e(
                                $alumni["date_of_birth"]
                                ?? "Not provided"
                            ) ?>
                        </strong>

                    </div>


                    <div class="profile-info-item">

                        <span>
                            Phone
                        </span>

                        <strong>
                            <?= e(
                                $alumni["phone"]
                                ?? "Not provided"
                            ) ?>
                        </strong>

                    </div>
                    <div class="profile-info-item">

                        <span>
                            Email
                        </span>

                        <strong>
                            <?= e($alumni["email"]) ?>
                        </strong>

                    </div>


                    <div class="profile-info-item">

                        <span>
                            Address
                        </span>

                        <strong>
                            <?= e(
                                $alumni["address"]
                                ?? "Not provided"
                            ) ?>
                        </strong>

                    </div>

                </div>

            </div>


            <!-- EDUCATION -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Education
                        </h2>

                        <p>
                            Your college information.
                        </p>

                    </div>

                </div>


                <div class="profile-info-grid">

                    <div class="profile-info-item">

                        <span>
                            Department
                        </span>

                        <strong>
                            <?= e($department) ?>
                        </strong>

                    </div>


                    <div class="profile-info-item">

                        <span>
                            Graduation Year
                        </span>

                        <strong>
                            <?= e(
                                $alumni["graduation_year"]
                            ) ?>
                        </strong>

                    </div>


                    <div class="profile-info-item">

                        <span>
                            College ID Number
                        </span>

                        <strong>
                            <?= e(
                                $alumni["college_id_number"]
                            ) ?>
                        </strong>

                    </div>

                </div>

            </div>


            <!-- BIO -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            About Me
                        </h2>

                    </div>

                </div>


                <div class="profile-bio">

                    <?php if (!empty($alumni["bio"])): ?>

                        <p>
                            <?= nl2br(
                                e($alumni["bio"])
                            ) ?>
                        </p>

                    <?php else: ?>

                        <p class="empty-text">
                            No biography has been added yet.
                        </p>

                    <?php endif; ?>

                </div>

            </div>


            <!-- ACCOUNT INFORMATION -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Profile Visibility
                        </h2>

                        <p>
                            Your current privacy settings.
                        </p>

                    </div>

                </div>


                <div class="visibility-list">

                    <div>

                        <span>
                            Show Profile
                        </span>

                        <strong>
                            <?= $alumni["show_profile"]
                                ? "Yes"
                                : "No" ?>
                        </strong>

                    </div>


                    <div>
                        <span>
                            Show Email
                        </span>

                        <strong>
                            <?= $alumni["show_email"]
                                ? "Yes"
                                : "No" ?>
                        </strong>

                    </div>


                    <div>

                        <span>
                            Show Phone
                        </span>

                        <strong>
                            <?= $alumni["show_phone"]
                                ? "Yes"
                                : "No" ?>
                        </strong>

                    </div>


                    <div>

                        <span>
                            Show Profession
                        </span>

                        <strong>
                            <?= $alumni["show_profession"]
                                ? "Yes"
                                : "No" ?>
                        </strong>

                    </div>


                    <div>

                        <span>
                            Show Skills
                        </span>

                        <strong>
                            <?= $alumni["show_skills"]
                                ? "Yes"
                                : "No" ?>
                        </strong>

                    </div>

                </div>

            </div>

        </section>

    </main>

</div>

</body>

</html>