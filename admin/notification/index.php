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
| Get Notifications
|--------------------------------------------------------------------------
*/

$notifications = $conn->query("
    SELECT
        n.notification_id,
        n.title,
        n.message,
        n.type,
        n.is_read,
        n.created_at,
        u.email
    FROM notifications n
    LEFT JOIN users u
        ON n.user_id = u.user_id
    ORDER BY n.created_at DESC
");

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
        Notification | <?= e(SITE_NAME) ?>
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

        <!-- TOP BAR -->

        <header class="admin-topbar">

            <div>

                <h1>
                    Notifications
                </h1>

                <p>
                    Manage notifications sent to users.
                </p>

            </div>

        </header>


        <!-- CONTENT -->

        <section class="dashboard-content">

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>
                        <h2>
                            All Notifications
                        </h2>

                        <p>
                            View and manage system notifications.
                        </p>

                    </div>

                    <a
                        href="create.php"
                        class="secondary-button"
                    >
                        + Create Notification
                    </a>

                </div>


                <?php if (
                    $notifications &&
                    $notifications->num_rows > 0
                ): ?>

                    <div class="table-responsive">

                        <table class="admin-table">

                            <thead>

                                <tr>

                                    <th>
                                        Recipient
                                    </th>

                                    <th>
                                        Title
                                    </th>

                                    <th>
                                        Type
                                    </th>

                                    <th>
                                        Message
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                    <th>
                                        Date
                                    </th>

                                    <th>
                                        Actions
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php while (
                                    $notification =
                                    $notifications->fetch_assoc()
                                ): ?>

                                    <tr>

                                        <td>

                                            <?= e(
                                                $notification["email"]
                                                ?? "Unknown User"
                                            ) ?>

                                        </td>

                                        <td>

                                            <strong>

                                                <?= e(
                                                    $notification["title"]
                                                ) ?>

                                            </strong>

                                        </td>

                                        <td>

                                            <span
                                                class="status-badge"
                                            >

                                                <?= e(
                                                    $notification["type"]
                                                ) ?>

                                            </span>

                                        </td>

                                        <td>

                                            <?= e(
                                                $notification["message"]
                                            ) ?>

                                        </td>

                                        <td>

                                            <?php if (
                                                (int)
                                                $notification["is_read"] === 1
                                            ): ?>
                                            <span
                                                    class="status-badge approved"
                                                >
                                                    Read
                                                </span>

                                            <?php else: ?>

                                                <span
                                                    class="status-badge pending"
                                                >
                                                    Unread
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                        <td>

                                            <?= e(
                                                date(
                                                    "M d, Y",
                                                    strtotime(
                                                        $notification["created_at"]
                                                    )
                                                )
                                            ) ?>

                                        </td>

                                        <td>

                                            <a
                                                href="edit.php?id=<?= (int) $notification["notification_id"] ?>"
                                                class="secondary-button"
                                            >
                                                Edit
                                            </a>

                                            <form
    method="POST"
    action="delete.php"
    style="display:inline;"
    onsubmit="return confirm('Are you sure you want to delete this notification?');"
>
    <?= csrf_field() ?>

    <input
        type="hidden"
        name="id"
        value="<?= (int) $notification["notification_id"] ?>"
    >

    <button
        type="submit"
        class="secondary-button"
    >
        Delete
    </button>
</form>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            </tbody>

                        </table>

                    </div>

                <?php else: ?>

                    <div class="empty-dashboard">

                        <div>
                            🔔
                        </div>

                        <h3>
                            No notifications yet
                        </h3>

                        <p>
                            Notifications will appear here
                            when they are created.
                        </p>

                        <br>

                        <a
                            href="create.php"
                            class="secondary-button"
                        >
                            + Create Notification
                        </a>

                    </div>

                <?php endif; ?>

            </div>

        </section>

    </main>

</div>

</body>

</html>