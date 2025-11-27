<?php
// session_start();
date_default_timezone_set('Asia/Singapore'); // adjust if needed

// autoload & env
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
requireRole($conn, 'Admin'); // must be logged in + have Admin

// auth guard (adjust to your app)
// if (!isset($_SESSION['user_id'])) {
//     header("Location: ./../../login.php");
//     exit;
// }

$username = $_SESSION['auth']['name'] ?? '';
$userId   = (int)($_SESSION['auth']['user_id'] ?? 0);

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// layout includes (optional – comment out if you don't use them)
if (is_file(__DIR__ . '/../../components/header.php')) require __DIR__ . '/../../components/header.php';
if (is_file(__DIR__ . '/../../components/navbar.php')) require __DIR__ . '/../../components/navbar.php';
require __DIR__ . '/schedule_lib.php';



// Refresh courses from API
if (isPost('refresh')) {
    if (!$conn) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'DB not configured.'];
    } else {
        $r = upsert_courses_from_api($conn);
        $_SESSION['flash'] = ['type' => $r['ok'] ? 'success' : 'danger', 'message' => $r['msg']];
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

function sanitize_text(string $text): string
{
    // Replace strange symbols or broken encodings with spaces
    $text = iconv('UTF-8', 'UTF-8//IGNORE', $text);
    $text = preg_replace('/[^\P{C}\n]+/u', ' ', $text); // remove control chars
    $text = preg_replace('/[�]+/u', ' ', $text); // remove replacement chars
    $text = preg_replace('/\s{2,}/', ' ', $text); // collapse extra spaces
    return trim($text);
}

function csv_to_rows(string $filepath): array
{
    if (!file_exists($filepath)) return [];

    $rows = [];
    setlocale(LC_ALL, 'en_US.UTF-8');
    ini_set('auto_detect_line_endings', '1');

    if (($handle = fopen($filepath, 'r')) === false) return [];

    // Read header row
    $header = fgetcsv($handle);
    if (!$header) return [];

    $header = array_map('trim', $header);
    $headerLower = array_map('strtolower', $header);

    // Mapping: CSV header name → array key
    $map = [
        'session id'              => 'session_id',
        'session day'             => 'session_day',
        'session of the day'      => 'session_of_the_day',
        'session code'            => 'session_code',
        'session mode'            => 'session_mode',
        'topics'                  => 'topics',
        'session day of module'   => 'session_day_of_module',
        'hours'                   => 'hours',
        'session type'            => 'session_type',
        'faculty'                 => 'faculty'
    ];

    // Find column index for each header
    $indexes = [];
    foreach ($map as $csvName => $key) {
        $idx = array_search(strtolower($csvName), $headerLower);
        $indexes[$key] = $idx !== false ? $idx : null;
    }

    // Read data rows
    while (($data = fgetcsv($handle)) !== false) {
        if (count(array_filter($data)) == 0) continue;

        $row = [];
        foreach ($indexes as $key => $idx) {
            $row[$key] = $idx !== null ? trim($data[$idx] ?? '') : '';
        }

        $row['topics'] = sanitize_text($row['topics']);

        $rows[] = $row;
    }

    fclose($handle);
    return $rows;
}


// Save template + rows
if (isPost('saveTemplate')) {
    $course_id     = trim($_POST['course_id'] ?? '');
    $course_code   = trim($_POST['course_code'] ?? '');
    $module_code   = '';
    $learning_mode = trim($_POST['learning_mode_text'] ?? '');

    if ($course_id === '' || $course_code === '' || $learning_mode === '') {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Please select Course, and Mode before saving.'];
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    if (!isset($_FILES['csvFile']) || !is_uploaded_file($_FILES['csvFile']['tmp_name'])) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Please upload a CSV file.'];
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    $rows = csv_to_rows($_FILES['csvFile']['tmp_name']);

    if (!$rows) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'No rows detected in CSV.'];
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // Begin transaction
    $conn->begin_transaction();
    try {
        // 1) Find the latest existing template for same keys
        $templateId = null;
        $stmt0 = $conn->prepare("
        SELECT id
        FROM templates
        WHERE course_id=? AND learning_mode=?
        ORDER BY created_at DESC
        LIMIT 1
    ");
        $stmt0->bind_param("ss", $course_id, $learning_mode);
        $stmt0->execute();
        $stmt0->bind_result($existingId);
        if ($stmt0->fetch()) {
            $templateId = (int)$existingId;
        }
        $stmt0->close();

        if ($templateId) {
            // 2) Optional: keep header fresh (course_code might change)
            $stmtH = $conn->prepare("
            UPDATE templates
            SET updated_at = NOW(), updated_user=?
            WHERE id = ?
        ");
            $stmtH->bind_param("ii", $userId, $templateId);
            $stmtH->execute();
            $stmtH->close();

            // 3) Clear old rows of this template
            $stmtDel = $conn->prepare("DELETE FROM template_data WHERE template_id = ?");
            $stmtDel->bind_param("i", $templateId);
            $stmtDel->execute();
            $stmtDel->close();
        } else {
            // 4) No existing template → create a new header

            $stmt = $conn->prepare("
            INSERT INTO templates
                (course_id, learning_mode, created_user, updated_user)
            VALUES (?,?,?,?)
        ");
            $stmt->bind_param("ssii", $course_id, $learning_mode, $userId, $userId);
            $stmt->execute();
            $templateId = (int)$conn->insert_id;
            $stmt->close();
        }

        // 5) Insert fresh rows (no need for ON DUPLICATE since we deleted old)
        $stmt2 = $conn->prepare("
        INSERT INTO template_data
            (template_id, module_code, session_id, session_day, session_of_the_day,
            session_code, session_mode, topics, session_day_of_module, hours,
            session_type, faculty)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
        ");

        foreach ($rows as $r) {
            // Adjust indexes based on your CSV structure
            // If your CSV matches: Session ID, Session Day, Session of Day, Session Code, Session Mode, Topics, Session Day of Module, Hours, Session Type, Faculty
            $session_id            = $r['session_id'] ?? '';
            $session_day           = $r['session_day'] ?? '';
            $session_of_the_day    = $r['session_of_the_day'] ?? '';
            $session_code          = $r['session_code'] ?? '';
            $session_mode          = $r['session_mode'] ?? '';
            $topics                = $r['topics'] ?? '';
            $session_day_of_module = $r['session_day_of_module'] ?? '';
            // $hours                 = $r['hours'] ?? '';

            // --- Normalize hours value ---
            $rawHours = trim($r['hours'] ?? '');

            if ($rawHours === '') {
                $hours = '';
            } elseif ($rawHours == '30') {
                // exactly 30 minutes
                $hours = 0.5;
            } elseif (preg_match('/^(\d+)[\.:](\d{1,2})$/', $rawHours, $m)) {
                $h = (int)$m[1];
                $min = (int)$m[2];
                $hours = $h + ($min / 60);
            } elseif (preg_match('/(\d+)\s*min/i', $rawHours, $m)) {
                $min = (int)$m[1];
                $hours = $min / 60;
            } else {
                $hours = (float)$rawHours;
            }


            $session_type          = $r['session_type'] ?? '';
            $faculty               = $r['faculty'] ?? '';

            $normalized = str_replace(
                ['–', '—', '‐'], // all types of dashes
                '-',            // force standard hyphen
                $session_code
            );

            $parts = explode('-', $normalized);

            if (count($parts) > 1) {
                array_pop($parts);
                $module_extracted = trim(implode('-', $parts));
            } else {
                $module_extracted = null;
            }

            // Insert each row into template_data
            $stmt2->bind_param(
                "isisssssssss",
                $templateId,
                $module_extracted,
                $session_id,
                $session_day,
                $session_of_the_day,
                $session_code,
                $session_mode,
                $topics,
                $session_day_of_module,
                $hours,
                $session_type,
                $faculty
            );
            $stmt2->execute();
        }
        $stmt2->close();

        $conn->commit();
        $_SESSION['flash'] = ['type' => 'success', 'message' => "Template updated (#$templateId) with " . count($rows) . " rows."];
        header('Location: ' . $_SERVER['PHP_SELF'] . '/../../../schedule_gen.php');
        exit;
    } catch (Throwable $e) {
        $conn->rollback();
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Save failed: ' . $e->getMessage()];
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <title>Schedule Generator</title> -->
    <title>Templates — Master</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        table thead th {
            position: sticky;
            top: 0;
            background: #0e1627;
            color: #fff;
            z-index: 2;
        }

        #results {
            position: absolute;
            z-index: 1000;
            width: 100%;
        }

        #results .list-group-item {
            cursor: pointer;
        }
    </style>
</head>

<body class="py-4">
    <div class="container">
        <?php if ($flash): ?>
            <div class="d-flex flex-inline justify-content-between alert alert-<?= h($flash['type'] ?? 'info') ?> mt-2"><?= h($flash['message'] ?? '') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Master: Create Session Template</h4>
            <form method="post"><button class="btn btn-primary" name="refresh" type="submit">Refresh Course List</button></form>
        </div>

        <!-- Course search -->
        <div class="mb-3 position-relative">
            <label class="form-label">Search Courses</label>
            <div class="input-group">
                <input type="text" id="search" class="form-control" placeholder="Type to search...">
                <button type="button" id="clearSearch" class="btn btn-outline-secondary" style="display:none;">&times;</button>
            </div>
            <div id="results" class="list-group"></div>
            <div class="form-text">Select a course to load its modules and modes.</div>
        </div>

        <form method="post" enctype="multipart/form-data" class="row g-3">
            <!-- Hidden selected course info -->
            <input type="hidden" name="course_id" id="course_id">
            <input type="hidden" name="course_code" id="course_code">
            <div class="col-12">
                <div id="courseTitle" class="fw-semibold"></div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Learning Modes</label>
                <select id="modeSelect" class="form-select" required>
                    <option value="">Select a mode...</option>
                </select>
                <input type="hidden" name="learning_mode_text" id="learning_mode_text">
                <div id="modeDetails" class="small mt-2"></div>
            </div>

            <div class="col-md-8">
                <label class="form-label">Upload CSV</label>
                <input class="form-control" type="file" name="csvFile" accept=".csv" required>
                <div class="form-text">
                    <!-- Expected columns: Session No, Session Type, Session Details, Duration Hr, Class Type -->
                </div>
            </div>

            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-success w-100" name="saveTemplate" type="submit">Save Template</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const searchInput = document.getElementById('search');
        const resultsBox = document.getElementById('results');
        const clearBtn = document.getElementById('clearSearch');
        const courseTitle = document.getElementById('courseTitle');
        const modeSelect = document.getElementById('modeSelect');
        const modeDetails = document.getElementById('modeDetails');
        const courseIdInp = document.getElementById('course_id');
        const courseCodeInp = document.getElementById('course_code');
        const lmTextInp = document.getElementById('learning_mode_text');

        let lastCourseData = null;

        searchInput.addEventListener('input', async () => {
            const q = searchInput.value.trim();
            clearBtn.style.display = q ? 'block' : 'none';
            if (!q) {
                resultsBox.innerHTML = '';
                return;
            }
            const res = await fetch('search_courses.php?q=' + encodeURIComponent(q));
            const rows = await res.json();
            resultsBox.innerHTML = rows.map(r => `
                <button class="list-group-item list-group-item-action"
                        data-id="${r.course_id}" data-code="${r.course_code}">
                    [${r.course_code}] ${r.course_title_external}
                </button>
            `).join('');
        });

        clearBtn.addEventListener('click', () => {
            searchInput.value = '';
            resultsBox.innerHTML = '';
            clearBtn.style.display = 'none';
            searchInput.focus();
        });
        resultsBox.addEventListener('click', async e => {
            const btn = e.target.closest('button');
            if (!btn) return;
            resultsBox.innerHTML = '';
            searchInput.value = btn.textContent.trim();
            courseIdInp.value = btn.dataset.id;
            courseCodeInp.value = btn.dataset.code;

            const res = await fetch('get_course_details.php?id=' + btn.dataset.id);
            const data = await res.json();
            lastCourseData = data;

            courseTitle.textContent = btn.textContent;

            modeSelect.innerHTML = '<option value="">Select a mode...</option>' +
                (data.data.master_learning_modes || []).map((m, i) => `<option value="${i}">${m.mode}</option>`).join('');
            modeDetails.innerHTML = '';
            lmTextInp.value = '';
        });
        modeSelect.addEventListener('change', function() {
            if (!lastCourseData || this.value === "") {
                modeDetails.innerHTML = "";
                lmTextInp.value = "";
                return;
            }
            const m = lastCourseData.data.master_learning_modes[this.value];
            modeDetails.innerHTML = `<div class="card card-body p-2">
                <div><b>Mode:</b> ${m.mode || ''} |
                <b>Duration:</b> ${m.course_duration || ''} |
                <b>Days/Week:</b> ${m.days_per_week || ''} |
                <b>Hours/Day:</b> ${m.hours_per_day || ''} |
                <b>Hours/Week:</b> ${m.hours_per_week || ''}</div>
            </div>`;
            lmTextInp.value = m.mode || '';
        });
    </script>
</body>

</html>