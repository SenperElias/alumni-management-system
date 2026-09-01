 <?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";

/*
|--------------------------------------------------------------------------
| ADMIN ACCESS
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
| VARIABLES
|--------------------------------------------------------------------------
*/

$error = "";

/*
|--------------------------------------------------------------------------
| CREATE EVENT
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /*
    |--------------------------------------------------------------------------
    | CSRF VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        !isset($_POST["csrf_token"]) ||
        !verify_csrf_token($_POST["csrf_token"])
    ) {
        $error = "Invalid security token. Please try again.";
    } else {

        /*
        |--------------------------------------------------------------------------
        | GET FORM DATA
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        if (
            $title === "" ||
            $eventDate === "" ||
            $startTime === "" ||
            $endTime === "" ||
            $location === ""
        ) {

            $error = "Please fill in all required fields.";

        } elseif ($eventDate < date("Y-m-d")) {

            $error = "Event date cannot be in the past.";

        } elseif ($endTime <= $startTime) {

            $error = "End time must be later than start time.";

        } elseif (
            $registrationDeadline !== "" &&
            $registrationDeadline > $eventDate
        ) {

            $error = "Registration deadline cannot be after the event date.";

        } elseif (
            $maxCapacity !== "" &&
            (int) $maxCapacity < 1
        ) {

            $error = "Maximum capacity must be at least 1.";

        } else {

            /*
            |--------------------------------------------------------------------------
            | MAX CAPACITY
            |--------------------------------------------------------------------------
            */

            $maxCapacityValue =
                $maxCapacity === ""
                    ? null
                    : (int) $maxCapacity;

            /*
            |--------------------------------------------------------------------------
            | EVENT STATUS
            |--------------------------------------------------------------------------
            |
            | Admin-created events are published immediately.
            |
            | Database ENUM:
            | draft
            | published
            | completed
            | cancelled
            |
            */

            $status = "published";
 /*
            |--------------------------------------------------------------------------
            | CREATED BY
            |--------------------------------------------------------------------------
            */

            $createdBy = (int) $_SESSION["user_id"];

            /*
            |--------------------------------------------------------------------------
            | INSERT EVENT
            |--------------------------------------------------------------------------
            */

            $stmt = $conn->prepare("
                INSERT INTO events (
                    title,
                    description,
                    event_date,
                    start_time,
                    end_time,
                    location,
                    registration_deadline,
                    max_capacity,
                    status,
                    created_by
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            if (!$stmt) {

                $error = "Database error: " . $conn->error;

            } else {

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
                    $createdBy
                );

                /*
                |--------------------------------------------------------------------------
                | EXECUTE
                |--------------------------------------------------------------------------
                */

                if ($stmt->execute()) {

                    /*
                    |--------------------------------------------------------------------------
                    | GET NEW EVENT ID
                    |--------------------------------------------------------------------------
                    */

                    $eventId = $stmt->insert_id;

                    $stmt->close();

                    /*
                    |--------------------------------------------------------------------------
                    | NOTIFY ACTIVE ALUMNI
                    |--------------------------------------------------------------------------
                    */

                    $notificationTitle = "New Event";

                    $notificationMessage =
                        'A new event "' .
                        $title .
                        '" has been published.';

                    $notificationType = "event";

                    $notifyStmt = $conn->prepare("
                        INSERT INTO notifications (
                            user_id,
                            title,
                            message,
                            type,
                            opportunity_id,
                            event_id,
                            is_read
                        )
                        SELECT
                            user_id,
                            ?,
                            ?,
                            ?,
                            NULL,
                            ?,
                            0
                        FROM users
                        WHERE role = 'alumni'
                          AND account_status = 'active'
                    ");

                    if ($notifyStmt) {

                        $notifyStmt->bind_param(
                            "sssi",
                            $notificationTitle,
                            $notificationMessage,
                            $notificationType,
                            $eventId
                        );

                        $notifyStmt->execute();
                        $notifyStmt->close();
                    }
 /*
                    |--------------------------------------------------------------------------
                    | REDIRECT
                    |--------------------------------------------------------------------------
                    */

                    header("Location: index.php");
                    exit;

                } else {

                    $error =
                        "Unable to create event: " .
                        $stmt->error;

                    $stmt->close();
                }
            }
        }
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
        Add Event | <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

</head>

<body class="admin-body">

<div class="admin-layout">

    <!--
    |--------------------------------------------------------------------------
    | SIDEBAR
    |--------------------------------------------------------------------------
    -->

    <?php

    $currentPage = "events";

    require_once __DIR__ . "/../includes/sidebar.php";

    ?>

    <!--
    |--------------------------------------------------------------------------
    | MAIN CONTENT
    |--------------------------------------------------------------------------
    -->

    <main class="admin-main">

        <!-- TOP BAR -->

        <header class="admin-topbar">

            <div>

                <h1>
                    Add Event
                </h1>

                <p>
                    Create a new college event.
                </p>

            </div>

        </header>


        <!-- CONTENT -->

        <section class="dashboard-content">

            <?php if ($error !== ""): ?>

                <div class="error-message">

                    <?= e($error) ?>

                </div>

            <?php endif; ?>


            <div class="dashboard-panel">

                <!-- PANEL HEADER -->

                <div class="panel-header">

                    <div>

                        <h2>
                            Event Information
                        </h2>

                        <p>
                            Enter the details of the event.
                        </p>

                    </div>

                </div>


                <!-- FORM -->

                <form method="POST">

                    <?= csrf_field() ?>


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
                                    $_POST["title"] ?? ""
                                ) ?>"
                                placeholder="e.g. Alumni Career Day"
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
                                    $_POST["location"] ?? ""
                                ) ?>"
                                placeholder="e.g. Main Hall"
                            >

                        </div>


                        <!-- EVENT DATE -->
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
                                    $_POST["event_date"] ?? ""
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
                                    $_POST["start_time"] ?? ""
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
                                    $_POST["end_time"] ?? ""
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
                                    $_POST["registration_deadline"] ?? ""
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
                                    $_POST["max_capacity"] ?? ""
                                ) ?>"
                                placeholder="Leave empty for unlimited"
                            >

                            <small>
                                Leave empty if there is no capacity limit.
                            </small>

                        </div>

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
                            placeholder="Describe the event..."
                        ><?= e(
                            $_POST["description"] ?? ""
                        ) ?></textarea>

                    </div>


                    <!-- BUTTONS -->

                    <div class="profile-form-actions">

                        <a
                            href="index.php"
                            class="secondary-button"
                        >
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="primary-button"
                        >
                            Create Event
                        </button>

                    </div>

                </form>

            </div>

        </section>

    </main>

</div>

</body>

</html>