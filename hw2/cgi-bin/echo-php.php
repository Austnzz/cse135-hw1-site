<?php
header("Content-Type: text/html; charset=utf-8");

function h($s) { return htmlspecialchars($s ?? "", ENT_QUOTES, "UTF-8"); }

$method = $_SERVER["REQUEST_METHOD"] ?? "";
$host = $_SERVER["HTTP_HOST"] ?? "";
$ip = $_SERVER["REMOTE_ADDR"] ?? "";
$ua = $_SERVER["HTTP_USER_AGENT"] ?? "";
$contentType = $_SERVER["CONTENT_TYPE"] ?? "";
$time = gmdate("c"); 

$queryString = $_SERVER["QUERY_STRING"] ?? "";
$rawBody = file_get_contents("php://input");


$parsed = null;

if ($method === "GET") {
  $parsed = $_GET;
} else {

  if (stripos($contentType, "application/json") !== false) {
    $decoded = json_decode($rawBody, true);
    $parsed = $decoded === null ? ["_json_error" => "Invalid JSON or empty body"] : $decoded;
  } elseif (stripos($contentType, "application/x-www-form-urlencoded") !== false) {

    if ($method === "POST") {
      $parsed = $_POST;
    } else {
      $tmp = [];
      parse_str($rawBody, $tmp);
      $parsed = $tmp;
    }
  } else {

    $parsed = [
      "_note" => "No recognized Content-Type; showing raw body only",
      "_rawBody" => $rawBody
    ];
  }
}

$parsedPretty = json_encode($parsed, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

echo "<!doctype html>
<html lang=\"en\">
<head>
  <meta charset=\"utf-8\" />
  <title>Echo (PHP)</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 24px; }
    code, pre { background:#f4f4f4; padding: 8px; display:block; overflow:auto; }
  </style>
</head>
<body>
  <h1>Echo (PHP)</h1>

  <h2>Request Info</h2>
  <ul>
    <li><b>Host:</b> " . h($host) . "</li>
    <li><b>Time (UTC):</b> " . h($time) . "</li>
    <li><b>Client IP:</b> " . h($ip) . "</li>
    <li><b>User-Agent:</b> " . h($ua) . "</li>
    <li><b>Method:</b> " . h($method) . "</li>
    <li><b>Content-Type:</b> " . h($contentType) . "</li>
  </ul>

  <h2>Raw Data</h2>
  <h3>Query String</h3>
  <pre>" . h($queryString) . "</pre>

  <h3>Body</h3>
  <pre>" . h($rawBody) . "</pre>

  <h2>Parsed Data</h2>
  <pre>" . h($parsedPretty) . "</pre>

</body>
</html>";
?>
