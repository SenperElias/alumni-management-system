 <?php
session_start();

require_once "../../config/database.php";
require_once "../../includes/functions.php";

/*
|--------------------------------------------------------------------------
| Authorization
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "system_admin") {
    header("Location: ../../index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/
$search = trim($_GET["search"] ?? "");

if ($search !== "") {

    $searchTerm = "%" . $search . "%";

    $stmt = $conn->prepare("
        SELECT
            a.audit_id,
            a.user_id,
            a.action,
            a.table_name,
            a.record_id,
            a.description,
            a.created_at,
            u.email,
            u.role
        FROM audit_logs a
        LEFT JOIN users u
            ON a.user_id = u.user_id
        WHERE
            u.email LIKE ?
            OR a.action LIKE ?
            OR a.table_name LIKE ?
            OR a.description LIKE ?
        ORDER BY a.created_at DESC
    ");

    $stmt->bind_param(
        "ssss",
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm
    );

} else {

    $stmt = $conn->prepare("
        SELECT
            a.audit_id,
            a.user_id,
            a.action,
            a.table_name,
            a.record_id,
            a.description,
            a.created_at,
            u.email,
            u.role
        FROM audit_logs a
        LEFT JOIN users u
            ON a.user_id = u.user_id
        ORDER BY a.created_at DESC
    ");
}

$stmt->execute();
$result = $stmt->get_result();

/*
|--------------------------------------------------------------------------
| Role Labels
|--------------------------------------------------------------------------
*/
$roleLabels = [
    "admin" => "Alumni President",
    "alumni" => "Alumni",
    "registrar" => "Registrar",
    "student_rep" => "Alumni Admin",
    "system_admin" => "System Administrator"
];
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Audit Logs</title>

    <link rel="stylesheet" href="../../assets/css/style.css">

    <style>

        /*
        |--------------------------------------------------------------------------
        | Page
        |--------------------------------------------------------------------------
        */

        .audit-container {
            width: 100%;
        }

        .audit-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 20px;
            margin-bottom: 22px;
            flex-wrap: wrap;
        }

        .audit-title h1 {
            margin: 0;
            color: #4f321f;
        }

        .audit-title p {
            margin: 7px 0 0;
            color: #777;
            font-size: 14px;
        }


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        .audit-search {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .audit-search input {
            width: 280px;
            padding: 10px 12px;
            border: 1px solid #d8c8bc;
            border-radius: 7px;
            background: #fff;
            font-size: 13px;
            outline: none;
        }

        .audit-search input:focus {
            border-color: #9b6a48;
        }

        .audit-search button {
            padding: 10px 16px;
            border: none;
            border-radius: 7px;
            background: #7a4b2a;
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }
.audit-search button:hover {
            background: #633b22;
        }

        .clear-search {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 13px;
            border-radius: 7px;
            background: #f1ebe6;
            color: #6f4328;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }


        /*
        |--------------------------------------------------------------------------
        | Table Card
        |--------------------------------------------------------------------------
        */

        .audit-card {
            background: #fff;
            border: 1px solid #e3d8cf;
            border-radius: 10px;
            overflow: hidden;
        }

        .audit-table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .audit-table {
            width: 100%;
            min-width: 1050px;
            border-collapse: collapse;
            table-layout: fixed;
        }


        /*
        |--------------------------------------------------------------------------
        | Table Columns
        |--------------------------------------------------------------------------
        */

        .audit-table th:nth-child(1) {
            width: 55px;
        }

        .audit-table th:nth-child(2) {
            width: 190px;
        }

        .audit-table th:nth-child(3) {
            width: 150px;
        }

        .audit-table th:nth-child(4) {
            width: 105px;
        }

        .audit-table th:nth-child(5) {
            width: 125px;
        }

        .audit-table th:nth-child(6) {
            width: 75px;
        }

        .audit-table th:nth-child(7) {
            width: 280px;
        }

        .audit-table th:nth-child(8) {
            width: 170px;
        }


        /*
        |--------------------------------------------------------------------------
        | Table Styling
        |--------------------------------------------------------------------------
        */

        .audit-table th,
        .audit-table td {
            padding: 11px 13px;
            text-align: left;
            border-bottom: 1px solid #eee7e1;
            vertical-align: middle;
        }

        .audit-table th {
            background: #f8f5f2;
            color: #5c3a25;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }

        .audit-table td {
            color: #444;
            font-size: 13px;
        }

        .audit-table tbody tr:hover {
            background: #fcfaf8;
        }

        .audit-table tbody tr:last-child td {
            border-bottom: none;
        }


        /*
        |--------------------------------------------------------------------------
        | User
        |--------------------------------------------------------------------------
        */

        .audit-user {
            font-weight: 600;
            color: #4f321f;
            word-break: break-word;
        }

        .audit-role {
            margin-top: 3px;
            color: #888;
            font-size: 11px;
        }


        /*
        |--------------------------------------------------------------------------
        | Action
        |--------------------------------------------------------------------------
        */

        .action-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 5px 8px;
            border-radius: 5px;
            background: #f1e7df;
            color: #6f4328;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }


        /*
        |--------------------------------------------------------------------------
        | Table Name
        |--------------------------------------------------------------------------
        */
.table-name {
            color: #6b5140;
            font-family: monospace;
            font-size: 12px;
            word-break: break-word;
        }


        /*
        |--------------------------------------------------------------------------
        | Record ID
        |--------------------------------------------------------------------------
        */

        .record-id {
            text-align: center;
            color: #666;
            font-weight: 600;
        }


        /*
        |--------------------------------------------------------------------------
        | Description
        |--------------------------------------------------------------------------
        */

        .description {
            color: #555;
            line-height: 1.4;
            max-width: 280px;

            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;

            overflow: hidden;
        }


        /*
        |--------------------------------------------------------------------------
        | Date
        |--------------------------------------------------------------------------
        */

        .audit-date {
            color: #666;
            font-size: 12px;
            white-space: nowrap;
        }


        /*
        |--------------------------------------------------------------------------
        | Empty State
        |--------------------------------------------------------------------------
        */

        .empty-state {
            padding: 55px 20px;
            text-align: center;
        }

        .empty-state strong {
            display: block;
            margin-bottom: 7px;
            color: #5c3a25;
            font-size: 17px;
        }

        .empty-state span {
            color: #888;
            font-size: 13px;
        }


        /*
        |--------------------------------------------------------------------------
        | Mobile
        |--------------------------------------------------------------------------
        */

        @media (max-width: 800px) {

            .audit-header {
                align-items: stretch;
            }

            .audit-search {
                width: 100%;
            }

            .audit-search input {
                flex: 1;
                width: auto;
            }

        }

    </style>

</head>

<body>

<?php include "../system_admin/sidebar.php"; ?>

<main class="admin-main">

    <div class="admin-content">

        <div class="audit-container">

            <!-- Header -->

            <div class="audit-header">

                <div class="audit-title">

                    <h1>Audit Logs</h1>

                    <p>
                        Monitor important actions performed in the system.
                    </p>

                </div>


                <!-- Search -->

                <form method="GET" class="audit-search">

                    <input
                        type="text"
                        name="search"
                        placeholder="Search logs..."
                        value="<?= e($search) ?>"
                    >

                    <button type="submit">
                        Search
                    </button>

                    <?php if ($search !== ""): ?>

                        <a href="index.php" class="clear-search">
                            Clear
                        </a>

                    <?php endif; ?>

                </form>

            </div>


            <!-- Audit Table -->

            <div class="audit-card">

                <div class="audit-table-wrapper">

                    <?php if ($result->num_rows > 0): ?>

                        <table class="audit-table">

                            <thead>
 <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                    <th>Table</th>
                                    <th>Record</th>
                                    <th>Description</th>
                                    <th>Date & Time</th>
                                </tr>

                            </thead>

                            <tbody>

                            <?php while ($log = $result->fetch_assoc()): ?>

                                <tr>

                                    <td>
                                        <?= (int) $log["audit_id"] ?>
                                    </td>


                                    <td>

                                        <div class="audit-user">
                                            <?= e($log["email"] ?? "Unknown user") ?>
                                        </div>

                                    </td>


                                    <td>

                                        <div class="audit-role">

                                            <?= e(
                                                $roleLabels[$log["role"] ?? ""]
                                                ?? ($log["role"] ?? "Unknown")
                                            ) ?>

                                        </div>

                                    </td>


                                    <td>

                                        <span class="action-badge">
                                            <?= e($log["action"]) ?>
                                        </span>

                                    </td>


                                    <td>

                                        <div class="table-name">
                                            <?= e($log["table_name"]) ?>
                                        </div>

                                    </td>


                                    <td>

                                        <div class="record-id">
                                            <?= (int) $log["record_id"] ?>
                                        </div>

                                    </td>


                                    <td>

                                        <div
                                            class="description"
                                            title="<?= e($log["description"]) ?>"
                                        >
                                            <?= e($log["description"]) ?>
                                        </div>

                                    </td>


                                    <td>

                                        <div class="audit-date">
                                            <?= e($log["created_at"]) ?>
                                        </div>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                            </tbody>

                        </table>

                    <?php else: ?>

                        <div class="empty-state">

                            <strong>No audit logs found</strong>

                            <span>
                                <?php if ($search !== ""): ?>
                                    No logs match your search.
                                <?php else: ?>
                                    No audit activity has been recorded yet.
                                <?php endif; ?>
                            </span>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>

</main>

</body>
</html>

<?php
$stmt->close();
?>