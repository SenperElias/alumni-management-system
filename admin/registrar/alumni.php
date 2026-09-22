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
| Search & Filter
|--------------------------------------------------------------------------
*/

$search = trim($_GET["search"] ?? "");
$departmentId = $_GET["department_id"] ?? "";


/*
|--------------------------------------------------------------------------
| Get Departments
|--------------------------------------------------------------------------
*/

$departments = [];

$departmentResult = $conn->query(
    "SELECT department_id, department_name
     FROM departments
     ORDER BY department_name ASC"
);

if ($departmentResult) {

    while ($row = $departmentResult->fetch_assoc()) {
        $departments[] = $row;
    }

}


/*
|--------------------------------------------------------------------------
| Get Alumni
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        a.alumni_id,
        a.college_id_number,
        a.first_name,
        a.last_name,
        u.email,
        a.phone,
        a.graduation_year,
        a.department_id,
        d.department_name,
        a.section_id,
        s.section_name,
        a.specialization_id,
        sp.specialization_name,
        a.level

    FROM alumni a

    LEFT JOIN users u
        ON a.user_id = u.user_id

    LEFT JOIN departments d
        ON a.department_id = d.department_id

        LEFT JOIN sections s
        ON a.section_id = s.section_id
        
    LEFT JOIN specializations sp
        ON a.specialization_id = sp.specialization_id


    WHERE 1 = 1
";

$params = [];
$types = "";


/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

if ($search !== "") {

    $sql .= "
        AND (
            a.first_name LIKE ?
            OR a.last_name LIKE ?
            OR a.college_id_number LIKE ?
            OR u.email LIKE ?
        )
    ";

    $searchValue = "%" . $search . "%";

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;

    $types .= "ssss";
}


/*
|--------------------------------------------------------------------------
| Department Filter
|--------------------------------------------------------------------------
*/

if ($departmentId !== "") {

    $sql .= "
        AND a.department_id = ?
    ";

    $params[] = (int) $departmentId;

    $types .= "i";
}


/*
|--------------------------------------------------------------------------
| Order
|--------------------------------------------------------------------------
*/

$sql .= "
    ORDER BY
        a.first_name ASC,
        a.last_name ASC
";


$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}


if (!empty($params)) {

    $stmt->bind_param(
        $types,
        ...$params
    );

}


$stmt->execute();

$result = $stmt->get_result();

?>

<?
$activePage = "alumni";
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
        Alumni Directory |
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
     require_once "includes/sidebar.php"; ?>

    <!-- =====================================================
         MAIN
    ====================================================== -->

    <main class="admin-main">


        <!-- TOPBAR -->

        <header class="admin-topbar">


            <div>

                <h1>
                    Alumni Directory
                </h1>

                <p>
                    View and search registered alumni.
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



        <!-- CONTENT -->

        <section class="dashboard-content">


            <!-- =================================================
                 SEARCH & FILTER
            ================================================== -->

            <div class="dashboard-panel">


                <div class="panel-header">


                    <div>

                        <h2>
                            Search Alumni
                        </h2>

                        <p>
                            Search by name, Alumni ID or email.
                        </p>

                    </div>


                </div>



                <form
                    method="GET"
                    action="alumni.php"
                >


                    <div class="form-grid">


                        <div class="form-field">


                            <label for="search">
                                Search
                            </label>


                            <input
                                type="text"
                                id="search"
                                name="search"
                                value="<?= e($search) ?>"
                                placeholder="Name, Alumni ID or email..."
                            >


                        </div>



                        <div class="form-field">


                            <label for="department_id">
                                Department
                            </label>


                            <select
                                id="department_id"
                                name="department_id"
                            >


                                <option value="">
                                    All Departments
                                </option>


                                <?php foreach (
                                    $departments
                                    as $department
                                ): ?>
 <option
                                        value="<?= (int) $department["department_id"] ?>"
                                        <?= $departmentId ==
                                            $department["department_id"]
                                            ? "selected"
                                            : "" ?>
                                    >

                                        <?= e(
                                            $department["department_name"]
                                        ) ?>

                                    </option>


                                <?php endforeach; ?>


                            </select>


                        </div>


                    </div>



                    <div class="form-actions">


                        <button
                            type="submit"
                            class="primary-button"
                        >
                            Search
                        </button>


                        <a
                            href="alumni.php"
                            class="secondary-button"
                        >
                            Clear
                        </a>


                    </div>


                </form>


            </div>



            <!-- =================================================
                 ALUMNI TABLE
            ================================================== -->

            <div class="dashboard-panel">


                <div class="panel-header">


                    <div>

                        <h2>
                            Registered Alumni
                        </h2>

                        <p>

                            Alumni currently registered
                            in the system.

                        </p>

                    </div>


                </div>



                <?php if (
                    $result->num_rows === 0
                ): ?>


                    <div class="form-alert">

                        No alumni found.

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
                                        Section / Progran
                                    </th>

                            <th>
                                        Specializations
                                    </th>

                                    <th>
                                        Level
                                    </th>

                                    <th>
                                        Phone
                                    </th>


                                    <th>
                                        Action
                                    </th>


                                </tr>


                            </thead>



                            <tbody>


                                <?php while (
                                    $row =
                                    $result->fetch_assoc()
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
    <?= e($row["department_name"]) ?>
</td>

<td>
    <?= e($row["section_name"] ?? "—") ?>
</td>

<td>
    <?= e($row["specialization_name"] ?? "—") ?>
</td>

<td>
    <?= !empty($row["level"])
        ? "Level " . e($row["level"])
        : "—"
    ?>
</td>

<td>
    <?= e($row["graduation_year"]) ?>
</td>

                                        <td>

                                            <?= e(
                                                $row["phone"]
                                            ) ?>

                                        </td>


                                        <td>

                                           <a
    href="view_alumni.php?id=<?= (int) $row["alumni_id"] ?>"
    style="color: #6b4f3a; font-weight: 600; text-decoration: underline;"
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
                 ADD ALUMNI
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