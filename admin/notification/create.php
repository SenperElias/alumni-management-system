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
    WHERE account_status = 'active' AND role = 'alumni'
    ORDER BY email ASC
");
$events = $conn->query("
    SELECT
        event_id,
        title
    FROM events
    WHERE status = 'published'
    ORDER BY event_date ASC
");
$opportunities = $conn->query("
    SELECT
        opportunity_id,
        title
    FROM opportunities
    WHERE status = 'published'
    ORDER BY created_at DESC
");

/*
|--------------------------------------------------------------------------
| Create Notification
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {
verify_csrf_token($_POST["csrf_token"] ?? "");
    $userId = $_POST["user_id"] ?? "";
    $title = trim($_POST["title"] ?? "");
    $message = trim($_POST["message"] ?? "");
    $type = trim($_POST["type"] ?? "");
$eventId = $_POST["event_id"] ?? "";
$eventId = $eventId !== "" ? (int) $eventId : null;

$opportunityId = $_POST["opportunity_id"] ?? "";
$opportunityId = $opportunityId !== "" ? (int) $opportunityId : null;

/*
|--------------------------------------------------------------------------
| Enforce Notification Link Type
|--------------------------------------------------------------------------
*/

if ($type === "Event") {
    $opportunityId = null;
} elseif ($type === "Opportunity") {
    $eventId = null;
} else {
    $eventId = null;
    $opportunityId = null;
}
    if (
    $userId === "" ||
    $title === "" ||
    $message === "" ||
    $type === ""
) {
    $error = "Please fill in all required fields.";
} else {
    $checkUser = $conn->prepare("
        SELECT user_id
        FROM users
        WHERE user_id = ?
          AND role = 'alumni'
          AND account_status = 'active'
        LIMIT 1
    ");

    $checkUser->bind_param("i", $userId);
    $checkUser->execute();
    $checkUser->store_result();

    if ($checkUser->num_rows === 0) {
        $error = "Invalid recipient selected.";
        $checkUser->close();
    } else {
        $checkUser->close();
    }


        // Your existing INSERT code continues here.

        $stmt = $conn->prepare("
            INSERT INTO notifications
            (
                user_id,
                title,
                message,
                type,
                event_id,
                opportunity_id,
                is_read,
                created_at
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                0,
                NOW()
            )
        ");

        $stmt->bind_param(
            "isssii",
            $userId,
            $title,
            $message,
            $type,
            $eventId,
            $opportunityId
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

     <?php require_once __DIR__ ."/../includes/sidebar.php"; ?>
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
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

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
    <label for="event_id">
        Event (Optional)
    </label>

    <select
        id="event_id"
        name="event_id"
    >
        <option value="">
            No Event Link
        </option>

        <?php if ($events): ?>
            <?php while ($event = $events->fetch_assoc()): ?>
                <option value="<?= (int) $event["event_id"] ?>">
                    <?= e($event["title"]) ?>
                </option>
            <?php endwhile; ?>
        <?php endif; ?>
    </select>
</div>
<div class="form-group">
    <label for="opportunity_id">
        Opportunity (Optional)
    </label>

    <select
        id="opportunity_id"
        name="opportunity_id"
    >
        <option value="">
            No Opportunity Link
        </option>

        <?php if ($opportunities): ?>
            <?php while ($opportunity = $opportunities->fetch_assoc()): ?>
                <option value="<?= (int) $opportunity["opportunity_id"] ?>">
                    <?= e($opportunity["title"]) ?>
                </option>
            <?php endwhile; ?>
        <?php endif; ?>
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
<script>
document.addEventListener("DOMContentLoaded", function () {
    const type = document.getElementById("type");
    const eventField = document.getElementById("event_id").closest(".form-group");
    const opportunityField = document.getElementById("opportunity_id").closest(".form-group");

    function updateLinkFields() {
    eventField.style.display = type.value === "Event" ? "block" : "none";
    opportunityField.style.display = type.value === "Opportunity" ? "block" : "none";
}

    type.addEventListener("change", updateLinkFields);

    updateLinkFields();
});
</script>
</body>

</html>