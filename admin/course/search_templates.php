<?php
require __DIR__ . '/../../db.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$q = $_GET['q'] ?? '';
$q = trim($q);

// remove square brackets (from UI display like [SE])
$q = preg_replace('/[\[\]]/', '', $q);

// split into words
$words = preg_split('/\s+/', $q);

$conditions = [];

foreach ($words as $w) {
    if ($w === '') continue;

    $w = "%" . $conn->real_escape_string($w) . "%";

    $conditions[] = "(
        c.course_title_external LIKE '$w'
        OR c.course_code LIKE '$w'
        OR c.course_id LIKE '$w'
        OR t.tag LIKE '$w'
        OR t.learning_mode LIKE '$w'
    )";
}

$whereSql = $conditions
    ? 'AND ' . implode(' AND ', $conditions)
    : '';

$sql = "
    SELECT
        t.id            AS template_id,
        t.course_id,
        t.tag,
        t.learning_mode,
        c.course_code,
        c.course_title_external
    FROM templates t
    INNER JOIN courses c
        ON c.course_id = t.course_id
    WHERE
        t.status = 'active'
        AND t.deleted_at IS NULL
        $whereSql
    ORDER BY
        c.course_title_external,
        t.tag,
        t.learning_mode
    LIMIT 10
";

$res = $conn->query($sql);

$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
