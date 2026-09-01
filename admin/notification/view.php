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

$adminUserId = (int) $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| GET NOTIFICATION ID
|--------------------------------------------------------------------------
*/

$notificationId = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;

if ($notificationId <= 0) {
    header("Location: ../dashboard.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| GET NOTIFICATION
|--------------------------------------------------------------------------
|
| Important:
| We make sure the notification belongs to the
| currently logged-in admin.
|
*/

$stmt = $conn->prepare("
    SELECT
        notification_id,
        title,
        message,
        type,
        is_read,
        created_at
    FROM notifications
    WHERE notification_id = ?
      AND user_id = ?
    LIMIT 1
");

if (!$stmt) {
    header("Location: ../dashboard.php");
    exit;
}

$stmt->bind_param(
    "ii",
    $notificationId,
    $adminUserId
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {

    $stmt->close();

    header("Location: ../dashboard.php");
    exit;
}

$notification = $result->fetch_assoc();

$stmt->close();

/*
|--------------------------------------------------------------------------
| MARK AS READ
|--------------------------------------------------------------------------
*/

if ((int) $notification["is_read"] === 0) {

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

/*
|--------------------------------------------------------------------------
| DETERMINE DESTINATION
|--------------------------------------------------------------------------
*/

$type = strtolower(
    trim(
        $notification["type"] ?? ""
    )
);

switch ($type) {

    /*
    |--------------------------------------------------------------------------
    | MENTORSHIP
    |--------------------------------------------------------------------------
    */

    case "mentorship":
    case "mentor":
    case "mentors":

        header(
            "Location: ../mentorship/index.php"
        );

        exit;


    /*
    |--------------------------------------------------------------------------
    | OPPORTUNITIES
    |--------------------------------------------------------------------------
    */

    case "opportunity":
    case "opportunities":

        header(
            "Location: ../opportunities/index.php"
        );

        exit;


    /*
    |--------------------------------------------------------------------------
    | EMPLOYMENT
    |--------------------------------------------------------------------------
    */

    case "employment":

        header(
            "Location: ../alumni/index.php"
        );

        exit;


    /*
    |--------------------------------------------------------------------------
    | ALUMNI
    |--------------------------------------------------------------------------
    */

    case "alumni":

        header(
            "Location: ../alumni/index.php"
        );

        exit;


    /*
    |--------------------------------------------------------------------------
    | DEFAULT
    |--------------------------------------------------------------------------
    */

    default:
 header(
            "Location: ../dashboard.php"
        );

        exit;
}

?>