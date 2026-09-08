 <?php
session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";

/*
|--------------------------------------------------------------------------
| AUTHENTICATION
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "alumni") {
    header("Location: ../../index.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| CURRENT PAGE
|--------------------------------------------------------------------------
*/

$currentPage = "notifications";

/*
|--------------------------------------------------------------------------
| MARK ALL NOTIFICATIONS AS READ
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["mark_all_as_read"])) {
verify_csrf_token($_POST["csrf_token"] ?? "");
    $sql = "
        UPDATE notifications
        SET is_read = 1
        WHERE user_id = ?
        AND is_read = 0
    ";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| MARK ONE NOTIFICATION AS READ
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["mark_as_read"])) {
    

    verify_csrf_token($_POST["csrf_token"] ?? "");

    // existing code...

    $notificationId = (int) ($_POST["notification_id"] ?? 0);

    if ($notificationId > 0) {

        $sql = "
            UPDATE notifications
            SET is_read = 1
            WHERE notification_id = ?
            AND user_id = ?
        ";

        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ii", $notificationId, $userId);
            $stmt->execute();
            $stmt->close();
        }
    }

    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| FETCH UNREAD COUNT
|--------------------------------------------------------------------------
*/

$unreadSql = "
    SELECT COUNT(*) AS unread_count
    FROM notifications
    WHERE user_id = ?
    AND is_read = 0
";

$unreadStmt = $conn->prepare($unreadSql);

if (!$unreadStmt) {
    die("Database error: " . $conn->error);
}

$unreadStmt->bind_param("i", $userId);
$unreadStmt->execute();

$unreadResult = $unreadStmt->get_result();
$unreadData = $unreadResult->fetch_assoc();

$unreadCount = (int) ($unreadData["unread_count"] ?? 0);

$unreadStmt->close();

/*
|--------------------------------------------------------------------------
| FETCH NOTIFICATIONS
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        notification_id,
        title,
        message,
        type,
        opportunity_id,
        event_id,
        is_read,
        created_at
    FROM notifications
    WHERE user_id = ?
    ORDER BY created_at DESC
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();

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
        Notifications | <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

    <style>

        .notifications-content {
            padding: 30px;
        }

        .notifications-page-header {
            margin-bottom: 25px;
        }

        .notifications-page-header h1 {
            margin: 0 0 8px;
            color: #5a321f;
            font-size: 28px;
        }
 .notifications-page-header p {
            margin: 0;
            color: #777;
        }

        /*
        |--------------------------------------------------------------------------
        | NOTIFICATION ACTIONS
        |--------------------------------------------------------------------------
        */

        .notification-actions {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }

        .mark-all-form {
            margin: 0;
        }

        .mark-all-button {
            border: none;
            background: #5a321f;
            color: #ffffff;
            padding: 10px 16px;
            border-radius: 7px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
        }

        .mark-all-button:hover {
            opacity: 0.9;
        }

        .notifications-container {
            max-width: 1000px;
        }

        .notification-card {
            background: #ffffff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 5px solid #8b5a3c;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .notification-card.unread {
            background: #fffaf6;
            border-left-color: #5a321f;
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 10px;
        }

        .notification-title {
            margin: 0;
            font-size: 18px;
            color: #5a321f;
            font-weight: 600;
        }

        .notification-title a {
            color: #5a321f;
            text-decoration: none;
        }

        .notification-title a:hover {
            text-decoration: underline;
        }

        .notification-type {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            background: #eee;
            color: #555;
            font-size: 12px;
            text-transform: capitalize;
            white-space: nowrap;
        }

        .notification-message {
            margin: 10px 0;
            color: #555;
            line-height: 1.6;
        }

        .notification-message a {
            color: #555;
            text-decoration: none;
        }

        .notification-message a:hover {
            color: #5a321f;
            text-decoration: underline;
        }

        .notification-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-top: 15px;
            padding-top: 12px;
            border-top: 1px solid #eee;
            font-size: 13px;
        }

        .notification-date {
            color: #888;
        }

        .notification-status {
            color: #777;
        }

        .unread-status {
            color: #5a321f;
            font-weight: 600;
        }

        .read-status {
            color: #888;
        }

        /*
        |--------------------------------------------------------------------------
        | MARK ONE AS READ
        |--------------------------------------------------------------------------
        */

        .mark-read-form {
            margin: 0;
        }

        .mark-read-button {
            border: none;
            background: #5a321f;
            color: #ffffff;
            padding: 7px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
        }

        .mark-read-button:hover {
            opacity: 0.9;
        }

        /*
        |--------------------------------------------------------------------------
        | EMPTY STATE
        |--------------------------------------------------------------------------
        */
 .empty-state {
            background: #ffffff;
            border-radius: 10px;
            padding: 50px 30px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .empty-state h2 {
            margin: 0 0 10px;
            color: #5a321f;
        }

        .empty-state p {
            margin: 0;
            color: #777;
        }

        /*
        |--------------------------------------------------------------------------
        | MOBILE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 768px) {

            .notifications-content {
                padding: 20px;
            }

            .notification-header {
                flex-direction: column;
            }

            .notification-footer {
                flex-direction: column;
                align-items: flex-start;
            }

            .notification-actions {
                justify-content: flex-start;
            }

        }

    </style>

</head>

<body class="admin-body">

<div class="admin-layout">

    <?php
    require_once __DIR__ . "/../includes/sidebar.php";
    ?>

    <main class="admin-main">

        <header class="admin-topbar">

            <div>

                <h1>
                    Notifications
                </h1>

                <p>
                    Stay updated with your alumni activities.
                </p>

            </div>

        </header>

        <section class="notifications-content">

            <div class="notifications-page-header">

                <h1>
                    Your Notifications
                </h1>

                <p>
                    Events, opportunities, mentorship requests,
                    and other important updates.
                </p>

            </div>

            <?php if ($unreadCount > 0): ?>

                <div class="notification-actions">

                    <form
                        method="POST"
                        class="mark-all-form"
                    >
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <button
                            type="submit"
                            name="mark_all_as_read"
                            class="mark-all-button"
                        >
                            Mark All as Read
                        </button>

                    </form>

                </div>

            <?php endif; ?>

            <div class="notifications-container">

                <?php if ($result->num_rows === 0): ?>

                    <div class="empty-state">

                        <h2>
                            No Notifications
                        </h2>

                        <p>
                            You currently have no notifications.
                        </p>

                    </div>

                <?php else: ?>

                    <?php while ($notification = $result->fetch_assoc()): ?>

                        <?php

                        $notificationType = strtolower(
                            trim($notification["type"] ?? "")
                        );

                        $hasEventLink =
                            !empty($notification["event_id"]);

                        $hasOpportunityLink =
                            !empty($notification["opportunity_id"]);

                        $isMentorshipNotification =
                            $notificationType === "mentorship";

                        $isNewMentorshipRequest =
                            $isMentorshipNotification &&
                            $notification["title"] ===
                            "New Mentorship Request";

                        ?>

                        <div
                            class="notification-card <?= !$notification["is_read"] ? "unread" : "" ?>"
                        >

                            <div class="notification-header">

                                <h3 class="notification-title">
 <?php if ($hasEventLink): ?>

                                        <a
                                            href="../events/view-event.php?id=<?= (int) $notification["event_id"] ?>"
                                        >
                                            <?= e($notification["title"]) ?>
                                        </a>

                                    <?php elseif ($hasOpportunityLink): ?>

                                        <a
                                            href="../opportunity.php?id=<?= (int) $notification["opportunity_id"] ?>"
                                        >
                                            <?= e($notification["title"]) ?>
                                        </a>

                                    <?php elseif ($isNewMentorshipRequest): ?>

                                        <a
                                            href="../mentorship/received-requests.php"
                                        >
                                            <?= e($notification["title"]) ?>
                                        </a>

                                    <?php elseif ($isMentorshipNotification): ?>

                                        <a
                                            href="../mentorship/my-requests.php"
                                        >
                                            <?= e($notification["title"]) ?>
                                        </a>

                                    <?php else: ?>

                                        <?= e($notification["title"]) ?>

                                    <?php endif; ?>

                                </h3>

                                <span class="notification-type">

                                    <?= e(
                                        $notificationType !== ""
                                            ? $notificationType
                                            : "general"
                                    ) ?>

                                </span>

                            </div>

                            <div class="notification-message">

                                <?php if ($hasEventLink): ?>

                                    <a
                                        href="../events/view-event.php?id=<?= (int) $notification["event_id"] ?>"
                                    >
                                        <?= e($notification["message"]) ?>
                                    </a>

                                <?php elseif ($hasOpportunityLink): ?>

                                    <a
                                        href="../opportunity.php?id=<?= (int) $notification["opportunity_id"] ?>"
                                    >
                                        <?= e($notification["message"]) ?>
                                    </a>

                                <?php elseif ($isNewMentorshipRequest): ?>

                                    <a
                                        href="../mentorship/received-requests.php"
                                    >
                                        <?= e($notification["message"]) ?>
                                    </a>

                                <?php elseif ($isMentorshipNotification): ?>

                                    <a
                                        href="../mentorship/my-requests.php"
                                    >
                                        <?= e($notification["message"]) ?>
                                    </a>

                                <?php else: ?>

                                    <?= e($notification["message"]) ?>

                                <?php endif; ?>

                            </div>

                            <div class="notification-footer">

                                <span class="notification-date">
 <?= e(
                                        date(
                                            "M d, Y h:i A",
                                            strtotime(
                                                $notification["created_at"]
                                            )
                                        )
                                    ) ?>

                                </span>

                                <?php if ($notification["is_read"]): ?>

                                    <span
                                        class="notification-status read-status"
                                    >
                                        Read
                                    </span>

                                <?php else: ?>

                                    <span
                                        class="notification-status unread-status"
                                    >
                                        Unread
                                    </span>

                                    <form
                                        method="POST"
                                        class="mark-read-form"
                                    >

                                        <input
                                            type="hidden"
                                            name="notification_id"
                                            value="<?= (int) $notification["notification_id"] ?>"
                                        >
<input
    type="hidden"
    name="csrf_token"
    value="<?= e(csrf_token()) ?>"
>
                                        <button
                                            type="submit"
                                            name="mark_as_read"
                                            class="mark-read-button"
                                        >
                                            Mark as Read
                                        </button>

                                    </form>

                                <?php endif; ?>

                            </div>

                        </div>

                    <?php endwhile; ?>

                <?php endif; ?>

            </div>

        </section>

    </main>

</div>

</body>

</html>

<?php
$stmt->close();
?>