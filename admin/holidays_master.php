<?php
// holidays_master.php — single file (MySQLi + Bootstrap + inline edit + Google import)
// REQUIREMENTS: db.php (MySQLi $conn), auth/guard.php, .env with GOOGLE_CALENDAR_API_KEY
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../auth/guard.php';

require __DIR__ . '/../db.php';
$baseUrl = $_ENV['BASE_URL'] ?? '/';
$apiKey  = $_ENV['GOOGLE_CALENDAR_API_KEY'] ?? '';

$me = $_SESSION['auth'] ?? null;
requireRole($conn, 'Admin'); // must be logged in + have Admin

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// ---------- Static ISO list (code => name) ----------
$COUNTRIES = [
    'AF' => 'Afghanistan',
    'AX' => 'Åland Islands',
    'AL' => 'Albania',
    'DZ' => 'Algeria',
    'AS' => 'American Samoa',
    'AD' => 'Andorra',
    'AO' => 'Angola',
    'AI' => 'Anguilla',
    'AQ' => 'Antarctica',
    'AG' => 'Antigua and Barbuda',
    'AR' => 'Argentina',
    'AM' => 'Armenia',
    'AW' => 'Aruba',
    'AU' => 'Australia',
    'AT' => 'Austria',
    'AZ' => 'Azerbaijan',
    'BS' => 'Bahamas',
    'BH' => 'Bahrain',
    'BD' => 'Bangladesh',
    'BB' => 'Barbados',
    'BY' => 'Belarus',
    'BE' => 'Belgium',
    'BZ' => 'Belize',
    'BJ' => 'Benin',
    'BM' => 'Bermuda',
    'BT' => 'Bhutan',
    'BO' => 'Bolivia',
    'BQ' => 'Bonaire, Sint Eustatius and Saba',
    'BA' => 'Bosnia and Herzegovina',
    'BW' => 'Botswana',
    'BV' => 'Bouvet Island',
    'BR' => 'Brazil',
    'IO' => 'British Indian Ocean Territory',
    'BN' => 'Brunei Darussalam',
    'BG' => 'Bulgaria',
    'BF' => 'Burkina Faso',
    'BI' => 'Burundi',
    'KH' => 'Cambodia',
    'CM' => 'Cameroon',
    'CA' => 'Canada',
    'CV' => 'Cabo Verde',
    'KY' => 'Cayman Islands',
    'CF' => 'Central African Republic',
    'TD' => 'Chad',
    'CL' => 'Chile',
    'CN' => 'China',
    'CX' => 'Christmas Island',
    'CC' => 'Cocos (Keeling) Islands',
    'CO' => 'Colombia',
    'KM' => 'Comoros',
    'CG' => 'Congo',
    'CD' => 'Congo, Democratic Republic',
    'CK' => 'Cook Islands',
    'CR' => 'Costa Rica',
    'CI' => 'Côte d’Ivoire',
    'HR' => 'Croatia',
    'CU' => 'Cuba',
    'CW' => 'Curaçao',
    'CY' => 'Cyprus',
    'CZ' => 'Czechia',
    'DK' => 'Denmark',
    'DJ' => 'Djibouti',
    'DM' => 'Dominica',
    'DO' => 'Dominican Republic',
    'EC' => 'Ecuador',
    'EG' => 'Egypt',
    'SV' => 'El Salvador',
    'GQ' => 'Equatorial Guinea',
    'ER' => 'Eritrea',
    'EE' => 'Estonia',
    'SZ' => 'Eswatini',
    'ET' => 'Ethiopia',
    'FK' => 'Falkland Islands',
    'FO' => 'Faroe Islands',
    'FJ' => 'Fiji',
    'FI' => 'Finland',
    'FR' => 'France',
    'GF' => 'French Guiana',
    'PF' => 'French Polynesia',
    'TF' => 'French Southern Territories',
    'GA' => 'Gabon',
    'GM' => 'Gambia',
    'GE' => 'Georgia',
    'DE' => 'Germany',
    'GH' => 'Ghana',
    'GI' => 'Gibraltar',
    'GR' => 'Greece',
    'GL' => 'Greenland',
    'GD' => 'Grenada',
    'GP' => 'Guadeloupe',
    'GU' => 'Guam',
    'GT' => 'Guatemala',
    'GG' => 'Guernsey',
    'GN' => 'Guinea',
    'GW' => 'Guinea-Bissau',
    'GY' => 'Guyana',
    'HT' => 'Haiti',
    'HM' => 'Heard & McDonald Islands',
    'VA' => 'Holy See',
    'HN' => 'Honduras',
    'HK' => 'Hong Kong',
    'HU' => 'Hungary',
    'IS' => 'Iceland',
    'IN' => 'India',
    'ID' => 'Indonesia',
    'IR' => 'Iran',
    'IQ' => 'Iraq',
    'IE' => 'Ireland',
    'IM' => 'Isle of Man',
    'IL' => 'Israel',
    'IT' => 'Italy',
    'JM' => 'Jamaica',
    'JP' => 'Japan',
    'JE' => 'Jersey',
    'JO' => 'Jordan',
    'KZ' => 'Kazakhstan',
    'KE' => 'Kenya',
    'KI' => 'Kiribati',
    'KP' => 'Korea (DPRK)',
    'KR' => 'Korea (Republic of)',
    'KW' => 'Kuwait',
    'KG' => 'Kyrgyzstan',
    'LA' => 'Lao PDR',
    'LV' => 'Latvia',
    'LB' => 'Lebanon',
    'LS' => 'Lesotho',
    'LR' => 'Liberia',
    'LY' => 'Libya',
    'LI' => 'Liechtenstein',
    'LT' => 'Lithuania',
    'LU' => 'Luxembourg',
    'MO' => 'Macao',
    'MG' => 'Madagascar',
    'MW' => 'Malawi',
    'MY' => 'Malaysia',
    'MV' => 'Maldives',
    'ML' => 'Mali',
    'MT' => 'Malta',
    'MH' => 'Marshall Islands',
    'MQ' => 'Martinique',
    'MR' => 'Mauritania',
    'MU' => 'Mauritius',
    'YT' => 'Mayotte',
    'MX' => 'Mexico',
    'FM' => 'Micronesia',
    'MD' => 'Moldova',
    'MC' => 'Monaco',
    'MN' => 'Mongolia',
    'ME' => 'Montenegro',
    'MS' => 'Montserrat',
    'MA' => 'Morocco',
    'MZ' => 'Mozambique',
    'MM' => 'Myanmar',
    'NA' => 'Namibia',
    'NR' => 'Nauru',
    'NP' => 'Nepal',
    'NL' => 'Netherlands',
    'NC' => 'New Caledonia',
    'NZ' => 'New Zealand',
    'NI' => 'Nicaragua',
    'NE' => 'Niger',
    'NG' => 'Nigeria',
    'NU' => 'Niue',
    'NF' => 'Norfolk Island',
    'MK' => 'North Macedonia',
    'MP' => 'Northern Mariana Islands',
    'NO' => 'Norway',
    'OM' => 'Oman',
    'PK' => 'Pakistan',
    'PW' => 'Palau',
    'PS' => 'Palestine, State of',
    'PA' => 'Panama',
    'PG' => 'Papua New Guinea',
    'PY' => 'Paraguay',
    'PE' => 'Peru',
    'PH' => 'Philippines',
    'PN' => 'Pitcairn',
    'PL' => 'Poland',
    'PT' => 'Portugal',
    'PR' => 'Puerto Rico',
    'QA' => 'Qatar',
    'RE' => 'Réunion',
    'RO' => 'Romania',
    'RU' => 'Russian Federation',
    'RW' => 'Rwanda',
    'BL' => 'Saint Barthélemy',
    'SH' => 'Saint Helena',
    'KN' => 'Saint Kitts and Nevis',
    'LC' => 'Saint Lucia',
    'MF' => 'Saint Martin (French)',
    'PM' => 'Saint Pierre and Miquelon',
    'VC' => 'Saint Vincent and the Grenadines',
    'WS' => 'Samoa',
    'SM' => 'San Marino',
    'ST' => 'Sao Tome and Principe',
    'SA' => 'Saudi Arabia',
    'SN' => 'Senegal',
    'RS' => 'Serbia',
    'SC' => 'Seychelles',
    'SL' => 'Sierra Leone',
    'SG' => 'Singapore',
    'SX' => 'Sint Maarten (Dutch)',
    'SK' => 'Slovakia',
    'SI' => 'Slovenia',
    'SB' => 'Solomon Islands',
    'SO' => 'Somalia',
    'ZA' => 'South Africa',
    'GS' => 'South Georgia & Sandwich',
    'SS' => 'South Sudan',
    'ES' => 'Spain',
    'LK' => 'Sri Lanka',
    'SD' => 'Sudan',
    'SR' => 'Suriname',
    'SJ' => 'Svalbard and Jan Mayen',
    'SE' => 'Sweden',
    'CH' => 'Switzerland',
    'SY' => 'Syrian Arab Republic',
    'TW' => 'Taiwan',
    'TJ' => 'Tajikistan',
    'TZ' => 'Tanzania',
    'TH' => 'Thailand',
    'TL' => 'Timor-Leste',
    'TG' => 'Togo',
    'TK' => 'Tokelau',
    'TO' => 'Tonga',
    'TT' => 'Trinidad and Tobago',
    'TN' => 'Tunisia',
    'TR' => 'Türkiye',
    'TM' => 'Turkmenistan',
    'TC' => 'Turks and Caicos Islands',
    'TV' => 'Tuvalu',
    'UG' => 'Uganda',
    'UA' => 'Ukraine',
    'AE' => 'United Arab Emirates',
    'GB' => 'United Kingdom',
    'US' => 'United States of America',
    'UM' => 'US Outlying Islands',
    'UY' => 'Uruguay',
    'UZ' => 'Uzbekistan',
    'VU' => 'Vanuatu',
    'VE' => 'Venezuela',
    'VN' => 'Viet Nam',
    'VG' => 'Virgin Islands (British)',
    'VI' => 'Virgin Islands (U.S.)',
    'WF' => 'Wallis and Futuna',
    'EH' => 'Western Sahara',
    'YE' => 'Yemen',
    'ZM' => 'Zambia',
    'ZW' => 'Zimbabwe'
];

// ---------- Known Google Holiday calendar exceptions ----------
$CALENDAR_EXCEPTIONS = [
    'SG' => 'en.singapore#holiday@group.v.calendar.google.com',
    'LK' => 'en.lk#holiday@group.v.calendar.google.com',
    'US' => 'en.usa#holiday@group.v.calendar.google.com',
    'GB' => 'en.uk#holiday@group.v.calendar.google.com',
    'IN' => 'en.indian#holiday@group.v.calendar.google.com',
    'AU' => 'en.australian#holiday@group.v.calendar.google.com',
    'CA' => 'en.canadian#holiday@group.v.calendar.google.com',
    'IE' => 'en.irish#holiday@group.v.calendar.google.com',
    'NZ' => 'en.new_zealand#holiday@group.v.calendar.google.com',
    'ZA' => 'en.sa#holiday@group.v.calendar.google.com',
    'PH' => 'en.philippines#holiday@group.v.calendar.google.com',
    'MY' => 'en.malaysia#holiday@group.v.calendar.google.com',
    'JP' => 'en.japanese#holiday@group.v.calendar.google.com',
    'DE' => 'en.german#holiday@group.v.calendar.google.com',
    'FR' => 'en.french#holiday@group.v.calendar.google.com',
    'IT' => 'en.italian#holiday@group.v.calendar.google.com',
    'ES' => 'en.spain#holiday@group.v.calendar.google.com',
    'BR' => 'en.brazilian#holiday@group.v.calendar.google.com'
];

// ---------- Helpers ----------
function slugify_country(string $name): string
{
    $slug = strtolower($name);
    $slug = str_replace(['&', '(', ')', ',', '.'], ' ', $slug);
    $slug = preg_replace('/\s+/', '_', trim($slug));
    return $slug;
}

function tryCalendarId(string $calendarId, string $apiKey): bool
{
    $url = "https://www.googleapis.com/calendar/v3/calendars/" . urlencode($calendarId) . "/events?maxResults=1&key=" . urlencode($apiKey);
    $json = @file_get_contents($url);
    if ($json === false) return false;
    $data = json_decode($json, true);
    return is_array($data) && (!isset($data['error']));
}

// Resolve Google holiday calendar id for a given ISO alpha-2
function resolveCalendarId(string $alpha2, array $COUNTRIES, array $EXC, string $apiKey): ?string
{
    $alpha2 = strtoupper($alpha2);
    if (isset($EXC[$alpha2])) return $EXC[$alpha2];

    $name = $COUNTRIES[$alpha2] ?? null;
    if (!$name) return null;

    // Candidate patterns (best-effort)
    $candidates = [];
    $candidates[] = 'en.' . strtolower($alpha2) . '#holiday@group.v.calendar.google.com';       // en.lk, en.sg
    $candidates[] = 'en.' . slugify_country($name) . '#holiday@group.v.calendar.google.com';     // en.sri_lanka, en.united_kingdom
    $candidates[] = 'en.' . strtolower(str_replace(' ', '', $name)) . '#holiday@group.v.calendar.google.com'; // en.srilanka

    foreach ($candidates as $cid) {
        if (tryCalendarId($cid, $apiKey)) return $cid;
    }
    return null; // Not available / unknown
}

// ---------- Actions (AJAX and non-AJAX) ----------
header('X-Content-Type-Options: nosniff');

$selected = isset($_GET['country_code']) ? strtoupper($_GET['country_code']) : 'SG';
if (!isset($COUNTRIES[$selected])) $selected = 'SG';

$statusMsg = '';

// Fetch from Google and save
if (isset($_GET['fetch'])) {
    $country = $selected;
    $start   = $_GET['start_date'] . 'T00:00:00Z';
    $end     = $_GET['end_date']   . 'T23:59:59Z';

    if (!$apiKey) {
        $statusMsg = "<div class='alert alert-danger'>Missing GOOGLE_CALENDAR_API_KEY in environment.</div>";
    } else {
        $calId = resolveCalendarId($country, $COUNTRIES, $CALENDAR_EXCEPTIONS, $apiKey);
        if (!$calId) {
            $statusMsg = "<div class='alert alert-warning'>No official Google public holiday calendar found for {$COUNTRIES[$country]}. You can still add holidays manually below.</div>";
        } else {
            $url = "https://www.googleapis.com/calendar/v3/calendars/" . urlencode($calId) .
                "/events?key=" . urlencode($apiKey) .
                "&timeMin=" . urlencode($start) .
                "&timeMax=" . urlencode($end) .
                "&singleEvents=true&orderBy=startTime&maxResults=2500";
            $json = @file_get_contents($url);
            if ($json !== false) {
                $data = json_decode($json, true);
                if (!empty($data['items'])) {
                    $stmt = $conn->prepare("INSERT IGNORE INTO public_holidays (country_code, hdate, name, source) VALUES (?, ?, ?, 'GOOGLE_API')");
                    foreach ($data['items'] as $event) {
                        $date = $event['start']['date'] ?? null;
                        $name = $event['summary'] ?? '';
                        if ($date && $name) {
                            $n = $conn->real_escape_string($name);
                            $stmt->bind_param('sss', $country, $date, $n);
                            $stmt->execute();
                        }
                    }
                    $stmt->close();
                    $statusMsg = "<div class='alert alert-success'>Holidays imported and saved for {$COUNTRIES[$country]}.</div>";
                } else {
                    $statusMsg = "<div class='alert alert-warning'>No holidays returned for the selected range.</div>";
                }
            } else {
                $statusMsg = "<div class='alert alert-danger'>Failed to fetch from Google Calendar API. Check API key/network.</div>";
            }
        }
    }
}

// AJAX: update (inline save)
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $id   = (int)($_POST['id'] ?? 0);
    $cc   = strtoupper(trim($_POST['country_code'] ?? ''));
    $date = trim($_POST['hdate'] ?? '');
    $name = trim($_POST['name'] ?? '');

    if ($id && isset($COUNTRIES[$cc]) && $date !== '' && $name !== '') {
        $stmt = $conn->prepare("UPDATE public_holidays SET country_code=?, hdate=?, name=? WHERE id=?");
        $stmt->bind_param('sssi', $cc, $date, $name, $id);
        $stmt->execute();
        $stmt->close();
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
        exit;
    }
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => 'Invalid input']);
    exit;
}

// AJAX: delete
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $conn->query("DELETE FROM public_holidays WHERE id = {$id}");
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
        exit;
    }
    header('Content-Type: application/json');
    echo json_encode(['ok' => false]);
    exit;
}

// AJAX: add
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $cc   = strtoupper(trim($_POST['country_code'] ?? ''));
    $date = trim($_POST['hdate'] ?? '');
    $name = trim($_POST['name'] ?? '');
    if (isset($COUNTRIES[$cc]) && $date !== '' && $name !== '') {
        $stmt = $conn->prepare("INSERT INTO public_holidays (country_code, hdate, name, source) VALUES (?, ?, ?, 'MANUAL')");
        $stmt->bind_param('sss', $cc, $date, $name);
        $stmt->execute();
        $newId = $conn->insert_id;
        $stmt->close();
        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'id' => $newId]);
        exit;
    }
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => 'Invalid input']);
    exit;
}

// Load table rows for selected country from DB
$stmt = $conn->prepare("SELECT id, country_code, hdate, name, source FROM public_holidays WHERE country_code=? ORDER BY hdate");
$stmt->bind_param("s", $selected);
$stmt->execute();
$result   = $stmt->get_result();
$holidays = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Build options HTML once
function renderCountryOptions(array $COUNTRIES, string $selected): string
{
    $html = '';
    foreach ($COUNTRIES as $code => $name) {
        $sel = ($code === $selected) ? 'selected' : '';
        $html .= "<option value=\"{$code}\" {$sel}>{$name} ({$code})</option>";
    }
    return $html;
}


require __DIR__ . '/../components/header.php';
require __DIR__ . '/../components/navbar.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Holiday Master</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table td,
        .table th {
            vertical-align: middle;
        }

        .row-editing select,
        .row-editing input[type="date"],
        .row-editing input[type="text"] {
            min-width: 160px;
        }
    </style>
</head>

<body class="p-4">
    <div class="container">
        <h4 class="mb-4">Holiday Master</h4>

        <?php if (!empty($statusMsg)) echo $statusMsg; ?>

        <!-- Fetch from Google -->
        <form class="row g-3 mb-4" method="get" id="fetchForm">
            <div class="col-md-3">
                <label class="form-label">Country</label>
                <select name="country_code" class="form-select" required>
                    <?= renderCountryOptions($COUNTRIES, $selected) ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Start date</label>
                <input type="date" name="start_date" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">End date</label>
                <input type="date" name="end_date" class="form-control" required>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary me-2" name="fetch" value="1">Fetch from Google</button>
                <a class="btn btn-outline-secondary" href="?country_code=<?= urlencode($selected) ?>">Refresh</a>
            </div>
        </form>

        <div class="d-flex justify-content-start align-items-center mb-2">
            <h5 class="m-0">Saved Holidays (<?= htmlspecialchars($COUNTRIES[$selected]) ?> — <?= htmlspecialchars($selected) ?>)</h5>
            <button id="addRowBtn" class="btn btn-success btn-sm ms-3">
                Add Holiday
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle" id="holidaysTable">
                <thead class="table-light">
                    <tr>
                        <th style="width:70px;">No.</th>
                        <!-- <th style="width:70px;">ID</th> -->
                        <th style="width:260px;">Country</th>
                        <th style="width:160px;">Date</th>
                        <th style="width:140px;">Day</th>
                        <th>Name</th>
                        <th style="width:120px;">Source</th>
                        <th style="width:220px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($holidays):
                        $i = 1;
                        foreach ($holidays as $h): ?>
                            <tr data-id="<?= (int)$h['id'] ?>">
                                <td class="cell-no"><?= $i++ ?></td>
                                <!-- <td class="cell-id"><?= (int)$h['id'] ?></td> -->
                                <td class="cell-country" data-value="<?= htmlspecialchars($h['country_code']) ?>">
                                    <?= htmlspecialchars($COUNTRIES[$h['country_code']] ?? $h['country_code']) ?> (<?= htmlspecialchars($h['country_code']) ?>)
                                </td>
                                <td class="cell-date" data-value="<?= htmlspecialchars($h['hdate']) ?>"><?= htmlspecialchars($h['hdate']) ?></td>
                                <td class="cell-day">
                                    <?= date('l', strtotime($h['hdate'])) ?>
                                </td>

                                <td class="cell-name" data-value="<?= htmlspecialchars($h['name']) ?>"><?= htmlspecialchars($h['name']) ?></td>
                                <td class="cell-source"><?= htmlspecialchars($h['source']) ?></td>
                                <td class="cell-actions">
                                    <button class="btn btn-sm btn-outline-primary btn-edit">Edit</button>
                                    <button class="btn btn-sm btn-outline-danger btn-delete">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach;
                    else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No holidays found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Inline edit helpers
        function countryOptions(selected) {
            const opts = `<?= preg_replace('/\s+/', ' ', renderCountryOptions($COUNTRIES, '__SELECT__')) ?>`;
            return opts.replace('value="__SELECT__"', 'value="' + selected + '"');
        }

        function toEditRow(tr) {
            if (tr.classList.contains('row-editing')) return;
            tr.classList.add('row-editing');

            const id = tr.dataset.id || '';
            // Country
            const tdCountry = tr.querySelector('.cell-country');
            const countryVal = tdCountry.getAttribute('data-value') || '<?= htmlspecialchars($selected) ?>';
            tdCountry.innerHTML = `<select class="form-select form-select-sm">${countryOptions(countryVal)}</select>`;

            // Date
            const tdDate = tr.querySelector('.cell-date');
            const dateVal = tdDate.getAttribute('data-value') || '';
            tdDate.innerHTML = `<input type="date" class="form-control form-control-sm" value="${dateVal}">`;

            // Name
            const tdName = tr.querySelector('.cell-name');
            const nameVal = tdName.getAttribute('data-value') || '';
            tdName.innerHTML = `<input type="text" class="form-control form-control-sm" value="${nameVal}">`;

            // Actions
            const tdActions = tr.querySelector('.cell-actions');
            tdActions.innerHTML = `
    <button class="btn btn-sm btn-success btn-save">Save</button>
    <button class="btn btn-sm btn-secondary btn-cancel">Cancel</button>
  `;
        }

        function fromEditRow(tr, payload) {
            tr.classList.remove('row-editing');
            tr.querySelector('.cell-country').setAttribute('data-value', payload.country_code);
            tr.querySelector('.cell-country').textContent = payload.country_label + ' (' + payload.country_code + ')';
            tr.querySelector('.cell-date').setAttribute('data-value', payload.hdate);
            tr.querySelector('.cell-date').textContent = payload.hdate;
            tr.querySelector('.cell-name').setAttribute('data-value', payload.name);
            tr.querySelector('.cell-name').textContent = payload.name;
            tr.querySelector('.cell-actions').innerHTML = `
    <button class="btn btn-sm btn-outline-primary btn-edit">Edit</button>
    <button class="btn btn-sm btn-outline-danger btn-delete">Delete</button>
  `;
        }

        // Edit / Delete handlers
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('button');
            if (!btn) return;
            const tr = e.target.closest('tr');
            if (!tr) return;

            // Edit
            if (btn.classList.contains('btn-edit')) {
                toEditRow(tr);
            }

            // Cancel
            if (btn.classList.contains('btn-cancel')) {
                // reload the page section to restore original (simpler)
                location.reload();
            }

            if (btn.classList.contains('btn-save')) {
                const id = tr.dataset.id || '';
                const cc = tr.querySelector('.cell-country select').value;
                const hdate = tr.querySelector('.cell-date input').value;
                const name = tr.querySelector('.cell-name input').value;

                const form = new FormData();
                form.append('action', 'update');
                form.append('id', id);
                form.append('country_code', cc);
                form.append('hdate', hdate);
                form.append('name', name);

                const res = await fetch('', {
                    method: 'POST',
                    body: form
                });
                const data = await res.json();

                if (data.ok) {
                    // ✅ RELOAD SAME PAGE WITH SAME COUNTRY
                    const url = new URL(window.location.href);
                    url.searchParams.set('country_code', cc);
                    url.searchParams.delete('fetch');
                    window.location.href = url.toString();
                    setTimeout(() => window.location.reload(), 200);
                    console.log('data-oki');


                } else {
                    alert('Save failed: ' + (data.error || 'unknown error'));
                }
            }


            // Delete
            if (btn.classList.contains('btn-delete')) {
                if (!confirm('Delete this holiday?')) return;
                const id = tr.dataset.id || '';
                const form = new FormData();
                form.append('action', 'delete');
                form.append('id', id);
                const res = await fetch('', {
                    method: 'POST',
                    body: form
                });
                const data = await res.json();
                if (data.ok) {
                    tr.remove();
                } else {
                    alert('Delete failed');
                }
            }
            // window.location = window.location.href; // EXACT Chrome refresh
            // // location.reload(); // 🔥 reload the same page automatically

        });

        // Add new row
        const addBtn = document.getElementById('addRowBtn');

        if (addBtn) {
            addBtn.addEventListener('click', () => {
                const tbody = document.querySelector('#holidaysTable tbody');
                const tr = document.createElement('tr');
                tr.classList.add('row-editing');
                tr.innerHTML = `
                <td class="cell-id">—</td>
                <td class="cell-country"><select class="form-select form-select-sm"><?= renderCountryOptions($COUNTRIES, $selected) ?></select></td>
                <td class="cell-date"><input type="date" class="form-control form-control-sm"></td>
                <td class="cell-day"></td>
                <td class="cell-name"><input type="text" class="form-control form-control-sm" placeholder="Holiday name"></td>
                <td class="cell-source">MANUAL</td>
                <td class="cell-actions">
                <button class="btn btn-sm btn-success btn-add-save">Save</button>
                <button class="btn btn-sm btn-secondary btn-add-cancel">Cancel</button>
                </td>
            `;
                tbody.prepend(tr);
            });
        }
        // Add row save / cancel
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('button');
            if (!btn) return;
            const tr = e.target.closest('tr');
            if (!tr) return;

            if (btn.classList.contains('btn-add-cancel')) {
                tr.remove();
            }

            if (btn.classList.contains('btn-add-save')) {
                const cc = tr.querySelector('.cell-country select').value;
                const hdate = tr.querySelector('.cell-date input').value;
                const name = tr.querySelector('.cell-name input').value;

                const form = new FormData();
                form.append('action', 'add');
                form.append('country_code', cc);
                form.append('hdate', hdate);
                form.append('name', name);

                const res = await fetch('', {
                    method: 'POST',
                    body: form
                });
                const data = await res.json();
                if (data.ok) {
                    // turn row into normal display mode
                    tr.classList.remove('row-editing');
                    tr.dataset.id = data.id;
                    const label = tr.querySelector('.cell-country select').selectedOptions[0].textContent.replace(/\s+\(\w+\)$/, '');
                    tr.querySelector('.cell-id').textContent = data.id;
                    tr.querySelector('.cell-country').setAttribute('data-value', cc);
                    tr.querySelector('.cell-country').textContent = `${label} (${cc})`;
                    tr.querySelector('.cell-date').setAttribute('data-value', hdate);
                    tr.querySelector('.cell-date').textContent = hdate;
                    tr.querySelector('.cell-name').setAttribute('data-value', name);
                    tr.querySelector('.cell-name').textContent = name;
                    tr.querySelector('.cell-actions').innerHTML = `
        <button class="btn btn-sm btn-outline-primary btn-edit">Edit</button>
        <button class="btn btn-sm btn-outline-danger btn-delete">Delete</button>
      `;
                    setTimeout(() => window.location.reload(), 200);
                    console.log('data-oki');
                } else {
                    alert('Add failed: ' + (data.error || 'unknown error'));
                }
            }
        });

        // Auto reload holidays when country changes
        document.querySelector('select[name="country_code"]').addEventListener('change', function() {
            const selectedCountry = this.value;
            const url = new URL(window.location.href);
            url.searchParams.set('country_code', selectedCountry);
            url.searchParams.delete('fetch'); // remove fetch flag if exists
            window.location.href = url.toString();
        });
    </script>
</body>

</html>