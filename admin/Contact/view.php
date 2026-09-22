 <?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";

/* -------------------------------------------------
   ADMIN ACCESS
------------------------------------------------- */

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "admin") {
    header("Location: ../../index.php");
    exit;
}

/* -------------------------------------------------
   GET INQUIRY ID
------------------------------------------------- */

$inquiry_id = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;

if ($inquiry_id <= 0) {
    die("Invalid inquiry ID.");
}

/* -------------------------------------------------
   PROCESS ADMIN ACTION
------------------------------------------------- */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";

    /* ---------------------------------------------
       MARK AS READ
    --------------------------------------------- */

    if ($action === "read") {

        $new_status = "read";

        $sql = "
            UPDATE contact_inquiries
            SET status = ?
            WHERE inquiry_id = ?
        ";

        $stmt = $conn->prepare($sql);

       if (!$stmt) {
    die("Unable to update the inquiry. Please try again later.");
}

        $stmt->bind_param(
            "si",
            $new_status,
            $inquiry_id
        );

        $stmt->execute();

        $stmt->close();

        header(
            "Location: view.php?id=" .
            $inquiry_id .
            "&success=read"
        );

        exit;
    }


    /* ---------------------------------------------
       RESPOND TO INQUIRY
    --------------------------------------------- */

    if ($action === "respond") {

        $admin_response = trim(
            $_POST["admin_response"] ?? ""
        );

        if ($admin_response === "") {

            $error =
                "Please enter a response.";

        } else {

            $new_status = "responded";

            $sql = "
                UPDATE contact_inquiries
                SET
                    admin_response = ?,
                    status = ?,
                    responded_at = NOW()
                WHERE inquiry_id = ?
            ";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                die(
                    "Database error: " .
                    $conn->error
                );
            }

            $stmt->bind_param(
                "ssi",
                $admin_response,
                $new_status,
                $inquiry_id
            );

            if ($stmt->execute()) {

                $stmt->close();

                header(
                    "Location: view.php?id=" .
                    $inquiry_id .
                    "&success=responded"
                );

                exit;

            } else {

                $error =
                    "Failed to save response: " .
                    $stmt->error;

                $stmt->close();
            }
        }
    }


    /* ---------------------------------------------
       CLOSE INQUIRY
    --------------------------------------------- */

    if ($action === "close") {

        $new_status = "closed";

        $sql = "
            UPDATE contact_inquiries
            SET status = ?
            WHERE inquiry_id = ?
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die("Database error: " . $conn->error);
        }

        $stmt->bind_param(
            "si",
            $new_status,
            $inquiry_id
        );

        if ($stmt->execute()) {

            $stmt->close();

            header(
                "Location: view.php?id=" .
                $inquiry_id .
                "&success=closed"
            );

            exit;

        } else {

            $error =
                "Failed to close inquiry: " .
                $stmt->error;

            $stmt->close();
        }
    }
}
 /* -------------------------------------------------
   GET INQUIRY
------------------------------------------------- */

$sql = "
    SELECT
        inquiry_id,
        name,
        email,
        subject,
        message,
        status,
        admin_response,
        created_at,
        responded_at
    FROM contact_inquiries
    WHERE inquiry_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param(
    "i",
    $inquiry_id
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Inquiry not found.");
}

$inquiry = $result->fetch_assoc();

$stmt->close();


/* -------------------------------------------------
   AUTOMATICALLY MARK NEW INQUIRY AS READ
------------------------------------------------- */

if (
    strtolower(
        trim(
            $inquiry["status"]
        )
    ) === "new"
) {

    $new_status = "read";

    $update_sql = "
        UPDATE contact_inquiries
        SET status = ?
        WHERE inquiry_id = ?
    ";

    $update_stmt =
        $conn->prepare($update_sql);

    if ($update_stmt) {

        $update_stmt->bind_param(
            "si",
            $new_status,
            $inquiry_id
        );

        $update_stmt->execute();

        $update_stmt->close();

        $inquiry["status"] = "read";
    }
}


/* -------------------------------------------------
   SUCCESS MESSAGE
------------------------------------------------- */

$success =
    $_GET["success"] ?? "";

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
        Inquiry Details |
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

    <style>

        .inquiry-header {

            display: flex;

            justify-content: space-between;

            align-items: flex-start;

            gap: 20px;

            margin-bottom: 25px;

        }

        .inquiry-header h2 {

            margin: 0 0 8px;

            color: #4a2c1d;

        }

        .inquiry-header p {

            margin: 0;

            color: #777;

        }

        .status-badge {

            display: inline-block;

            padding: 7px 12px;

            border-radius: 6px;

            font-size: 13px;

            font-weight: 700;

            text-transform: capitalize;

        }

        .status-new {

            background: #fff3cd;

            color: #856404;

        }

        .status-read {

            background: #e8f0fe;

            color: #2457a6;

        }

        .status-responded {

            background: #e8f5e9;

            color: #2e7d32;

        }

        .status-closed {

            background: #eeeeee;

            color: #555;

        }

        .inquiry-info {

            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 18px;

        }

        .info-item {

            padding: 15px;

            background: #faf8f6;

            border-radius: 8px;

        }

        .info-item span {

            display: block;

            color: #777;

            font-size: 13px;

            margin-bottom: 5px;

        }

        .info-item strong {

            color: #333;

        }

        .message-box {

            padding: 18px;

            background: #faf8f6;

            border-radius: 8px;

            line-height: 1.7;

            color: #444;

            white-space: normal;

        }

        .response-box {

            padding: 18px;

            background: #f3ebe5;

            border-left: 4px solid #7a4b2a;

            border-radius: 8px;

            line-height: 1.7;

            color: #4a2c1d;

        }

        .response-form textarea {

            width: 100%;

            min-height: 180px;

            box-sizing: border-box;

            padding: 13px;

            border: 1px solid #ddd;
 border-radius: 8px;

            font-family: inherit;

            font-size: 14px;

            resize: vertical;

        }

        .response-form textarea:focus {

            outline: none;

            border-color: #8b5e3c;

        }

        .action-buttons {

            display: flex;

            gap: 12px;

            flex-wrap: wrap;

            margin-top: 20px;

        }

        .action-buttons button {

            border: none;

            padding: 11px 18px;

            border-radius: 7px;

            font-weight: 600;

            cursor: pointer;

        }

        .respond-button {

            background: #7a4b2a;

            color: white;

        }

        .respond-button:hover {

            background: #5f3921;

        }

        .close-button {

            background: #555;

            color: white;

        }

        .close-button:hover {

            background: #333;

        }

        .back-button {

            display: inline-block;

            padding: 10px 16px;

            border: 1px solid #8b5e3c;

            border-radius: 7px;

            color: #8b5e3c;

            text-decoration: none;

            font-weight: 600;

        }

        .back-button:hover {

            background: #8b5e3c;

            color: white;

        }

        .alert {

            padding: 14px 16px;

            border-radius: 8px;

            margin-bottom: 20px;

        }

        .alert-success {

            background: #e8f5e9;

            color: #2e7d32;

            border: 1px solid #c8e6c9;

        }

        .alert-error {

            background: #ffebee;

            color: #b71c1c;

            border: 1px solid #ffcdd2;

        }

        @media (max-width: 700px) {

            .inquiry-header {

                flex-direction: column;

            }

            .inquiry-info {

                grid-template-columns: 1fr;

            }

        }

    </style>

</head>

<body class="admin-body">

<div class="admin-layout">


    <!-- =================================================
         SIDEBAR
    ================================================== -->

     <?php require_once __DIR__ ."/../includes/sidebar.php"; ?>
 <!-- =================================================
         MAIN CONTENT
    ================================================== -->

    <main class="admin-main">


        <header class="admin-topbar">

            <div>

                <h1>
                    Inquiry Details
                </h1>

                <p>
                    View and respond to this contact inquiry.
                </p>

            </div>

            <a
                href="index.php"
                class="back-button"
            >
                ← Back
            </a>

        </header>


        <section class="dashboard-content">


            <!-- =================================================
                 MESSAGES
            ================================================== -->

            <?php if ($success === "read"): ?>

                <div class="alert alert-success">
                    Inquiry marked as read.
                </div>

            <?php elseif ($success === "responded"): ?>

                <div class="alert alert-success">
                    Response saved successfully.
                </div>

            <?php elseif ($success === "closed"): ?>

                <div class="alert alert-success">
                    Inquiry closed successfully.
                </div>

            <?php endif; ?>


            <?php if (!empty($error)): ?>

                <div class="alert alert-error">
                    <?= e($error) ?>
                </div>

            <?php endif; ?>


            <!-- =================================================
                 BASIC INFORMATION
            ================================================== -->

            <div class="dashboard-panel">

                <div class="inquiry-header">

                    <div>

                        <h2>
                            <?= e(
                                $inquiry["subject"]
                            ) ?>
                        </h2>

                        <p>
                            From:
                            <strong>
                                <?= e(
                                    $inquiry["name"]
                                ) ?>
                            </strong>
                        </p>

                    </div>


                    <div>

                        <?php

                        $status =
                            strtolower(
                                trim(
                                    $inquiry["status"]
                                )
                            );

                        ?>

                        <span
                            class="
                                status-badge
                                status-<?= e($status) ?>
                            "
                        >

                            <?= e(
                                $inquiry["status"]
                            ) ?>

                        </span>

                    </div>

                </div>


                <div class="inquiry-info">

                    <div class="info-item">

                        <span>
                            Name
                        </span>

                        <strong>
                            <?= e(
                                $inquiry["name"]
                            ) ?>
                        </strong>

                    </div>


                    <div class="info-item">

                        <span>
                            Email
                        </span>

                        <strong>
                            <?= e(
                                $inquiry["email"]
                            ) ?>
                        </strong>

                    </div>


                    <div class="info-item">

                        <span>
                            Created At
                        </span>
<strong>
                            <?= e(
                                $inquiry["created_at"]
                            ) ?>
                        </strong>

                    </div>


                    <div class="info-item">

                        <span>
                            Responded At
                        </span>

                        <strong>

                            <?php

                            if (
                                empty(
                                    $inquiry["responded_at"]
                                )
                            ) {

                                echo "Not responded";

                            } else {

                                echo e(
                                    $inquiry["responded_at"]
                                );

                            }

                            ?>

                        </strong>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 MESSAGE
            ================================================== -->

            <div class="dashboard-panel">

                <h2>
                    Visitor Message
                </h2>

                <div class="message-box">

                    <?= nl2br(
                        e(
                            $inquiry["message"]
                        )
                    ) ?>

                </div>

            </div>


            <!-- =================================================
                 ADMIN RESPONSE
            ================================================== -->

            <?php if (
                !empty(
                    $inquiry["admin_response"]
                )
            ): ?>

                <div class="dashboard-panel">

                    <h2>
                        Admin Response
                    </h2>

                    <div class="response-box">

                        <?= nl2br(
                            e(
                                $inquiry["admin_response"]
                            )
                        ) ?>

                    </div>

                </div>

            <?php endif; ?>


            <!-- =================================================
                 RESPONSE FORM
            ================================================== -->

            <?php if (
                $status !== "closed"
            ): ?>

                <div class="dashboard-panel">

                    <h2>
                        <?= $status === "responded"
                            ? "Update Response"
                            : "Respond to Inquiry"
                        ?>
                    </h2>

                    <p>
                        Write a response to the visitor.
                    </p>

                    <form
                        method="POST"
                        class="response-form"
                    >

                        <textarea
                            name="admin_response"
                            placeholder="Write your response here..."
                            required
                        ><?= e(
                            $inquiry["admin_response"]
                            ?? ""
                        ) ?></textarea>


                        <div class="action-buttons">

                            <button
                                type="submit"
                                name="action"
                                value="respond"
                                class="respond-button"
                            >
                                Send Response
                            </button>

                        </div>

                    </form>

                </div>

            <?php endif; ?> <!-- =================================================
                 CLOSE INQUIRY
            ================================================== -->

            <?php if (
                $status === "responded"
            ): ?>

                <div class="dashboard-panel">

                    <h2>
                        Close Inquiry
                    </h2>

                    <p>
                        Close this inquiry after the
                        matter has been completed.
                    </p>

                    <form method="POST">

                        <button
                            type="submit"
                            name="action"
                            value="close"
                            class="close-button"
                            onclick="
                                return confirm(
                                    'Are you sure you want to close this inquiry?'
                                );
                            "
                        >
                            Close Inquiry
                        </button>

                    </form>

                </div>

            <?php endif; ?>


        </section>

    </main>

</div>

</body>

</html>