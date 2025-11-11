<?php

declare(strict_types=1);
session_start();
require __DIR__ . '/../db.php';

$tenantId = $_ENV['TENANT_ID'] ?? '';
$base     = rtrim($_ENV['BASE_URL'] ?? '/', '/') . '/';

$type = $_GET['type'] ?? 'app';

if ($type === 'app') {
    $_SESSION = [];
    session_destroy();
    header('Location: ' . $base . 'login.php');
    exit;
}

// default full Microsoft logout
$logoutUrl = "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/logout"
    . "?post_logout_redirect_uri=" . urlencode($base);
header("Location: {$logoutUrl}");
exit;
