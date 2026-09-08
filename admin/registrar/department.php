<?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";

/*
|--------------------------------------------------------------------------
| REGISTRAR ACCESS
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

$registrarId = (int) $_SESSION["user_id"];


/*
|--------------------------------------------------------------------------
| GET DEPARTMENT ID
|--------------------------------------------------------------------------
*/

$department_id = (int) ($_GET["id"] ?? 0);

if ($department_id <= 0) {
    die("Invalid department.");
}


/*
|--------------------------------------------------------------------------
| GET DEPARTMENT
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        department_id,
        department_name,
        description
    FROM departments
    WHERE department_id = ?
    LIMIT 1
");

if (!$stmt) {

    error_log(
        "Database prepare error in registrar/department.php: " .
        $conn->error
    );

    die(
        "Unable to load department information. " .
        "Please try again later."
    );
}

$stmt->bind_param(
    "i",
    $department_id
);

if (!$stmt->execute()) {

    error_log(
        "Database execute error in registrar/department.php: " .
        $stmt->error
    );

    $stmt->close();

    die(
        "Unable to load department information. " .
        "Please try again later."
    );
}

$result = $stmt->get_result();

$department = $result->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| DEPARTMENT NOT FOUND
|--------------------------------------------------------------------------
*/

if (!$department) {
    die("Department not found.");
}


/*
|--------------------------------------------------------------------------
| GET REGISTRATIONS FOR DEPARTMENT
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        registration_id,
        college_id_number,
        first_name,
        last_name,
        email,
        graduation_year,
        status,
        created_at,
        verified_at
    FROM alumni_registrations
    WHERE department_id = ?
    ORDER BY created_at DESC
");

if (!$stmt) {

    error_log(
        "Database prepare error in registrar/department.php " .
        "(registrations): " .
        $conn->error
    );

    die(
        "Unable to load department registrations. " .
        "Please try again later."
    );
}

$stmt->bind_param(
    "i",
    $department_id
);

if (!$stmt->execute()) {

    error_log(
        "Database execute error in registrar/department.php " .
        "(registrations): " .
        $stmt->error
    );

    $stmt->close();

    die(
        "Unable to load department registrations. " .
        "Please try again later."
    );
}

$registrationResult = $stmt->get_result();

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

        <?= e($department["department_name"]) ?>

        Registrations |

        <?= e(SITE_NAME) ?>

    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

</head>


<body class="admin-body">


<div class="admin-layout">


    <!-- =====================================================
         SIDEBAR
    ====================================================== -->

    <?php

    $activePage = "dashboard";

    require_once __DIR__ . "/includes/sidebar.php";

    ?>


    <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->
 <main class="admin-main">


        <!-- =================================================
             TOPBAR
        ================================================== -->

        <header class="admin-topbar">

            <div>

                <h1>

                    <?= e(
                        $department["department_name"]
                    ) ?>

                </h1>

                <p>

                    Alumni registration applications
                    for this department.

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


        <!-- =================================================
             CONTENT
        ================================================== -->

        <section class="dashboard-content">


            <!-- =================================================
                 DEPARTMENT INFORMATION
            ================================================== -->

            <div class="dashboard-panel">


                <div class="panel-header">

                    <div>

                        <h2>

                            <?= e(
                                $department["department_name"]
                            ) ?>

                        </h2>


                        <?php if (
                            !empty(
                                $department["description"]
                            )
                        ): ?>

                            <p>

                                <?= e(
                                    $department["description"]
                                ) ?>

                            </p>

                        <?php else: ?>

                            <p>

                                Registration applications
                                for this department.

                            </p>

                        <?php endif; ?>

                    </div>

                </div>


            </div>


            <!-- =================================================
                 REGISTRATIONS
            ================================================== -->

            <div class="dashboard-panel">


                <div class="panel-header">

                    <div>

                        <h2>

                            Department Registrations

                        </h2>


                        <p>

                            All alumni registration
                            applications submitted under
                            this department.

                        </p>

                    </div>

                </div>


                <?php if (
                    $registrationResult->num_rows === 0
                ): ?>


                    <div class="form-alert">

                        No registrations found for this
                        department.

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
                                        Graduation Year
                                    </th>
 <th>
                                        Status
                                    </th>

                                    <th>
                                        Registered
                                    </th>

                                    <th>
                                        Action
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                            <?php while (
                                $row =
                                $registrationResult->fetch_assoc()
                            ): ?>


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
                                            $row["college_id_number"]
                                        ) ?>

                                    </td>


                                    <td>

                                        <?= e(
                                            $row["email"]
                                        ) ?>

                                    </td>


                                    <td>

                                        <?= e(
                                            $row["graduation_year"]
                                        ) ?>

                                    </td>


                                    <td>

                                        <strong>

                                            <?= e(
                                                ucfirst(
                                                    $row["status"]
                                                )
                                            ) ?>

                                        </strong>

                                    </td>


                                    <td>

                                        <?= e(
                                            $row["created_at"]
                                        ) ?>

                                    </td>


                                    <td>

                                        <a
                                            href="view.php?id=<?= (int) $row["registration_id"] ?>"
                                        >

                                            View

                                        </a>

                                    </td>


                                </tr>


                            <?php endwhile; ?>


                            </tbody>


                        </table>

                    </div>


                <?php endif; ?>


            </div>


            <!-- =================================================
                 BACK TO DASHBOARD
            ================================================== -->

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


<?php

$stmt->close();

?>