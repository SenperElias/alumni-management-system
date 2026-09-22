<!-- Student Representative Sidebar -->

<aside class="admin-sidebar">

    <div class="admin-brand">

        
        <div class="brand-logo">
            <img src="/almuni-management-system/assets/img/college-logo.jpg" alt="Taferi Mekonnen polytechnic Techhnical college Logo">
        </div>
        <div>
            <strong>Alumni System</strong>
            <small>Alumni Representative Portal</small>
        </div>
    </div>

    <nav class="admin-nav">

        <a
            href="dashboard.php"
            class="<?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>"
        >
            Dashboard
        </a>

        <div class="nav-section">
            ALUMNI
        </div>

        <a
            href="add.php"
            class="<?= ($activePage ?? '') === 'add' ? 'active' : '' ?>"
        >
            Add Alumni
        </a>

        <a
            href="manage_alumni.php"
            class="<?= ($activePage ?? '') === 'manage_alumni' ? 'active' : '' ?>"
        >
            Manage Alumni
        </a>

        <div class="nav-section">
            SYSTEM
        </div>

        <a
            href="../../auth/logout.php"
            class="logout-link"
        >
            Logout
        </a>

    </nav>

</aside>