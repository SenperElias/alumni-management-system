 <?php

session_start();

require_once "../config/database.php";
require_once "../config/config.php";
require_once "../includes/functions.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SESSION["role"] !== "alumni") {
    header("Location: ../index.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];

$error = "";
$success = "";


/*
|--------------------------------------------------------------------------
| Messages
|--------------------------------------------------------------------------
*/

if (isset($_GET["deleted"])) {
    $success = "Employment record deleted successfully.";
}

if (isset($_GET["error"])) {
    $error = "Unable to delete employment record.";
}


/*
|--------------------------------------------------------------------------
| Get Alumni ID
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT alumni_id, first_name, last_name
     FROM alumni
     WHERE user_id = ?
     LIMIT 1"
);

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Alumni profile not found.");
}

$alumni = $result->fetch_assoc();

$stmt->close();

$alumniId = (int) $alumni["alumni_id"];


/*
|--------------------------------------------------------------------------
| Add Employment / Education Record
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $employmentStatus = trim($_POST["employment_status"] ?? "");

    /*
    | Employment fields
    */
    $companyName = trim($_POST["company_name"] ?? "");
    $jobPosition = trim($_POST["job_position"] ?? "");
    $workLocation = trim($_POST["Work_location"] ?? "");
    $industry = trim($_POST["industry"] ?? "");
    $employmentDate = trim($_POST["employment_date"] ?? "");
    $endDate = trim($_POST["End_date"] ?? "");

    /*
    | Education fields
    */
    $educationInstitution = trim(
        $_POST["education_institution"] ?? ""
    );

    $educationProgram = trim(
        $_POST["education_program"] ?? ""
    );

    $educationLevel = trim(
        $_POST["education_level"] ?? ""
    );

    $educationStartDate = trim(
        $_POST["education_start_date"] ?? ""
    );

    $expectedCompletionDate = trim(
        $_POST["expected_completion_date"] ?? ""
    );


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($employmentStatus === "") {

        $error = "Please select an employment status.";

    } elseif (
        in_array(
            $employmentStatus,
            ["employed", "self_employed"],
            true
        )
        &&
        (
            $companyName === ""
            || $jobPosition === ""
            || $employmentDate === ""
        )
    ) {

        $error =
            "Company, job position, and employment start date are required.";

    } elseif (
        $employmentStatus === "continuing_education"
        &&
        (
            $educationInstitution === ""
            || $educationProgram === ""
            || $educationLevel === ""
            || $educationStartDate === ""
        )
    ) {

        $error =
            "Institution, program, education level, and study start date are required.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Verification
        |--------------------------------------------------------------------------
        */

        $verificationStatus = "pending";


        /*
        |--------------------------------------------------------------------------
        | Insert Record
        |--------------------------------------------------------------------------
        */
 $stmt = $conn->prepare(
            "INSERT INTO employment
            (
                alumni_id,
                employment_status,
                company_name,
                job_position,
                Work_location,
                industry,
                employment_date,
                End_date,
                education_institution,
                education_program,
                education_level,
                expected_completion_date,
                Verification_status,
                Verification_notes,
                Updated_by
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, ?)"
        );


        $stmt->bind_param(
            "issssssssssssi",
            $alumniId,
            $employmentStatus,
            $companyName,
            $jobPosition,
            $workLocation,
            $industry,
            $employmentDate,
            $endDate,
            $educationInstitution,
            $educationProgram,
            $educationLevel,
            $expectedCompletionDate,
            $verificationStatus,
            $userId
        );


        if ($stmt->execute()) {

            $success =
                "Employment information submitted successfully.";

        } else {

            $error =
                "Unable to save employment information.";

        }

        $stmt->close();
    }
}


/*
|--------------------------------------------------------------------------
| Get Employment Records
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT
        employment_id,
        employment_status,
        company_name,
        job_position,
        Work_location,
        industry,
        employment_date,
        End_date,
        education_institution,
        education_program,
        education_level,
        expected_completion_date,
        Verification_status,
        Verification_notes,
        created_at,
        updated_at
     FROM employment
     WHERE alumni_id = ?
     ORDER BY
        CASE
            WHEN employment_date IS NOT NULL
                 AND employment_date != ''
            THEN employment_date
            ELSE created_at
        END DESC"
);

$stmt->bind_param("i", $alumniId);
$stmt->execute();

$employmentResult = $stmt->get_result();

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
        Employment |
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

    $currentPage = "employment";

    require_once __DIR__ . "/includes/sidebar.php";

    ?>


    <!-- MAIN -->

    <main class="admin-main">


        <header class="admin-topbar">

            <div>

                <h1>
                    Employment
                </h1>

                <p>
                    Manage your employment and education history.
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


            <!-- ADD EMPLOYMENT -->

            <div class="dashboard-panel">


                <div class="panel-header">

                    <div>

                        <h2>
                            Add Employment / Education
                        </h2>

                        <p>
                            Add your current employment, previous employment,
                            unemployment status, or continuing education.
                        </p>
 </div>

                </div>


                <form method="POST">


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

                                <option value="employed">
                                    Employed
                                </option>

                                <option value="self_employed">
                                    Self-employed
                                </option>

                                <option value="unemployed">
                                    Unemployed
                                </option>

                                <option value="continuing_education">
                                    Continuing Education
                                </option>

                            </select>

                        </div>


                    </div>


                    <!-- EMPLOYMENT FIELDS -->

                    <div
                        id="employment-fields"
                        class="form-grid"
                        style="display:none;"
                    >


                        <div class="form-group">

                            <label for="company_name">
                                Company / Organization *
                            </label>

                            <input
                                type="text"
                                id="company_name"
                                name="company_name"
                            >

                        </div>


                        <div class="form-group">

                            <label for="job_position">
                                Job Position *
                            </label>

                            <input
                                type="text"
                                id="job_position"
                                name="job_position"
                            >

                        </div>


                        <div class="form-group">

                            <label for="Work_location">
                                Work Location
                            </label>

                            <input
                                type="text"
                                id="Work_location"
                                name="Work_location"
                                placeholder="e.g. Addis Ababa"
                            >

                        </div>


                        <div class="form-group">

                            <label for="industry">
                                Industry
                            </label>

                            <input
                                type="text"
                                id="industry"
                                name="industry"
                                placeholder="e.g. Information Technology"
                            >

                        </div>


                        <div class="form-group">

                            <label for="employment_date">
                                Employment Start Date *
                            </label>

                            <input
                                type="date"
                                id="employment_date"
                                name="employment_date"
                            >

                        </div>


                        <div class="form-group">
                          <label for="End_date">
                                End Date
                            </label>

                            <input
                                type="date"
                                id="End_date"
                                name="End_date"
                            >

                            <small>
                                Leave empty if you currently work here.
                            </small>

                        </div>


                    </div>


                    <!-- EDUCATION FIELDS -->

                    <div
                        id="education-fields"
                        class="form-grid"
                        style="display:none;"
                    >


                        <div class="form-group">

                            <label for="education_institution">
                                Institution *
                            </label>

                            <input
                                type="text"
                                id="education_institution"
                                name="education_institution"
                                placeholder="e.g. Addis Ababa University"
                            >

                        </div>


                        <div class="form-group">

                            <label for="education_program">
                                Program / Field of Study *
                            </label>

                            <input
                                type="text"
                                id="education_program"
                                name="education_program"
                                placeholder="e.g. Computer Science"
                            >

                        </div>


                        <div class="form-group">

                            <label for="education_level">
                                Education Level *
                            </label>

                            <select
                                id="education_level"
                                name="education_level"
                            >

                                <option value="">
                                    Select education level
                                </option>

                                <option value="Certificate">
                                    Certificate
                                </option>

                                <option value="Diploma">
                                    Diploma
                                </option>

                                <option value="Bachelor's Degree">
                                    Bachelor's Degree
                                </option>

                                <option value="Master's Degree">
                                    Master's Degree
                                </option>

                                <option value="Doctorate">
                                    Doctorate
                                </option>

                                <option value="Other">
                                    Other
                                </option>

                            </select>

                        </div>


                        <div class="form-group">

                            <label for="education_start_date">
                                Study Start Date *
                            </label>

                            <input
                                type="date"
                                id="education_start_date"
                                name="education_start_date"
                            >

                        </div>


                        <div class="form-group">

                            <label for="expected_completion_date">
                                Expected Completion Date
                            </label>
                             <input
                                type="date"
                                id="expected_completion_date"
                                name="expected_completion_date"
                            >

                        </div>


                    </div>


                    <div class="profile-form-actions">

                        <button
                            type="submit"
                            class="primary-button"
                        >
                            Submit Information
                        </button>

                    </div>


                </form>


            </div>


            <!-- HISTORY -->

            <div class="dashboard-panel">


                <div class="panel-header">

                    <div>

                        <h2>
                            Employment & Education History
                        </h2>

                        <p>
                            Your submitted employment and education records.
                        </p>

                    </div>

                </div>


                <?php if ($employmentResult->num_rows > 0): ?>


                    <div class="employment-list">


                        <?php while (
                            $job = $employmentResult->fetch_assoc()
                        ): ?>


                            <?php

                            $recordStatus =
                                strtolower(
                                    trim(
                                        $job["employment_status"] ?? ""
                                    )
                                );

                            $verificationStatus =
                                strtolower(
                                    $job["Verification_status"]
                                    ?? "pending"
                                );

                            ?>


                            <div class="employment-card">


                                <!-- CARD HEADER -->

                                <div class="employment-card-header">


                                    <div>


                                        <?php if (
                                            $recordStatus ===
                                            "continuing_education"
                                        ): ?>


                                            <h3>
                                                <?= e(
                                                    $job["education_program"]
                                                    ?: "Continuing Education"
                                                ) ?>
                                            </h3>


                                            <strong>
                                                <?= e(
                                                    $job["education_institution"]
                                                    ?: "Institution not provided"
                                                ) ?>
                                            </strong>


                                        <?php elseif (
                                            $recordStatus ===
                                            "unemployed"
                                        ): ?>


                                            <h3>
                                                Unemployed
                                            </h3>


                                            <strong>
                                                Employment Status
                                            </strong>


                                        <?php else: ?>


                                            <h3>
                                               <?= e(
                                                    $job["job_position"]
                                                    ?: "Position not provided"
                                                ) ?>
                                            </h3>


                                            <strong>
                                                <?= e(
                                                    $job["company_name"]
                                                    ?: "Organization not provided"
                                                ) ?>
                                            </strong>


                                        <?php endif; ?>


                                    </div>


                                    <span
                                        class="verification-badge
                                        <?= e($verificationStatus) ?>"
                                    >

                                        <?= e(
                                            ucfirst(
                                                $verificationStatus
                                            )
                                        ) ?>

                                    </span>


                                </div>


                                <!-- DETAILS -->

                                <div class="employment-details">


                                    <?php if (
                                        $recordStatus ===
                                        "continuing_education"
                                    ): ?>


                                        <!-- EDUCATION RECORD -->


                                        <div>

                                            <span>
                                                Status
                                            </span>

                                            <strong>
                                                Continuing Education
                                            </strong>

                                        </div>


                                        <div>

                                            <span>
                                                Institution
                                            </span>

                                            <strong>
                                                <?= e(
                                                    $job[
                                                        "education_institution"
                                                    ]
                                                    ?: "Not provided"
                                                ) ?>
                                            </strong>

                                        </div>


                                        <div>

                                            <span>
                                                Program / Field
                                            </span>

                                            <strong>
                                                <?= e(
                                                    $job[
                                                        "education_program"
                                                    ]
                                                    ?: "Not provided"
                                                ) ?>
                                            </strong>

                                        </div>


                                        <div>

                                            <span>
                                                Education Level
                                            </span>
                                             <strong>
                                                <?= e(
                                                    $job[
                                                        "education_level"
                                                    ]
                                                    ?: "Not provided"
                                                ) ?>
                                            </strong>

                                        </div>


                                        <div>

                                            <span>
                                                Study Start Date
                                            </span>

                                            <strong>
                                                <?= e(
                                                    $job[
                                                        "employment_date"
                                                    ]
                                                    ?: "Not provided"
                                                ) ?>
                                            </strong>

                                        </div>


                                        <div>

                                            <span>
                                                Expected Completion
                                            </span>

                                            <strong>

                                                <?php if (
                                                    !empty(
                                                        $job[
                                                            "expected_completion_date"
                                                        ]
                                                    )
                                                ): ?>

                                                    <?= e(
                                                        $job[
                                                            "expected_completion_date"
                                                        ]
                                                    ) ?>

                                                <?php else: ?>

                                                    Not provided

                                                <?php endif; ?>

                                            </strong>

                                        </div>


                                    <?php elseif (
                                        $recordStatus ===
                                        "unemployed"
                                    ): ?>


                                        <!-- UNEMPLOYED RECORD -->


                                        <div>

                                            <span>
                                                Status
                                            </span>

                                            <strong>
                                                Unemployed
                                            </strong>

                                        </div>


                                        <div>

                                            <span>
                                                Record Date
                                            </span>

                                            <strong>
                                                <?= e(
                                                    $job["created_at"]
                                                ) ?>
                                            </strong>

                                        </div>


                                    <?php else: ?>


                                        <!-- EMPLOYMENT RECORD -->


                                        <div>
 <span>
                                                Status
                                            </span>

                                            <strong>
                                                <?= e(
                                                    ucwords(
                                                        str_replace(
                                                            "_",
                                                            " ",
                                                            $recordStatus
                                                        )
                                                    )
                                                ) ?>
                                            </strong>

                                        </div>


                                        <div>

                                            <span>
                                                Location
                                            </span>

                                            <strong>
                                                <?= e(
                                                    $job["Work_location"]
                                                    ?: "Not provided"
                                                ) ?>
                                            </strong>

                                        </div>


                                        <div>

                                            <span>
                                                Industry
                                            </span>

                                            <strong>
                                                <?= e(
                                                    $job["industry"]
                                                    ?: "Not provided"
                                                ) ?>
                                            </strong>

                                        </div>


                                        <div>

                                            <span>
                                                Start Date
                                            </span>

                                            <strong>
                                                <?= e(
                                                    $job["employment_date"]
                                                    ?: "Not provided"
                                                ) ?>
                                            </strong>

                                        </div>


                                        <div>

                                            <span>
                                                End Date
                                            </span>

                                            <strong>

                                                <?php if (
                                                    !empty(
                                                        $job["End_date"]
                                                    )
                                                ): ?>

                                                    <?= e(
                                                        $job["End_date"]
                                                    ) ?>

                                                <?php else: ?>

                                                    Present

                                                <?php endif; ?>

                                            </strong>

                                        </div>


                                    <?php endif; ?>


                                </div>


                                <!-- VERIFICATION NOTES -->
                                  <?php if (
                                    !empty(
                                        $job["Verification_notes"]
                                    )
                                ): ?>


                                    <div class="verification-notes">

                                        <strong>
                                            Admin Note:
                                        </strong>

                                        <?= e(
                                            $job[
                                                "Verification_notes"
                                            ]
                                        ) ?>

                                    </div>


                                <?php endif; ?>


                                <!-- ACTIONS -->

                                <div class="employment-actions">


                                    <a
                                        href="edit-employment.php?id=<?= (int) $job["employment_id"] ?>"
                                        class="secondary-button"
                                    >
                                        Edit
                                    </a>


                                    <a
                                        href="delete-employment.php?id=<?= (int) $job["employment_id"] ?>"
                                        class="danger-button"
                                        onclick="return confirm('Are you sure you want to delete this record?');"
                                    >
                                        Delete
                                    </a>


                                </div>


                            </div>


                        <?php endwhile; ?>


                    </div>


                <?php else: ?>


                    <div class="empty-dashboard">

                        <div>
                            💼
                        </div>

                        <h3>
                            No employment or education records
                        </h3>

                        <p>
                            Add your current employment, previous employment,
                            unemployment status, or continuing education above.
                        </p>

                    </div>


                <?php endif; ?>


            </div>


        </section>


    </main>


</div>


<!-- STATUS FIELD TOGGLE -->

<script>

document.addEventListener("DOMContentLoaded", function () {

    const statusSelect =
        document.getElementById("employment_status");

    const employmentFields =
        document.getElementById("employment-fields");

    const educationFields =
        document.getElementById("education-fields");


    function updateFields() {

        const status =
            statusSelect.value;


        employmentFields.style.display = "none";

        educationFields.style.display = "none";


        if (
            status === "employed"
            ||
            status === "self_employed"
        ) {

            employmentFields.style.display = "grid";

        }


        if (
            status === "continuing_education"
        ) {

            educationFields.style.display = "grid";

        }

    }


    statusSelect.addEventListener(
        "change",
        updateFields
    );


    updateFields();

});

</script>


</body>

</html>