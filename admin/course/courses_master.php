<?php

date_default_timezone_set('Asia/Singapore');

require_once __DIR__ . '/../../vendor/autoload.php';
if (class_exists(\Dotenv\Dotenv::class)) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->safeLoad();
}
$baseUrl = $_ENV['BASE_URL'] ?? '/';

// DB
require __DIR__ . '/../../db.php';
require __DIR__ . '/../../auth/guard.php';
$me = $_SESSION['auth'] ?? null;

requireRole($conn, 'Admin');

// Fallback escape helper
if (!function_exists('h')) {
    function h($v)
    {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
}

// Fetch all courses
$sql = "
    SELECT course_id, course_code, course_title_external, status
    FROM courses
    ORDER BY status DESC, course_code ASC
";
$result = $conn->query($sql);
$courses = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Layout includes
if (is_file(__DIR__ . '/../../components/header.php')) require __DIR__ . '/../../components/header.php';
if (is_file(__DIR__ . '/../../components/navbar.php')) require __DIR__ . '/../../components/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Courses — Master</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        table {
            table-layout: fixed;
        }

        table thead th {
            position: sticky;
            top: 0;
            background: #0e1627;
            color: #fff;
            z-index: 2;
            font-size: 14px;
        }

        table tbody td {
            padding: 6px 8px;
            /* smaller row height */
            font-size: 14px;
            vertical-align: middle;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .col-code {
            width: 100px;
        }

        .col-id {
            width: 200px;
        }

        .col-title {
            width: auto;
        }

        /* ~2x course_id */
        .col-status {
            width: 140px;
        }

        /* ~1x course_code */

        .status-dot {
            font-size: 12px;
            margin-right: 6px;
        }

        .status-active {
            color: #198754;
            font-weight: 600;
        }

        .status-inactive {
            color: #dc3545;
            font-weight: 600;
        }
    </style>
</head>

<body class="py-4">

    <div class="container-fluid px-4">

        <div class="d-flex justify-content-between align-items-center mb-3">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Courses Master</h4>
            </div>

            <a href="templates_manage.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>

        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th style="width:60px;">No</th>
                                <th class="col-code">Course Code</th>
                                <th class="col-id">Course ID</th>
                                <th class="col-title">Course Title</th>
                                <th class="col-status">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$courses): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        No courses found
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($courses as $i => $c): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= h($c['course_code']) ?></td>
                                        <td><?= h($c['course_id']) ?></td>
                                        <td title="<?= h($c['course_title_external']) ?>">
                                            <?= h($c['course_title_external']) ?>
                                        </td>
                                        <td>
                                            <?php if ($c['status'] === 'active'): ?>
                                                <span class="status-active">
                                                    <span class="status-dot">●</span>Active
                                                </span>
                                            <?php else: ?>
                                                <span class="status-inactive">
                                                    <span class="status-dot">●</span>Inactive
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>