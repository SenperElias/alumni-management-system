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
| SEARCH
|--------------------------------------------------------------------------
*/

$search = trim(
    $_GET["search"] ?? ""
);


/*
|--------------------------------------------------------------------------
| GET USERS
|--------------------------------------------------------------------------
*/

$users = [];


if ($search !== "") {

    $sql = "
        SELECT
            user_id,
            email,
            role,
            account_status,
            created_at
        FROM users
        WHERE
            email LIKE ?
            OR role LIKE ?
        ORDER BY created_at DESC
    ";

    $stmt = $conn->prepare($sql);

    $search_value =
        "%" . $search . "%";

    $stmt->bind_param(
        "ss",
        $search_value,
        $search_value
    );

    $stmt->execute();

    $result = $stmt->get_result();

} else {

    $sql = "
        SELECT
            user_id,
            email,
            role,
            account_status,
            created_at
        FROM users
        ORDER BY created_at DESC
    ";

    $result = $conn->query($sql);

}


if ($result) {

    while (
        $row = $result->fetch_assoc()
    ) {

        $users[] = $row;

    }

}


if (isset($stmt)) {

    $stmt->close();

}


$total_users = count($users);

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
        User Management |
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

    <style>

        .users-page {
            padding: 30px;
        }

        .users-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
        }

        .users-header h1 {
            margin: 0 0 6px;
            color: #4a2c1d;
        }

        .users-header p {
            margin: 0;
            color: #777;
        }

        .user-count {
            background: #f8f5f2;
            color: #7a4b2a;
            padding: 10px 15px;
            border-radius: 8px;
            font-weight: 600;
        }

        .search-box {
            background: #ffffff;
            border: 1px solid #eeeeee;
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 20px;
        }

        .search-form {
            display: flex;
            gap: 10px;
        }

        .search-form input {
            flex: 1;
            padding: 11px 13px;
            border: 1px solid #dddddd;
            border-radius: 8px;
            font-size: 14px;
        }

        .search-button {
            border: none;
            background: #7a4b2a;
            color: #ffffff;
            padding: 11px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .clear-button {
            display: inline-flex;
            align-items: center;
            padding: 11px 15px;
            background: #eeeeee;
            color: #444444;
            text-decoration: none;
            border-radius: 8px;
        }

        .users-card {
            background: #ffffff;
            border: 1px solid #eeeeee;
            border-radius: 14px;
            padding: 20px;
            box-shadow:
                0 5px 20px
                rgba(0,0,0,0.04);
        }
        .table-wrapper {
            overflow-x: auto;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }

        .users-table th {
            background: #f8f5f2;
            color: #4a2c1d;
            padding: 13px;
            text-align: left;
            font-size: 13px;
        }

        .users-table td {
            padding: 13px;
            border-top: 1px solid #eeeeee;
            color: #555;
        }

        .role-badge,
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 700;
        }

        .role-admin {
            background: #eadfd6;
            color: #4a2c1d;
        }

        .role-alumni {
            background: #eee5dd;
            color: #7a4b2a;
        }

        .active-status {
            background: #d4edda;
            color: #155724;
        }

        .inactive-status {
            background: #f8d7da;
            color: #721c24;
        }

        .empty {
            text-align: center;
            padding: 35px;
            color: #777;
        }

        @media (max-width: 650px) {

            .users-page {
                padding: 15px;
            }

            .users-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .search-form {
                flex-direction: column;
            }

        }

    </style>

</head>


<body class="admin-body">


<div class="admin-layout">


    <!-- SIDEBAR -->

      <?php require_once __DIR__ ."/../includes/sidebar.php"; ?>
          


    <!-- MAIN -->

    <main class="admin-main">


        <header class="admin-topbar">

            <div>

                <h1>
                    User Management
                </h1>

                <p>
                    View registered accounts and their access roles.
                </p>

            </div>

        </header>


        <section class="dashboard-content">


            <div class="users-page">


                <div class="users-header">

                    <div>

                        <h1>
                            System Users
                        </h1>

                        <p>
                            Manage administrator and alumni accounts.
                        </p>

                    </div>


                    <div class="user-count">

                        <?= $total_users ?>
                        Users

                    </div>

                </div>


                <!-- SEARCH -->

                <div class="search-box">

                    <form
                        method="GET"
                        class="search-form"
                    >

                        <input
                            type="text"
                            name="search"
                            value="<?= e($search) ?>"
                            placeholder="Search by email or role..."
                        >

                        <button
                            type="submit"
                            class="search-button"
                        >
                            Search
                        </button>


                        <?php if (
                            $search !== ""
                        ): ?>

                            <a
                                href="index.php"
                                class="clear-button"
                            >
                                Clear
                            </a>

                        <?php endif; ?>

                    </form>

                </div>


                <!-- USERS -->

                <div class="users-card">

                    <div class="table-wrapper">


                        <?php if (
                            empty($users)
                        ): ?>

                            <div class="empty">
                                No users found.
                            </div>

                        <?php else: ?>


                            <table class="users-table">


                                <thead>

                                    <tr>

                                        <th>
                                            ID
                                        </th>

                                        <th>
                                            Email
                                        </th>

                                        <th>
                                            Role
                                        </th>

                                        <th>
                                            Account Status
                                        </th>

                                        <th>
                                            Registered
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>


                                    <?php foreach (
                                        $users as $user
                                    ): ?>


                                        <?php

                                        $role =
                                            strtolower(
                                                trim(
                                                    $user[
                                                        "role"
                                                    ]
                                                )
                                            );

                                        $status =
                                            strtolower(
                                                trim(
                                                    $user[
                                                        "account_status"
                                                    ]
                                                )
                                            );

                                        ?>


                                        <tr>


                                            <td>
                                          <?= (int)
                                                    $user[
                                                        "user_id"
                                                    ]
                                                ?>

                                            </td>


                                            <td>

                                                <strong>

                                                    <?= e(
                                                        $user[
                                                            "email"
                                                        ]
                                                    ) ?>

                                                </strong>

                                            </td>


                                            <td>

                                                <span
                                                    class="role-badge
                                                    <?= $role === "admin"
                                                        ? "role-admin"
                                                        : "role-alumni"
                                                    ?>"
                                                >

                                                    <?= e(
                                                        ucfirst(
                                                            $role
                                                        )
                                                    ) ?>

                                                </span>

                                            </td>


                                            <td>

                                                <span
                                                    class="status-badge
                                                    <?= (
                                                        $status === "active"
                                                        || $status === "1"
                                                    )
                                                        ? "active-status"
                                                        : "inactive-status"
                                                    ?>"
                                                >

                                                    <?= e(
                                                        ucfirst(
                                                            $status
                                                        )
                                                    ) ?>

                                                </span>

                                            </td>


                                            <td>

                                                <?= e(
                                                    date(
                                                        "M d, Y",
                                                        strtotime(
                                                            $user[
                                                                "created_at"
                                                            ]
                                                        )
                                                    )
                                                ) ?>

                                            </td>


                                        </tr>


                                    <?php endforeach; ?>


                                </tbody>


                            </table>


                        <?php endif; ?>


                    </div>

                </div>


            </div>


        </section>


    </main>


</div>


</body>

</html>      