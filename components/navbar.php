<style>
    html {
        padding: 0px 30px;
        overflow: auto;
        scrollbar-width: none;
        /* Firefox */
        -ms-overflow-style: none;
        /* IE and Edge */
    }

    html::-webkit-scrollbar {
        display: none;
        /* Chrome, Safari, Opera */
    }
</style>

<nav class="navbar navbar-expand-lg mb-2">
    <div class="container-fluid px-0">

        <a class="navbar-brand"
            href="<?= htmlspecialchars($baseUrl) ?>index.php">
            Schedule Generator
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center gap-1">

                <?php if ($me): ?>

                    <!-- ================= SCHEDULE SECTION ================= -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?= htmlspecialchars($baseUrl) ?>schedule_gen.php">
                            Create Schedule
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= htmlspecialchars($baseUrl) ?>index.php">
                            Schedules
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= htmlspecialchars($baseUrl) ?>admin/course/">
                            Manage Schedule
                        </a>
                    </li>

                    <?php if (hasRole($conn, 'Admin') || hasRole($conn, 'SuperAdmin')): ?>

                        <!-- divider -->
                        <li class="nav-item text-muted">|</li>

                        <!-- ================= ADMIN SECTION ================= -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($baseUrl) ?>admin/course/master_temp.php">
                                Import Templates
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($baseUrl) ?>admin/course/templates_manage.php">
                                Templates
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($baseUrl) ?>admin/holidays_master.php">
                                Holidays
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($baseUrl) ?>admin/users.php">
                                Users
                            </a>
                        </li>

                    <?php endif; ?>

                    <!-- ================= LOGOUT ================= -->
                    <li class="nav-item ms-4">
                        <a href="<?= htmlspecialchars($baseUrl) ?>sso/logout.php"
                            class="btn btn-danger btn-sm">
                            Logout
                        </a>
                    </li>

                <?php else: ?>

                    <!-- ================= LOGIN ================= -->
                    <li class="nav-item ms-4">
                        <a href="<?= htmlspecialchars($baseUrl) ?>login.php"
                            class="btn btn-primary btn-sm">
                            Login
                        </a>
                    </li>

                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>