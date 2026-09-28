 <?php

session_start();

require_once "../config/database.php";
require_once "../config/config.php";
require_once "../includes/functions.php";
require_once "../includes/auth-check.php";

/*|--------------------------------------------------------------------------
| ADMIN ACCESS
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "admin") {
    header("Location: ../index.php");
    exit;
}

$adminUserId = (int) $_SESSION["user_id"];


/*|--------------------------------------------------------------------------
| MARK NOTIFICATION AS READ
|--------------------------------------------------------------------------
|
| When the admin clicks a notification, this marks that
| specific notification as read before returning to dashboard.
|
*/

if (isset($_GET["mark_notification"])) {

    $notificationId = (int) $_GET["mark_notification"];

    if ($notificationId > 0) {

        $stmt = $conn->prepare("
            UPDATE notifications
            SET is_read = 1
            WHERE notification_id = ?
              AND user_id = ?
        ");

        if ($stmt) {

            $stmt->bind_param(
                "ii",
                $notificationId,
                $adminUserId
            );

            $stmt->execute();

            $stmt->close();
        }
    }

    header("Location: dashboard.php");
    exit;
}


/*|--------------------------------------------------------------------------
| DASHBOARD STATISTICS
|--------------------------------------------------------------------------
*/

$totalAlumni = 0;
$totalEmployed = 0;
$totalUnemployed = 0;
$totalOpportunities = 0;
$pendingOpportunities = 0;
$approvedOpportunities = 0;


/* Total Alumni */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM alumni
");

if ($result) {

    $row = $result->fetch_assoc();

    $totalAlumni = (int) $row["total"];
}


/* Employment Statistics */

$result = $conn->query("
    SELECT
        SUM(employment_status = 'Employed') AS employed,
        SUM(employment_status = 'Unemployed') AS unemployed
    FROM employment
");

if ($result) {

    $row = $result->fetch_assoc();

    $totalEmployed =
        (int) ($row["employed"] ?? 0);

    $totalUnemployed =
        (int) ($row["unemployed"] ?? 0);
}


/* Opportunity Statistics */

$result = $conn->query("
    SELECT
        COUNT(*) AS total,
        SUM(status = 'Pending') AS pending,
        SUM(status = 'Approved') AS approved
    FROM opportunities
");

if ($result) {

    $row = $result->fetch_assoc();

    $totalOpportunities =
        (int) ($row["total"] ?? 0);

    $pendingOpportunities =
        (int) ($row["pending"] ?? 0);

    $approvedOpportunities =
        (int) ($row["approved"] ?? 0);
}


/*|--------------------------------------------------------------------------
| ADMIN NOTIFICATIONS
|--------------------------------------------------------------------------
*/


/* Get unread notification count */

$unreadNotifications = 0;

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE user_id = ?
      AND is_read = 0
");

if ($stmt) {

    $stmt->bind_param(
        "i",
        $adminUserId
    );

    $stmt->execute();

    $notificationCountResult =
        $stmt->get_result();

    if ($notificationCountResult) {

        $notificationCountRow =
            $notificationCountResult->fetch_assoc();

        $unreadNotifications =
            (int) (
                $notificationCountRow["total"] ?? 0
            );
    }

    $stmt->close();
}


/* Get latest notifications */

$adminNotifications = [];

$stmt = $conn->prepare("
    SELECT
        notification_id,
        title,
        message,
        type,
        is_read,
        created_at
    FROM notifications
    WHERE user_id = ?
    AND is_read = 0
    ORDER BY created_at DESC
    LIMIT 10
");

if ($stmt) {

    $stmt->bind_param(
        "i",
        $adminUserId
    );

    $stmt->execute();

    $notificationResult =
        $stmt->get_result();
 while (
        $notification =
        $notificationResult->fetch_assoc()
    ) {

        $adminNotifications[] =
            $notification;
    }

    $stmt->close();
}


/*|--------------------------------------------------------------------------
| RECENT OPPORTUNITIES
|--------------------------------------------------------------------------
*/

$recentOpportunities = $conn->query("
    SELECT
        opportunity_id,
        title,
        company_name,
        type,
        status,
        created_at
    FROM opportunities
    ORDER BY created_at DESC
    LIMIT 5
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
        Alumni_president Dashboard |
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <style>

        /*--------------------------------------------------------------
        NOTIFICATION AREA
        --------------------------------------------------------------*/

        .admin-notification-area {
            position: relative;
            margin-left: auto;
        }


        /*--------------------------------------------------------------
        NOTIFICATION BELL
        --------------------------------------------------------------*/

        .notification-bell-button {

            position: relative;

            width: 44px;
            height: 44px;

            border: 1px solid #e5e0dc;

            border-radius: 50%;

            background: #ffffff;

            display: flex;

            align-items: center;

            justify-content: center;

            cursor: pointer;

            font-size: 20px;

            color: #4a2c1d;

            transition: 0.2s;
        }


        .notification-bell-button:hover {

            background: #f8f5f2;
        }


        /*--------------------------------------------------------------
        NOTIFICATION COUNT
        --------------------------------------------------------------*/

        .notification-count {

            position: absolute;

            top: -4px;
            right: -4px;

            min-width: 19px;

            height: 19px;

            padding: 0 5px;

            border-radius: 20px;

            background: #c62828;

            color: #ffffff;

            font-size: 11px;

            font-weight: 700;

            display: flex;

            align-items: center;

            justify-content: center;

            border: 2px solid #ffffff;
        }


        /*--------------------------------------------------------------
        NOTIFICATION DROPDOWN
        --------------------------------------------------------------*/

        .notification-dropdown {

            display: none;

            position: absolute;

            top: 52px;

            right: 0;

            width: 360px;

            background: #ffffff;

            border: 1px solid #eeeeee;

            border-radius: 12px;

            box-shadow:
                0 10px 30px
                rgba(0, 0, 0, 0.12);

            z-index: 1000;

            overflow: hidden;
        }


        .notification-dropdown.show {

            display: block;
        }


        /*--------------------------------------------------------------
        NOTIFICATION HEADER
        --------------------------------------------------------------*/

        .notification-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            padding: 15px 17px;

            border-bottom: 1px solid #eeeeee;
        }


        .notification-header strong {

            color: #4a2c1d;

            font-size: 15px;
        }


        .notification-header a {

            color: #7a4b2a;

            font-size: 12px;

            text-decoration: none;
        }
 /*--------------------------------------------------------------
        NOTIFICATION LIST
        --------------------------------------------------------------*/

        .notification-list {

            max-height: 400px;

            overflow-y: auto;
        }


        .notification-item {

            display: block;

            padding: 14px 17px;

            border-bottom: 1px solid #f0f0f0;

            text-decoration: none;

            color: inherit;
        }


        .notification-item:hover {

            background: #faf8f6;
        }


        .notification-item.unread {

            background: #fffaf5;
        }


        .notification-item-title {

            display: flex;

            justify-content: space-between;

            gap: 10px;

            color: #4a2c1d;

            font-size: 14px;

            font-weight: 700;

            margin-bottom: 5px;
        }


        .notification-item-message {

            color: #666666;

            font-size: 13px;

            line-height: 1.5;
        }


        .notification-item-date {

            margin-top: 7px;

            color: #999999;

            font-size: 11px;
        }


        .notification-dot {

            width: 7px;

            height: 7px;

            border-radius: 50%;

            background: #c62828;

            flex-shrink: 0;

            margin-top: 4px;
        }


        .notification-empty {

            padding: 35px 20px;

            text-align: center;

            color: #888888;

            font-size: 13px;
        }


        /*--------------------------------------------------------------
        TOP BAR
        --------------------------------------------------------------*/

        .admin-topbar {

            display: flex;

            align-items: center;

            gap: 20px;
        }


        /*--------------------------------------------------------------
        MOBILE
        --------------------------------------------------------------*/

        @media (max-width: 600px) {

            .notification-dropdown {

                position: fixed;

                top: 70px;

                left: 15px;

                right: 15px;

                width: auto;
            }
        }

    </style>

</head>


<body class="admin-body">


<div class="admin-layout">


    <!--==============================================================
    SIDEBAR
    ==============================================================-->

    <?php
    require_once __DIR__ . "/includes/sidebar.php";
    ?>


    <!--==============================================================
    MAIN CONTENT
    ==============================================================-->

    <main class="admin-main">


        <!--============================================================
        TOP BAR
        ==============================================================-->

        <header class="admin-topbar">

            <div>

                <h1>
                    Alumni President Dashboard
                </h1>

                <p>
                    Welcome back, Oversee alumni administration and activities from here
                    
                </p>

            </div>


            <!--========================================================
            NOTIFICATION BELL
            =========================================================-->

            <div class="admin-notification-area">


                <button
                    type="button"
                    class="notification-bell-button"
                    id="notificationBell"
                    aria-label="Notifications"
                >

                    🔔

                    <?php if ($unreadNotifications > 0): ?>

                        <span
                            class="notification-count"
                        >

                            <?= $unreadNotifications > 99
                                ? "99+"
                                : $unreadNotifications
                            ?>

                        </span>
 <?php endif; ?>

                </button>


                <!--==================================================
                NOTIFICATION DROPDOWN
                ===================================================-->

                <div
                    class="notification-dropdown"
                    id="notificationDropdown"
                >


                    <div class="notification-header">

                        <strong>
                            Notifications
                        </strong>

                        <a
                            href="notification/index.php"
                        >
                            View All
                        </a>

                    </div>


                    <div class="notification-list">


                        <?php if (
                            count($adminNotifications) === 0
                        ): ?>

                            <div
                                class="notification-empty"
                            >

                                No notifications yet.

                            </div>


                        <?php else: ?>


                            <?php foreach (
                                $adminNotifications
                                as $notification
                            ): ?>


                                <?php

                                /*
                                |--------------------------------------------------
                                | Notification URL
                                |--------------------------------------------------
                                |
                                | Clicking an individual notification sends
                                | the admin back to dashboard.php with the
                                | notification ID.
                                |
                                | dashboard.php then marks it as read.
                                |
                                */

                                $notificationUrl =
                                    "dashboard.php?mark_notification=" .
                                    (int) $notification[
                                        "notification_id"
                                    ];

                                ?>


                                <a
                                    href="<?= e($notificationUrl) ?>"
                                    class="notification-item
                                    <?= (int)
                                        $notification["is_read"] === 0
                                        ? "unread"
                                        : ""
                                    ?>"
                                >


                                    <div
                                        class="notification-item-title"
                                    >

                                        <span>

                                            <?= e(
                                                $notification["title"]
                                            ) ?>

                                        </span>


                                        <?php if (
                                            (int)
                                            $notification["is_read"] === 0
                                        ): ?>

                                            <span
                                                class="notification-dot"
                                            ></span>

                                        <?php endif; ?>


                                    </div>


                                    <div
                                        class="notification-item-message"
                                    >

                                        <?= e(
                                            $notification["message"]
                                        ) ?>
</div>


                                    <div
                                        class="notification-item-date"
                                    >

                                        <?= e(
                                            $notification["created_at"]
                                        ) ?>

                                    </div>


                                </a>


                            <?php endforeach; ?>


                        <?php endif; ?>


                    </div>

                </div>

            </div>

        </header>


        <!--============================================================
        CONTENT
        ==============================================================-->

        <section class="dashboard-content">


            <!--========================================================
            STATISTICS
            =========================================================-->

            <div class="stats-grid">


                <!-- TOTAL ALUMNI -->

                <div class="stat-card">

                    <div class="stat-icon">
                        👥
                    </div>

                    <div>

                        <span>
                            Total Alumni
                        </span>

                        <strong>
                            <?= $totalAlumni ?>
                        </strong>

                    </div>

                </div>


                <!-- EMPLOYED -->

                <div class="stat-card">

                    <div class="stat-icon">
                        💼
                    </div>

                    <div>

                        <span>
                            Employed
                        </span>

                        <strong>
                            <?= $totalEmployed ?>
                        </strong>

                    </div>

                </div>


                <!-- UNEMPLOYED -->

                <div class="stat-card">

                    <div class="stat-icon">
                        📊
                    </div>

                    <div>

                        <span>
                            Unemployed
                        </span>

                        <strong>
                            <?= $totalUnemployed ?>
                        </strong>

                    </div>

                </div>


                <!-- OPPORTUNITIES -->

                <div class="stat-card">

                    <div class="stat-icon">
                        🎯
                    </div>

                    <div>

                        <span>
                            Opportunities
                        </span>

                        <strong>
                            <?= $totalOpportunities ?>
                        </strong>

                    </div>

                </div>


            </div>


            <!--========================================================
            OPPORTUNITY SUMMARY
            =========================================================-->

            <div class="dashboard-grid">


                <div class="dashboard-panel">

                    <div class="panel-header">

                        <div>

                            <h2>
                                Opportunity Status
                            </h2>

                            <p>
                                Current opportunity submissions.
                            </p>

                        </div>

                    </div>


                    <div class="quick-stats">


                        <div>

                            <span>
                                Pending
                            </span>

                            <strong>
                                <?= $pendingOpportunities ?>
                            </strong>

                        </div>


                        <div>
 <span>
                                Approved
                            </span>

                            <strong>
                                <?= $approvedOpportunities ?>
                            </strong>

                        </div>


                    </div>

                </div>


                <!-- QUICK ACTIONS -->

                <div class="dashboard-panel">


                    <div class="panel-header">

                        <div>

                            <h2>
                                Quick Actions
                            </h2>

                            <p>
                                Common alumni administration tasks.
                            </p>

                        </div>

                    </div>


                    <div class="quick-actions">


                        <a
                            href="alumni/add.php"
                            class="secondary-button"
                        >
                            + Add Alumni
                        </a>


                        <a
                            href="opportunities/add.php"
                            class="secondary-button"
                        >
                            + Add Opportunity
                        </a>


                        <a
                            href="alumni/index.php"
                            class="secondary-button"
                        >
                            Manage Alumni
                        </a>


                    </div>

                </div>


            </div>


            <!--========================================================
            RECENT OPPORTUNITIES
            =========================================================-->

            <div class="dashboard-panel">


                <div class="panel-header">

                    <div>

                        <h2>
                            Recent Opportunities
                        </h2>

                        <p>
                            Latest jobs, internships and
                            training opportunities.
                        </p>

                    </div>


                    <a
                        href="opportunities/index.php"
                        class="secondary-button"
                    >
                        View All
                    </a>

                </div>


                <?php if (
                    $recentOpportunities &&
                    $recentOpportunities->num_rows > 0
                ): ?>


                    <div class="table-responsive">


                        <table class="admin-table">


                            <thead>

                                <tr>

                                    <th>
                                        Title
                                    </th>

                                    <th>
                                        Company
                                    </th>

                                    <th>
                                        Type
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                    <th>
                                        Date
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                                <?php while (
                                    $opportunity =
                                    $recentOpportunities->fetch_assoc()
                                ): ?>


                                    <tr>


                                        <td>

                                            <strong>

                                                <?= e(
                                                    $opportunity["title"]
                                                ) ?>
</strong>

                                        </td>


                                        <td>

                                            <?= e(
                                                $opportunity["company_name"]
                                            ) ?>

                                        </td>


                                        <td>

                                            <?= e(
                                                $opportunity["type"]
                                            ) ?>

                                        </td>


                                        <td>

                                            <span
                                                class="status-badge
                                                <?= e(
                                                    strtolower(
                                                        $opportunity["status"]
                                                    )
                                                ) ?>"
                                            >

                                                <?= e(
                                                    $opportunity["status"]
                                                ) ?>

                                            </span>

                                        </td>


                                        <td>

                                            <?= e(
                                                $opportunity["created_at"]
                                            ) ?>

                                        </td>


                                    </tr>


                                <?php endwhile; ?>


                            </tbody>


                        </table>


                    </div>


                <?php else: ?>


                    <p>
                        No opportunities found.
                    </p>


                <?php endif; ?>


            </div>


        </section>


    </main>


</div>


<!--==============================================================
NOTIFICATION JAVASCRIPT
===============================================================-->

<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {


        const bell =
            document.getElementById(
                "notificationBell"
            );


        const dropdown =
            document.getElementById(
                "notificationDropdown"
            );


        if (!bell || !dropdown) {

            return;
        }


        /* Open / close dropdown */

        bell.addEventListener(
            "click",
            function (event) {

                event.stopPropagation();

                dropdown.classList.toggle(
                    "show"
                );

            }
        );


        /* Close when clicking outside */

        document.addEventListener(
            "click",
            function (event) {

                if (
                    !dropdown.contains(
                        event.target
                    ) &&
                    !bell.contains(
                        event.target
                    )
                ) {

                    dropdown.classList.remove(
                        "show"
                    );

                }

            }
        );


    }
);

</script>


</body>

</html>