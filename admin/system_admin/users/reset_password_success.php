<?php
session_start();

require_once "../../../config/database.php";
require_once "../../../includes/functions.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "system_admin") {
    header("Location: ../../../index.php");
    exit;
}

if (!isset($_SESSION["reset_password_user"])) {
    header("Location: index.php");
    exit;
}

$resetUser = $_SESSION["reset_password_user"];

unset($_SESSION["reset_password_user"]);

$roleLabels = [
    "admin" => "Alumni President",
    "alumni" => "Alumni",
    "registrar" => "Registrar",
    "student_rep" => "Alumni Admin",
    "system_admin" => "System Administrator"
];

$roleLabel = $roleLabels[$resetUser["role"]] ?? $resetUser["role"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../../../assets/css/style.css">

    <title>Password Reset Successful</title>

    <style>
        .reset-success-container {
            max-width: 850px;
            margin: 0 auto;
        }

        .reset-success-card {
            background: #ffffff;
            border: 1px solid #e3d8cf;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        }

        .reset-success-alert {
            background: #edf8f0;
            border: 1px solid #b9dfc2;
            color: #246b38;
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 25px;
            font-weight: 600;
        }

        .reset-info {
            display: grid;
            grid-template-columns: 180px 1fr;
            gap: 18px;
            padding: 16px 0;
            border-bottom: 1px solid #eee6df;
        }

        .reset-info:last-of-type {
            border-bottom: none;
        }

        .reset-label {
            font-weight: 700;
            color: #5c3a25;
        }

        .reset-value {
            color: #333333;
        }

        .temporary-password-box {
            background: #f8f5f2;
            border: 2px dashed #9b6a48;
            border-radius: 8px;
            padding: 16px;
            font-size: 20px;
            font-weight: 700;
            color: #6f4328;
            letter-spacing: 1px;
            word-break: break-all;
        }

        .reset-warning {
            margin-top: 25px;
            background: #fff7e8;
            border: 1px solid #e8c98d;
            color: #6b4b1f;
            border-radius: 8px;
            padding: 16px;
            line-height: 1.6;
        }

        .reset-actions {
            display: flex;
            gap: 12px;
            margin-top: 28px;
            flex-wrap: wrap;
        }

        .reset-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }

        .reset-primary {
            background: #7a4b2a;
            color: #ffffff;
        }

        .reset-primary:hover {
            background: #633b22;
        }

        .reset-secondary {
            background: #f1ebe6;
            color: #6f4328;
            border: 1px solid #d8c8bc;
        }

        .reset-secondary:hover {
            background: #e7ddd5;
        }

        @media (max-width: 700px) {
            .reset-info {
                grid-template-columns: 1fr;
                gap: 7px;
            }

            .reset-success-card {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<?php include "../sidebar.php"; ?>

<main class="admin-main">

    <div class="admin-content">

        <div class="reset-success-container">

            <div class="page-header">
                <div>
                    <h1>Password Reset Successful</h1>
                    <p>The user's temporary password has been generated.</p>
                </div>
            </div>

            <div class="reset-success-card">
 <div class="reset-success-alert">
                    Password reset successfully.
                </div>

                <div class="reset-info">
                    <div class="reset-label">Email</div>
                    <div class="reset-value">
                        <?= e($resetUser["email"]) ?>
                    </div>
                </div>

                <div class="reset-info">
                    <div class="reset-label">Role</div>
                    <div class="reset-value">
                        <?= e($roleLabel) ?>
                    </div>
                </div>

                <div class="reset-info">
                    <div class="reset-label">Temporary Password</div>

                    <div class="reset-value">
                        <div class="temporary-password-box">
                            <?= e($resetUser["temporary_password"]) ?>
                        </div>
                    </div>
                </div>

                <div class="reset-warning">
                    <strong>Important:</strong>
                    Give this temporary password to the user securely.
                    The user will be required to change the password when they log in.
                    This temporary password will not be displayed again after leaving this page.
                </div>

                <div class="reset-actions">
                    <a href="index.php" class="reset-primary">
                        Back to Users
                    </a>

                    <a href="add.php" class="reset-secondary">
                        Add Another User
                    </a>
                </div>

            </div>

        </div>

    </div>

</main>

</body>
</html>