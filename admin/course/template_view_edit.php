<?php
date_default_timezone_set('Asia/Singapore');

require_once __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../db.php';
require __DIR__ . '/../../auth/guard.php';
$me = $_SESSION['auth'] ?? null;
$baseUrl = $_ENV['BASE_URL'] ?? '/';

requireRole($conn, 'Admin');

$username = $_SESSION['auth']['name'] ?? '';
$userId = (int)($_SESSION['auth']['user_id'] ?? 0);

$templateId = (int)($_GET['id'] ?? 0);
if ($templateId <= 0) {
    die('Invalid template ID');
}

/* ==========================
   FETCH TEMPLATE HEADER
========================== */
$stmt = $conn->prepare("
    SELECT
        t.id,
        t.course_id,
        c.course_code,
        t.learning_mode,
        t.tag,
        t.status
    FROM templates t
    JOIN courses c ON c.course_id = t.course_id
    WHERE t.id = ? AND t.deleted_at IS NULL
");
$stmt->bind_param("i", $templateId);
$stmt->execute();
$template = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$template) {
    die('Template not found.');
}

/* ==========================
   SAVE EDITED ROWS
========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $rows = $_POST['rows'] ?? [];

    if (!$rows) {
        $_SESSION['flash'] = [
            'type' => 'danger',
            'message' => 'No rows to save.'
        ];
        header("Location: template_view_edit.php?id=$templateId");
        exit;
    }

    $conn->begin_transaction();
    try {

        // Remove existing rows
        $del = $conn->prepare("DELETE FROM template_data WHERE template_id=?");
        $del->bind_param("i", $templateId);
        $del->execute();
        $del->close();

        // Insert updated rows
        $ins = $conn->prepare("
            INSERT INTO template_data
            (template_id, module_code, session_id, session_day, session_of_the_day,
            session_code, session_mode, topics, hours, session_type)
            VALUES (?,?,?,?,?,?,?,?,?,?)
        ");

        foreach ($rows as $r) {

            // Derive module_code from session_code
            $normalized = str_replace(['–', '—', '‐'], '-', $r['session_code']);
            $module_code = preg_replace('/-[^-]+$/', '', $normalized);

            $ins->bind_param(
                "isiissssds",
                $templateId,
                $module_code,
                $r['session_id'],
                $r['session_day'],
                $r['session_of_the_day'],
                $r['session_code'],
                $r['session_mode'],
                $r['topics'],
                $r['hours'],
                $r['session_type']
            );

            $ins->execute();
        }

        $ins->close();
        $conn->commit();

        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Template updated successfully.'
        ];

        header("Location: templates_manage.php");
        exit;
    } catch (Throwable $e) {
        $conn->rollback();
        die('Save failed: ' . $e->getMessage());
    }
}

// layout includes (optional – comment out if you don't use them)
if (is_file(__DIR__ . '/../../components/header.php')) require __DIR__ . '/../../components/header.php';
if (is_file(__DIR__ . '/../../components/navbar.php')) require __DIR__ . '/../../components/navbar.php';

/* ==========================
   FETCH TEMPLATE ROWS
   ORDERED BY session_id ASC
========================== */
$stmt = $conn->prepare("
    SELECT
        module_code,
        session_id,
        session_day,
        session_of_the_day,
        session_code,
        session_mode,
        topics,
        hours,
        session_type
    FROM template_data
    WHERE template_id = ?
    ORDER BY session_id ASC
");
$stmt->bind_param("i", $templateId);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Template</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        table thead th {
            position: sticky;
            top: 0;
            background: #0e1627;
            color: #fff;
        }

        .short-col {
            width: fit-content;
        }

        .short-col2 {
            width: 200px;
        }

        .long-col {
            width: 35%;
        }

        textarea {
            resize: vertical;
        }
    </style>
</head>

<body class="py-4">
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5>
                    Template:
                    <?= htmlspecialchars($template['course_code']) ?> |
                    <?= htmlspecialchars($template['learning_mode']) ?> |
                    <?= htmlspecialchars($template['tag']) ?>
                </h5>
                <span class="badge <?= $template['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                    <?= ucfirst($template['status']) ?>
                </span>
            </div>

            <div>
                <a href="templates_manage.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
                <button form="editForm" class="btn btn-success">
                    <i class="bi bi-save"></i> Save Changes
                </button>
            </div>
        </div>

        <form method="post" id="editForm">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th></th>
                            <th class="short-col">Session ID</th>
                            <th class="short-col">Session Day</th>
                            <th class="short-col">SOTD</th>
                            <th class="short-col2">Session Code</th>
                            <th class="short-col">Mode</th>
                            <th class="long-col">Topics</th>
                            <th class="short-col">Hours</th>
                            <th class="short-col">Session Type</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="rows">
                        <?php foreach ($rows as $i => $r): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><input name="rows[<?= $i ?>][session_id]" class="form-control" value="<?= htmlspecialchars($r['session_id']) ?>"></td>
                                <td><input name="rows[<?= $i ?>][session_day]" class="form-control" value="<?= htmlspecialchars($r['session_day']) ?>"></td>
                                <td><input name="rows[<?= $i ?>][session_of_the_day]" class="form-control" value="<?= htmlspecialchars($r['session_of_the_day']) ?>"></td>
                                <td><input name="rows[<?= $i ?>][session_code]" class="form-control" value="<?= htmlspecialchars($r['session_code']) ?>"></td>
                                <td><input name="rows[<?= $i ?>][session_mode]" class="form-control" value="<?= htmlspecialchars($r['session_mode']) ?>"></td>
                                <td><textarea rows="3" name="rows[<?= $i ?>][topics]" class="form-control"><?= htmlspecialchars($r['topics']) ?></textarea></td>
                                <td><input type="number" step="0.25" name="rows[<?= $i ?>][hours]" class="form-control" value="<?= htmlspecialchars($r['hours']) ?>"></td>
                                <td><input name="rows[<?= $i ?>][session_type]" class="form-control" value="<?= htmlspecialchars($r['session_type']) ?>"></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <button type="button" class="btn btn-outline-primary" onclick="addRow()">
                <i class="bi bi-plus"></i> Add Row
            </button>
        </form>

    </div>

    <script>
        function addRow() {
            const tbody = document.getElementById('rows');
            const i = tbody.children.length;

            tbody.insertAdjacentHTML('beforeend', `
        <tr>
            <td>${i+1}</td>
            <td><input name="rows[${i}][session_id]" class="form-control"></td>
            <td><input name="rows[${i}][session_day]" class="form-control"></td>
            <td>
                <select name="rows[${i}][session_of_the_day]" class="form-select">
                    <option>s1</option><option>s2</option><option>s3</option>
                </select>
            </td>
            <td><input name="rows[${i}][session_code]" class="form-control"></td>
            <td><input name="rows[${i}][session_mode]" class="form-control"></td>
            <td><textarea name="rows[${i}][topics]" class="form-control"></textarea></td>
            <td><input type="number" step="0.25" name="rows[${i}][hours]" class="form-control"></td>
            <td><input name="rows[${i}][session_type]" class="form-control"></td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">
                    <i class="bi bi-x"></i>
                </button>
            </td>
        </tr>
    `);
        }
    </script>

</body>

</html>