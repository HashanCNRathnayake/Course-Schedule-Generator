<?php
// index.php
// session_start();
date_default_timezone_set('Asia/Singapore');

require_once __DIR__ . '/vendor/autoload.php';
if (class_exists(\Dotenv\Dotenv::class)) {
  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
  $dotenv->safeLoad();
}

require __DIR__ . '/auth/guard.php';
$me = $_SESSION['auth'] ?? null;
$userName = $me['name'] ?? 'Guest';

$baseUrl = $_ENV['BASE_URL'] ?? '/';


require __DIR__ . '/db.php';
require __DIR__ . '/components/header.php';
require __DIR__ . '/components/navbar.php';



$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);




?>

<link rel="icon" href="images/favicon.ico" type="image/ico">

</head>

<body>

</body>

</html>