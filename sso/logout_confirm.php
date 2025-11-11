<?php
session_start();
require __DIR__ . '/../db.php';

$base = rtrim($_ENV['BASE_URL'] ?? '/', '/');
$tenant = $_ENV['TENANT_ID'] ?? '';
$msLogout = "https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/logout?post_logout_redirect_uri=" . urlencode($base);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Logout Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4 shadow-sm text-center" style="max-width:400px;">
        <h5>Do you want to logout from:</h5>
        <hr>
        <form method="post">
            <a href="logout.php?type=app" class="btn btn-outline-primary w-100 mb-2">This App Only</a>
            <a href="<?= htmlspecialchars($msLogout) ?>" class="btn btn-outline-danger w-100">Microsoft Account + App</a>
        </form>
        <p class="text-muted small mt-3">You’ll be redirected back after logout.</p>
    </div>
</body>

</html>