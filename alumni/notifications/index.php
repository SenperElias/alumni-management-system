 <?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";

/*
|--------------------------------------------------------------------------
| Alumni Access
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
| Get Alumni Notifications
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
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
");

$stmt->bind_param("i", $userId);
$stmt->execute();

$notifications = $stmt->get_result();

$stmt->close();

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

</head>

<body class="admin-body">

<div class="admin-layout">

    <!-- =========================================================
         SIDEBAR
    ========================================================== -->

    <?php

    $currentPage = "notifications";

    require_once __DIR__ . "/../includes/sidebar.php";

    ?>

    <!-- =========================================================
         MAIN CONTENT
    ========================================================== -->

    <main class="admin-main">

        <header class="admin-topbar">

            <div>

                <h1>
                    Notifications
                </h1>

                <p>
                    Stay updated with important information from the alumni system.
                </p>

            </div>

        </header>


        <section class="dashboard-content">

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Your Notifications
                        </h2>

                        <p>
                            Recent messages and updates.
                        </p>

                    </div>

                </div>


                <?php if ($notifications->num_rows > 0): ?>

                    <div class="notification-list">

                        <?php while (
                            $notification = $notifications->fetch_assoc()
                        ): ?>

                            <div
                                class="notification-card"
                                style="
                                    border: 1px solid #eeeeee;
                                    border-radius: 12px;
                                    padding: 20px;
                                    margin-bottom: 15px;
                                    background:
                                        <?= (int) $notification["is_read"] === 1
                                            ? "#ffffff"
                                            : "#f8f5f2" ?>;
                                "
                            >

                                <div
                                    style="
                                        display: flex;
                                        justify-content: space-between;
                                        align-items: flex-start;
                                        gap: 15px;
                                    "
                                >

                                    <div style="flex: 1;">
 <!-- =================================================
                                             NOTIFICATION TITLE
                                        ================================================== -->

                                        <h3
                                            style="
                                                margin: 0 0 8px;
                                                color: #4a2c1d;
                                            "
                                        >

                                            <?php if (!empty($notification["event_id"])): ?>

                                                <!-- EVENT NOTIFICATION -->

                                                <a
                                                    href="../events/view-event.php?id=<?= (int) $notification["event_id"] ?>"
                                                    style="
                                                        color: #4a2c1d;
                                                        text-decoration: none;
                                                    "
                                                >
                                                    <?= e($notification["title"]) ?>
                                                </a>

                                            <?php elseif (!empty($notification["opportunity_id"])): ?>

                                                <!-- OPPORTUNITY NOTIFICATION -->

                                                <a
                                                    href="../opportunity.php?id=<?= (int) $notification["opportunity_id"] ?>"
                                                    style="
                                                        color: #4a2c1d;
                                                        text-decoration: none;
                                                    "
                                                >
                                                    <?= e($notification["title"]) ?>
                                                </a>

                                            <?php else: ?>

                                                <!-- GENERAL NOTIFICATION -->

                                                <?= e($notification["title"]) ?>

                                            <?php endif; ?>

                                        </h3>


                                        <!-- =================================================
                                             NOTIFICATION MESSAGE
                                        ================================================== -->

                                        <p
                                            style="
                                                margin: 0;
                                                color: #666666;
                                                line-height: 1.7;
                                            "
                                        >

                                            <?php if (!empty($notification["event_id"])): ?>

                                                <!-- EVENT MESSAGE -->

                                                <a
                                                    href="../events/view-event.php?id=<?= (int) $notification["event_id"] ?>"
                                                    style="
                                                        color: #666666;
                                                        text-decoration: none;
                                                    "
                                                >
                                                    <?= nl2br(e($notification["message"])) ?>
                                                </a>

                                            <?php elseif (!empty($notification["opportunity_id"])): ?>
 <!-- OPPORTUNITY MESSAGE -->

                                                <a
                                                    href="../opportunity.php?id=<?= (int) $notification["opportunity_id"] ?>"
                                                    style="
                                                        color: #666666;
                                                        text-decoration: none;
                                                    "
                                                >
                                                    <?= nl2br(e($notification["message"])) ?>
                                                </a>

                                            <?php else: ?>

                                                <!-- GENERAL MESSAGE -->

                                                <?= nl2br(e($notification["message"])) ?>

                                            <?php endif; ?>

                                        </p>

                                    </div>


                                    <!-- =================================================
                                         NOTIFICATION TYPE
                                    ================================================== -->

                                    <span
                                        style="
                                            background: #f3ebe5;
                                            color: #7a4b2a;
                                            padding: 6px 10px;
                                            border-radius: 6px;
                                            font-size: 12px;
                                            font-weight: 600;
                                            white-space: nowrap;
                                        "
                                    >
                                        <?= e($notification["type"]) ?>
                                    </span>

                                </div>


                                <!-- =========================================================
                                     DATE + READ STATUS
                                ========================================================== -->

                                <div
                                    style="
                                        margin-top: 15px;
                                        padding-top: 10px;
                                        border-top: 1px solid #eeeeee;
                                        color: #999999;
                                        font-size: 12px;
                                        background: transparent;
                                        border-radius: 0;
                                    "
                                >

                                    <?= e(
                                        date(
                                            "M d, Y • h:i A",
                                            strtotime(
                                                $notification["created_at"]
                                            )
                                        )
                                    ) ?>


                                    <?php if (
                                        (int) $notification["is_read"] === 0
                                    ): ?>

                                        <a
                                            href="read.php?id=<?= (int) $notification["notification_id"] ?>"
                                            style="
                                                display: inline-block;
                                                margin-left: 12px;
 color: #7a4b2a;
                                                font-weight: 700;
                                                text-decoration: none;
                                                background: #f3ebe5;
                                                padding: 5px 9px;
                                                border-radius: 5px;
                                            "
                                        >
                                            Mark as Read
                                        </a>

                                    <?php endif; ?>

                                </div>

                            </div>

                        <?php endwhile; ?>

                    </div>

                <?php else: ?>

                    <!-- =========================================================
                         EMPTY STATE
                    ========================================================== -->

                    <div class="empty-dashboard">

                        <div>
                            🔔
                        </div>

                        <h3>
                            No notifications yet
                        </h3>

                        <p>
                            You don't have any notifications at the moment.
                        </p>

                    </div>

                <?php endif; ?>

            </div>

        </section>

    </main>

</div>

</body>

</html>