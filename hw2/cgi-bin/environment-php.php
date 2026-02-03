<?php
header("Content-Type: text/html; charset=utf-8");

function h($s) { return htmlspecialchars($s ?? "", ENT_QUOTES, "UTF-8"); }

$env = $_SERVER;
ksort($env);

echo "<!doctype html>
<html lang=\"en\">
<head>
  <meta charset=\"utf-8\" />
  <title>PHP Environment Variables</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 24px; }
    ul { line-height: 1.5; }
    code { background:#f4f4f4; padding:2px 6px; }
  </style>
</head>
<body>
  <h1>PHP Environment Variables</h1>
  <hr>
  <ul>
";

foreach ($env as $k => $v) {
  if (is_array($v)) $v = json_encode($v);
  echo "    <li><b>" . h($k) . "</b> = <code>" . h((string)$v) . "</code></li>\n";
}

echo "  </ul>
</body>
</html>";
?>
