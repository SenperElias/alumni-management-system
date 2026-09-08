 <?php

session_start();

require_once "../../../config/database.php";
require_once "../../../config/config.php";
require_once "../../../includes/functions.php";

/*
|--------------------------------------------------------------------------
| System Administrator Access
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "system_admin") {
    header("Location: ../../../index.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| Get Administrator Information
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        user_id,
        email,
        role,
        account_status,
        created_at,
        updated_at
    FROM users
    WHERE user_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();
    header("Location: ../dashboard.php");
    exit;
}

$admin = $result->fetch_assoc();

$stmt->close();

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
        System Settings | <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../../assets/css/style.css"
    >

</head>

<body class="admin-body">

<div class="admin-layout">

    <?php include "../sidebar.php"; ?>

    <main class="admin-main">

        <header class="admin-topbar">

            <div>

                <h1>
                    System Settings
                </h1>

                <p>
                    Manage system-level information and your administrator account.
                </p>

            </div>

        </header>


        <section class="dashboard-content">


            <!-- System Administrator Profile -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            System Administrator Profile
                        </h2>

                        <p>
                            Your technical administrator account information.
                        </p>

                    </div>

                </div>


                <div class="quick-stats">

                    <div>

                        <span>
                            Email
                        </span>

                        <strong>
                            <?= e($admin["email"]) ?>
                        </strong>

                    </div>


                    <div>

                        <span>
                            Role
                        </span>

                        <strong>
                            System Administrator
                        </strong>

                    </div>


                    <div>

                        <span>
                            Account Status
                        </span>

                        <strong>
                            <?= e($admin["account_status"]) ?>
                        </strong>

                    </div>

                </div>

            </div>


            <!-- System Information -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            System Information
                        </h2>

                        <p>
                            Basic information about the alumni management system.
                        </p>

                    </div>

                </div>


                <div class="quick-stats">

                    <div>

                        <span>
                            System Name
                        </span>
<strong>
                            <?= e(SITE_NAME) ?>
                        </strong>

                    </div>


                    <div>

                        <span>
                            Institution
                        </span>

                        <strong>
                            Taferi Mekonnen Polytechnic Technical College
                        </strong>

                    </div>


                    <div>

                        <span>
                            Academic Departments
                        </span>

                        <strong>
                            10
                        </strong>

                    </div>

                </div>

            </div>


            <!-- Security -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Security & Administration
                        </h2>

                        <p>
                            Technical system administration responsibilities.
                        </p>

                    </div>

                </div>


                <div class="quick-stats">

                    <div>

                        <span>
                            User Management
                        </span>

                        <strong>
                            Enabled
                        </strong>

                    </div>


                    <div>

                        <span>
                            Audit Logging
                        </span>

                        <strong>
                            Enabled
                        </strong>

                    </div>


                    <div>

                        <span>
                            Password Security
                        </span>

                        <strong>
                            Enabled
                        </strong>

                    </div>

                </div>

            </div>


            <!-- Account Information -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Account Information
                        </h2>

                    </div>

                </div>


                <div class="quick-stats">

                    <div>

                        <span>
                            Account Created
                        </span>

                        <strong>
                            <?= e(
                                date(
                                    "M d, Y",
                                    strtotime($admin["created_at"])
                                )
                            ) ?>
                        </strong>

                    </div>


                    <div>

                        <span>
                            Last Updated
                        </span>

                        <strong>
                            <?= e(
                                date(
                                    "M d, Y",
                                    strtotime($admin["updated_at"])
                                )
                            ) ?>
                        </strong>

                    </div>

                </div>

            </div>


        </section>

    </main>

</div>

</body>

</html>