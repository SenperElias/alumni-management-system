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
            <small>Alumni President Portal</small>
        </div>
    </div>

    <nav class="admin-nav">

        <!-- Dashboard -->
        <a
           href="/almuni-management-system/admin/dashboard.php"
            class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>"
        >
            Dashboard
        </a>

        <div class="nav-section">
            MANAGEMENT
        </div>

        <!-- Alumni -->
        <a
            href="/almuni-management-system/admin/alumni/index.php"
            class="<?= $currentFolder === 'alumni' ? 'active' : '' ?>"
        >
            Alumni
        </a>

        <!-- Employment -->
        <a
           href="/almuni-management-system/admin/employment/index.php"
            class="<?= $currentFolder === 'employment' ? 'active' : '' ?>"
        >
            Employment
        </a>

        <!-- Opportunities -->
        <a
           href="/almuni-management-system/admin/opportunities/index.php"
            class="<?= $currentFolder === 'opportunities' ? 'active' : '' ?>"
        >
            Opportunities
        </a>

        <!-- Events -->
        <a
           href="/almuni-management-system/admin/events/index.php"
            class="<?= $currentFolder === 'events' ? 'active' : '' ?>"
        >
            Events
        </a>

        <!-- Mentorship -->
        <a
           href="/almuni-management-system/admin/mentors/index.php"
            class="<?= $currentFolder === 'mentors' ? 'active' : '' ?>"
        >
            Mentorship
        </a>

        <!-- Projects -->
        <a
           href="/almuni-management-system/admin/projects/index.php"
            class="<?= $currentFolder === 'projects' ? 'active' : '' ?>"
        >
            Projects
        </a>

        <!-- Contributions -->
        <a
            href="/almuni-management-system/admin/contributions/index.php"
            class="<?= $currentFolder === 'contributions' ? 'active' : '' ?>"
        >
            Contributions
        </a>

        <!-- Communication -->
        <div class="nav-section">
            COMMUNICATION
        </div>

        <!-- Contact Inquiries -->
        <a
           href="/almuni-management-system/admin/contact/index.php"
            class="<?= $currentFolder === 'contact' ? 'active' : '' ?>"
        >
            Contact Inquiries
        </a>

        <div class="nav-section">
            REPORTS
        </div>

        <!-- Employment Report -->
        <a
            href="/almuni-management-system/admin/reports/index.php"
            class="<?= $currentFolder === 'reports' ? 'active' : '' ?>"
        >
            Employment Report
        </a>

        <div class="nav-section">
            SYSTEM
        </div>



        <!-- Notifications -->
        <a
            href="/almuni-management-system/admin/notification/index.php"
            class="<?= $currentFolder === 'notification' ? 'active' : '' ?>"
        >
            Notifications
        </a>

        <!-- Settings -->
        <a
            href="/almuni-management-system/admin/seetings/index.php"
            class="<?= $currentFolder === 'seetings' ? 'active' : '' ?>"
        >
            Settings
        </a>

        <!-- Logout -->
        <a
           href="/almuni-management-system/auth/logout.php"
            class="logout-link"
        >
            Logout
        </a>

    </nav>

</aside>