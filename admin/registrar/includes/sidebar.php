<!-- Registrar Sidebar -->

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
                Registrar Panel
            </small>
        </div>

    </div>


    <nav class="admin-nav">


        <!-- DASHBOARD -->

        <a
            href="dashboard.php"
            class="<?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>"
        >
            Dashboard
        </a>


        <!-- REGISTRATION -->

        <div class="nav-section">
            REGISTRATION
        </div>


        <a
            href="pending.php"
            class="<?= ($activePage ?? '') === 'pending' ? 'active' : '' ?>"
        >
            Pending Registrations
        </a>


        <a
            href="approved.php"
            class="<?= ($activePage ?? '') === 'approved' ? 'active' : '' ?>"
        >
            Approved Registrations
        </a>


        <a
            href="rejected.php"
            class="<?= ($activePage ?? '') === 'rejected' ? 'active' : '' ?>"
        >
            Rejected Registrations
        </a>


        <!-- ALUMNI -->

        <div class="nav-section">
            ALUMNI
        </div>


        <a
            href="alumni.php"
            class="<?= ($activePage ?? '') === 'alumni' ? 'active' : '' ?>"
        >
            Alumni Directory
        </a>


        <!-- SYSTEM -->

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