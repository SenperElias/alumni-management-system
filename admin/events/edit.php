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

$eventId = (int) ($_GET["id"] ?? 0);

if ($eventId <= 0) {
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Get Event
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
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
        status
    FROM events
    WHERE event_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $eventId);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();
    die("Event not found.");
}

$event = $result->fetch_assoc();

$stmt->close();

$error = "";

/*
|--------------------------------------------------------------------------
| Update Event
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $eventDate = trim($_POST["event_date"] ?? "");
    $startTime = trim($_POST["start_time"] ?? "");
    $endTime = trim($_POST["end_time"] ?? "");
    $location = trim($_POST["location"] ?? "");
    $registrationDeadline = trim(
        $_POST["registration_deadline"] ?? ""
    );
    $maxCapacity = trim($_POST["max_capacity"] ?? "");
    $status = trim($_POST["status"] ?? "");
if (!in_array($status, ["draft", "published", "completed", "cancelled"], true)) {
    $error = "Invalid event status.";
}
    if (
        $title === "" ||
        $eventDate === "" ||
        $startTime === "" ||
        $endTime === "" ||
        $location === ""
    ) {

        $error = "Please fill in all required fields.";

    } else {

        $maxCapacityValue =
            $maxCapacity === ""
            ? null
            : (int) $maxCapacity;

        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            UPDATE events
            SET
                title = ?,
                description = ?,
                event_date = ?,
                start_time = ?,
                end_time = ?,
                location = ?,
                registration_deadline = ?,
                max_capacity = ?,
                status = ?
            WHERE event_id = ?
        ");

        $stmt->bind_param(
            "sssssssisi",
            $title,
            $description,
            $eventDate,
            $startTime,
            $endTime,
            $location,
            $registrationDeadline,
            $maxCapacityValue,
            $status,
            $eventId
        );

        if ($stmt->execute()) {

            $stmt->close();

            header(
                "Location: view.php?id=" . $eventId
            );

            exit;

        } else {

            $error = "Unable to update event.";
        }

        $stmt->close();
    }

    /*
    |--------------------------------------------------------------------------
    | Keep Submitted Values
    |--------------------------------------------------------------------------
    */

    $event["title"] = $title;
    $event["description"] = $description;
    $event["event_date"] = $eventDate;
    $event["start_time"] = $startTime;
    $event["end_time"] = $endTime;
    $event["location"] = $location;
    $event["registration_deadline"] =
        $registrationDeadline;
    $event["max_capacity"] = $maxCapacity;
    $event["status"] = $status;
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
        Edit Event | <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

</head>

<body class="admin-body">

<div class="admin-layout">

   <?php require_once __DIR__ ."/../includes/sidebar.php"; ?>

        

    <!-- MAIN -->

    <main class="admin-main">

        <header class="admin-topbar">

            <div>

                <h1>
                    Edit Event
                </h1>

                <p>
                    Update event information.
                </p>

            </div>

        </header>


        <section class="dashboard-content">


            <?php if ($error !== ""): ?>

                <div class="error-message">
                    <?= e($error) ?>
                </div>

            <?php endif; ?>


            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Event Information
                        </h2>

                        <p>
                            Update the details of this event.
                        </p>

                    </div>

                </div>


                <form method="POST">


                    <div class="form-grid">


                        <!-- TITLE -->

                        <div class="form-group">

                            <label for="title">
                                Event Title *
                            </label>

                            <input
                                type="text"
                                id="title"
                                name="title"
                                required
                                value="<?= e(
                                    $event["title"]
                                ) ?>"
                            >

                        </div>


                        <!-- LOCATION -->

                        <div class="form-group">

                            <label for="location">
                                Location *
                            </label>

                            <input
                            type="text"
                                id="location"
                                name="location"
                                required
                                value="<?= e(
                                    $event["location"]
                                ) ?>"
                            >

                        </div>


                        <!-- DATE -->

                        <div class="form-group">

                            <label for="event_date">
                                Event Date *
                            </label>

                            <input
                                type="date"
                                id="event_date"
                                name="event_date"
                                required
                                value="<?= e(
                                    $event["event_date"]
                                ) ?>"
                            >

                        </div>


                        <!-- START TIME -->

                        <div class="form-group">

                            <label for="start_time">
                                Start Time *
                            </label>

                            <input
                                type="time"
                                id="start_time"
                                name="start_time"
                                required
                                value="<?= e(
                                    $event["start_time"]
                                ) ?>"
                            >

                        </div>


                        <!-- END TIME -->

                        <div class="form-group">

                            <label for="end_time">
                                End Time *
                            </label>

                            <input
                                type="time"
                                id="end_time"
                                name="end_time"
                                required
                                value="<?= e(
                                    $event["end_time"]
                                ) ?>"
                            >

                        </div>


                        <!-- REGISTRATION DEADLINE -->

                        <div class="form-group">

                            <label for="registration_deadline">
                                Registration Deadline
                            </label>

                            <input
                                type="date"
                                id="registration_deadline"
                                name="registration_deadline"
                                value="<?= e(
                                    $event["registration_deadline"]
                                ) ?>"
                            >

                        </div>


                        <!-- MAX CAPACITY -->

                        <div class="form-group">

                            <label for="max_capacity">
                                Maximum Capacity
                            </label>

                            <input
                                type="number"
                                id="max_capacity"
                                name="max_capacity"
                                min="1"
                                value="<?= e(
                                    $event["max_capacity"]
                                ) ?>"
                            >

                            <small>
                                Leave empty for unlimited capacity.
                            </small>

                        </div>


                       <!-- STATUS -->
<div class="form-group">
    <label for="status">
        Status
    </label>

    <select
        id="status"
        name="status"
    >
        <option
            value="draft"
            <?= $event["status"] === "draft" ? "selected" : "" ?>
        >
            Draft
        </option>

        <option
            value="published"
            <?= $event["status"] === "published" ? "selected" : "" ?>
        >
            Published
        </option>

        <option
            value="completed"
            <?= $event["status"] === "completed" ? "selected" : "" ?>
        >
            Completed
        </option>

        <option
            value="cancelled"
            <?= $event["status"] === "cancelled" ? "selected" : "" ?>
        >
            Cancelled
        </option>
    </select>
</div>

                    <!-- DESCRIPTION -->

                    <div class="form-group">

                        <label for="description">
                            Description
                        </label>

                        <textarea
                            id="description"
                            name="description"
                            rows="6"
                            class="form-textarea"
                        ><?= e(
                            $event["description"]
                        ) ?></textarea>

                    </div>

</div> <!-- End of form-grid -->        
                    <!-- BUTTONS -->

                    <div class="profile-form-actions">
    <button
        type="submit"
        class="primary-button"
    >
        Save Changes
    </button>

    <a
        href="view.php?id=<?= $eventId ?>"
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