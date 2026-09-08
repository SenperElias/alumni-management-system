<?php

$currentPage = $currentPage ?? "";
$basePath = $basePath ?? "";

?>

<aside class="admin-sidebar">

    <div class="admin-brand">

        <div class="brand-logo">
            TM
        </div>

        <div>
            <strong>Alumni System</strong>
            <small>Alumni Portal</small>
        </div>

    </div>

    <nav class="admin-nav">

        <a
            href="/almuni-management-system/alumni/dashboard.php"
            class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>"
        >
            Dashboard
        </a>

        <div class="nav-section">
            MY ACCOUNT
        </div>

        <a
            href="/almuni-management-system/alumni/profile.php"
            class="<?= $currentPage === 'profile' ? 'active' : '' ?>"
        >
            My Profile
        </a>

        <a
            href="/almuni-management-system/alumni/employment.php"
            class="<?= $currentPage === 'employment' ? 'active' : '' ?>"
        >
            Employment
        </a>

        <div class="nav-section">
            OPPORTUNITIES
        </div>

        <a
            href="/almuni-management-system/alumni/jobs.php"
            class="<?= $currentPage === 'jobs' ? 'active' : '' ?>"
        >
            Jobs &amp; Internships
        </a>

        <a
            href="/almuni-management-system/alumni/mentorship/index.php"
            class="<?= $currentPage === 'mentorship' ? 'active' : '' ?>"
        >
            Mentorship
        </a>

        <div class="nav-section">
            ACTIVITIES
        </div>

        <a
            href="/almuni-management-system/alumni/projects/index.php"
            class="<?= $currentPage === 'projects' ? 'active' : '' ?>"
        >
            Projects
        </a>

        <a
            href="/almuni-management-system/alumni/events/events.php"
            class="<?= $currentPage === 'events' ? 'active' : '' ?>"
        >
            Events
        </a>

        <a
            href="/almuni-management-system/alumni/contributions/index.php"
            class="<?= $currentPage === 'contributions' ? 'active' : '' ?>"
        >
            Contributions
        </a>

        <div class="nav-section">
            SYSTEM
        </div>

        <a
            href="/almuni-management-system/alumni/notifications/index.php"
            class="<?= $currentPage === 'notifications' ? 'active' : '' ?>"
        >
            Notifications
        </a>

        <a
            href="/almuni-management-system/alumni/settings/index.php"
            class="<?= $currentPage === 'settings' ? 'active' : '' ?>"
        >
            Settings
        </a>

        <a
            href="/almuni-management-system/auth/logout.php"
            class="logout-link"
        >
            Logout
        </a>

    </nav>

</aside>