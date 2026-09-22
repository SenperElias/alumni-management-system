<?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";

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
| Search and Filters
|--------------------------------------------------------------------------
*/

$search = trim($_GET["search"] ?? "");
$department = $_GET["department"] ?? "";
$graduationYear = $_GET["graduation_year"] ?? "";

/*
|--------------------------------------------------------------------------
| Get Departments
|--------------------------------------------------------------------------
*/

$departments = [];

$result = $conn->query(
    "SELECT department_id, department_name
     FROM departments
     ORDER BY department_name ASC"
);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }
}

/*
|--------------------------------------------------------------------------
| Get Graduation Years
|--------------------------------------------------------------------------
*/

$years = [];

$result = $conn->query(
    "SELECT DISTINCT graduation_year
     FROM alumni
     WHERE graduation_year IS NOT NULL
     ORDER BY graduation_year DESC"
);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $years[] = $row["graduation_year"];
    }
}

/*
|--------------------------------------------------------------------------
| Build Alumni Query
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        a.alumni_id,
        a.college_id_number,
        a.first_name,
        a.last_name,
        a.gender,
        a.graduation_year,
        a.profile_photo,
        d.department_name
    FROM alumni a
    LEFT JOIN departments d
        ON a.department_id = d.department_id
    WHERE 1=1
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
        )
    ";

    $searchValue = "%" . $search . "%";

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;

    $types .= "sss";
}

/*
|--------------------------------------------------------------------------
| Department Filter
|--------------------------------------------------------------------------
*/

if ($department !== "") {

    $sql .= " AND a.department_id = ? ";

    $params[] = $department;

    $types .= "i";
}

/*
|--------------------------------------------------------------------------
| Graduation Year Filter
|--------------------------------------------------------------------------
*/

if ($graduationYear !== "") {

    $sql .= " AND a.graduation_year = ? ";

    $params[] = $graduationYear;

    $types .= "i";
}

$sql .= "
    ORDER BY a.created_at DESC
";

/*
|--------------------------------------------------------------------------
| Execute Query
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$result = $stmt->get_result();

$pageTitle = "Alumni Management";

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        <?= e($pageTitle) ?> |
        <?= e(SITE_NAME) ?>
    </title>

    <link rel="stylesheet"
          href="../../assets/css/style.css">

</head>

<body class="admin-body">

<div class="admin-layout">

    <?php require_once __DIR__ ."/../includes/sidebar.php"; ?>



    <!-- Main Content -->

    <main class="admin-main">

        <header class="admin-topbar">

            <div>

                <h1>Alumni Management</h1>

                <p>
                    View and manage registered alumni.
                </p>

            </div>

            <div class="admin-user">

                <div class="admin-avatar">
                    A
                </div>

                <div>

                    <strong>Alumni</strong>

                    <small>
                        Alumni Portal
                    </small>

                </div>

            </div>

        </header>


        <section class="dashboard-content">

            <!-- Page Header -->

            <div class="page-heading">

                <div>

                    <h2>Alumni Records</h2>

                    <p>
                        Search, filter and manage alumni records.
                    </p>

                </div>

                <a href="add.php"
                   class="primary-button">
                    + Add Alumni
                </a>

            </div>


            <!-- Filters -->

            <div class="dashboard-panel">

                <form method="GET"
                      class="alumni-filters">

                    <div class="filter-group">

                        <label for="search">
                            Search
                        </label>

                        <input
                            type="text"
                            id="search"
                            name="search"
                            placeholder="Name or Alumni ID"
                            value="<?= e($search) ?>"
                        >

                    </div>


                    <div class="filter-group">

                        <label for="department">
                            Department
                        </label>

                        <select
                            id="department"
                            name="department"
                        >

                            <option value="">
                                All Departments
                            </option>

                            <?php foreach ($departments as $dept): ?>
           <option
                                    value="<?= e($dept["department_id"]) ?>"
                                    <?= $department == $dept["department_id"]
                                        ? "selected"
                                        : "" ?>
                                >
                                    <?= e($dept["department_name"]) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="filter-group">

                        <label for="graduation_year">
                            Graduation Year
                        </label>

                        <select
                            id="graduation_year"
                            name="graduation_year"
                        >

                            <option value="">
                                All Years
                            </option>

                            <?php foreach ($years as $year): ?>

                                <option
                                    value="<?= e($year) ?>"
                                    <?= $graduationYear == $year
                                        ? "selected"
                                        : "" ?>
                                >
                                    <?= e($year) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="filter-actions">

                        <button type="submit"
                                class="primary-button">
                            Search
                        </button>

                        <a href="index.php"
                           class="secondary-button">
                            Reset
                        </a>

                    </div>

                </form>

            </div>


            <!-- Alumni Table -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>Alumni List</h2>

                        <p>
                            Registered alumni records
                        </p>

                    </div>

                </div>


                <div class="table-container">

                    <table class="data-table">

                        <thead>

                            <tr>

                                <th>Alumni</th>

                                <th>Alumni ID</th>

                                <th>Department</th>

                                <th>Gender</th>

                                <th>Graduation Year</th>

                                <th>Actions</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php if ($result->num_rows > 0): ?>

                            <?php while ($alumni = $result->fetch_assoc()): ?>

                                <tr>

                                    <td>

                                        <div class="alumni-person">

                                            <?php if (!empty($alumni["profile_photo"])): ?>

                                                <img
                                                    src="../../uploads/<?= e($alumni["profile_photo"]) ?>"
                                                    alt="Profile"
                                                    class="alumni-avatar"
                                                >

                                            <?php else: ?>

                                                <div class="alumni-avatar-placeholder">
                                                        <?= strtoupper(
                                                        substr(
                                                            $alumni["first_name"],
                                                            0,
                                                            1
                                                        )
                                                    ) ?>
                                                </div>

                                            <?php endif; ?>

                                            <div>

                                                <strong>
                                                    <?= e(
                                                        $alumni["first_name"]
                                                        . " "
                                                        . $alumni["last_name"]
                                                    ) ?>
                                                </strong>

                                            </div>

                                        </div>

                                    </td>


                                    <td>
                                        <?= e(
                                            $alumni["college_id_number"]
                                        ) ?>
                                    </td>


                                    <td>
                                        <?= e(
                                            $alumni["department_name"]
                                            ?? "Not assigned"
                                        ) ?>
                                    </td>


                                    <td>
                                        <?= e(
                                            $alumni["gender"]
                                        ) ?>
                                    </td>


                                    <td>
                                        <?= e(
                                            $alumni["graduation_year"]
                                        ) ?>
                                    </td>


                                    <td>

                                        <div class="table-actions">

                                            <a
                                                href="view.php?id=<?= e($alumni["alumni_id"]) ?>"
                                                class="action-view"
                                            >
                                                View
                                            </a>

                                            <a
                                                href="edit.php?id=<?= e($alumni["alumni_id"]) ?>"
                                                class="action-edit"
                                            >
                                                Edit
                                            </a>

                                            <a
                                                href="delete.php?id=<?= e($alumni["alumni_id"]) ?>"
                                                class="action-edit"
                                            >
                                                Delete
                                            </a>

                                        </div>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="6">

                                    <div class="empty-state">

                                        <div class="empty-icon">
                                            A
                                        </div>

                                        <h3>
                                            No alumni found
                                        </h3>

                                        <p>
                                            No records match your search or filters.
                                        </p>

                                    </div>

                                </td>

                            </tr>
                           <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </section>

    </main>

</div>

</body>

</html>                  