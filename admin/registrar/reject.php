 <?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";

/*
|--------------------------------------------------------------------------
| Registrar Access
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "registrar") {
    header("Location: ../../index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Get Registration ID
|--------------------------------------------------------------------------
| GET is allowed here ONLY to display the rejection form.
| The actual rejection happens through POST below.
|--------------------------------------------------------------------------
*/

$registration_id = (int) ($_GET["id"] ?? $_POST["registration_id"] ?? 0);

if ($registration_id <= 0) {
    die("Invalid registration.");
}

/*
|--------------------------------------------------------------------------
| Get Registration
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        registration_id,
        status
    FROM alumni_registrations
    WHERE registration_id = ?
    LIMIT 1
");

if (!$stmt) {
    error_log(
        "Database prepare error in registrar/reject.php: " .
        $conn->error
    );

    die(
        "Unable to load the registration. " .
        "Please try again later."
    );
}

$stmt->bind_param("i", $registration_id);
$stmt->execute();

$result = $stmt->get_result();
$registration = $result->fetch_assoc();

$stmt->close();

if (!$registration) {
    die("Registration not found.");
}

/*
|--------------------------------------------------------------------------
| Make Sure Registration Is Still Pending
|--------------------------------------------------------------------------
*/

if ($registration["status"] !== "pending") {
    die("This registration has already been processed.");
}

/*
|--------------------------------------------------------------------------
| Process Rejection
|--------------------------------------------------------------------------
*/

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /*
    |--------------------------------------------------------------------------
    | CSRF Protection
    |--------------------------------------------------------------------------
    */

    if (!verify_csrf_token()) {
        $error = "Invalid security token. Please try again.";
    } else {

        /*
        |--------------------------------------------------------------------------
        | Get Rejection Reason
        |--------------------------------------------------------------------------
        */

        $reason = trim(
            $_POST["verification_notes"] ?? ""
        );

        if ($reason === "") {

            $error =
                "Please enter a reason for rejecting this registration.";

        } elseif (strlen($reason) > 1000) {

            $error =
                "The rejection reason must not exceed 1000 characters.";

        } else {

            /*
            |--------------------------------------------------------------------------
            | Update Registration
            |--------------------------------------------------------------------------
            */

            $stmt = $conn->prepare("
                UPDATE alumni_registrations
                SET
                    status = 'rejected',
                    verification_notes = ?,
                    verified_by = ?,
                    verified_at = NOW()
                WHERE registration_id = ?
                  AND status = 'pending'
            ");

            if (!$stmt) {

                error_log(
                    "Database prepare error in registrar/reject.php: " .
                    $conn->error
                );
 $error =
                    "Unable to reject the registration. " .
                    "Please try again later.";

            } else {

                $registrarId = (int) $_SESSION["user_id"];

                $stmt->bind_param(
                    "sii",
                    $reason,
                    $registrarId,
                    $registration_id
                );

                if (!$stmt->execute()) {

                    error_log(
                        "Database execute error in registrar/reject.php: " .
                        $stmt->error
                    );

                    $error =
                        "Unable to reject the registration. " .
                        "Please try again later.";

                } elseif ($stmt->affected_rows !== 1) {

                    $error =
                        "Registration could not be rejected. " .
                        "It may have already been processed.";

                } else {

                    $stmt->close();

                    /*
                    |--------------------------------------------------------------------------
                    | Audit Log
                    |--------------------------------------------------------------------------
                    */

                    logAudit(
                        $conn,
                        $registrarId,
                        "REJECT",
                        "alumni_registrations",
                        $registration_id,
                        "Registrar rejected alumni registration. " .
                        "Reason: " . $reason
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Redirect
                    |--------------------------------------------------------------------------
                    */

                    header(
                        "Location: pending.php?success=" .
                        urlencode(
                            "Registration rejected successfully."
                        )
                    );

                    exit;
                }

                if ($stmt) {
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
        Reject Registration |
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

</head>

<body class="admin-body">

<div class="admin-layout">

    <!-- Shared Registrar Sidebar -->

    <?php
    $activePage = "pending";
    require_once __DIR__ . "/includes/sidebar.php";
    ?>

    <!-- Main -->

    <main class="admin-main">

        <!-- Topbar -->

        <header class="admin-topbar">

            <div>

                <h1>
                    Reject Registration
                </h1>

                <p>
                    Provide a reason for rejecting this application.
                </p>

            </div>

            <div class="admin-user">

                <div class="admin-avatar">
                    R
                </div>

                <div>

                    <strong>
                        Registrar
                    </strong>

                    <small>
                        Registration Officer
                    </small>

                </div>

            </div>

        </header>

        <!-- Content -->

        <section class="dashboard-content">

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Rejection Reason
                        </h2>
<p>
                            Enter a clear reason explaining why
                            the applicant could not be verified.
                        </p>

                    </div>

                </div>

                <?php if ($error !== ""): ?>

                    <div class="form-alert error-message">

                        <?= e($error) ?>

                    </div>

                <?php endif; ?>


                <!-- Rejection Form -->

                <form method="POST">

                    <?= csrf_field() ?>

                    <input
                        type="hidden"
                        name="registration_id"
                        value="<?= $registration_id ?>"
                    >

                    <div class="form-field">

                        <label for="verification_notes">

                            Reason for Rejection *

                        </label>

                        <textarea
                            id="verification_notes"
                            name="verification_notes"
                            rows="6"
                            maxlength="1000"
                            required
                            placeholder="Example: The Alumni ID does not match the official college record."
                        ></textarea>

                    </div>

                    <div class="form-actions">

                        <a
                            href="view.php?id=<?= $registration_id ?>"
                            class="secondary-button"
                        >
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="primary-button"
                        >
                            Confirm Rejection
                        </button>

                    </div>

                </form>

            </div>

        </section>

    </main>

</div>

</body>

</html>