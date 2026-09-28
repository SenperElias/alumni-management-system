 <?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";


/* -------------------------------------------------------------
   Student Representative Access
------------------------------------------------------------- */

if (!isset($_SESSION["user_id"])) {

    header("Location: ../../auth/login.php");
    exit;

}

if ($_SESSION["role"] !== "student_rep") {

    header("Location: ../../index.php");
    exit;

}


$activePage = "manage_alumni";


/* -------------------------------------------------------------
   Search and Filters
------------------------------------------------------------- */

$search = trim($_GET["search"] ?? "");

$departmentId = (int) ($_GET["department_id"] ?? 0);

$graduationYear = trim($_GET["graduation_year"] ?? "");

$alumni = [];

$departments = [];


/* -------------------------------------------------------------
   Load Active Departments
------------------------------------------------------------- */

$departmentResult = $conn->query("
    SELECT
        department_id,
        department_name
    FROM departments
    WHERE status = 'active'
    ORDER BY department_name ASC
");

if (!$departmentResult) {

    error_log(
        "Database error in student_rep/manage_alumni.php " .
        "(departments): " .
        $conn->error
    );

    die(
        "Unable to load departments. " .
        "Please try again later."
    );

}

while ($row = $departmentResult->fetch_assoc()) {

    $departments[] = $row;

}


/* -------------------------------------------------------------
   Search Alumni
------------------------------------------------------------- */

if (
    $search !== "" ||
    $departmentId > 0 ||
    $graduationYear !== ""
) {

    $sql = "
        SELECT
            a.alumni_id,
            a.user_id,
            a.college_id_number,
            a.first_name,
            a.last_name,
            a.phone,
            a.graduation_year,
            a.section_id,
            s.section_name,
            a.specialization_id,
            sp.specialization_name,
            a.level,
            u.email,
            d.department_name

        FROM alumni a

        INNER JOIN users u
            ON a.user_id = u.user_id

        INNER JOIN departments d
            ON a.department_id = d.department_id

            LEFT JOIN sections s
            ON a.section_id = s.section_id

            LEFT JOIN specializations sp
            ON a.specialization_id = sp.specialization_id

        WHERE u.role = 'alumni'
          AND u.account_status = 'active'
    ";


    $types = "";

    $params = [];


    /* ---------------------------------------------------------
       Text Search
    --------------------------------------------------------- */

    if ($search !== "") {

        $sql .= "
            AND (
                a.college_id_number LIKE ?
                OR a.first_name LIKE ?
                OR a.last_name LIKE ?
                OR u.email LIKE ?
                OR a.phone LIKE ?
            )
        ";

        $term = "%" . $search . "%";

        $types .= "sssss";

        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;

    }


    /* ---------------------------------------------------------
       Department Filter
    --------------------------------------------------------- */

    if ($departmentId > 0) {

        $sql .= "
            AND a.department_id = ?
        ";

        $types .= "i";

        $params[] = $departmentId;

    }


    /* ---------------------------------------------------------
       Graduation Year Filter
    --------------------------------------------------------- */

    if ($graduationYear !== "") {

        if (
            preg_match(
                "/^\d{4}$/",
                $graduationYear
            )
        ) {

            $sql .= "
                AND a.graduation_year = ?
            ";

            $types .= "s";

            $params[] = $graduationYear;

        }

    }


    /* ---------------------------------------------------------
       Ordering
    --------------------------------------------------------- */

    $sql .= "
        ORDER BY
            a.first_name ASC,
            a.last_name ASC
    ";
 /* ---------------------------------------------------------
       Prepare Query
    --------------------------------------------------------- */

    $stmt = $conn->prepare($sql);

    if (!$stmt) {

        error_log(
            "Database prepare error in " .
            "student_rep/manage_alumni.php: " .
            $conn->error
        );

        die(
            "Unable to search alumni. " .
            "Please try again later."
        );

    }


    /* ---------------------------------------------------------
       Bind Dynamic Parameters
    --------------------------------------------------------- */

    if ($types !== "") {

        $stmt->bind_param(
            $types,
            ...$params
        );

    }


    /* ---------------------------------------------------------
       Execute Search
    --------------------------------------------------------- */

    if (!$stmt->execute()) {

        error_log(
            "Database execute error in " .
            "student_rep/manage_alumni.php: " .
            $stmt->error
        );

        $stmt->close();

        die(
            "Unable to search alumni. " .
            "Please try again later."
        );

    }


    $result = $stmt->get_result();


    while ($row = $result->fetch_assoc()) {

        $alumni[] = $row;

    }


    $stmt->close();

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
        Manage Alumni |
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

</head>


<body class="admin-body">


<div class="admin-layout">


    <!-- SIDEBAR -->

    <?php include "includes/sidebar.php"; ?>


    <!-- MAIN CONTENT -->

    <main class="admin-main">


        <!-- TOPBAR -->

        <header class="admin-topbar">

            <div>

                <h1>
                    Manage Alumni
                </h1>

                <p>
                    Find approved alumni accounts and assist with account recovery.
                </p>

            </div>


            <div class="admin-user">

                <div class="admin-avatar">
                    S
                </div>

                <div>

                    <strong>
                        Student Representative
                    </strong>

                    <small>
                        Alumni Registration Officer
                    </small>

                </div>

            </div>

        </header>


        <!-- CONTENT -->

        <section class="dashboard-content">


            <div class="dashboard-panel">


                <div class="panel-header">

                    <div>

                        <h2>
                            Search Alumni
                        </h2>

                        <p>
                            Search using College ID Number, name, email, phone number, department, or graduation year.
                        </p>

                    </div>

                </div>


                <!-- SEARCH FORM -->

                <form
                    method="GET"
                    action="manage_alumni.php"
                    class="admin-search-form"
                >


                    <!-- TEXT SEARCH -->

                    <input
                        type="text"
                        name="search"
                        value="<?= e($search) ?>"
                        placeholder="Search College ID Number, name, email, or phone..."
                    >


                    <!-- DEPARTMENT -->

                    <select
                        name="department_id"
                    >

                        <option value="">
                            All Departments
                        </option>


                        <?php foreach ($departments as $department): ?>
 <option
                                value="<?= (int) $department["department_id"] ?>"
                                <?= $departmentId === (int) $department["department_id"] ? "selected" : "" ?>
                            >
                                <?= e($department["department_name"]) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>


                    <!-- GRADUATION YEAR -->

                    <select
                        name="graduation_year"
                    >

                        <option value="">
                            All Graduation Years
                        </option>


                        <?php

                        $currentYear = (int) date("Y");

                        for (
                            $year = $currentYear;
                            $year >= 1980;
                            $year--
                        ):

                        ?>

                            <option
                                value="<?= $year ?>"
                                <?= $graduationYear === (string) $year ? "selected" : "" ?>
                            >
                                <?= $year ?>
                            </option>

                        <?php endfor; ?>

                    </select>


                    <button
                        type="submit"
                        class="primary-button"
                    >
                        Search
                    </button>


                </form>


                <?php
                $hasSearch =
                    $search !== "" ||
                    $departmentId > 0 ||
                    $graduationYear !== "";
                ?>


                <?php if ($hasSearch): ?>


                    <?php if (count($alumni) > 0): ?>


                        <div class="table-responsive">


                            <table class="admin-table">


                                <thead>

                                    <tr>

                                        <th>
                                            College ID Number
                                        </th>

                                        <th>
                                            Name
                                        </th>

                                        <th>
                                            Email
                                        </th>

                                        <th>
                                            Phone
                                        </th>

                                        <th>
                                            Department
                                        </th>

                                        <th>
                                            Section / Program
                                        </th>

                                        <th>
                                            Specialization
                                        </th>

                                        <th>
                                            Level
                                        </th>

                                        <th>
                                            Graduation Year
                                        </th>

                                        <th>
                                            Action
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>


                                <?php foreach ($alumni as $person): ?>


                                    <tr>


                                        <td>

                                            <?php if (
                                                !empty($person["college_id_number"])
                                            ): ?>

                                                <?= e(
                                                    $person["college_id_number"]
                                                ) ?>

                                            <?php else: ?>

                                                <span>
                                                    Not provided
                                                </span>
 `php
<?php endif; ?>

                                        </td>


                                        <td>

                                            <?= e(
                                                $person["first_name"] .
                                                " " .
                                                $person["last_name"]
                                            ) ?>

                                        </td>


                                        <td>

                                            <?= e(
                                                $person["email"]
                                            ) ?>

                                        </td>


                                        <td>

                                            <?= e(
                                                $person["phone"] ?? ""
                                            ) ?>

                                        </td>


                                        <td>

                                            <?= e(
                                                $person["department_name"]
                                            ) ?>

                                        </td>

                                        <td>

                                            <?= e(
                                                $person["section_name"]
                                            ) ?>

                                        </td>

                                        <td>

                                            <?= e(
                                                $person["specialization_name"]
                                            ) ?>

                                        </td>

                                        <td>
    <?= !empty($person["level"])
        ? "Level " . e($person["level"])
        : "—"
    ?>
</td>

                                        <td>

                                            <?= e(
                                                $person["graduation_year"]
                                            ) ?>

                                        </td>


                                        <td>

                                            <a
                                                href="reset_password.php?id=<?= (int) $person["user_id"] ?>"
                                                class="secondary-button"
                                            >
                                                Reset Password
                                            </a>

                                        </td>


                                    </tr>


                                <?php endforeach; ?>


                                </tbody>


                            </table>


                        </div>


                    <?php else: ?>


                        <div class="empty-state">

                            <p>
                                No approved alumni account was found.
                            </p>

                        </div>


                    <?php endif; ?>


                <?php endif; ?>


            </div>


        </section>


    </main>


</div>


</body>

</html>