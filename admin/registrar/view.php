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

$registration_id = (int) ($_GET["id"] ?? 0);

if ($registration_id <= 0) {
    die("Invalid registration.");
}

/*
|--------------------------------------------------------------------------
| Get Registration
|--------------------------------------------------------------------------
*/
$sql = "
    SELECT
        r.*,
        d.department_name,
        s.section_name,
        sp.specialization_name
    FROM alumni_registrations r

    LEFT JOIN departments d
        ON r.department_id = d.department_id

    LEFT JOIN sections s
        ON r.section_id = s.section_id

    LEFT JOIN specializations sp
        ON r.specialization_id = sp.specialization_id

    WHERE r.registration_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $registration_id);
$stmt->execute();

$result = $stmt->get_result();

$registration = $result->fetch_assoc();

$stmt->close();

if (!$registration) {
    die("Registration not found.");
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
        Review Registration |
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
                    Review Alumni Registration
                </h1>

                <p>
                    Verify the applicant's information against
                    official college records.
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


            <!-- Verification Notice -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Verification Required
                        </h2>

                        <p>
                            Carefully compare the information below
                            with the official college records before
                            making a decision.
                        </p>

                    </div>
 </div>

            </div>


            <!-- Personal Information -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Personal Information
                        </h2>

                        <p>
                            Information submitted by the applicant.
                        </p>

                    </div>

                </div>


                <?php if (!empty($registration["profile_photo"])): ?>

                    <div style="margin-bottom: 20px;">

                        <img
                            src="../../uploads/<?= e($registration["profile_photo"]) ?>"
                            alt="Applicant Profile Photo"
                            style="
                                width: 120px;
                                height: 120px;
                                object-fit: cover;
                                border-radius: 50%;
                            "
                        >

                    </div>

                <?php endif; ?>


                <div class="form-grid">

                    <div class="form-field">

                        <label>
                            First Name
                        </label>

                        <input
                            type="text"
                            value="<?= e($registration["first_name"]) ?>"
                            readonly
                        >

                    </div>


                    <div class="form-field">

                        <label>
                            Last Name
                        </label>

                        <input
                            type="text"
                            value="<?= e($registration["last_name"]) ?>"
                            readonly
                        >

                    </div>


                    <div class="form-field">

                        <label>
                            College ID Number
                        </label>

                        <input
                            type="text"
                            value="<?= e($registration["college_id_number"]) ?>"
                            readonly
                        >

                    </div>


                    <div class="form-field">

                        <label>
                            Gender
                        </label>

                        <input
                            type="text"
                            value="<?= e($registration["gender"]) ?>"
                            readonly
                        >

                    </div>


                    <div class="form-field">

                        <label>
                            Date of Birth
                        </label>

                        <input
                            type="text"
                            value="<?= e($registration["date_of_birth"]) ?>"
                            readonly
                        >

                    </div>


                    <div class="form-field">

                        <label>
                            Email Address
                        </label>

                        <input
                            type="text"
                            value="<?= e($registration["email"]) ?>"
                            readonly
                        >

                    </div>


                    <div class="form-field">

                        <label>
                            Phone
                        </label>

                        <input
                            type="text"
                            value="<?= e($registration["phone"]) ?>"
                            readonly
                        >

                    </div>


                    <div class="form-field">

                        <label>
                            Address
                        </label>
 <input
                            type="text"
                            value="<?= e($registration["address"]) ?>"
                            readonly
                        >

                    </div>

                </div>

            </div>


            <!-- Education -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Education Information
                        </h2>

                        <p>
                            Academic information submitted by the applicant.
                        </p>

                    </div>

                </div>


                <div class="form-field">

    <label>
        Department
    </label>

    <input
        type="text"
        value="<?= e($registration["department_name"]) ?>"
        readonly
    >

</div>


<div class="form-field">

    <label>
        Section / Program
    </label>

    <input
        type="text"
        value="<?= e($registration["section_name"] ?? "—") ?>"
        readonly
    >

</div>


<div class="form-field">

    <label>
        Specialization
    </label>

    <input
        type="text"
        value="<?= e($registration["specialization_name"] ?? "—") ?>"
        readonly
    >

</div>


<div class="form-field">

    <label>
        Level
    </label>

    <input
        type="text"
        value="<?= $registration["level"] !== null
            ? e("Level " . $registration["level"])
            : "—" ?>"
        readonly
    >

</div>


<div class="form-field">

    <label>
        Graduation Year
    </label>

    <input
        type="text"
        value="<?= e($registration["graduation_year"]) ?>"
        readonly
    >

</div>
                </div>

            


            <!-- Biography -->

            <?php if (!empty($registration["bio"])): ?>

                <div class="dashboard-panel">

                    <div class="panel-header">

                        <div>

                            <h2>
                                Biography
                            </h2>

                        </div>

                    </div>

                    <p>
                        <?= nl2br(e($registration["bio"])) ?>
                    </p>

                </div>

            <?php endif; ?>


            <!-- Registration Details -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Registration Details
                        </h2>

                    </div>

                </div>

                <div class="form-grid">

                    <div class="form-field">

                        <label>
                            Current Status
                        </label>

                        <input
                            type="text"
                            value="<?= e(ucfirst($registration["status"])) ?>"
                            readonly
                        >

                    </div>


                    <div class="form-field">

                        <label>
                            Registration Date
                        </label>

                        <input
                            type="text"
                            value="<?= e($registration["created_at"]) ?>"
                            readonly
                        >

                    </div>

                </div>

            </div>


            <!-- Decision -->

            <?php if ($registration["status"] === "pending"): ?>

                <div class="dashboard-panel">

                    <div class="panel-header">

                        <div>

                            <h2>
                                Registrar Decision
                            </h2>

                            <p>
                                After checking the official records,
                                choose the appropriate action.
                            </p>

                        </div>

                    </div>


                   <div class="form-actions">

    <!-- Approve -->
    <form
        method="POST"
        action="approve.php"
        style="display:inline;"
        onsubmit="return confirm('Are you sure you want to approve this registration?');"
    >
        <?= csrf_field() ?>

        <input
            type="hidden"
            name="registration_id"
            value="<?= $registration_id ?>"
        >

        <button
            type="submit"
            class="primary-button"
        >
            Approve Registration
        </button>
    </form>

    <!-- Reject -->
  <a href="reject.php?id=<?= $registration_id ?>" class="secondary-button">

        Reject Registration
        </a>
        <?= csrf_field() ?>

        <input
            type="hidden"
            name="registration_id"
            value="<?= $registration_id ?>"
        >

        
    </form>

</div>

                </div>

            <?php else: ?>

                <div class="dashboard-panel">

                    <div class="panel-header">

                        <div>

                            <h2>
                                Registration Processed
                            </h2>

                            <p>
                                This registration has already been
                                <?= e($registration["status"]) ?>.
                            </p>

                        </div>

                    </div>

                </div>

            <?php endif; ?>


            <div class="form-actions">

                <a
                    href="pending.php"
                    class="secondary-button"
                >
                    ← Back to Pending Registrations
                </a>

            </div>

        </section>

    </main>

</div>

</body>

</html>