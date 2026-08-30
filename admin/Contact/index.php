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
   GET INQUIRIES
------------------------------------------------- */

$sql = "
    SELECT
        inquiry_id,
        name,
        email,
        subject,
        status,
        created_at,
        responded_at
    FROM contact_inquiries
    ORDER BY created_at DESC
";

$result = $conn->query($sql);

if (!$result) {
    die("Unable to load contact inquiries. Please try again later.");
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
        Contact Inquiries |
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

    <style>

        .inquiries-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 25px;
        }

        .inquiries-header h2 {
            margin: 0 0 8px;
            color: #4a2c1d;
        }

        .inquiries-header p {
            margin: 0;
            color: #777;
        }

        .table-wrapper {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #eeeeee;
            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.05);
            overflow-x: auto;
        }

        .inquiries-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
        }

        .inquiries-table th {
            background: #f8f5f2;
            color: #4a2c1d;
            text-align: left;
            padding: 15px;
            font-size: 13px;
            border-bottom: 1px solid #eeeeee;
        }

        .inquiries-table td {
            padding: 15px;
            border-bottom: 1px solid #eeeeee;
            color: #555;
            font-size: 14px;
        }

        .inquiries-table tr:last-child td {
            border-bottom: none;
        }

        .inquiries-table tr:hover {
            background: #fcfaf8;
        }

        .subject-cell {
            font-weight: 600;
            color: #4a2c1d;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 12px;
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
            color: #555555;
        }

        .view-button {
            display: inline-block;
            padding: 8px 13px;
            border-radius: 7px;
            background: #7a4b2a;
            color: #ffffff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        .view-button:hover {
            background: #5f3921;
        }

        .empty-state {
            padding: 50px 20px;
            text-align: center;
            color: #777;
        }

        .empty-state h3 {
            margin-bottom: 8px;
            color: #4a2c1d;
        }

        @media (max-width: 700px) {

            .inquiries-header {
                flex-direction: column;
            }

        }

    </style>

</head>

<body class="admin-body">

<div class="admin-layout">
 <!-- =================================================
         SIDEBAR
    ================================================== -->

    <?php
    require_once __DIR__ . "/../includes/sidebar.php";
    ?>


    <!-- =================================================
         MAIN CONTENT
    ================================================== -->

    <main class="admin-main">

        <header class="admin-topbar">

            <div>

                <h1>
                    Contact Inquiries
                </h1>

                <p>
                    Review and respond to messages
                    submitted through the public website.
                </p>

            </div>

        </header>


        <section class="dashboard-content">

            <div class="inquiries-header">

                <div>

                    <h2>
                        Visitor Messages
                    </h2>

                    <p>
                        Manage contact inquiries and
                        track their response status.
                    </p>

                </div>

            </div>


            <div class="table-wrapper">

                <?php if ($result->num_rows > 0): ?>

                    <table class="inquiries-table">

                        <thead>

                            <tr>

                                <th>
                                    Name
                                </th>


                                <th>
                                    Inquiry ID
                                </th>

                                <th>
                                    Email
                                </th>

                                <th>
                                    Subject
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Date
                                </th>

                                <th>
                                    Action
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php while (
                            $inquiry = $result->fetch_assoc()
                        ): ?>
 <?php

                            $status =
                                strtolower(
                                    trim(
                                        $inquiry["status"]
                                    )
                                );

                            ?>

                            
                               <tr>
    <td>
        #<?= (int) $inquiry["inquiry_id"] ?>
    </td>

    <td>
        <?= e(
            $inquiry["name"]
        ) ?>
    </td>

                                <td>
                                    <?= e(
                                        $inquiry["email"]
                                    ) ?>
                                </td>

                                <td class="subject-cell">

                                    <?= e(
                                        $inquiry["subject"]
                                    ) ?>

                                </td>

                                <td>

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

                                </td>

                                <td>

                                    <?= e(
                                        $inquiry["created_at"]
                                    ) ?>

                                </td>

                                <td>

                                    <a
                                        href="view.php?id=<?= (int) $inquiry["inquiry_id"] ?>"
                                        class="view-button"
                                    >
                                        View
                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                        </tbody>

                    </table>

                <?php else: ?>

                    <div class="empty-state">

                        <h3>
                            No Contact Inquiries
                        </h3>

                        <p>
                            There are currently no messages
                            submitted through the contact form.
                        </p>

                    </div>

                <?php endif; ?>

            </div>

        </section>

    </main>

</div>

</body>

</html>