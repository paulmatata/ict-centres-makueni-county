<?php
// admin/includes/admin_sidebar.php
//
// Role-aware sidebar for the admin panel. Include this from any page inside
// the admin/ folder with:  include 'includes/admin_sidebar.php';
// Requires $_SESSION['admin_role'] and $_SESSION['admin_name'] to already be set
// (i.e. include this AFTER admin_auth.php).

$current_page = basename($_SERVER['SCRIPT_NAME']);
$admin_role = $_SESSION['admin_role'] ?? 'centre_admin';

function nav_active($page, $current_page) {
    return $page === $current_page ? 'active' : '';
}
?>

<!-- Mobile toggle button (place this stays fixed top-left on small screens) -->
<button class="btn admin-sidebar-toggle d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar">
    <i class="bi bi-list"></i>
</button>

<!-- Desktop sidebar -->
<nav class="admin-sidebar d-none d-lg-flex flex-column">

    <div class="admin-sidebar-brand">
        <img src="../assets/images/makueni-logo.png" alt="Makueni County" height="36">
        <span>ICT Centres Admin</span>
    </div>

    <div class="admin-sidebar-user">
        <i class="bi bi-person-circle"></i>
        <div>
            <div class="fw-semibold"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></div>
            <div class="admin-sidebar-role"><?php echo $admin_role === 'super_admin' ? 'Super Admin' : 'Centre Admin'; ?></div>
        </div>
    </div>

    <ul class="nav flex-column admin-sidebar-nav">

        <li class="nav-item">
            <a class="nav-link <?php echo nav_active('admin_dashboard.php', $current_page); ?>" href="admin_dashboard.php">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo nav_active('manage_students.php', $current_page); ?>" href="manage_students.php">
                <i class="bi bi-people"></i> Manage Students
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo nav_active('add_student.php', $current_page); ?>" href="add_student.php">
                <i class="bi bi-person-plus"></i> Add Student
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo nav_active('manage_notes.php', $current_page); ?>" href="manage_notes.php">
                <i class="bi bi-journal-text"></i> Manage Notes
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo nav_active('reviews.php', $current_page); ?>" href="reviews.php">
                <i class="bi bi-star"></i> Reviews
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo nav_active('reports.php', $current_page); ?>" href="reports.php">
                <i class="bi bi-bar-chart"></i>Student Reports
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo nav_active('centre_report.php', $current_page); ?>" href="centre_report.php">
                <i class="bi bi-building"></i> Centre Report
            </a>
        </li>

        <?php if ($admin_role === 'super_admin'): ?>

            <li class="admin-sidebar-divider"></li>

            <li class="nav-item">
                <a class="nav-link <?php echo nav_active('manage_admins.php', $current_page); ?>" href="manage_admins.php">
                    <i class="bi bi-shield-lock"></i> Manage Admins
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo nav_active('add_admin.php', $current_page); ?>" href="add_admin.php">
                    <i class="bi bi-person-badge"></i> Add Admin
                </a>
            </li>

        <?php endif; ?>

        <li class="admin-sidebar-divider"></li>

        <li class="nav-item">
            <a class="nav-link <?php echo nav_active('change_admin_password.php', $current_page); ?>" href="change_admin_password.php">
                <i class="bi bi-key"></i> Change Password
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link text-danger" href="logout.php">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </li>

    </ul>

</nav>

<!-- Mobile offcanvas sidebar (same links, shown via the toggle button above) -->
<div class="offcanvas offcanvas-start admin-sidebar-offcanvas d-lg-none" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel">

    <div class="offcanvas-header">
        <span class="admin-sidebar-brand mb-0" id="adminSidebarLabel">
            <img src="../assets/images/makueni-logo.png" alt="Makueni County" height="32">
            ICT Centres Admin
        </span>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body p-0">

        <div class="admin-sidebar-user">
            <i class="bi bi-person-circle"></i>
            <div>
                <div class="fw-semibold"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></div>
                <div class="admin-sidebar-role"><?php echo $admin_role === 'super_admin' ? 'Super Admin' : 'Centre Admin'; ?></div>
            </div>
        </div>

        <ul class="nav flex-column admin-sidebar-nav">

            <li class="nav-item">
                <a class="nav-link <?php echo nav_active('admin_dashboard.php', $current_page); ?>" href="admin_dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo nav_active('manage_students.php', $current_page); ?>" href="manage_students.php">
                    <i class="bi bi-people"></i> Manage Students
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo nav_active('add_student.php', $current_page); ?>" href="add_student.php">
                    <i class="bi bi-person-plus"></i> Add Student
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo nav_active('manage_notes.php', $current_page); ?>" href="manage_notes.php">
                    <i class="bi bi-journal-text"></i> Manage Notes
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo nav_active('reviews.php', $current_page); ?>" href="reviews.php">
                    <i class="bi bi-star"></i> Reviews
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo nav_active('reports.php', $current_page); ?>" href="reports.php">
                    <i class="bi bi-bar-chart"></i> Reports
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo nav_active('center_report.php', $current_page); ?>" href="center_report.php">
                    <i class="bi bi-building"></i> Centre Report
                </a>
            </li>

            <?php if ($admin_role === 'super_admin'): ?>

                <li class="admin-sidebar-divider"></li>

                <li class="nav-item">
                    <a class="nav-link <?php echo nav_active('manage_admins.php', $current_page); ?>" href="manage_admins.php">
                        <i class="bi bi-shield-lock"></i> Manage Admins
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo nav_active('add_admin.php', $current_page); ?>" href="add_admin.php">
                        <i class="bi bi-person-badge"></i> Add Admin
                    </a>
                </li>

            <?php endif; ?>

            <li class="admin-sidebar-divider"></li>

            <li class="nav-item">
                <a class="nav-link <?php echo nav_active('change_admin_password.php', $current_page); ?>" href="change_admin_password.php">
                    <i class="bi bi-key"></i> Change Password
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-danger" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </li>

        </ul>

    </div>

</div>
