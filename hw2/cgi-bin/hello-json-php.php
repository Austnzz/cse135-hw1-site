<?php
header("Content-Type: application/json; charset=utf-8");

$now = gmdate("c");

$ip = $_SERVER["HTTP_X_FORWARDED_FOR"]
   ?? $_SERVER["HTTP_X_REAL_IP"]
   ?? $_SERVER["REMOTE_ADDR"]
   ?? "unknown";
$ip = trim(explode(",", $ip)[0]);

$data = [
  "title" => "Hello, PHP!",
  "heading" => "Hello, PHP!",
  "message" => "Hello World from Austin (PHP)!",
  "time" => $now,
  "ipAddress" => $ip
];

echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), "\n";
?>
