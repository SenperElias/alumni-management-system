 <?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "registrar") {
    header("Location: ../../index.php");
    exit;
}

$registration_id = (int) ($_GET["id"] ?? 0);

if ($registration_id <= 0) {
    die("Invalid registration.");
}

/*
|--------------------------------------------------------------------------
| Get Registration
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT registration_id, status
     FROM alumni_registrations
     WHERE registration_id = ?
     LIMIT 1"
);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $registration_id);
$stmt->execute();

$result = $stmt->get_result();

$registration = $result->fetch_assoc();

$stmt->close();

if (!$registration) {
    die("Registration not found.");
}

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

    $reason = trim($_POST["verification_notes"] ?? "");

    if ($reason === "") {

        $error = "Please enter a reason for rejecting this registration.";

    } else {

        $stmt = $conn->prepare(
            "UPDATE alumni_registrations
             SET
                status = 'rejected',
                verification_notes = ?,
                verified_by = ?,
                verified_at = NOW()
             WHERE registration_id = ?"
        );

        if (!$stmt) {
            die("Database error: " . $conn->error);
        }

        $stmt->bind_param(
            "sii",
            $reason,
            $_SESSION["user_id"],
            $registration_id
        );

        if ($stmt->execute()) {

            $stmt->close();

            header(
                "Location: pending.php?success="
                . urlencode("Registration rejected successfully.")
            );

            exit;
        }

        $error = "Unable to reject registration: " . $stmt->error;

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

    <!-- Sidebar -->

    <aside class="admin-sidebar">

        <div class="admin-brand">

            <div class="brand-logo">
                TM
            </div>

            <div>
                <strong>Alumni System</strong>
                <small>Registrar Panel</small>
            </div>

        </div>

        <nav class="admin-nav">

            <a href="dashboard.php">
                Dashboard
            </a>

            <div class="nav-section">
                REGISTRATION
            </div>

            <a href="pending.php">
                Pending Registrations
            </a>

            <a href="approved.php">
                Approved Registrations
            </a>

            <a href="rejected.php">
                Rejected Registrations
            </a>

            <div class="nav-section">
                SYSTEM
            </div>

            <a
                href="../../auth/logout.php"
                class="logout-link"
            >
                Logout
            </a>

        </nav>

    </aside>


    <!-- Main -->

    <main class="admin-main">

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


                <form method="POST">

                    <div class="form-field">

                        <label for="verification_notes">
                            Reason for Rejection *
                        </label>

                        <textarea
                            id="verification_notes"
                            name="verification_notes"
                            rows="6"
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