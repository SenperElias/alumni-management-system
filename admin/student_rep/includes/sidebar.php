<!-- Student Representative Sidebar -->

<aside class="admin-sidebar">

    <div class="admin-brand">

        <div class="brand-logo">
            TM
        </div>

        <div>
            <strong>Alumni System</strong>
            <small>Student Representative Panel</small>
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