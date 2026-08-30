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
            die("Database error: " . $conn->error);
        }

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

            $stmt->close();
header("Location: index.php");
            exit;

        } else {

            $error = "Unable to create event: " . $stmt->error;

            $stmt->close();
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
    | EXISTING SIDEBAR INCLUDE
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