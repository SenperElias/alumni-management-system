<?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";


/*
|--------------------------------------------------------------------------
| Admin Access
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
| Search & Filters
|--------------------------------------------------------------------------
*/

$search = trim($_GET["search"] ?? "");
$type = trim($_GET["type"] ?? "");
$status = trim($_GET["status"] ?? "");

/*
|--------------------------------------------------------------------------
| Approve / Reject Opportunity
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $opportunityId = (int) ($_POST["opportunity_id"] ?? 0);
    $action = $_POST["action"] ?? "";

    if ($opportunityId > 0 && $action === "approve") {

        $newStatus = "Approved";

        $stmt = $conn->prepare(
            "UPDATE opportunities
             SET status = ?,
                 reviewed_by = ?,
                 reviewed_at = NOW(),
                 updated_at = NOW()
             WHERE opportunity_id = ?"
        );

        $stmt->bind_param(
            "sii",
            $newStatus,
            $_SESSION["user_id"],
            $opportunityId
        );

        if ($stmt->execute()) {
            header("Location: index.php?status=Pending");
            exit;
        }

        $stmt->close();
    }


    if ($opportunityId > 0 && $action === "reject") {

        $newStatus = "Rejected";

        $stmt = $conn->prepare(
            "UPDATE opportunities
             SET status = ?,
                 reviewed_by = ?,
                 reviewed_at = NOW(),
                 updated_at = NOW()
             WHERE opportunity_id = ?"
        );

        $stmt->bind_param(
            "sii",
            $newStatus,
            $_SESSION["user_id"],
            $opportunityId
        );

        if ($stmt->execute()) {
            header("Location: index.php?status=Pending");
            exit;
        }

        $stmt->close();
    }
}
/*
|--------------------------------------------------------------------------
| Base Query
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        o.opportunity_id,
        o.type,
        o.title,
        o.company_name,
        o.location,
        o.deadline,
        o.status,
        o.created_at
    FROM opportunities o
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
            o.title LIKE ?
            OR o.company_name LIKE ?
            OR o.location LIKE ?
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
| Type Filter
|--------------------------------------------------------------------------
*/

if ($type !== "") {

    $sql .= " AND o.type = ? ";

    $params[] = $type;

    $types .= "s";
}


/*
|--------------------------------------------------------------------------
| Status Filter
|--------------------------------------------------------------------------
*/

if ($status !== "") {

    $sql .= " AND o.status = ? ";

    $params[] = $status;

    $types .= "s";
}


/*
|--------------------------------------------------------------------------
| Order
|--------------------------------------------------------------------------
*/

$sql .= " ORDER BY o.created_at DESC";


/*
|--------------------------------------------------------------------------
| Execute Query
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database query error: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$result = $stmt->get_result();

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
        Opportunities |
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

</head>


<body class="admin-body">


<div class="admin-layout">


    <!-- =========================================================
         SIDEBAR
    ========================================================== -->

    <aside class="admin-sidebar">


        <div class="admin-brand">

            <div class="brand-logo">
                TM
            </div>


            <div>

                <strong>
                    Alumni System
                </strong>

                <small>
                    Admin Portal
                </small>

            </div>

        </div>


        <nav class="admin-nav">


            <a href="../dashboard.php">
                Dashboard
            </a>


            <div class="nav-section">
                MANAGEMENT
            </div>
            <a href="../alumni/index.php">
                Alumni
            </a>


            <a
                href="index.php"
                class="active"
            >
                Opportunities
            </a>


            <a href="../events/index.php">
                Events
            </a>


            <a href="../mentors/index.php">
                Mentorship
            </a>


            <a href="../projects/index.php">
                Projects
            </a>


            <div class="nav-section">
                REPORTS
            </div>


            <a href="../reports/employment_reports.php">
                Employment Reports
            </a>


            <a href="../reports/alumni_reports.php">
                Alumni Reports
            </a>


            <div class="nav-section">
                SYSTEM
            </div>


            <a href="../notifications/index.php">
                Notifications
            </a>


            <a href="../settings/index.php">
                Settings
            </a>


            <a
                href="../../auth/logout.php"
                class="logout-link"
            >
                Logout
            </a>


        </nav>


    </aside>



    <!-- =========================================================
         MAIN CONTENT
    ========================================================== -->

    <main class="admin-main">


        <header class="admin-topbar">


            <div>

                <h1>
                    Opportunities
                </h1>

                <p>
                    Manage jobs, internships and training opportunities.
                </p>

            </div>


            <a
                href="add.php"
                class="primary-button"
            >
                + Add Opportunity
            </a>


        </header>



        <section class="dashboard-content">


            <!-- =================================================
                 SEARCH & FILTER
            ================================================== -->

            <div class="dashboard-panel">


                <form
                    method="GET"
                    class="search-filter-form"
                >


                    <div class="form-group">

                        <label for="search">
                            Search
                        </label>

                        <input
                            type="text"
                            id="search"
                            name="search"
                            value="<?= e($search) ?>"
                            placeholder="Search title, company or location..."
                        >

                    </div>



                    <div class="form-group">

                        <label for="type">
                            Type
                        </label>


                        <select
                            id="type"
                            name="type"
                        >

                            <option value="">
                                All Types
                            </option>


                            <option
                                value="Job"
                                <?= $type === "Job"
                                    ? "selected"
                                    : "" ?>
                            >
                                Job
                            </option>


                            <option
                                value="Internship"
                                <?= $type === "Internship"
                                    ? "selected"
                                    : "" ?>
                            >
                                Internship
                            </option>


                            <option
                                value="Training"
                                <?= $type === "Training"
                                    ? "selected"
                                    : "" ?>
                            >
                                Training
                            </option>


                        </select>
                       </div>



                    <div class="form-group">

                        <label for="status">
                            Status
                        </label>


                        <select
                            id="status"
                            name="status"
                        >

                            <option value="">
                                All Statuses
                            </option>


                            <option
                                value="Pending"
                                <?= $status === "Pending"
                                    ? "selected"
                                    : "" ?>
                            >
                                Pending
                            </option>


                            <option
                                value="Approved"
                                <?= $status === "Approved"
                                    ? "selected"
                                    : "" ?>
                            >
                                Approved
                            </option>


                            <option
                                value="Rejected"
                                <?= $status === "Rejected"
                                    ? "selected"
                                    : "" ?>
                            >
                                Rejected
                            </option>


                        </select>

                    </div>



                    <div class="form-group search-button-group">

                        <button
                            type="submit"
                            class="primary-button"
                        >
                            Search
                        </button>

                    </div>


                </form>


            </div>



            <!-- =================================================
                 OPPORTUNITIES
            ================================================== -->

            <div class="dashboard-panel">


                <div class="panel-header">


                    <div>

                        <h2>
                            Opportunity Management
                        </h2>

                        <p>
                            Review and manage jobs, internships and training.
                        </p>

                    </div>


                </div>



                <?php if ($result->num_rows > 0): ?>


                    <div class="table-responsive">


                        <table class="admin-table">


                            <thead>

                                <tr>

                                    <th>
                                        Title
                                    </th>

                                    <th>
                                        Company
                                    </th>

                                    <th>
                                        Type
                                    </th>

                                    <th>
                                        Location
                                    </th>

                                    <th>
                                        Deadline
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                    <th>
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                                <?php while (
                                    $opportunity =
                                    $result->fetch_assoc()
                                ): ?>


                                    <tr>


                                        <td>
                          <strong>

                                                <?= e(
                                                    $opportunity["title"]
                                                ) ?>

                                            </strong>

                                        </td>


                                        <td>

                                            <?= e(
                                                $opportunity["company_name"]
                                            ) ?>

                                        </td>


                                        <td>

                                            <?= e(
                                                $opportunity["type"]
                                            ) ?>

                                        </td>


                                        <td>

                                            <?= e(
                                                $opportunity["location"]
                                                ?: "Not specified"
                                            ) ?>

                                        </td>


                                        <td>

                                            <?= e(
                                                $opportunity["deadline"]
                                                ?: "Not specified"
                                            ) ?>

                                        </td>


                                        <td>


                                            <span
                                                class="status-badge <?= e(
                                                    strtolower(
                                                        $opportunity["status"]
                                                    )
                                                ) ?>"
                                            >

                                                <?= e(
                                                    $opportunity["status"]
                                                ) ?>

                                            </span>


                                        </td>


                                        <td>


                                            <div class="table-actions">


                                                <a
                                                    href="view.php?id=<?= (int)$opportunity["opportunity_id"] ?>"
                                                    class="secondary-button"
                                                >
                                                    View
                                                </a>

<?php if ($opportunity["status"] === "Pending"): ?>

    <form method="POST" style="display:inline;">

        <input
            type="hidden"
            name="opportunity_id"
            value="<?= (int)$opportunity["opportunity_id"] ?>"
        >

        <input
            type="hidden"
            name="action"
            value="approve"
        >

        <button
            type="submit"
            class="secondary-button"
            onclick="return confirm('Approve this opportunity?');"
        >
            Approve
        </button>

    </form>


    <form method="POST" style="display:inline;">

        <input
            type="hidden"
            name="opportunity_id"
            value="<?= (int)$opportunity["opportunity_id"] ?>"
        >

        <input
            type="hidden"
            name="action"
            value="reject"
        >

        <button
            type="submit"
            class="danger-button"
            onclick="return confirm('Reject this opportunity?');"
        >
            Reject
        </button>

    </form>

<?php endif; ?>
                                                <a
                                                    href="edit.php?id=<?= (int)$opportunity["opportunity_id"] ?>"
                                                    class="secondary-button"
                                                >
                                                    Edit
                                                </a>


                                                <a
                                                    href="delete.php?id=<?= (int)$opportunity["opportunity_id"] ?>"
                                                    class="danger-button"
                                                    onclick="return confirm('Are you sure you want to delete this opportunity?');"
                                                >
                                                    Delete
                                                </a>


                                            </div>


                                        </td>


                                    </tr>


                                <?php endwhile; ?>


                            </tbody>


                        </table>


                    </div>
                       <?php else: ?>


                    <div class="empty-dashboard">


                        <div>
                            💼
                        </div>


                        <h3>
                            No opportunities found
                        </h3>


                        <p>
                            There are currently no opportunities in the system.
                        </p>


                        <a
                            href="add.php"
                            class="primary-button"
                        >
                            + Add First Opportunity
                        </a>


                    </div>


                <?php endif; ?>


            </div>


        </section>


    </main>


</div>


</body>

</html>                