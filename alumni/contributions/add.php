<?php

session_start();

require_once "../../config/database.php";
require_once "../../config/config.php";
require_once "../../includes/functions.php";


/*
|--------------------------------------------------------------------------
| ALUMNI ACCESS
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {

    header("Location: ../../auth/login.php");
    exit;

}

if ($_SESSION["role"] !== "alumni") {

    header("Location: ../../index.php");
    exit;

}


$user_id = (int) $_SESSION["user_id"];
$alumni_sql = "SELECT alumni_id FROM alumni WHERE user_id = ? LIMIT 1";
$alumni_stmt = $conn->prepare($alumni_sql);
$alumni_stmt->bind_param("i", $user_id);
$alumni_stmt->execute();

$alumni_result = $alumni_stmt->get_result();
$alumni = $alumni_result->fetch_assoc();

$alumni_stmt->close();

if (!$alumni) {
    die("Alumni profile not found.");
}

$alumni_id = (int) $alumni["alumni_id"];


/*
|--------------------------------------------------------------------------
| GET REAL ALUMNI ID
|--------------------------------------------------------------------------
*/

$alumni_sql = "

    SELECT alumni_id

    FROM alumni

    WHERE user_id = ?

    LIMIT 1

";


$alumni_stmt =
    $conn->prepare($alumni_sql);


if (!$alumni_stmt) {

    die(
        "Database error: "
        . $conn->error
    );

}


$alumni_stmt->bind_param(
    "i",
    $user_id
);


$alumni_stmt->execute();


$alumni_result =
    $alumni_stmt->get_result();


$alumni =
    $alumni_result->fetch_assoc();


$alumni_stmt->close();


if (!$alumni) {

    die(
        "Alumni profile not found for this account."
    );

}


$alumni_id =
    (int) $alumni["alumni_id"];


/*
|--------------------------------------------------------------------------
| FORM VARIABLES
|--------------------------------------------------------------------------
*/

$errors = [];

$contribution_type = "";

$description = "";

$amount = "";

$contribution_date =
    date("Y-m-d");

$purpose = "";


/*
|--------------------------------------------------------------------------
| PROCESS FORM
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
) {


    $contribution_type =
        trim(
            $_POST[
                "contribution_type"
            ] ?? ""
        );

      

    $description =
        trim(
            $_POST[
                "description"
            ] ?? ""
        );


    $amount =
        trim(
            $_POST[
                "amount"
            ] ?? ""
        );


    $contribution_date =
        trim(
            $_POST[
                "contribution_date"
            ] ?? ""
        );


    $purpose =
        trim(
            $_POST[
                "purpose"
            ] ?? ""
        );


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        $contribution_type === ""
    ) {

        $errors[] =
            "Please select a contribution type.";

    }


    if ($amount === "") {

        $errors[] =
            "Please enter the contribution amount.";

    }

    elseif (
        !is_numeric($amount)
        || (float) $amount < 0
    ) {

        $errors[] =
            "Please enter a valid amount.";

    }


    if (
        $contribution_date === ""
    ) {

        $errors[] =
            "Please select the contribution date.";

    }


    if ($purpose === "") {

        $errors[] =
            "Please enter the purpose of the contribution.";

    }


    /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {


        $status = "Pending";


        $amount_value =
            (float) $amount;


        $sql = "

            INSERT INTO contributions

            (
                alumni_id,
                contribution_type,
                description,
                amount,
                contribution_date,
                purpose,
                status,
                created_at
            )

            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                NOW()
            )

        ";


        $stmt =
            $conn->prepare($sql);


        if (!$stmt) {

            die(
                "Database error: "
                . $conn->error
            );

        }


        $stmt->bind_param(

            "issdsss",

            $alumni_id,

            $contribution_type,

            $description,

            $amount_value,

            $contribution_date,

            $purpose,

            $status

        );


        if (
            $stmt->execute()
        ) {


            $stmt->close();


            header(
                "Location: index.php?success="
                . urlencode(
                    "Contribution submitted successfully."
                )
            );


            exit;

        }


        $errors[] =
            "Unable to save contribution: "
            . $stmt->error;


        $stmt->close();

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

        Add Contribution |

        <?= e(SITE_NAME) ?>

    </title>


    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >


    <style>

        .contribution-form-page {

            max-width: 850px;

            margin: 35px auto;

            padding: 20px;

        }


        .form-card {

            background: #ffffff;

            border: 1px solid #eeeeee;

            border-radius: 14px;

            padding: 30px;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.05);

        }


        .form-header {

            margin-bottom: 25px;

        }


        .form-header h1 {

            margin: 0 0 8px;

            color: #4a2c1d;

        }


        .form-header p {

            margin: 0;

            color: #777;

            line-height: 1.6;

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

            padding: 11px 13px;

            border: 1px solid #dddddd;

            border-radius: 8px;

            font-family: inherit;

            font-size: 14px;

            background: #ffffff;

        }


        .form-group textarea {

            min-height: 120px;

            resize: vertical;

        }


        .form-group input:focus,

        .form-group select:focus,

        .form-group textarea:focus {

            outline: none;

            border-color: #7a4b2a;

        }


        .help-text {

            margin-top: 6px;

            color: #888;

            font-size: 12px;

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


        .form-actions {

            display: flex;

            gap: 10px;

            margin-top: 25px;

            flex-wrap: wrap;

        }


        .submit-button,

        .cancel-button {

            display: inline-block;

            padding: 11px 18px;

            border-radius: 8px;

            border: none;

            font-weight: 600;

            text-decoration: none;

            cursor: pointer;

        }


        .submit-button {

            background: #7a4b2a;

            color: #ffffff;

        }


        .submit-button:hover {

            background: #5f3921;

        }


        .cancel-button {
            background: #eeeeee;

            color: #444444;

        }


        .cancel-button:hover {

            background: #dddddd;

        }


        @media (max-width: 650px) {

            .contribution-form-page {

                padding: 12px;

            }


            .form-card {

                padding: 20px;

            }

        }

    </style>

</head>


<body class="admin-body">


<div class="admin-layout">


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
                    Alumni Portal
                </small>

            </div>


        </div>


        <nav class="admin-nav">


            <a href="../dashboard.php">
                Dashboard
            </a>


            <div class="nav-section">
                MY ACCOUNT
            </div>


            <a href="../profile.php">
                My Profile
            </a>


            <a href="../employment.php">
                Employment
            </a>


            <div class="nav-section">
                OPPORTUNITIES
            </div>


            <a href="../jobs.php">
                Jobs & Internships
            </a>


            <a href="../mentorship/index.php">
                Mentorship
            </a>


            <div class="nav-section">
                ACTIVITIES
            </div>


            <a href="../projects/browse.php">
                Projects
            </a>


            <a href="../events/events.php">
                Events
            </a>


            <a
                href="index.php"
                class="active"
            >
                Contributions
            </a>


            <div class="nav-section">
                SYSTEM
            </div>


            <a href="#">
                Notifications
            </a>


            <a href="#">
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


    <main class="admin-main">


        <header class="admin-topbar">


            <div>

                <h1>
                    Add Contribution
                </h1>

                <p>
                    Submit a contribution or donation record.
                </p>

            </div>


        </header>


        <section class="dashboard-content">


            <div class="contribution-form-page">


                <div class="form-card">


                    <div class="form-header">


                        <h1>
                            Submit Contribution
                        </h1>


                        <p>
                            Enter the details below. Your contribution will be submitted for admin verification.
                        </p>


                    </div>


                    <?php if (
                        !empty($errors)
                    ): ?>


                        <div class="alert alert-error">


                            <?php foreach (
                                $errors
                                as $error
                            ): ?>


                                <div>

                                    <?= e($error) ?>

                                </div>


                            <?php endforeach; ?>


                        </div>


                    <?php endif; ?>


                    <form
                        method="POST"
                        action=""
                    >


                        <div class="form-group">


                            <label
                                for="contribution_type"
                            >

                                Contribution Type
                                <span>*</span>
                           </label>


                            <select
                                name="contribution_type"
                                id="contribution_type"
                                required
                            >

<option value="">Select type</option>

<option
    value="financial_donation"
    <?= $contribution_type === "financial_donation" ? "selected" : "" ?>
>
    Financial Donation
</option>

<option
    value="equipment_donation"
    <?= $contribution_type === "equipment_donation" ? "selected" : "" ?>
>
    Equipment Donation
</option>

<option
    value="training_support"
    <?= $contribution_type === "training_support" ? "selected" : "" ?>
>
    Training Support
</option>

<option
    value="internship_support"
    <?= $contribution_type === "internship_support" ? "selected" : "" ?>
>
    Internship Support
</option>

<option
    value="other"
    <?= $contribution_type === "other" ? "selected" : "" ?>
>
    Other
</option>
                               
                                   
                                


                            </select>


                        </div>


                        <div class="form-group">


                            <label for="amount">

                                Amount
                                <span>*</span>

                            </label>


                            <input
                                type="number"
                                name="amount"
                                id="amount"
                                min="0"
                                step="0.01"
                                value="<?= e($amount) ?>"
                                placeholder="Enter amount"
                                required
                            >


                            <div class="help-text">

                                Enter 0 if the contribution does not have a monetary value.

                            </div>


                        </div>


                        <div class="form-group">


                            <label
                                for="contribution_date"
                            >

                                Contribution Date
                                <span>*</span>

                            </label>


                            <input
                                type="date"
                                name="contribution_date"
                                id="contribution_date"
                                value="<?= e($contribution_date) ?>"
                                required
                            >


                        </div>


                        <div class="form-group">


                            <label for="purpose">

                                Purpose
                                <span>*</span>

                            </label>
                            <input
                                type="text"
                                name="purpose"
                                id="purpose"
                                value="<?= e($purpose) ?>"
                                placeholder="Example: College library support"
                                maxlength="255"
                                required
                            >


                        </div>


                        <div class="form-group">


                            <label for="description">

                                Description

                            </label>


                            <textarea
                                name="description"
                                id="description"
                                placeholder="Add any additional information about your contribution..."
                            ><?= e($description) ?></textarea>


                        </div>


                        <div class="form-actions">


                            <button
                                type="submit"
                                class="submit-button"
                            >

                                Submit Contribution

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