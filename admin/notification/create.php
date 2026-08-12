<?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";

/*
|--------------------------------------------------------------------------
| Admin Access
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "admin") {
    header("Location: ../../index.php");
    exit;
}

$error = "";
$success = "";

/*
|--------------------------------------------------------------------------
| Get Users
|--------------------------------------------------------------------------
*/

$users = $conn->query("
    SELECT
        user_id,
        email
    FROM users
    WHERE account_status = 'active'
    ORDER BY email ASC
");

/*
|--------------------------------------------------------------------------
| Create Notification
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $userId = $_POST["user_id"] ?? "";
    $title = trim($_POST["title"] ?? "");
    $message = trim($_POST["message"] ?? "");
    $type = trim($_POST["type"] ?? "");

    if (
        $userId === "" ||
        $title === "" ||
        $message === "" ||
        $type === ""
    ) {

        $error = "Please fill in all required fields.";

    } else {

        $stmt = $conn->prepare("
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
                ?,
                ?,
                ?,
                ?,
                0,
                NOW()
            )
        ");

        $stmt->bind_param(
            "isss",
            $userId,
            $title,
            $message,
            $type
        );

        if ($stmt->execute()) {

            header("Location: index.php");
            exit;

        } else {

            $error = "Unable to create notification.";
        }

        $stmt->close();
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
        Create Notification | <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

</head>

<body class="admin-body">

<div class="admin-layout">

    <!-- =========================================================
         SIDEBAR
    ========================================================== -->

    <aside class="admin-sidebar">

        <div class="admin-brand">

            <div class="brand-logo">
                TM
            </div>

            <div>
                <strong>Alumni System</strong>
                <small>Admin Portal</small>
            </div>

        </div>

        <nav class="admin-nav">

            <a href="../dashboard.php">
                Dashboard
            </a>

            <div class="nav-section">
                MANAGEMENT
            </div>

            <a href="../alumni/index.php">
                Alumni
            </a>

            <a href="../employment/index.php">
                Employment
            </a>

            <a href="../opportunities/index.php">
                Opportunities
            </a>

            <a href="../events/index.php">
                Events
            </a>

            <a href="../mentors/index.php">
                Mentorship
            </a>

            <a href="../projects/index.php">
                Projects
            </a>

            <a href="../contributions/index.php">
                Contributions
            </a>

            <div class="nav-section">
                REPORTS
            </div>

            <a href="../reports/index.php">
                Employment Reports
            </a>
            <div class="nav-section">
                SYSTEM
            </div>

            <a href="../users/index.php">
                Users
            </a>

            <a
                href="index.php"
                class="active"
            >
                Notifications
            </a>

            <a href="#">
                Settings
            </a>

            <a
                href="../../auth/logout.php"
                class="logout-link"
            >
                Logout
            </a>

        </nav>

    </aside>


    <!-- =========================================================
         MAIN CONTENT
    ========================================================== -->

    <main class="admin-main">

        <header class="admin-topbar">

            <div>

                <h1>
                    Create Notification
                </h1>

                <p>
                    Send a notification to an active user.
                </p>

            </div>

        </header>


        <section class="dashboard-content">

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Notification Details
                        </h2>

                        <p>
                            Enter the information for the new notification.
                        </p>

                    </div>

                </div>


                <?php if ($error !== ""): ?>

                    <div class="error-message">

                        <?= e($error) ?>

                    </div>

                <?php endif; ?>


                <form
                    method="POST"
                    action=""
                >

                    <div class="form-group">

                        <label for="user_id">
                            Recipient
                        </label>

                        <select
                            id="user_id"
                            name="user_id"
                            required
                        >

                            <option value="">
                                Select User
                            </option>

                            <?php if ($users): ?>

                                <?php while (
                                    $user = $users->fetch_assoc()
                                ): ?>

                                    <option
                                        value="<?= (int) $user["user_id"] ?>"
                                    >

                                        <?= e($user["email"]) ?>

                                    </option>

                                <?php endwhile; ?>

                            <?php endif; ?>

                        </select>

                    </div>


                    <div class="form-group">

                        <label for="title">
                            Title
                        </label>

                        <input
                            type="text"
                            id="title"
                            name="title"
                            maxlength="255"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="type">
                            Notification Type
                        </label>

                        <select
                            id="type"
                            name="type"
                            required
                        >

                            <option value="">
                                Select Type
                            </option>

                            <option value="General">
                                General
                            </option>

                            <option value="Event">
                                Event
                            </option>
                            <option value="Opportunity">
                                Opportunity
                            </option>

                            <option value="Mentorship">
                                Mentorship
                            </option>

                            <option value="System">
                                System
                            </option>

                        </select>

                    </div>


                    <div class="form-group">

                        <label for="message">
                            Message
                        </label>

                        <textarea
                            id="message"
                            name="message"
                            rows="6"
                            required
                        ></textarea>

                    </div>


                    <div class="quick-actions">

                        <button
                            type="submit"
                            class="secondary-button"
                        >
                            Create Notification
                        </button>

                        <a
                            href="index.php"
                            class="secondary-button"
                        >
                            Cancel
                        </a>

                    </div>

                </form>

            </div>

        </section>

    </main>

</div>

</body>

</html>