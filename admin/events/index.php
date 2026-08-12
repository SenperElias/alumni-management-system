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

$search = trim($_GET["search"] ?? "");
$status = trim($_GET["status"] ?? "");

/*
|--------------------------------------------------------------------------
| Search & Filter
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        event_id,
        title,
        description,
        event_date,
        start_time,
        end_time,
        location,
        registration_deadline,
        max_capacity,
        status,
        created_at
    FROM events
    WHERE 1 = 1
";

$params = [];
$types = "";

if ($search !== "") {
    $sql .= "
        AND (
            title LIKE ?
            OR location LIKE ?
            OR description LIKE ?
        )
    ";

    $searchValue = "%" . $search . "%";

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;

    $types .= "sss";
}

if ($status !== "") {
    $sql .= " AND status = ? ";

    $params[] = $status;
    $types .= "s";
}

$sql .= " ORDER BY event_date DESC, start_time DESC";

/*
|--------------------------------------------------------------------------
| Execute Query
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database query error: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

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
        Events | <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

</head>

<body class="admin-body">

<div class="admin-layout">

    <!-- =====================================================
         SIDEBAR
    ====================================================== -->

        <?php require_once __DIR__ ."/../includes/sidebar.php"; ?>

 

    <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->

    <main class="admin-main">


        <header class="admin-topbar">

            <div>

                <h1>
                    Events
                </h1>

                <p>
                    Manage college events and registrations.
                </p>

            </div>


            <a
                href="add.php"
                class="primary-button"
            >
                + Add Event
            </a>

        </header>


        <section class="dashboard-content">


            <!-- =================================================
                 SEARCH & FILTER
            ================================================== -->

            <div class="dashboard-panel">

                <form
                    method="GET"
                    class="search-filter-form"
                >

                    <div class="form-group">

                        <label for="search">
                            Search
                        </label>

                        <input
                            type="text"
                            id="search"
                            name="search"
                            value="<?= e($search) ?>"
                            placeholder="Search events or locations..."
                        >

                    </div>


                    <div class="form-group">

                        <label for="status">
                            Status
                        </label>

                        <select
                            id="status"
                            name="status"
                        >

                            <option value="">
                                All Statuses
                            </option>

                            <option
                                value="Pending"
                                <?= $status === "Pending" ? "selected" : "" ?>
                            >
                                Pending
                            </option>

                            <option
                                value="Approved"
                                <?= $status === "Approved" ? "selected" : "" ?>
                            >
                                Approved
                            </option>

                            <option
                                value="Rejected"
                                <?= $status === "Rejected" ? "selected" : "" ?>
                            >
                                Rejected
                            </option>

                        </select>

                    </div>


                    <div class="form-group search-button-group">

                        <button
                            type="submit"
                            class="primary-button"
                        >
                            Search
                        </button>

                    </div>

                </form>

            </div>


            <!-- =================================================
                 EVENTS TABLE
            ================================================== -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Event Management
                        </h2>

                        <p>
                            Review and manage college events.
                        </p>

                    </div>

                </div>


                <?php if ($result->num_rows > 0): ?>

                    <div class="table-responsive">

                        <table class="admin-table">

                            <thead>

                                <tr>

                                    <th>
                                        Event
                                    </th>
                                    <th>
                                        Date
                                    </th>

                                    <th>
                                        Time
                                    </th>

                                    <th>
                                        Location
                                    </th>

                                    <th>
                                        Capacity
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                    <th>
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                            <?php while (
                                $event = $result->fetch_assoc()
                            ): ?>

                                <tr>

                                    <td>

                                        <strong>
                                            <?= e($event["title"]) ?>
                                        </strong>

                                    </td>


                                    <td>

                                        <?= e(
                                            $event["event_date"]
                                        ) ?>

                                    </td>


                                    <td>

                                        <?= e(
                                            $event["start_time"]
                                        ) ?>

                                        -

                                        <?= e(
                                            $event["end_time"]
                                        ) ?>

                                    </td>


                                    <td>

                                        <?= e(
                                            $event["location"]
                                            ?: "Not specified"
                                        ) ?>

                                    </td>


                                    <td>

                                        <?= e(
                                            $event["max_capacity"]
                                            ?: "Unlimited"
                                        ) ?>

                                    </td>


                                    <td>

                                        <span
                                            class="status-badge <?= e(
                                                strtolower(
                                                    $event["status"]
                                                )
                                            ) ?>"
                                        >

                                            <?= e(
                                                $event["status"]
                                            ) ?>

                                        </span>

                                    </td>


                                    <td>

                                        <div class="table-actions">

                                            <a
                                                href="view.php?id=<?= (int)$event["event_id"] ?>"
                                                class="secondary-button"
                                            >
                                                View
                                            </a>
                                 <a
                                                href="edit.php?id=<?= (int)$event["event_id"] ?>"
                                                class="secondary-button"
                                            >
                                                Edit
                                            </a>
<a
    href="registrations.php?id=<?= (int)$event["event_id"] ?>"
    class="secondary-button"
>
    Registrations
</a>

                                            <a
                                                href="delete.php?id=<?= (int)$event["event_id"] ?>"
                                                class="danger-button"
                                                onclick="return confirm('Are you sure you want to delete this event?');"
                                            >
                                                Delete
                                            </a>

                                        </div>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                            </tbody>

                        </table>

                    </div>


                <?php else: ?>


                    <div class="empty-dashboard">

                        <div>
                            📅
                        </div>

                        <h3>
                            No events found
                        </h3>

                        <p>
                            There are currently no events in the system.
                        </p>

                        <a
                            href="add.php"
                            class="primary-button"
                        >
                            + Add First Event
                        </a>

                    </div>


                <?php endif; ?>

            </div>

        </section>

    </main>

</div>

</body>

</html>           