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

/*
|--------------------------------------------------------------------------
| Get Alumni ID
|--------------------------------------------------------------------------
*/

$alumniId = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($alumniId <= 0) {
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Get Alumni Profile
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT
        a.*,
        u.email,
        d.department_name
     FROM alumni a
     LEFT JOIN users u
        ON a.user_id = u.user_id
     LEFT JOIN departments d
        ON a.department_id = d.department_id
     WHERE a.alumni_id = ?
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

$pageTitle =
    $alumni["first_name"] . " " . $alumni["last_name"];

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        <?= e($pageTitle) ?> |
        <?= e(SITE_NAME) ?>
    </title>

    <link rel="stylesheet"
          href="../../assets/css/style.css">

</head>

<body class="admin-body">

<div class="admin-layout">

    <!-- Sidebar -->

    <aside class="admin-sidebar">

        <div class="admin-brand">

            <div class="brand-logo">
                TM
            </div>

            <div>
                <strong>Alumni System</strong>
                <small>Admin Panel</small>
            </div>

        </div>

        <nav class="admin-nav">

            <a href="../dashboard.php">
                Dashboard
            </a>

            <div class="nav-section">
                MANAGEMENT
            </div>

            <a href="index.php" class="active">
                Alumni
            </a>

            <a href="#">
                Employment
            </a>

            <a href="#">
                Opportunities
            </a>

            <a href="#">
                Mentorship
            </a>

            <a href="#">
                Projects
            </a>

            <a href="#">
                Events
            </a>

            <a href="#">
                Contributions
            </a>

            <div class="nav-section">
                CONTENT
            </div>

            <a href="#">
                Success Stories
            </a>

            <a href="#">
                Announcements
            </a>

            <a href="#">
                Gallery
            </a>

            <a href="#">
                Contact Messages
            </a>

            <div class="nav-section">
                SYSTEM
            </div>

            <a href="#">
                Reports
            </a>

            <a href="#">
                Notifications
            </a>

            <a href="#">
                Settings
            </a>

            <a href="../../auth/logout.php"
               class="logout-link">
                Logout
            </a>

        </nav>

    </aside>


    <!-- Main Content -->

    <main class="admin-main">

        <header class="admin-topbar">

            <div>

                <h1>Alumni Profile</h1>

                <p>
                    View complete alumni information.
                </p>

            </div>

            <div class="admin-user">

                <div class="admin-avatar">
                    A
                </div>

                <div>

                    <strong>Administrator</strong>
                    <small>
                        System Admin
                    </small>

                </div>

            </div>

        </header>


        <section class="dashboard-content">

            <!-- Profile Header -->

            <div class="alumni-profile-header">

                <div class="profile-photo-large">

                    <?php if (!empty($alumni["profile_photo"])): ?>

                        <img
                            src="../../uploads/<?= e($alumni["profile_photo"]) ?>"
                            alt="Profile photo"
                        >

                    <?php else: ?>

                        <div class="profile-initial">

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

                    <span class="profile-label">
                        ALUMNI
                    </span>

                    <h2>
                        <?= e(
                            $alumni["first_name"]
                            . " "
                            . $alumni["last_name"]
                        ) ?>
                    </h2>

                    <p>
                        <?= e(
                            $alumni["department_name"]
                            ?? "Department not assigned"
                        ) ?>
                    </p>

                    <span class="profile-id">
                        ID:
                        <?= e(
                            $alumni["college_id_number"]
                        ) ?>
                    </span>

                </div>


                <div class="profile-header-actions">

                    <a
                        href="edit.php?id=<?= e($alumni["alumni_id"]) ?>"
                        class="primary-button"
                    >
                        Edit Profile
                    </a>

                    <a
                        href="index.php"
                        class="secondary-button"
                    >
                        Back
                    </a>

                </div>

            </div>


            <!-- Personal Information -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>Personal Information</h2>

                        <p>
                            Basic information about this alumni.
                        </p>

                    </div>

                </div>


                <div class="profile-info-grid">

                    <div class="profile-info-item">

                        <span>
                            First Name
                        </span>

                        <strong>
                            <?= e(
                                $alumni["first_name"]
                            ) ?>
                        </strong>

                    </div>


                    <div class="profile-info-item">

                        <span>
                            Last Name
                        </span>

                        <strong>
                            <?= e(
                                $alumni["last_name"]
                            ) ?>
                        </strong>

                    </div>


                    <div class="profile-info-item">

                        <span>
                            Gender
                        </span>

                        <strong>
                            <?= e(
                                $alumni["gender"]
                                ?: "Not provided"
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
                                ?: "Not provided"
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
                                ?: "Not provided"
                            ) ?>
                        </strong>

                    </div>


                    <div class="profile-info-item">

                        <span>
                            Email
                        </span>

                        <strong>
                            <?= e(
                                $alumni["email"]
                                ?: "Not provided"
                            ) ?>
                        </strong>

                    </div>


                    <div class="profile-info-item profile-info-full">

                        <span>
                            Address
                        </span>

                        <strong>
                            <?= e(
                                $alumni["address"]
                                ?: "Not provided"
                            ) ?>
                        </strong>

                    </div>

                </div>

            </div>


            <!-- Education -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>Education</h2>

                        <p>
                            College and graduation information.
                        </p>

                    </div>

                </div>


                <div class="profile-info-grid">

                    <div class="profile-info-item">

                        <span>
                            Alumni ID
                        </span>

                        <strong>
                            <?= e(
                                $alumni["college_id_number"]
                            ) ?>
                        </strong>

                    </div>


                    <div class="profile-info-item">

                        <span>
                            Department
                        </span>

                        <strong>
                            <?= e(
                                $alumni["department_name"]
                                ?? "Not assigned"
                            ) ?>
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

                </div>

            </div>


            <!-- Biography -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>Biography</h2>

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

                        <p class="muted-text">
                            No biography has been provided.
                        </p>
                        <?php endif; ?>

                </div>

            </div>


            <!-- Visibility Settings -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>Profile Visibility</h2>

                        <p>
                            Information visibility settings.
                        </p>

                    </div>

                </div>


                <div class="visibility-grid">

                    <div class="visibility-item">

                        <span>Profile</span>

                        <?php if ($alumni["show_profile"]): ?>

                            <span class="visibility-on">
                                Visible
                            </span>

                        <?php else: ?>

                            <span class="visibility-off">
                                Hidden
                            </span>

                        <?php endif; ?>

                    </div>


                    <div class="visibility-item">

                        <span>Profession</span>

                        <?php if ($alumni["show_profession"]): ?>

                            <span class="visibility-on">
                                Visible
                            </span>

                        <?php else: ?>

                            <span class="visibility-off">
                                Hidden
                            </span>

                        <?php endif; ?>

                    </div>


                    <div class="visibility-item">

                        <span>Skills</span>

                        <?php if ($alumni["show_skills"]): ?>

                            <span class="visibility-on">
                                Visible
                            </span>

                        <?php else: ?>

                            <span class="visibility-off">
                                Hidden
                            </span>

                        <?php endif; ?>

                    </div>


                    <div class="visibility-item">

                        <span>Email</span>

                        <?php if ($alumni["show_email"]): ?>

                            <span class="visibility-on">
                                Visible
                            </span>

                        <?php else: ?>

                            <span class="visibility-off">
                                Hidden
                            </span>

                        <?php endif; ?>

                    </div>


                    <div class="visibility-item">

                        <span>Phone</span>

                        <?php if ($alumni["show_phone"]): ?>

                            <span class="visibility-on">
                                Visible
                            </span>

                        <?php else: ?>

                            <span class="visibility-off">
                                Hidden
                            </span>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </section>

    </main>

</div>

</body>

</html>