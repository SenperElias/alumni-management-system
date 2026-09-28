<?php
$currentPage = basename($_SERVER["PHP_SELF"]);
?>

<aside class="admin-sidebar">

    
         <div class="admin-brand">
        <div class="brand-logo">
            <img src="/almuni-management-system/assets/img/college-logo.jpg" alt="Taferi Mekonnen polytechnic Techhnical college Logo">
        </div>
        <div>
            <strong>Alumni System</strong>
            <small>System Administrator Portal</small>
        </div>
    </div>
       
    <nav class="admin-nav">

        <a href="/almuni-management-system/admin/system_admin/dashboard.php"
           class="<?= $currentPage === "dashboard.php" ? "active" : "" ?>">
            Dashboard
        </a>

        <div class="nav-section">SYSTEM MANAGEMENT</div>

        <a href="/almuni-management-system/admin/system_admin/users/index.php"
           class="<?= strpos($_SERVER["PHP_SELF"], "/system_admin/users/") !== false ? "active" : "" ?>">
            Users
        </a>

        

        <a href="/almuni-management-system/admin/system_admin/settings/index.php"
           class="<?= strpos($_SERVER["PHP_SELF"], "/system_admin/settings/") !== false ? "active" : "" ?>">
            Settings
        </a>

       <div class="nav-section">SECURITY</div>

<a href="/almuni-management-system/admin/system_admin/backups/index.php"
   class="<?= strpos($_SERVER["PHP_SELF"], "/system_admin/backups/") !== false ? "active" : "" ?>">
    Backup & Recovery
</a>

<a href="/almuni-management-system/admin/audit/index.php"
   class="<?= strpos($_SERVER["PHP_SELF"], "/audit/") !== false ? "active" : "" ?>">
    Audit Logs
</a>

        <a href="/almuni-management-system/auth/logout.php" class="logout-link">
            Logout
        </a>

    </nav>

</aside>