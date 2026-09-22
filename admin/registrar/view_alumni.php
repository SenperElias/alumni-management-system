<?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";

/*
|--------------------------------------------------------------------------
| Authorization
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

/*
|--------------------------------------------------------------------------
| Get Alumni ID
|--------------------------------------------------------------------------
*/

$alumni_id = (int) ($_GET["id"] ?? 0);

if ($alumni_id <= 0) {
    die("Invalid alumni ID.");
}

/*
|--------------------------------------------------------------------------
| Get Alumni Profile
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        a.*,
        u.email,
        u.account_status,
        d.department_name,
        s.section_name,
        sp.specialization_name
    FROM alumni a
    LEFT JOIN users u
        ON a.user_id = u.user_id
    LEFT JOIN departments d
        ON a.department_id = d.department_id
    LEFT JOIN sections s
        ON a.section_id = s.section_id
    LEFT JOIN specializations sp
        ON a.specialization_id = sp.specialization_id
    WHERE a.alumni_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log(
        "Database error in registrar/view_alumni.php: " .
        $conn->error
    );

    die(
        "Unable to load alumni information. " .
        "Please try again later."
    );
}

$stmt->bind_param("i", $alumni_id);

if (!$stmt->execute()) {
    error_log(
        "Database execute error in registrar/view_alumni.php: " .
        $stmt->error
    );

    $stmt->close();

    die(
        "Unable to load alumni information. " .
        "Please try again later."
    );
}

$result = $stmt->get_result();

$alumni = $result->fetch_assoc();

$stmt->close();

/*
|--------------------------------------------------------------------------
| Alumni Not Found
|--------------------------------------------------------------------------
*/

if (!$alumni) {
    die("Alumni record not found.");
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
        View Alumni |
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

    $activePage = "alumni";

    require_once __DIR__ . "/includes/sidebar.php";

    ?>


    <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->

    <main class="admin-main">

        <!-- TOPBAR -->

        <header class="admin-topbar">

            <div>

                <h1>
                    Alumni Profile
                </h1>

                <p>
                    View registered alumni information.
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
                 PROFILE HEADER
            ================================================== -->

            <div class="dashboard-panel">
 <div
                    style="
                        display:flex;
                        align-items:center;
                        gap:20px;
                        flex-wrap:wrap;
                    "
                >

                    <?php if (!empty($alumni["profile_photo"])): ?>

                        <img
                            src="../../uploads/<?= e($alumni["profile_photo"]) ?>"
                            alt="Profile Photo"
                            style="
                                width:120px;
                                height:120px;
                                object-fit:cover;
                                border-radius:50%;
                            "
                        >

                    <?php else: ?>

                        <div
                            style="
                                width:120px;
                                height:120px;
                                border-radius:50%;
                                display:flex;
                                align-items:center;
                                justify-content:center;
                                background:#eee;
                                font-size:40px;
                                font-weight:bold;
                            "
                        >

                            <?= e(
                                strtoupper(
                                    substr(
                                        $alumni["first_name"],
                                        0,
                                        1
                                    )
                                )
                            ) ?>

                        </div>

                    <?php endif; ?>


                    <div>

                        <h2>

                            <?= e(
                                $alumni["first_name"]
                                . " "
                                . $alumni["last_name"]
                            ) ?>

                        </h2>


                        <p>

                            <strong>
                                Alumni ID:
                            </strong>

                            <?= e(
                                $alumni["college_id_number"]
                            ) ?>

                        </p>


                        <p>

                            <strong>
                                Department:
                            </strong>

                            <?= e(
                                $alumni["department_name"]
                            ) ?>

                        </p>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 PERSONAL INFORMATION
            ================================================== -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Personal Information
                        </h2>

                    </div>

                </div>


                <div class="form-grid">


                    <div class="form-field">

                        <label>
                            First Name
                        </label>

                        <p>
                            <?= e(
                                $alumni["first_name"]
                            ) ?>
                        </p>

                    </div>


                    <div class="form-field">

                        <label>
                            Last Name
                        </label>

                        <p>
                            <?= e(
                                $alumni["last_name"]
                            ) ?>
                        </p>

                    </div>


                    <div class="form-field">
<label>
                            Gender
                        </label>

                        <p>
                            <?= e(
                                $alumni["gender"]
                            ) ?>
                        </p>

                    </div>


                    <div class="form-field">

                        <label>
                            Date of Birth
                        </label>

                        <p>
                            <?= e(
                                $alumni["date_of_birth"]
                            ) ?>
                        </p>

                    </div>


                    <div class="form-field">

                        <label>
                            Phone
                        </label>

                        <p>
                            <?= e(
                                $alumni["phone"]
                            ) ?>
                        </p>

                    </div>


                    <div class="form-field">

                        <label>
                            Email
                        </label>

                        <p>
                            <?= e(
                                $alumni["email"]
                            ) ?>
                        </p>

                    </div>


                    <div class="form-field form-full">

                        <label>
                            Address
                        </label>

                        <p>
                            <?= e(
                                $alumni["address"]
                            ) ?>
                        </p>

                    </div>


                </div>

            </div>


            <!-- =================================================
                 EDUCATION
            ================================================== -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Education Information
                        </h2>

                    </div>

                </div>


                <div class="form-grid">


                    <div class="form-field">

                        <label>
                            Department
                        </label>

                        <p>
                            <?= e(
                                $alumni["department_name"]
                            ) ?>
                        </p>

                    </div>
                    <div class="form-field">

    <label>
        Section / Program
    </label>

    <p>
        <?= e(
            $alumni["section_name"] ?? "—"
        ) ?>
    </p>

</div>

<div class="form-field">

    <label>
        Specialization
    </label>

    <p>
        <?= e(
            $alumni["specialization_name"] ?? "—"
        ) ?>
    </p>

</div>
<div class="form-field">

    <label>
        Level
    </label>

    <p>
        <?= !empty($alumni["level"])
            ? "Level " . e($alumni["level"])
            : "—"
        ?>
    </p>

</div>


                    <div class="form-field">

                        <label>
                            Graduation Year
                        </label>

                        <p>
                            <?= e(
                                $alumni["graduation_year"]
                            ) ?>
                        </p>

                    </div>


                </div>

            </div>


            <!-- =================================================
                 BIOGRAPHY
            ================================================== -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <div>

                        <h2>
                            Biography
                        </h2>

                    </div>

                </div>


                <?php if (!empty($alumni["bio"])): ?>

                    <p>

                        <?= nl2br(
                            e($alumni["bio"])
                        ) ?>

                    </p>

                <?php else: ?>

                    <p>
                        No biography provided.
                    </p>

                <?php endif; ?>


            </div>


            <!-- =================================================
                 ACCOUNT INFORMATION
            ================================================== -->

            <div class="dashboard-panel">
 <div class="panel-header">

                    <div>

                        <h2>
                            Account Information
                        </h2>

                    </div>

                </div>


                <div class="form-grid">


                    <div class="form-field">

                        <label>
                            Account Status
                        </label>

                        <p>
                            <?= e(
                                $alumni["account_status"]
                            ) ?>
                        </p>

                    </div>


                    <div class="form-field">

                        <label>
                            Alumni Record ID
                        </label>

                        <p>
                            <?= e(
                                $alumni["alumni_id"]
                            ) ?>
                        </p>

                    </div>


                </div>

            </div>


            <!-- =================================================
                 ACTIONS
            ================================================== -->

            <div class="form-actions">

                <a
                    href="alumni.php"
                    class="secondary-button"
                >
                    ← Back to Alumni Directory
                </a>

            </div>


        </section>

    </main>

</div>

</body>

</html>