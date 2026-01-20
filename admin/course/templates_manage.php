<?php
date_default_timezone_set('Asia/Singapore');

require_once __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../db.php';
require __DIR__ . '/../../auth/guard.php';
$me = $_SESSION['auth'] ?? null;

$baseUrl = $_ENV['BASE_URL'] ?? '/';

requireRole($conn, 'Admin');

$userId = (int)($_SESSION['auth']['user_id'] ?? 0);
$username = $_SESSION['auth']['name'] ?? '';

$flash  = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);


// ==========================
// HANDLE TOGGLE STATUS
// ==========================
if (isset($_POST['toggle_id'])) {
    $id = (int)$_POST['toggle_id'];

    $stmt = $conn->prepare("
        UPDATE templates
        SET status = IF(status='active','inactive','active'),
            updated_user = ?,
            updated_at = NOW()
        WHERE id = ? AND deleted_at IS NULL
    ");
    $stmt->bind_param("ii", $userId, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: templates_manage.php");
    exit;
}

// ==========================
// HANDLE SOFT DELETE
// ==========================
if (isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];

    $stmt = $conn->prepare("
        UPDATE templates
        SET deleted_at = NOW(),
            updated_user = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->bind_param("ii", $userId, $id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['flash'] = [
        'type' => 'success',
        'message' => 'Template deleted successfully.'
    ];

    header("Location: templates_manage.php");
    exit;
}

if (is_file(__DIR__ . '/../../components/header.php')) require __DIR__ . '/../../components/header.php';
if (is_file(__DIR__ . '/../../components/navbar.php')) require __DIR__ . '/../../components/navbar.php';

// ==========================
// FETCH TEMPLATE LIST
// ==========================
$sql = "
    SELECT
        t.id,
        t.course_id,
        c.course_code,
        t.learning_mode,
        t.tag,
        t.status,
        t.updated_at,
        COUNT(td.id) AS row_count
    FROM templates t
    JOIN courses c ON c.course_id = t.course_id
    LEFT JOIN template_data td ON td.template_id = t.id
    WHERE t.deleted_at IS NULL
    GROUP BY t.id
    ORDER BY t.updated_at DESC
";

$templates = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Template Management</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        table thead th {
            background: #0e1627;
            color: #fff;
        }
    </style>
</head>

<body class="py-4">
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Template Management</h4>
            <a href="master_temp.php" class="btn btn-outline-primary">
                <i class="bi bi-plus-circle"></i> Create Template
            </a>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Course</th>
                        <th>Mode</th>
                        <th>Tag</th>
                        <th>Status</th>
                        <th>Rows</th>
                        <th>Updated</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$templates): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">No templates found.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($templates as $i => $t): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($t['course_code']) ?></td>
                            <td><?= htmlspecialchars($t['learning_mode']) ?></td>
                            <td><?= htmlspecialchars($t['tag']) ?></td>

                            <td>
                                <span class="badge <?= $t['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= ucfirst($t['status']) ?>
                                </span>
                            </td>

                            <td><?= (int)$t['row_count'] ?></td>
                            <td><?= date('Y-m-d', strtotime($t['updated_at'])) ?></td>

                            <td class="text-center">

                                <!-- OPEN -->
                                <a href="template_view_edit.php?id=<?= $t['id'] ?>"
                                    class="btn btn-sm btn-outline-primary"
                                    title="Open Template">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <!-- TOGGLE -->
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="toggle_id" value="<?= $t['id'] ?>">
                                    <button class="btn btn-sm btn-outline-warning"
                                        title="Activate / Deactivate">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                </form>

                                <!-- DELETE -->
                                <form method="post" class="d-inline"
                                    onsubmit="return confirm('Are you sure you want to delete this template?');">
                                    <input type="hidden" name="delete_id" value="<?= $t['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger"
                                        title="Delete Template">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>