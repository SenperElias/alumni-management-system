 <?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";

/*|--------------------------------------------------------------------------
| ALUMNI ACCESS
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

$user_id = (int) $_SESSION["user_id"];

/*|--------------------------------------------------------------------------
| GET LOGGED-IN ALUMNI ID
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        alumni_id
    FROM alumni
    WHERE user_id = ?
    LIMIT 1
");

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$alumni = $result->fetch_assoc();

$stmt->close();

if (!$alumni) {
    die("Alumni profile not found.");
}

$alumni_id = (int) $alumni["alumni_id"];

/*|--------------------------------------------------------------------------
| CHECK IF LOGGED-IN ALUMNI IS A MENTOR
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        mentor_profile_id,
        alumni_id
    FROM mentor_profiles
    WHERE alumni_id = ?
      AND LOWER(TRIM(status)) = 'active'
    LIMIT 1
");

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $alumni_id);
$stmt->execute();

$result = $stmt->get_result();
$mentor = $result->fetch_assoc();

$stmt->close();

/*
 * mentorship_requests.mentor_id currently stores alumni_id,
 * so we use the mentor's alumni_id here.
 */

$mentor_profile_id = $mentor
    ? (int) $mentor["alumni_id"]
    : 0;

/*|--------------------------------------------------------------------------
| HANDLE ACCEPT / REJECT
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $request_id = (int) ($_POST["request_id"] ?? 0);
    $action = $_POST["action"] ?? "";

    if ($request_id <= 0) {
        die("Invalid request.");
    }

    if ($action !== "accept" && $action !== "reject") {
        die("Invalid action.");
    }

    if ($mentor_profile_id <= 0) {
        die("You are not an active mentor.");
    }

    /*
     * Determine new status.
     */

    $new_status = ($action === "accept")
        ? "Accepted"
        : "Rejected";

    /*----------------------------------------------------------------------
    | GET THE REQUEST + MENTEE INFORMATION
    ----------------------------------------------------------------------
    |
    | We need the mentee's user_id so the notification goes to
    | the correct account.
    |
    */

    $stmt = $conn->prepare("
        SELECT
            mr.request_id,
            mr.mentee_id,
            a.user_id,
            a.first_name,
            a.last_name
        FROM mentorship_requests mr
        INNER JOIN alumni a
            ON mr.mentee_id = a.alumni_id
        WHERE mr.request_id = ?
          AND mr.mentor_id = ?
          AND LOWER(TRIM(mr.status)) = 'pending'
        LIMIT 1
    ");

    if (!$stmt) {
        die("Database error: " . $conn->error);
    }

    $stmt->bind_param(
        "ii",
        $request_id,
        $mentor_profile_id
    );

    $stmt->execute();

    $result = $stmt->get_result();
    $mentee = $result->fetch_assoc();

    $stmt->close();

    if (!$mentee) {
        die(
            "Request not found, already responded to, " .
            "or this request does not belong to you."
        );
    }

    /*----------------------------------------------------------------------
    | UPDATE REQUEST STATUS
    ----------------------------------------------------------------------
    */
 $stmt = $conn->prepare("
        UPDATE mentorship_requests
        SET
            status = ?,
            responded_at = NOW()
        WHERE request_id = ?
          AND mentor_id = ?
          AND LOWER(TRIM(status)) = 'pending'
    ");

    if (!$stmt) {
        die("Database error: " . $conn->error);
    }

    $stmt->bind_param(
        "sii",
        $new_status,
        $request_id,
        $mentor_profile_id
    );

    if (!$stmt->execute()) {
        die("Unable to update request: " . $stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        $stmt->close();

        die(
            "No request was updated. " .
            "The request may already have been responded to."
        );
    }

    $stmt->close();

    /*----------------------------------------------------------------------
    | NOTIFY MENTEE
    ----------------------------------------------------------------------
    */

    if ($action === "accept") {

        $notificationTitle = "Mentorship Request Accepted";

        $notificationMessage =
            "Your mentorship request has been accepted. " .
            "You can now connect with your mentor.";

    } else {

        $notificationTitle = "Mentorship Request Rejected";

        $notificationMessage =
            "Your mentorship request has been rejected.";

    }

    $notificationType = "mentorship";

    $notifyStmt = $conn->prepare("
        INSERT INTO notifications
        (
            user_id,
            title,
            message,
            type,
            opportunity_id,
            event_id,
            is_read
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            NULL,
            NULL,
            0
        )
    ");

    if ($notifyStmt) {

        $mentee_user_id = (int) $mentee["user_id"];

        $notifyStmt->bind_param(
            "isss",
            $mentee_user_id,
            $notificationTitle,
            $notificationMessage,
            $notificationType
        );

        $notifyStmt->execute();

        $notifyStmt->close();
    }

/*|--------------------------------------------------------------------------
| REDIRECT
|--------------------------------------------------------------------------
*/

    header("Location: received-requests.php");
    exit;
}

/*|--------------------------------------------------------------------------
| GET REQUESTS RECEIVED BY THIS MENTOR
|--------------------------------------------------------------------------
*/

$received_requests = [];

if ($mentor_profile_id > 0) {

    $stmt = $conn->prepare("
        SELECT
            mr.request_id,
            mr.mentor_id,
            mr.mentee_id,
            mr.message,
            mr.status,
            mr.created_at,
            mr.responded_at,
            a.first_name,
            a.last_name,
            a.college_id_number
        FROM mentorship_requests mr
        INNER JOIN alumni a
            ON mr.mentee_id = a.alumni_id
        WHERE mr.mentor_id = ?
        ORDER BY mr.created_at DESC
    ");

    if (!$stmt) {
        die("Database error: " . $conn->error);
    }

    $stmt->bind_param(
        "i",
        $mentor_profile_id
    );

    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $received_requests[] = $row;
    }

    $stmt->close();
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
        Received Mentorship Requests |
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

    <style>

        .requests-wrapper {
            max-width: 1100px;
            margin: 40px auto;
            padding: 20px;
        }
 .requests-panel {
            background: #ffffff;
            border: 1px solid #eeeeee;
            border-radius: 14px;
            padding: 25px;
            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.05);
        }

        .requests-panel h2 {
            color: #4a2c1d;
            margin-top: 0;
            margin-bottom: 25px;
        }

        .request-card {
            border: 1px solid #eeeeee;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 18px;
            background: #ffffff;
        }

        .request-card:last-child {
            margin-bottom: 0;
        }

        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 15px;
        }

        .person-name {
            color: #4a2c1d;
            font-size: 19px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .person-info {
            color: #7a4b2a;
            font-weight: 600;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-accepted {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-rejected {
            background: #ffebee;
            color: #c62828;
        }

        .request-message {
            background: #f8f5f2;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            color: #555555;
            line-height: 1.6;
        }

        .request-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .meta-label {
            display: block;
            color: #888888;
            font-size: 12px;
            margin-bottom: 4px;
        }

        .meta-value {
            color: #4a2c1d;
            font-weight: 600;
        }

        .request-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eeeeee;
        }

        .accept-button,
        .reject-button {
            border: none;
            border-radius: 8px;
            padding: 10px 18px;
            font-weight: 600;
            cursor: pointer;
        }

        .accept-button {
            background: #2e7d32;
            color: #ffffff;
        }

        .reject-button {
            background: #c62828;
            color: #ffffff;
        }

        .no-requests {
            text-align: center;
            padding: 45px 20px;
            color: #777777;
        }

        .no-requests h3 {
            color: #4a2c1d;
        }

        @media (max-width: 650px) {

            .request-header {
                flex-direction: column;
            }

            .request-meta {
                grid-template-columns: 1fr;
            }

            .request-actions {
                flex-direction: column;
            }

            .accept-button,
            .reject-button {
                width: 100%;
            }

        }

    </style>

</head>

<body class="admin-body">

<div class="admin-layout">

    <?php

    $currentPage = "received-requests.php";

    require_once __DIR__ . "/../includes/sidebar.php";

    ?>

    <main class="admin-main">

        <header class="admin-topbar">

            <div>

                <h1>
                    Received Mentorship Requests
                </h1>

                <p>
                    View and manage mentorship requests sent to you.
                </p>

            </div>

        </header>

        <section class="dashboard-content">

            <div class="requests-wrapper">

                <div class="requests-panel">
 <h2>
                        Requests Received
                    </h2>

                    <?php if ($mentor_profile_id <= 0): ?>

                        <div class="no-requests">

                            <h3>
                                You Are Not an Active Mentor
                            </h3>

                            <p>
                                You need an active mentor profile
                                to receive mentorship requests.
                            </p>

                        </div>

                    <?php elseif (count($received_requests) === 0): ?>

                        <div class="no-requests">

                            <h3>
                                No Requests Received
                            </h3>

                            <p>
                                You currently have no mentorship requests.
                            </p>

                        </div>

                    <?php else: ?>

                        <?php foreach ($received_requests as $request): ?>

                            <?php

                            $status = strtolower(
                                trim($request["status"] ?? "")
                            );

                            $statusClass = "status-pending";

                            if ($status === "accepted") {
                                $statusClass = "status-accepted";
                            } elseif ($status === "rejected") {
                                $statusClass = "status-rejected";
                            }

                            ?>

                            <div class="request-card">

                                <div class="request-header">

                                    <div>

                                        <div class="person-name">

                                            <?= e($request["first_name"]) ?>

                                            <?= e($request["last_name"]) ?>

                                        </div>

                                        <div class="person-info">

                                            Alumni ID:

                                            <?= e(
                                                $request["college_id_number"]
                                            ) ?>

                                        </div>

                                    </div>

                                    <span
                                        class="status-badge <?= e($statusClass) ?>"
                                    >

                                        <?= e(
                                            ucfirst($status)
                                        ) ?>

                                    </span>

                                </div>

                                <div class="request-message">

                                    <?= nl2br(
                                        e($request["message"])
                                    ) ?>

                                </div>

                                <div class="request-meta">

                                    <div>

                                        <span class="meta-label">
                                            Request Sent
                                        </span>

                                        <span class="meta-value">

                                            <?= e(
                                                $request["created_at"]
                                            ) ?>

                                        </span>

                                    </div>

                                    <div>

                                        <span class="meta-label">
                                            Responded
                                        </span>

                                        <span class="meta-value">
<?php if (
                                                !empty(
                                                    $request["responded_at"]
                                                )
                                            ): ?>

                                                <?= e(
                                                    $request["responded_at"]
                                                ) ?>

                                            <?php else: ?>

                                                Not yet

                                            <?php endif; ?>

                                        </span>

                                    </div>

                                </div>

                                <?php if ($status === "pending"): ?>

                                    <div class="request-actions">

                                        <form
                                            method="POST"
                                            action="received-requests.php"
                                            onsubmit="return confirm('Accept this mentorship request?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="request_id"
                                                value="<?= (int) $request["request_id"] ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="accept"
                                            >

                                            <button
                                                type="submit"
                                                class="accept-button"
                                            >
                                                Accept
                                            </button>

                                        </form>

                                        <form
                                            method="POST"
                                            action="received-requests.php"
                                            onsubmit="return confirm('Reject this mentorship request?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="request_id"
                                                value="<?= (int) $request["request_id"] ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="reject"
                                            >

                                            <button
                                                type="submit"
                                                class="reject-button"
                                            >
                                                Reject
                                            </button>

                                        </form>

                                    </div>

                                <?php endif; ?>

                            </div>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>

            </div>

        </section>

    </main>

</div>

</body>

</html>