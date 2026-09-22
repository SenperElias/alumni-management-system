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

/*
|--------------------------------------------------------------------------
| Get Notification ID
|--------------------------------------------------------------------------
*/

$notificationId = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;

if ($notificationId <= 0) {
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Get Notification
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        notification_id,
        user_id,
        title,
        message,
        type,
        is_read
    FROM notifications
    WHERE notification_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $notificationId);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();
    header("Location: index.php");
    exit;
}

$notification = $result->fetch_assoc();

$stmt->close();

$error = "";

/*
|--------------------------------------------------------------------------
| Get Active Users
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
| Update Notification
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $userId = (int) ($_POST["user_id"] ?? 0);
    $title = trim($_POST["title"] ?? "");
    $message = trim($_POST["message"] ?? "");
    $type = trim($_POST["type"] ?? "");

    if (
        $userId <= 0 ||
        $title === "" ||
        $message === "" ||
        $type === ""
    ) {

        $error = "Please fill in all required fields.";

    } else {

        $update = $conn->prepare("
            UPDATE notifications
            SET
                user_id = ?,
                title = ?,
                message = ?,
                type = ?
            WHERE notification_id = ?
        ");

        $update->bind_param(
            "isssi",
            $userId,
            $title,
            $message,
            $type,
            $notificationId
        );

        if ($update->execute()) {

            $update->close();

            header("Location: index.php");
            exit;

        } else {

            $error = "Unable to update notification.";

            $update->close();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Keep Entered Values After Validation Error
    |--------------------------------------------------------------------------
    */

    $notification["user_id"] = $userId;
    $notification["title"] = $title;
    $notification["message"] = $message;
    $notification["type"] = $type;
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
        Edit Notification | <?= e(SITE_NAME) ?>
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

     <?php require_once __DIR__ ."/../includes/sidebar.php"; ?>


    <!-- =========================================================
         MAIN CONTENT
    ========================================================== -->

    <main class="admin-main">

        <header class="admin-topbar">

            <div>

                <h1>
                    Edit Notification
                </h1>

                <p>
                    Update notification information.
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
                            Modify the notification below.
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
                                        <?= (
                                            (int) $notification["user_id"] ===
                                            (int) $user["user_id"]
                                        ) ? "selected" : "" ?>
                                    ><?= e($user["email"]) ?>

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
                            value="<?= e($notification["title"]) ?>"
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

                            <option
                                value="General"
                                <?= $notification["type"] === "General"
                                    ? "selected"
                                    : "" ?>
                            >
                                General
                            </option>

                            <option
                                value="Event"
                                <?= $notification["type"] === "Event"
                                    ? "selected"
                                    : "" ?>
                            >
                                Event
                            </option>

                            <option
                                value="Opportunity"
                                <?= $notification["type"] === "Opportunity"
                                    ? "selected"
                                    : "" ?>
                            >
                                Opportunity
                            </option>

                            <option
                                value="Mentorship"
                                <?= $notification["type"] === "Mentorship"
                                    ? "selected"
                                    : "" ?>
                            >
                                Mentorship
                            </option>

                            <option
                                value="System"
                                <?= $notification["type"] === "System"
                                    ? "selected"
                                    : "" ?>
                            >
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
                        ><?= e($notification["message"]) ?></textarea>

                    </div>


                    <div class="quick-actions">

                        <button
                            type="submit"
                            class="secondary-button"
                        >
                            Save Changes
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