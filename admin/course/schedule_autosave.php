<?php
session_start();

header('Content-Type: application/json');

$module = $_POST['module'] ?? null;
$index  = $_POST['index'] ?? null;
$field  = $_POST['field'] ?? null;
$value  = $_POST['value'] ?? null;

if (!$module || !$index || !$field) {
    echo json_encode(["ok" => false, "error" => "Missing parameters"]);
    exit;
}

if (!isset($_SESSION['grid_rows'][$module][$index - 1])) {
    echo json_encode(["ok" => false, "error" => "Invalid module/index"]);
    exit;
}

// Save into session
$_SESSION['grid_rows'][$module][$index - 1][$field] = $value;

echo json_encode(["ok" => true]);
exit;
