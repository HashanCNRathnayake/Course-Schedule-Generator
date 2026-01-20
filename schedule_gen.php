<?php
// session_start();
date_default_timezone_set('Asia/Singapore'); // adjust if needed

require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$baseUrl = $_ENV['BASE_URL'] ?? '/';

require __DIR__ . '/db.php';
require __DIR__ . '/auth/guard.php';
requireRole($conn, 'Admin');

require __DIR__ . '/admin/course/schedule_lib.php';

$me     = $_SESSION['auth'] ?? null;
$userId = $_SESSION['auth']['user_id'] ?? 0;

// ----- helper: schedule engine ----------------------------------------------
/**
 * Generate schedule into the same grid structure.
 *
 * @param array  $grid            [module_code => [rows...]]
 * @param array  $modulesOrdered  ordered module codes
 * @param string $socDmy          "dd/mm/yyyy"
 * @param array  $patternDays     [1..7] (1=Mon)
 * @param array  $publicHolidaysYmd ['YYYY-MM-DD', ...]
 * @param string $startTime       "HH:MM"
 * @param string $endTime         "HH:MM"
 * @param array  $mentorsByModule ['MODCODE' => 'Mentor Name']
 * @param int    $moduleBreakDays
 *
 * @return array ['grid' => $gridWithSchedule, 'eoc' => 'dd/mm/yyyy'|null]
 * @throws Exception on invalid sequences or bad date
 */
function generateScheduleForGrid(
  array $grid,
  array $modulesOrdered,
  string $socDmy,
  array $patternDays,
  array $publicHolidaysYmd,
  string $startTime,
  // string $endTime,
  array $mentorsByModule,
  int $moduleBreakDays
): array {
  $soc = DateTime::createFromFormat('d/m/Y', $socDmy);
  if (!$soc) {
    throw new Exception('Invalid SOC date format. Use dd/mm/yyyy.');
  }

  if (empty($patternDays)) {
    throw new Exception('Please select at least one day in the day pattern.');
  }

  // Fast lookup for PH
  $phSet = array_flip($publicHolidaysYmd);

  // This is the "search pointer" we use for S1 sessions
  $currentDate  = clone $soc;
  $lastDateUsed = null;
  $firstModule  = true;

  foreach ($modulesOrdered as $modCode) {
    if (!isset($grid[$modCode])) continue;

    $rows = $grid[$modCode];

    // For each module we track:
    $lastSod    = null; // 'S1','S2','S3'
    $lastS1Date = null; // DateTime of the last S1 in this module

    // ---- Apply module break (AFTER previous module) ----
    if (!$firstModule && $moduleBreakDays > 0 && $lastDateUsed) {
      $currentDate = clone $lastDateUsed;
      $currentDate->modify('+' . $moduleBreakDays . ' day');
    } elseif (!$firstModule && $lastDateUsed) {
      // No module break but at least move one day forward
      $currentDate = clone $lastDateUsed;
      $currentDate->modify('+1 day');
    }

    $firstModule = false;

    foreach ($rows as $idx => $row) {
      $sodRaw = trim((string)($row['session_of_the_day'] ?? ''));
      $sid    = $row['session_id'] ?? '??';

      // ---- Normalise & validate S1/S2/S3 ----
      if ($sodRaw === '') {
        throw new Exception("Missing session_of_the_day in module $modCode at session_id $sid.");
      }

      if (!preg_match('/^S([123])$/i', $sodRaw, $m)) {
        throw new Exception("Invalid session_of_the_day '$sodRaw' in module $modCode at session_id $sid. Only S1, S2, S3 are allowed.");
      }

      $sod = 'S' . $m[1]; // 'S1','S2','S3'

      // ---- Decide date based on S1/S2/S3 ----
      if ($sod === 'S1') {
        // S1 ALWAYS starts a NEW date

        // If this is the very first S1 of the module, we start from currentDate.
        // If not the first S1, we must move at least one calendar day forward.
        if ($lastSod !== null) {
          $currentDate->modify('+1 day');
        }

        // Find next VALID date (pattern + not holiday)
        while (true) {
          $dow   = (int)$currentDate->format('N');      // 1..7
          $key   = $currentDate->format('Y-m-d');
          $valid = in_array($dow, $patternDays, true) && !isset($phSet[$key]);
          if ($valid) break;
          $currentDate->modify('+1 day');
        }

        $assignedDate = clone $currentDate;
        $lastS1Date   = clone $assignedDate;
      } elseif ($sod === 'S2') {
        // S2 MUST follow S1, same date as last S1
        if ($lastSod !== 'S1' || !$lastS1Date) {
          $flow = $lastSod ? ($lastSod . ' → S2') : ('(start) → S2');
          throw new Exception(
            "Invalid session flow in module $modCode at session_id $sid: $flow. " .
              "Allowed flows: S1→S2, S1→S2→S3, S1→S2→S1, S1→S2→S3→S1."
          );
        }
        $assignedDate = clone $lastS1Date;
      } else { // S3
        // S3 MUST follow S2, same date as last S1
        if ($lastSod !== 'S2' || !$lastS1Date) {
          $flow = $lastSod ? ($lastSod . ' → S3') : ('(start) → S3');
          throw new Exception(
            "Invalid session flow in module $modCode at session_id $sid: $flow. " .
              "Allowed flows: S1→S2, S1→S2→S3, S1→S2→S1, S1→S2→S3→S1."
          );
        }
        $assignedDate = clone $lastS1Date;
      }

      // ---- Assign formatted date/time into row ----
      $rows[$idx]['scheduled_date'] = $assignedDate->format('d/m/Y');
      $rows[$idx]['scheduled_day']  = $assignedDate->format('D'); // Mon/Tue...

      $isSync  = stripos($row['session_mode'], 'sync') !== false;
      $isAsync = stripos($row['session_mode'], 'async') !== false;

      // ---------- Faculty logic ----------
      if ($isAsync) {
        // Async session → always NA
        // $rows[$idx]['faculty'] = 'NA';
      } else {
        // Sync session → apply mentor name
        $rows[$idx]['faculty'] = $mentorsByModule[$modCode] ?? '';
      }


      // ---------- Time logic ----------
      $modeLower = strtolower($row['session_mode'] ?? '');

      if (strpos($modeLower, 'as-async') !== false) {

        // AS-Async → 08:00 - 23:59
        $rows[$idx]['scheduled_time'] = "08:00 - 23:59";
      } elseif (strpos($modeLower, 'as-sync') !== false) {

        // AS-Sync → 08:00 - 22:00
        $rows[$idx]['scheduled_time'] = "08:00 - 22:00";
      } elseif (!$isAsync) {

        // Normal Sync session: calculate from start_time + duration
        $startObj = DateTime::createFromFormat('H:i', $startTime);
        if (!$startObj) {
          throw new Exception("Invalid start time format. Must be HH:MM (24h).");
        }

        $durationHours = floatval($row['hours'] ?? 0);
        $minutes = (int) round($durationHours * 60);
        $endObj = clone $startObj;
        $endObj->modify("+{$minutes} minutes");

        $rows[$idx]['scheduled_time'] =
          $startObj->format("H:i") . " - " . $endObj->format("H:i");
      } else {

        // Normal Async session
        $rows[$idx]['scheduled_time'] = "SAMPLE";
      }


      $lastDateUsed = clone $assignedDate;
      $lastSod      = $sod;
    }

    $grid[$modCode] = $rows;
  }

  $eoc = $lastDateUsed ? $lastDateUsed->format('d/m/Y') : null;
  return ['grid' => $grid, 'eoc' => $eoc];
}

// ---- STATE (selected) -------------------------------------------------------
$selected = [
  'course_id'     => trim($_POST['course_id'] ?? ($_SESSION['selected']['course_id'] ?? '')),
  'course_code'   => trim($_POST['course_code'] ?? ($_SESSION['selected']['course_code'] ?? '')),
  'learning_mode' => trim($_POST['learning_mode'] ?? ($_SESSION['selected']['learning_mode'] ?? '')),
  'course_title'  => trim($_POST['course_title'] ?? ($_SESSION['selected']['course_title'] ?? '')),
];

$_SESSION['selected'] = $selected;

// day labels for UI
$DAY_LABELS = [
  1 => 'Mon',
  2 => 'Tue',
  3 => 'Wed',
  4 => 'Thu',
  5 => 'Fri',
  6 => 'Sat',
  7 => 'Sun',
];

// existing meta (for re-populating form)
$meta            = $_SESSION['meta'] ?? [];
$selectedPattern = $meta['pattern_days'] ?? [];
$selectedCountries = $_SESSION['selected_countries'] ?? ($meta['countries'] ?? ['SG']);
$cohortSuffix    = $_SESSION['cohort_suffix'] ?? '';
$mentorsSaved    = $_SESSION['mentors'] ?? [];
$eoc             = $_SESSION['eoc'] ?? null;

// CLEAR All
if (isPost('clearCsv')) {
  unset(
    $_SESSION['grid_rows'],
    $_SESSION['generated'],
    $_SESSION['selected'],
    $_SESSION['meta'],
    $_SESSION['module_titles'],
    $_SESSION['mentors'],
    $_SESSION['cohort_suffix'],
    $_SESSION['eoc']
  );
  header('Location: ' . $_SERVER['PHP_SELF']);
  exit;
}

// LOAD TEMPLATES FOR ALL MODULES
if (isPost('loadAllTemplates')) {

  $courseId       = trim($_POST['course_id'] ?? '');
  $learningMode   = trim($_POST['learning_mode'] ?? '');
  $modulesOrdered = (array)($_POST['modules'] ?? []);       // user-ordered modules
  $moduleTitles   = (array)($_POST['module_titles'] ?? []); // code => title

  // 1️⃣ Get latest template
  $stmt = $conn->prepare("
        SELECT id FROM templates
        WHERE course_id=? AND learning_mode=?
        ORDER BY created_at DESC LIMIT 1
    ");
  $stmt->bind_param("ss", $courseId, $learningMode);
  $stmt->execute();
  $stmt->bind_result($templateId);
  $templateId = null;
  $stmt->fetch();
  $stmt->close();

  // Final grouped data: [module_code => rows[]]
  $grouped = [];

  // Prepare blank default rows
  $blankRows = [
    [
      'session_id' => 1,
      'session_of_the_day' => 'S1',
      'session_mode' => '',
      'topics' => '',
      'hours' => '',
      'faculty' => ''
    ],
    [
      'session_id' => 2,
      'session_of_the_day' => 'S2',
      'session_mode' => '',
      'topics' => '',
      'hours' => '',
      'faculty' => ''
    ],
    [
      'session_id' => 3,
      'session_of_the_day' => 'S3',
      'session_mode' => '',
      'topics' => '',
      'hours' => '',
      'faculty' => ''
    ],
  ];

  if ($templateId) {

    // 2️⃣ Fetch ALL template_data rows
    $stmt2 = $conn->prepare("
            SELECT session_id, session_day, session_of_the_day, module_code,
                   session_code, session_mode, topics, session_day_of_module,
                   hours, session_type, faculty
            FROM template_data
            WHERE template_id=?
            ORDER BY session_id ASC
        ");
    $stmt2->bind_param("i", $templateId);
    $stmt2->execute();
    $stmt2->bind_result(
      $session_id,
      $session_day,
      $session_of_the_day,
      $module_code,
      $session_code,
      $session_mode,
      $topics,
      $session_day_of_module,
      $hours,
      $session_type,
      $faculty
    );

    // 3️⃣ Group rows by module_code
    while ($stmt2->fetch()) {
      if (!$module_code) continue; // skip rows without module (e.g. orientation)
      $grouped[$module_code][] = [
        'session_id'            => $session_id,
        'session_day'           => $session_day,
        'session_of_the_day'    => $session_of_the_day,
        'module_code'           => $module_code,
        'session_code'          => $session_code,
        'session_mode'          => $session_mode,
        'topics'                => $topics,
        'session_day_of_module' => $session_day_of_module,
        'hours'                 => $hours,
        'session_type'          => $session_type,
        'faculty'               => $faculty
      ];
    }
    $stmt2->close();
  }

  // 4️⃣ Ensure all modules (user-ordered) exist in grouped
  $grid = [];

  foreach ($modulesOrdered as $modCode) {
    if (isset($grouped[$modCode])) {
      $grid[$modCode] = $grouped[$modCode];
    } else {
      // No data for this module → insert blank rows
      $grid[$modCode] = $blankRows;
    }
  }

  // Save to session
  $_SESSION['grid_rows'] = $grid;               // final grouped and ordered data
  $_SESSION['modules']   = $modulesOrdered;     // ordered module list
  $_SESSION['titles']    = $moduleTitles;       // module titles

  $_SESSION['selected']['course_id']     = $courseId;
  $_SESSION['selected']['learning_mode'] = $learningMode;

  $_SESSION['flash'] = [
    'type' => 'success',
    'message' => 'Templates loaded successfully.'
  ];

  header('Location: ' . $_SERVER['PHP_SELF']);
  exit;
}

// GENERATE SCHEDULE (after templates are loaded)
if (isPost('generateSchedule')) {
  $socDmy        = trim($_POST['soc'] ?? '');
  $patternDays   = array_map('intval', $_POST['pattern_days'] ?? []);
  $countries = (array)($_POST['countries'] ?? []);
  $startTime     = trim($_POST['start_time'] ?? '');
  // $endTime       = trim($_POST['end_time'] ?? '');
  $moduleBreak   = (int)($_POST['module_break'] ?? 0);
  $cohortSuffix  = trim($_POST['cohort_suffix'] ?? '');
  $mentorsInput  = $_POST['mentors'] ?? []; // mentors[MODCODE]

  $grid          = $_SESSION['grid_rows'] ?? [];
  $modulesOrdered = $_SESSION['modules'] ?? [];

  $_SESSION['cohort_suffix']   = $cohortSuffix;
  $_SESSION['mentors']         = $mentorsInput;
  $_SESSION['meta'] = [
    'soc'          => $socDmy,
    'pattern_days' => $patternDays,
    'countries'    => $countries,
    'start_time'   => $startTime,
    // 'end_time'     => $endTime,
    'module_break' => $moduleBreak,
  ];
  $_SESSION['selected_countries'] = $countries;


  if (!$grid) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Please load templates before generating schedule.'];
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
  }

  // Fetch PH dates for selected countries
  $phDates = [];
  if (!empty($countries)) {
    $placeholders = implode(',', array_fill(0, count($countries), '?'));
    $types        = str_repeat('s', count($countries));
    $stmtPh       = $conn->prepare("SELECT hdate FROM public_holidays WHERE country_code IN ($placeholders)");
    $stmtPh->bind_param($types, ...$countries);
    $stmtPh->execute();
    $res = $stmtPh->get_result();
    while ($row = $res->fetch_assoc()) {
      $phDates[] = $row['hdate']; // YYYY-MM-DD
    }
    $stmtPh->close();
  }

  try {
    $result = generateScheduleForGrid(
      $grid,
      $modulesOrdered,
      $socDmy,
      $patternDays,
      $phDates,
      $startTime,
      // $endTime,
      $mentorsInput,
      $moduleBreak
    );

    $_SESSION['grid_rows']       = $result['grid'];
    $_SESSION['eoc']             = $result['eoc'];


    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Schedule generated successfully.'];
  } catch (Exception $ex) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => $ex->getMessage()];
  }

  header('Location: ' . $_SERVER['PHP_SELF']);
  exit;
}
$preselectedCountries = $selectedCountries ?? ['SG'];


// flash (optional – keep if you plan to show messages)
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

require __DIR__ . '/components/header.php';
require __DIR__ . '/components/navbar.php';
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Select Course, Mode & Modules</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Flatpickr CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

  <style>
    #results {
      position: absolute;
      z-index: 1000;
      width: 100%;
    }

    #results .list-group-item {
      cursor: pointer;
    }

    .drag-btn {
      min-width: 2.25rem;
    }

    .short_col {
      width: 50px !important;
    }

    .short_col2 {
      width: 150px !important;
    }

    .details_col {
      width: 450px;
      white-space: pre-line;
      word-break: break-word;
    }

    .preline {
      font-size: 14px !important;
      white-space: pre-line;
    }
  </style>
</head>

<body class="py-4">
  <div class="container m-0 px-0 w-100" style="max-width:1436px;">

    <!-- Flash Msg (optional) -->
    <?php if ($flash): ?>
      <div class="d-flex justify-content-between alert alert-<?= h($flash['type'] ?? 'info') ?> mt-2">
        <?= h($flash['message'] ?? '') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <h4 class="mb-3">Select Course, Mode & Modules</h4>

    <!-- Course Search + Mode + Modules Order -->
    <form method="post" class="mb-4">

      <div class="row g-2 justify-content-start align-items-start">

        <!-- Course search -->
        <div class="col-6 position-relative">
          <div class="input-group">
            <input type="text" id="search" class="form-control shadow-none border-secondary border-end-0 me-1" placeholder="<?= $selected['course_title'] ? h($selected['course_title']) : 'Search Courses' ?>">
            <button type="button" id="clearSearch" class="btn btn-outline-secondary">&times;</button>
          </div>
          <div id="results" class="list-group"></div>
          <div id="courseTitle" class="fw-semibold ms-2">
            <?php if ($selected['course_title']): ?>
              <?= h($selected['course_title']) ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- Hidden carries -->
        <input type="hidden" name="course_id" id="course_id" value="<?= h($selected['course_id']) ?>">
        <input type="hidden" name="course_code" id="course_code" value="<?= h($selected['course_code']) ?>">
        <input type="hidden" name="course_title" id="course_title" value="<?= h($selected['course_title']) ?>">

        <!-- Mode select -->
        <div class="col-md-3">
          <select id="modeSelect" class="form-select shadow-none" name="learning_mode" required>
            <?php if ($selected['learning_mode']): ?>
              <option value="<?= h($selected['learning_mode']) ?>" selected><?= h($selected['learning_mode']) ?></option>
            <?php else: ?>
              <option value="">Learning Mode</option>
            <?php endif; ?>
          </select>
          <div id="modeDetails" class="small"></div>
        </div>

        <!-- Modules list (ordered) -->
        <div class="col-12 mt-3">
          <label class="form-label mb-1">Modules (arrange in desired order)</label>
          <ul id="modulesList" class="list-group w-100">
            <!-- JS will populate li items (module title + code + hidden input name="modules[]") -->
          </ul>
          <div class="form-text">
            Use ↑ / ↓ to order modules. The list order decides loading & generation order.
          </div>
        </div>

        <!-- Load / Clear -->
        <div class="col-12 d-flex justify-content-end mt-2">
          <button class="btn btn-primary" name="loadAllTemplates" value="1" type="submit">Load</button>
          <button type="submit" name="clearCsv" value="1" class="btn btn-danger ms-1">Clear All</button>
        </div>

      </div>
    </form>

    <!-- Schedule settings (shown only after templates loaded) -->
    <?php if (!empty($_SESSION['grid_rows'])): ?>
      <form method="post" class="mb-4 border rounded p-3">
        <h5 class="mb-3">Schedule Settings</h5>
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">SOC (Start of Course)</label>
            <input type="text" name="soc" id="soc" class="form-control form-control-sm" placeholder="dd/mm/yyyy" value="<?= h($meta['soc'] ?? '') ?>" required>
          </div>

          <div class="col-md-3">
            <label class="form-label">Sync Session Start Time</label>
            <input type="time" name="start_time" class="form-control form-control-sm" value="<?= h($meta['start_time'] ?? '') ?>" required>
          </div>

          <!-- <div class="col-md-3">
            <label class="form-label">Session End Time</label>
            <input type="time" name="end_time" class="form-control form-control-sm" value="<?= h($meta['end_time'] ?? '') ?>" required>
          </div> -->

          <div class="col-md-3">
            <label class="form-label">Module Break (days)</label>
            <input type="number" name="module_break" class="form-control form-control-sm" min="0" value="<?= h($meta['module_break'] ?? 0) ?>">
          </div>

          <div class="col-md-4">
            <label class="form-label">Day Pattern</label><br>
            <?php foreach ($DAY_LABELS as $num => $label): ?>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="day<?= $num ?>" name="pattern_days[]" value="<?= $num ?>" <?= in_array($num, $selectedPattern ?? [], true) ? 'checked' : '' ?>>
                <label class="form-check-label" for="day<?= $num ?>"><?= $label ?></label>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="col-md-4">
            <label class="form-label">Countries (Public Holidays)</label>

            <div class="dropdown w-100">
              <button
                class="btn btn-outline-primary w-100 d-flex justify-content-between align-items-center"
                type="button"
                data-bs-toggle="dropdown"
                data-bs-auto-close="outside">
                <span id="countriesSummary">Select countries…</span>
                <span class="badge text-bg-secondary" id="countriesCount">0</span>
              </button>

              <div class="dropdown-menu p-3 w-100 shadow">

                <!-- Search -->
                <input type="text" class="form-control form-control-sm mb-2"
                  id="countrySearch" placeholder="Search country…">

                <!-- List -->
                <div id="countriesList" class="d-grid gap-2" style="max-height:220px; overflow:auto;">
                  <?php
                  $countriesPreset = [
                    'SG' => 'Singapore',
                    'IN' => 'India',
                    'LK' => 'Sri Lanka',
                    'BD' => 'Bangladesh',
                    'MM' => 'Myanmar',
                    'PH' => 'Philippines',
                    'MY' => 'Malaysia'
                  ];
                  foreach ($countriesPreset as $code => $label): ?>
                    <label class="form-check">
                      <input class="form-check-input country-check"
                        type="checkbox"
                        value="<?= $code ?>"
                        data-label="<?= $label ?>"
                        <?= in_array($code, $preselectedCountries) ? 'checked' : '' ?>>
                      <span class="form-check-label"><?= $label ?></span>
                    </label>
                  <?php endforeach; ?>
                </div>

                <!-- Buttons -->
                <div class="d-flex gap-2 mt-2">
                  <button type="button" class="btn btn-sm btn-light" id="selectAllCountries">Select all</button>
                  <button type="button" class="btn btn-sm btn-light" id="clearCountries">Clear</button>
                </div>
              </div>
            </div>

            <!-- Badges -->
            <div class="mt-2 d-flex flex-wrap gap-2" id="selectedCountryBadges"></div>

            <!-- Hidden Inputs -->
            <div id="countriesHidden"></div>

            <div class="small mt-1 text-muted">
              The system skips holidays that appear in any selected country.
            </div>
          </div>

          <div class="col-md-4">
            <label class="form-label">Cohort Suffix (mmyy)</label>
            <input type="text" name="cohort_suffix" class="form-control form-control-sm" placeholder="1125" value="<?= h($cohortSuffix) ?>">
          </div>

          <div class="col-12">
            <label class="form-label">Module Mentors</label>
            <div class="row g-2">
              <?php foreach ($_SESSION['modules'] as $modCode): ?>
                <div class="col-md-4">
                  <div class="input-group input-group-sm mb-1">
                    <span class="input-group-text"><?= h($modCode) ?></span>
                    <input type="text" name="mentors[<?= h($modCode) ?>]" class="form-control" placeholder="Mentor name" value="<?= h($mentorsSaved[$modCode] ?? '') ?>">
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="col-12 d-flex justify-content-end">
            <button type="submit" name="generateSchedule" value="1" class="btn btn-success">Generate Schedule</button>
          </div>
        </div>
      </form>
    <?php endif; ?>

    <?php if ($eoc): ?>
      <div class="alert alert-info py-2">
        <strong>End of Course:</strong> <?= h($eoc) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['grid_rows'])): ?>

      <div class="accordion" id="modulesAccordion">

        <?php
        $index = 0;
        $cohortSuffix = $_SESSION['cohort_suffix'] ?? '';
        $mentorsSaved = $_SESSION['mentors'] ?? [];
        foreach ($_SESSION['modules'] as $modCode):
          $rows  = $_SESSION['grid_rows'][$modCode];
          $title = $_SESSION['titles'][$modCode] ?? $modCode;
          $sessionNumber = 1;
          $mentorName = $mentorsSaved[$modCode] ?? '';
          $cohortCode = $cohortSuffix ? ($modCode . '-' . $cohortSuffix) : '';
        ?>
          <div class="accordion-item">
            <h2 class="accordion-header" id="heading<?= $index ?>">
              <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#collapse<?= $index ?>">
                <?= h("[$modCode] $title") ?>
                <?php if ($cohortCode): ?>
                  <span class="ms-2 badge text-bg-secondary">Cohort: <?= h($cohortCode) ?></span>
                <?php endif; ?>
                <?php if ($mentorName): ?>
                  <span class="ms-2 badge text-bg-info">Mentor: <?= h($mentorName) ?></span>
                <?php endif; ?>
              </button>
            </h2>

            <div id="collapse<?= $index ?>"
              class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>"
              data-bs-parent="#modulesAccordion">
              <div class="accordion-body">

                <table class="table table-bordered align-middle">
                  <thead>
                    <tr>
                      <th class="short_col text-center">No</th>
                      <!-- <th class="">Session Mode</th> -->
                      <th class="short_col2">Mode</th>
                      <th class="details_col">Session Details</th>
                      <th>Hours</th>
                      <!-- <th>Duration [h]</th> -->
                      <th class="short_col2">Faculty Name</th>
                      <th class="short_col2">Date</th>
                      <th class="short_col2">Day</th>
                      <th class="">Time (HH:MM)</th>
                    </tr>
                  </thead>

                  <tbody>
                    <?php foreach ($rows as $r): ?>
                      <tr>
                        <td class="short_col text-center"><?= $sessionNumber++ ?></td>
                        <td><?= h($r['session_mode']) ?></td>
                        <td class="preline"><?= nl2br(h($r['topics'])) ?></td>
                        <td>
                          <?php
                          $h = $r['hours'];
                          // remove .0 but keep decimals like .5
                          if (strpos($h, '.') !== false) {
                            $h = rtrim(rtrim($h, '0'), '.');
                          }
                          echo h($h);
                          ?>
                        </td>
                        <td><?= h($r['faculty']) ?></td>
                        <td>
                          <input type="text" name="edit_date[<?= $modCode ?>][<?= $sessionNumber ?>]"
                            class="form-control form-control-sm date-input"
                            value="<?= h($r['scheduled_date'] ?? '') ?>">
                        </td>

                        <td>
                          <input type="text" class="form-control form-control-sm"
                            value="<?= h($r['scheduled_day'] ?? '') ?>" readonly>
                        </td>

                        <td class="">
                          <div class="d-flex flex-column justify-content-center align-items-center">
                            <?php
                            // show SAMPLE if async (non sync, non AS-async, non AS-sync)
                            $modeLower = strtolower($r['session_mode'] ?? '');
                            $isAsyncFront = strpos($modeLower, 'async') !== false;
                            $isASAsyncFront = strpos($modeLower, 'as-async') !== false;
                            $isASSyncFront = strpos($modeLower, 'as-sync') !== false;

                            if ($isAsyncFront && !$isASAsyncFront && !$isASSyncFront):
                            ?>
                              <div class="small text-muted mt-1 text-center">Before Next Sync Session</div>
                            <?php endif; ?>


                            <div class="d-flex flex-row w-100">
                              <input type="text"
                                class="form-control form-control-sm time-input"
                                value="<?= isset($r['scheduled_time']) ? explode(' - ', $r['scheduled_time'])[0] : '' ?>"
                                placeholder="Start">

                              ～

                              <input type="text"
                                class="form-control form-control-sm time-input ms-1"
                                value="<?= isset($r['scheduled_time']) ? explode(' - ', $r['scheduled_time'])[1] ?? '' : '' ?>"
                                placeholder="End">
                            </div>


                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>

                </table>

              </div>
            </div>
          </div>

        <?php
          $index++;
        endforeach;
        ?>

      </div>

    <?php endif; ?>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

  <script>
    // ----- Helpers -----
    function debounce(fn, ms = 250) {
      let t;
      return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...args), ms);
      };
    }

    function setAll(name, value) {
      document.querySelectorAll(`input[name="${name}"]`).forEach(el => el.value = value ?? '');
    }

    // ----- Elements -----
    const searchInput = document.getElementById('search');
    const resultsBox = document.getElementById('results');
    const clearBtn = document.getElementById('clearSearch');
    const courseTitle = document.getElementById('courseTitle');
    const modeSelect = document.getElementById('modeSelect');
    const modeDetails = document.getElementById('modeDetails');
    const modulesList = document.getElementById('modulesList');

    let lastCourseData = null;

    // Flatpickr for SOC
    if (document.getElementById('soc')) {
      flatpickr("#soc", {
        dateFormat: "d/m/Y"
      });
    }

    // ----- Course search -----
    searchInput?.addEventListener('input', debounce(async () => {
      const q = searchInput.value.trim();
      if (!q) {
        resultsBox.innerHTML = '';
        return;
      }
      const res = await fetch('/schedule_gen/admin/course/search_courses.php?q=' + encodeURIComponent(q));
      const rows = await res.json();
      resultsBox.innerHTML = rows.map(r => `
        <button class="list-group-item list-group-item-action"
                data-id="${r.course_id}"
                data-code="${r.course_code}">
          [${r.course_code}] ${r.course_title_external}
        </button>
      `).join('');
    }, 250));

    clearBtn?.addEventListener('click', () => {
      searchInput.value = '';
      resultsBox.innerHTML = '';
      searchInput.focus();
    });

    // When a course is clicked in the search results
    resultsBox?.addEventListener('click', async e => {
      const btn = e.target.closest('button');
      if (!btn) return;

      resultsBox.innerHTML = '';
      searchInput.value = btn.textContent.trim();
      setAll('course_id', btn.dataset.id);
      setAll('course_code', btn.dataset.code);

      const res = await fetch('/schedule_gen/admin/course/get_course_details.php?id=' + btn.dataset.id);
      const data = await res.json();
      lastCourseData = data;

      courseTitle.textContent = btn.textContent;
      setAll('course_title', courseTitle.textContent.trim());

      // Fill modes
      modeSelect.innerHTML = (data.data.master_learning_modes || [])
        .map(m => `<option value="${m.mode}">${m.mode}</option>`)
        .join('');
      setAll('learning_mode', '');

      modeDetails.innerHTML = '';

      // Render module ORDER list
      const mods = data.data.modules || [];
      if (!mods.length) {
        modulesList.innerHTML = '<li class="list-group-item text-muted">No modules found for this course.</li>';
        return;
      }

      modulesList.innerHTML = mods.map(m => `
        <li class="list-group-item d-flex align-items-center justify-content-between"
            data-code="${m.module_code}"
            data-title="${m.module_title}">
          <div class="d-flex align-items-center justify-content-start me-2">
            <div class="">[${m.module_code}]&nbsp;</div>
            <div class="">${m.module_title}</div>
            <input type="hidden" name="modules[]" value="${m.module_code}">
            <input type="hidden" name="module_titles[${m.module_code}]" value="${m.module_title}">
          </div>
          <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary btn-sm drag-btn move-up" title="Move up">↑</button>
            <button type="button" class="btn btn-outline-secondary btn-sm drag-btn move-down" title="Move down">↓</button>
          </div>
        </li>
      `).join('');
    });

    // ----- Mode change -----
    modeSelect?.addEventListener('change', function() {
      if (!lastCourseData || this.value === "") {
        setAll('learning_mode', '');
        modeDetails.innerHTML = "";
        return;
      }

      const m = (lastCourseData.data.master_learning_modes || [])
        .find(x => x.mode === this.value);

      setAll('learning_mode', m?.mode || '');

      if (m) {
        modeDetails.innerHTML = `
          <div class="card card-body p-2">
            <div>
              <b>Mode:</b> ${m.mode} |
              <b>Duration:</b> ${m.course_duration ?? ''} |
              <b>Days/Week:</b> ${m.days_per_week ?? ''} |
              <b>Hours/Day:</b> ${m.hours_per_day ?? ''} |
              <b>Hours/Week:</b> ${m.hours_per_week ?? ''}
            </div>
          </div>`;
      } else {
        modeDetails.innerHTML = '';
      }
    });

    // ----- Modules order buttons -----
    modulesList?.addEventListener('click', e => {
      const li = e.target.closest('li');
      if (!li) return;

      if (e.target.classList.contains('move-up')) {
        const prev = li.previousElementSibling;
        if (prev) li.parentNode.insertBefore(li, prev);
      } else if (e.target.classList.contains('move-down')) {
        const next = li.nextElementSibling;
        if (next) li.parentNode.insertBefore(next, li);
      }

      // re-sync hidden inputs order
      Array.from(modulesList.querySelectorAll('li')).forEach(node => {
        const hidden = node.querySelector('input[name="modules[]"]');
        if (hidden) hidden.value = node.dataset.code;
      });
    });

    (function() {

      const preselected = <?= json_encode($preselectedCountries) ?>;

      const checks = Array.from(document.querySelectorAll('.country-check'));
      const badgesBox = document.getElementById('selectedCountryBadges');
      const hiddenBox = document.getElementById('countriesHidden');
      const summaryEl = document.getElementById('countriesSummary');
      const countEl = document.getElementById('countriesCount');
      const searchEl = document.getElementById('countrySearch');

      // Load old selections
      checks.forEach(c => c.checked = preselected.includes(c.value));

      function getSelected() {
        return checks
          .filter(c => c.checked)
          .map(c => ({
            code: c.value,
            label: c.dataset.label
          }));
      }

      function renderSelected() {
        const selected = getSelected();

        // Badges
        badgesBox.innerHTML = selected.map(s => `
      <span class="badge bg-primary">
        ${s.label}
        <button type="button"
                class="btn-close btn-close-white btn-sm remove-country"
                data-code="${s.code}">
        </button>
      </span>
    `).join('');

        // Hidden inputs
        hiddenBox.innerHTML = selected.map(s =>
          `<input type="hidden" name="countries[]" value="${s.code}">`
        ).join('');

        // Summary label
        if (selected.length === 0) summaryEl.textContent = "Select countries…";
        else if (selected.length === 1) summaryEl.textContent = selected[0].label;
        else summaryEl.textContent = `${selected[0].label} + ${selected.length - 1} more`;

        // Count badge
        countEl.textContent = selected.length;
      }

      renderSelected();

      // Checkbox change
      checks.forEach(c => c.addEventListener('change', renderSelected));

      // Badge remove
      badgesBox.addEventListener('click', e => {
        const btn = e.target.closest('.remove-country');
        if (!btn) return;
        const target = checks.find(c => c.value === btn.dataset.code);
        if (target) target.checked = false;
        renderSelected();
      });

      // Search filter
      searchEl.addEventListener('input', () => {
        const q = searchEl.value.toLowerCase();
        document.querySelectorAll('#countriesList .form-check').forEach(lbl => {
          lbl.style.display = lbl.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
      });

      // Select all
      document.getElementById('selectAllCountries')
        .addEventListener('click', () => {
          checks.forEach(c => c.checked = true);
          renderSelected();
        });

      // Clear all
      document.getElementById('clearCountries')
        .addEventListener('click', () => {
          checks.forEach(c => c.checked = false);
          renderSelected();
        });

    })();

    flatpickr(".date-input", {
      dateFormat: "d/m/Y"
    });

    flatpickr(".time-input", {
      enableTime: true,
      noCalendar: true,
      time_24hr: true,
      dateFormat: "H:i",
      allowInput: true // allows typing “09:00 - 12:00”
    });
  </script>
</body>

</html>