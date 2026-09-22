<?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";


/*
|--------------------------------------------------------------------------
| ADMIN ACCESS
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
| VARIABLES
|--------------------------------------------------------------------------
*/

$error = "";

$success = "";


/*
|--------------------------------------------------------------------------
| CREATE OPPORTUNITY
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $title = trim(
        $_POST["title"] ?? ""
    );


    $description = trim(
        $_POST["description"] ?? ""
    );


    $type = trim(
        $_POST["type"] ?? ""
    );


    $company_name = trim(
        $_POST["company_name"] ?? ""
    );


    $location = trim(
        $_POST["location"] ?? ""
    );


    $deadline =
        trim(
            $_POST[
                "deadline"
            ] ?? ""
        );


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */


    if (
        $title === ""
        || $description === ""
        || $type === ""
        || $company_name === ""
        || $location === ""
        || $deadline === ""
    ) {

        $error =
            "Please fill in all required fields.";

    }


    /*
    |--------------------------------------------------------------------------
    | TYPE VALIDATION
    |--------------------------------------------------------------------------
    */


    elseif (
        !in_array(
            $type,
            [
                "Job",
                "Internship"
            ],
            true
        )
    ) {

        $error =
            "Invalid opportunity type.";

    }


    /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */


    else {


        $status = "pending";


        $sql = "

            INSERT INTO opportunities (
                created_by,
                title,
                description,
                type,
                company_name,
                location,
                deadline,
                status,
                created_at

            )

            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())

        ";


        $stmt =
            $conn->prepare($sql);


        if (!$stmt) {

            $error =
                "Database error: "
                . $conn->error;

        } else {


            $stmt->bind_param(

                "isssssss",
                $_SESSION["user_id"],
                $title,
                $description,
                $type,
                $company_name,
                $location,
                $deadline,
                $status

            );


            if ($stmt->execute()) {


                $success =
                    "Opportunity created successfully and sent for approval.";


                /*
                |--------------------------------------------------------------------------
                | CLEAR FORM
                |--------------------------------------------------------------------------
                */

                $title = "";

                $description = "";

                $type = "";

                $company_name = "";

                $location = "";

                $deadline = "";


            } else {
                $error =
                    "Failed to create opportunity: "
                    . $stmt->error;

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

        Add Opportunity |

        <?= e(SITE_NAME) ?>

    </title>


    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >


    <style>

        .create-wrapper {

            max-width: 900px;

            margin: 35px auto;

            padding: 20px;

        }


        .form-card {

            background: #ffffff;

            border-radius: 14px;

            border: 1px solid #eeeeee;

            padding: 30px;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.05);

        }


        .form-card h2 {

            margin-top: 0;

            color: #4a2c1d;

        }


        .form-card p {

            color: #777777;

        }


        .form-group {

            margin-bottom: 20px;

        }


        .form-group label {

            display: block;

            margin-bottom: 7px;

            color: #4a2c1d;

            font-weight: 600;

            font-size: 14px;

        }


        .form-group input,

        .form-group select,

        .form-group textarea {

            width: 100%;

            box-sizing: border-box;

            padding: 12px 13px;

            border: 1px solid #dddddd;

            border-radius: 8px;

            font-family: inherit;

            font-size: 14px;

        }


        .form-group textarea {

            min-height: 170px;

            resize: vertical;

            line-height: 1.6;

        }


        .form-group input:focus,

        .form-group select:focus,

        .form-group textarea:focus {

            outline: none;

            border-color: #8b5e3c;

        }


        .required {

            color: #b3261e;

        }


        .form-actions {

            display: flex;

            gap: 10px;

            margin-top: 25px;

        }


        .submit-button {

            border: none;

            padding: 12px 20px;

            border-radius: 8px;

            background: #7a4b2a;

            color: #ffffff;

            font-weight: 600;

            cursor: pointer;

        }


        .submit-button:hover {

            background: #5f3921;

        }


        .cancel-button {

            display: inline-block;

            padding: 12px 20px;

            border-radius: 8px;

            border: 1px solid #8b5e3c;

            color: #8b5e3c;

            text-decoration: none;

            font-weight: 600;

        }


        .cancel-button:hover {

            background: #8b5e3c;

            color: #ffffff;

        }


        .alert {

            padding: 13px 15px;

            border-radius: 8px;

            margin-bottom: 20px;

        }


        .alert-error {

            background: #ffebee;

            color: #b71c1c;

            border: 1px solid #ffcdd2;

        }


        .alert-success {

            background: #e8f5e9;

            color: #2e7d32;

            border: 1px solid #c8e6c9;

        }


        .info-box {

            margin-top: 20px;

            padding: 15px;

            border-radius: 9px;

            background: #f8f5f2;

            color: #6b5140;

            line-height: 1.6;

            font-size: 14px;

        }


        @media (max-width: 600px) {

            .create-wrapper {

                padding: 10px;

            }


            .form-card {

                padding: 20px;

            }


            .form-actions {

                flex-direction: column;

            }

        }

    </style>


</head>


<body class="admin-body">


<div class="admin-layout">


    <!-- =====================================================
         SIDEBAR
    ====================================================== -->
    
<?php require_once __DIR__ ."/../includes/sidebar.php"; ?>

    <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->


    <main class="admin-main">


        <header class="admin-topbar">


            <div>

                <h1>
                    Add Opportunity
                </h1>

                <p>
                    Create a new job or internship opportunity.
                </p>

            </div>


        </header>


        <section class="dashboard-content">


            <div class="create-wrapper">


                <div class="form-card">


                    <h2>
                        New Job / Internship
                    </h2>


                    <p>
                        Complete the information below. New opportunities will be created as <strong>Pending</strong>.
                    </p>


                    <?php if ($error !== ""): ?>


                        <div class="alert alert-error">

                            <?= e($error) ?>

                        </div>


                    <?php endif; ?>


                    <?php if ($success !== ""): ?>


                        <div class="alert alert-success">

                            <?= e($success) ?>

                        </div>


                    <?php endif; ?>


                    <form
                        method="POST"
                        action=""
                    >


                        <!-- TITLE -->


                        <div class="form-group">


                            <label for="title">

                                Opportunity Title

                                <span class="required">
                                    *
                                </span>

                            </label>


                            <input
                                type="text"
                                id="title"
                                name="title"
                                placeholder="e.g. Junior Web Developer"
                                value="<?= e($title ?? "") ?>"
                                required
                            >


                        </div>


                        <!-- TYPE -->


                        <div class="form-group">


                            <label for="type">
                                Opportunity Type

                                <span class="required">
                                    *
                                </span>

                            </label>


                            <select
                                id="type"
                                name="type"
                                required
                            >


                                <option value="">
                                    Select Type
                                </option>


                                <option
                                    value="Job"
                                    <?= (
                                        ($type ?? "")
                                        === "Job"
                                    )
                                        ? "selected"
                                        : ""
                                    ?>
                                >
                                    Job
                                </option>


                                <option
                                    value="Internship"
                                    <?= (
                                        ($type ?? "")
                                        === "Internship"
                                    )
                                        ? "selected"
                                        : ""
                                    ?>
                                >
                                    Internship
                                </option>


                            </select>


                        </div>


                        <!-- COMPANY -->


                        <div class="form-group">


                            <label for="company_name">

                                Company Name

                                <span class="required">
                                    *
                                </span>

                            </label>


                            <input
                                type="text"
                                id="company_name"
                                name="company_name"
                                placeholder="e.g. ABC Technology"
                                value="<?= e($company_name ?? "") ?>"
                                required
                            >


                        </div>


                        <!-- LOCATION -->


                        <div class="form-group">


                            <label for="location">

                                Location

                                <span class="required">
                                    *
                                </span>

                            </label>


                            <input
                                type="text"
                                id="location"
                                name="location"
                                placeholder="e.g. Addis Ababa"
                                value="<?= e($location ?? "") ?>"
                                required
                            >


                        </div>


                        <!-- DEADLINE -->


                        <div class="form-group">


                            <label for="application_deadline">

                                Application Deadline

                                <span class="required">
                                    *
                                </span>

                            </label>


                            <input
                                type="date"
                                id="deadline"
                                name="deadline"
                                value="<?= e($deadline ?? "") ?>"
                                required
                            >


                        </div>
                        <!-- DESCRIPTION -->


                        <div class="form-group">


                            <label for="description">

                                Description

                                <span class="required">
                                    *
                                </span>

                            </label>


                            <textarea
                                id="description"
                                name="description"
                                placeholder="Describe the job/internship, requirements, responsibilities, qualifications, and other important information..."
                                required
                            ><?= e($description ?? "") ?></textarea>


                        </div>


                        <!-- INFO -->


                        <div class="info-box">

                            <strong>
                                Approval workflow:
                            </strong>

                            <br>

                            New opportunities are saved as
                            <strong>Pending</strong>.

                            After reviewing the opportunity,
                            the administrator can change its status
                            to <strong>Approved</strong>.

                            Only approved opportunities are shown
                            to alumni.

                        </div>


                        <!-- ACTIONS -->


                        <div class="form-actions">


                            <button
                                type="submit"
                                class="submit-button"
                            >

                                Create Opportunity

                            </button>


                            <a
                                href="index.php"
                                class="cancel-button"
                            >

                                Cancel

                            </a>


                        </div>


                    </form>


                </div>


            </div>


        </section>


    </main>


</div>


</body>

</html>