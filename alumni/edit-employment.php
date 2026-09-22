 <?php

error_reporting(E_ALL);
session_start();

require_once "../config/database.php";
require_once "../config/config.php";
require_once "../includes/functions.php";

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "alumni") {
    header("Location: ../index.php");
    exit;
}

requirePasswordChange();

$userId = (int) $_SESSION["user_id"];

$error = "";
$success = "";

/*
|--------------------------------------------------------------------------
| Get Alumni ID
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT alumni_id
    FROM alumni
    WHERE user_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Alumni profile not found.");
}

$alumni = $result->fetch_assoc();
$alumniId = (int) $alumni["alumni_id"];

$stmt->close();

/*
|--------------------------------------------------------------------------
| Get Employment ID
|--------------------------------------------------------------------------
*/

$employmentId = (int) ($_GET["id"] ?? 0);

if ($employmentId <= 0) {
    header("Location: employment.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Get Employment Record
|--------------------------------------------------------------------------
|
| IMPORTANT:
| alumni_id is checked here.
| This prevents an alumni from editing another alumni's record.
|
*/

$stmt = $conn->prepare("
    SELECT
        employment_id,
        employment_status,
        company_name,
        job_position,
        work_location,
        industry,
        employment_date,
        end_date,
        verification_status,
        verification_notes
    FROM employment
    WHERE employment_id = ?
      AND alumni_id = ?
    LIMIT 1
");

$stmt->bind_param(
    "ii",
    $employmentId,
    $alumniId
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Employment record not found.");
}

$employment = $result->fetch_assoc();

$stmt->close();

/*
|--------------------------------------------------------------------------
| Update Employment
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /*
    |--------------------------------------------------------------------------
    | CSRF Protection
    |--------------------------------------------------------------------------
    */

    if (!verify_csrf_token()) {
        $error = "Invalid security token. Please try again.";
    } else {

        $employmentStatus = trim(
            $_POST["employment_status"] ?? ""
        );

        $companyName = trim(
            $_POST["company_name"] ?? ""
        );

        $jobPosition = trim(
            $_POST["job_position"] ?? ""
        );

        $workLocation = trim(
            $_POST["work_location"] ?? ""
        );

        $industry = trim(
            $_POST["industry"] ?? ""
        );

        $employmentDate = trim(
            $_POST["employment_date"] ?? ""
        );

        $endDate = trim(
            $_POST["end_date"] ?? ""
        );

        /*
        |--------------------------------------------------------------------------
        | Allowed Statuses
        |--------------------------------------------------------------------------
        */

        $allowedStatuses = [
            "employed",
            "self_employed",
            "unemployed",
            "continuing_education"
        ];

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */
 if (!in_array($employmentStatus, $allowedStatuses, true)) {

            $error = "Invalid employment status.";

        } elseif (
            $employmentStatus !== "unemployed" &&
            $employmentStatus !== "continuing_education" &&
            (
                $companyName === "" ||
                $jobPosition === "" ||
                $employmentDate === ""
            )
        ) {

            $error = "Please fill in all required employment fields.";

        } elseif (
            $employmentStatus === "continuing_education" &&
            (
                $employmentDate === ""
            )
        ) {

            $error = "Please enter the education start date.";

        } else {

            /*
            |--------------------------------------------------------------------------
            | Normalize Data
            |--------------------------------------------------------------------------
            */

            if ($employmentStatus === "unemployed") {

                $companyName = "";
                $jobPosition = "";
                $workLocation = "";
                $industry = "";
                $employmentDate = "";
                $endDate = "";

            } elseif ($employmentStatus === "continuing_education") {

                /*
                | employment_date is used as the education start date
                | because the database does not have a separate
                | education_start_date column.
                */

                $companyName = "";
                $jobPosition = "";
                $workLocation = "";
                $industry = "";
                $endDate = "";

            }

            /*
            |--------------------------------------------------------------------------
            | Update
            |--------------------------------------------------------------------------
            |
            | Alumni cannot change:
            | - verification_status
            | - verification_notes
            |
            | Those remain controlled by the administrator.
            |
            */

            $stmt = $conn->prepare("
                UPDATE employment
                SET
                    employment_status = ?,
                    company_name = ?,
                    job_position = ?,
                    work_location = ?,
                    industry = ?,
                    employment_date = ?,
                    end_date = ?,
                    updated_at = NOW(),
                    updated_by = ?
                WHERE employment_id = ?
                  AND alumni_id = ?
            ");

            $stmt->bind_param(
                "sssssssiii",
                $employmentStatus,
                $companyName,
                $jobPosition,
                $workLocation,
                $industry,
                $employmentDate,
                $endDate,
                $userId,
                $employmentId,
                $alumniId
            );

            if ($stmt->execute()) {

                $success =
                    "Employment information updated successfully.";

                /*
                |--------------------------------------------------------------------------
                | Reload Record
                |--------------------------------------------------------------------------
                */

                $reload = $conn->prepare("
                    SELECT
                        employment_id,
                        employment_status,
                        company_name,
                        job_position,
                        work_location,
                        industry,
                        employment_date,
                        end_date,
                        verification_status,
                        verification_notes
                    FROM employment
                    WHERE employment_id = ?
                      AND alumni_id = ?
                    LIMIT 1
                ");
 $reload->bind_param(
                    "ii",
                    $employmentId,
                    $alumniId
                );

                $reload->execute();

                $reloadResult = $reload->get_result();

                $employment = $reloadResult->fetch_assoc();

                $reload->close();

            } else {

                $error =
                    "Unable to update employment information.";
            }

            $stmt->close();
        }
    }
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
        Edit Employment |
        <?= e(SITE_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body class="admin-body">

<div class="admin-layout">

    <!-- SIDEBAR -->

    <?php
    require_once __DIR__ . "/includes/sidebar.php";
    ?>
                


    <!-- MAIN -->

    <main class="admin-main">

        <header class="admin-topbar">

            <div>

                <h1>
                    Edit Employment
                </h1>

                <p>
                    Update your employment information.
                </p>

            </div>

        </header>


        <section class="dashboard-content">

            <?php if ($error !== ""): ?>

                <div class="error-message">
                    <?= e($error) ?>
                </div>

            <?php endif; ?>


            <?php if ($success !== ""): ?>

                <div class="success-message">
                    <?= e($success) ?>
                </div>

            <?php endif; ?>


            <!-- VERIFICATION STATUS -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Verification Status
                        </h2>

                        <p>
                            This status is controlled by the administrator.
                        </p>

                    </div>

                </div>


                <div>

                    <span
 class="verification-badge
                        <?= e(
                            strtolower(
                                $employment["verification_status"]
                                ?? "pending"
                            )
                        )
                        ?>"
                    >

                        <?= e(
                            ucfirst(
                                str_replace(
                                    "_",
                                    " ",
                                    strtolower(
                                        $employment["verification_status"]
                                        ?? "pending"
                                    )
                                )
                            )
                        ) ?>

                    </span>

                </div>


                <?php if (
                    !empty(
                        $employment["verification_notes"]
                    )
                ): ?>

                    <div class="verification-notes">

                        <strong>
                            Admin Note:
                        </strong>

                        <?= e(
                            $employment["verification_notes"]
                        ) ?>

                    </div>

                <?php endif; ?>

            </div>


            <!-- EDIT FORM -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Employment Information
                        </h2>

                    </div>

                </div>


                <form method="POST">

                    <?= csrf_field() ?>


                    <div class="form-grid">


                        <!-- STATUS -->

                        <div class="form-group">

                            <label for="employment_status">
                                Employment Status *
                            </label>

                            <select
                                id="employment_status"
                                name="employment_status"
                                required
                            >

                                <option value="">
                                    Select status
                                </option>

                                <option
                                    value="employed"
                                    <?= $employment["employment_status"] === "employed"
                                        ? "selected"
                                        : "" ?>
                                >
                                    Employed
                                </option>

                                <option
                                    value="self_employed"
                                    <?= $employment["employment_status"] === "self_employed"
                                        ? "selected"
                                        : "" ?>
                                >
                                    Self-employed
                                </option>

                                <option
                                    value="unemployed"
                                    <?= $employment["employment_status"] === "unemployed"
                                        ? "selected"
                                        : "" ?>
                                >
                                    Unemployed
                                </option>

                                <option
                                    value="continuing_education"
<?= $employment["employment_status"] === "continuing_education"
                                        ? "selected"
                                        : "" ?>
                                >
                                    Continuing Education
                                </option>

                            </select>

                        </div>


                        <!-- COMPANY -->

                        <div class="form-group">

                            <label for="company_name">
                                Company / Organization
                            </label>

                            <input
                                type="text"
                                id="company_name"
                                name="company_name"
                                value="<?= e(
                                    $employment["company_name"] ?? ""
                                ) ?>"
                            >

                        </div>


                        <!-- POSITION -->

                        <div class="form-group">

                            <label for="job_position">
                                Job Position
                            </label>

                            <input
                                type="text"
                                id="job_position"
                                name="job_position"
                                value="<?= e(
                                    $employment["job_position"] ?? ""
                                ) ?>"
                            >

                        </div>


                        <!-- WORK LOCATION -->

                        <div class="form-group">

                            <label for="work_location">
                                Work Location
                            </label>

                            <input
                                type="text"
                                id="work_location"
                                name="work_location"
                                value="<?= e(
                                    $employment["work_location"] ?? ""
                                ) ?>"
                            >

                        </div>


                        <!-- INDUSTRY -->

                        <div class="form-group">

                            <label for="industry">
                                Industry
                            </label>

                            <input
                                type="text"
                                id="industry"
                                name="industry"
                                value="<?= e(
                                    $employment["industry"] ?? ""
                                ) ?>"
                            >

                        </div>


                        <!-- START DATE -->

                        <div class="form-group">

                            <label for="employment_date">
                                Employment Start Date
                            </label>

                            <input
                                type="date"
                                id="employment_date"
                                name="employment_date"
                                value="<?= e(
                                    $employment["employment_date"] ?? ""
                                ) ?>"
                            >

                        </div>


                        <!-- END DATE -->

                        <div class="form-group">

                            <label for="end_date">
                                End Date
                            </label>
 <input
                                type="date"
                                id="end_date"
                                name="end_date"
                                value="<?= e(
                                    $employment["end_date"] ?? ""
                                ) ?>"
                            >

                            <small>
                                Leave empty if you currently work here.
                            </small>

                        </div>

                    </div>


                    <div class="profile-form-actions">

                        <a
                            href="employment.php"
                            class="secondary-button"
                        >
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="primary-button"
                        >
                            Save Changes
                        </button>

                    </div>

                </form>

            </div>

        </section>

    </main>

</div>

</body>

</html>