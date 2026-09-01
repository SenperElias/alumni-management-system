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

/*
|--------------------------------------------------------------------------
| Get Rejected Registrations
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        r.registration_id,
        r.alumni_id_number,
        r.first_name,
        r.last_name,
        r.email,
        r.graduation_year,
        r.verification_notes,
        r.verified_at,
        d.department_name
    FROM alumni_registrations r
    LEFT JOIN departments d
        ON r.department_id = d.department_id
    WHERE r.status = 'rejected'
    ORDER BY r.verified_at DESC
";

$result = $conn->query($sql);

if (!$result) {
    die("Database error: " . $conn->error);
}

?>

<?php
$activePage = "rejected";
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
        Rejected Registrations |
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

    
            <?php
 include "includes/sidebar.php"; 
?>  
                


    <!-- Main -->

    <main class="admin-main">


        <!-- Topbar -->

        <header class="admin-topbar">

            <div>

                <h1>
                    Rejected Registrations
                </h1>

                <p>
                    Alumni applications that were rejected
                    during verification.
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
                            Rejected Applications
                        </h2>

                        <p>
                            Review the applications that could
                            not be verified and the reasons
                            recorded by the Registrar.
                        </p>

                    </div>

                </div>


                <?php if ($result->num_rows === 0): ?>


                    <div class="form-alert success-message">
 No rejected registrations.

                    </div>


                <?php else: ?>


                    <div style="overflow-x: auto;">

                        <table class="data-table">


                            <thead>

                                <tr>

                                    <th>
                                        Name
                                    </th>

                                    <th>
                                        Alumni ID
                                    </th>

                                    <th>
                                        Email
                                    </th>

                                    <th>
                                        Department
                                    </th>

                                    <th>
                                        Graduation Year
                                    </th>

                                    <th>
                                        Rejection Reason
                                    </th>

                                    <th>
                                        Rejected At
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                                <?php while ($row = $result->fetch_assoc()): ?>


                                    <tr>


                                        <td>

                                            <?= e(
                                                $row["first_name"]
                                                . " "
                                                . $row["last_name"]
                                            ) ?>

                                        </td>


                                        <td>

                                            <?= e(
                                                $row["alumni_id_number"]
                                            ) ?>

                                        </td>


                                        <td>

                                            <?= e(
                                                $row["email"]
                                            ) ?>

                                        </td>


                                        <td>

                                            <?= e(
                                                $row["department_name"]
                                            ) ?>

                                        </td>


                                        <td>

                                            <?= e(
                                                $row["graduation_year"]
                                            ) ?>

                                        </td>


                                        <td>

                                            <div
                                                style="
                                                    max-width: 350px;
                                                    white-space: normal;
                                                "
                                            >

                                                <?= e(
                                                    $row["verification_notes"]
                                                    ?? "No reason provided"
                                                ) ?>

                                            </div>

                                        </td>


                                        <td>

                                            <?= e(
                                                $row["verified_at"]
                                            ) ?>

                                        </td>


                                    </tr>


                                <?php endwhile; ?>


                            </tbody>
 </table>

                    </div>


                <?php endif; ?>


            </div>


            <!-- Back -->

            <div class="form-actions">

                <a
                    href="dashboard.php"
                    class="secondary-button"
                >
                    ← Back to Dashboard
                </a>

            </div>


        </section>


    </main>

</div>

</body>

</html>