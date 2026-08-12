<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$currentFolder = basename(dirname($_SERVER['PHP_SELF']));
?>

<aside class="admin-sidebar">

    <div class="admin-brand">
        <div class="brand-logo">
            TM
        </div>

        <div>
            <strong>Alumni System</strong>
            <small>Admin Portal</small>
        </div>
    </div>

    <nav class="admin-nav">

        <!-- Dashboard -->
        <a
            href="../dashboard.php"
            class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>"
        >
            Dashboard
        </a>

        <div class="nav-section">
            MANAGEMENT
        </div>

        <!-- Alumni -->
        <a
            href="../alumni/index.php"
            class="<?= $currentFolder === 'alumni' ? 'active' : '' ?>"
        >
            Alumni
        </a>

        <!-- Employment -->
        <a
            href="../employment/index.php"
            class="<?= $currentFolder === 'employment' ? 'active' : '' ?>"
        >
            Employment
        </a>

        <!-- Opportunities -->
        <a
            href="../opportunities/index.php"
            class="<?= $currentFolder === 'opportunities' ? 'active' : '' ?>"
        >
            Opportunities
        </a>

        <!-- Events -->
        <a
            href="../events/index.php"
            class="<?= $currentFolder === 'events' ? 'active' : '' ?>"
        >
            Events
        </a>

        <!-- Mentorship -->
        <a
            href="../mentors/index.php"
            class="<?= $currentFolder === 'mentors' ? 'active' : '' ?>"
        >
            Mentorship
        </a>

        <!-- Projects -->
        <a
            href="../projects/index.php"
            class="<?= $currentFolder === 'projects' ? 'active' : '' ?>"
        >
            Projects
        </a>

        <!-- Contributions -->
        <a
            href="../contributions/index.php"
            class="<?= $currentFolder === 'contributions' ? 'active' : '' ?>"
        >
            Contributions
        </a>

        <div class="nav-section">
            REPORTS
        </div>

        <!-- Employment Report -->
        <a
            href="../reports/index.php"
            class="<?= $currentFolder === 'reports' ? 'active' : '' ?>"
        >
            Employment Report
        </a>

        <div class="nav-section">
            SYSTEM
        </div>

        <!-- Users -->
        <a
            href="../users/index.php"
            class="<?= $currentFolder === 'users' ? 'active' : '' ?>"
        >
            Users
        </a>

        <!-- Notifications -->
        <a
            href="../notification/index.php"
            class="<?= $currentFolder === 'notification' ? 'active' : '' ?>"
        >
            Notifications
        </a>

        <!-- Settings -->
        <a
            href="../seetings/index.php"
            class="<?= $currentFolder === 'seetings' ? 'active' : '' ?>"
        >
            Settings
        </a>

        <!-- Logout -->
        <a
            href="../../auth/logout.php"
            class="logout-link"
        >
            Logout
        </a>

    </nav>

</aside>