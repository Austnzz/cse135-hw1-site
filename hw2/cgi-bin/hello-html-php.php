<?php
header("Content-Type: text/html; charset=utf-8");

$now = gmdate("c");

$ip = $_SERVER["HTTP_X_FORWARDED_FOR"]
   ?? $_SERVER["HTTP_X_REAL_IP"]
   ?? $_SERVER["REMOTE_ADDR"]
   ?? "unknown";
$ip = trim(explode(",", $ip)[0]);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Hello HTML (PHP)</title>
</head>
<body>
  <h1>Hello World from Austin (PHP)!</h1>
  <p><b>Language:</b> PHP</p>
  <p><b>Time (UTC):</b> <?= htmlspecialchars($now) ?></p>
  <p><b>Your IP:</b> <?= htmlspecialchars($ip) ?></p>
</body>
</html>
