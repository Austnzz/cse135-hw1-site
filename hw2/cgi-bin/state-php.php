<?php
header("Content-Type: text/html; charset=utf-8");

function h($s) { return htmlspecialchars($s ?? "", ENT_QUOTES, "UTF-8"); }

$host = $_SERVER["HTTP_HOST"] ?? "";
$ip = $_SERVER["REMOTE_ADDR"] ?? "";
$time = gmdate("c");

$action = $_GET["action"] ?? "";
$value = $_GET["value"] ?? "";

$cookieName = "hw2_state_php";

if ($action === "set") {
  $stored = substr($value, 0, 200);
  setcookie($cookieName, $stored, [
    "expires" => time() + 86400,
    "path" => "/",
    "secure" => true,
    "httponly" => false,
    "samesite" => "Lax"
  ]);
} elseif ($action === "clear") {
  setcookie($cookieName, "", [
    "expires" => time() - 3600,
    "path" => "/",
    "secure" => true,
    "httponly" => false,
    "samesite" => "Lax"
  ]);
}

$current = $_COOKIE[$cookieName] ?? "(none)";

echo "<!doctype html>
<html lang=\"en\">
<head>
  <meta charset=\"utf-8\" />
  <title>State (PHP)</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 24px; }
    .box { background:#f7f7f7; padding: 12px; border-left: 4px solid #ccc; max-width: 800px; }
    input, button { padding: 8px; margin-top: 6px; }
    form { margin-top: 16px; }
    code { background:#f4f4f4; padding: 2px 6px; }
    a { margin-right: 12px; }
  </style>
</head>
<body>
  <h1>State Demo (PHP)</h1>

  <div class=\"box\">
    <ul>
      <li><b>Host:</b> " . h($host) . "</li>
      <li><b>Time (UTC):</b> " . h($time) . "</li>
      <li><b>Your IP:</b> " . h($ip) . "</li>
      <li><b>Stored Value:</b> <code>" . h($current) . "</code></li>
    </ul>
  </div>

  <form method=\"GET\" action=\"state-php.php\">
    <input type=\"hidden\" name=\"action\" value=\"set\" />
    <label>Value to store:</label><br/>
    <input name=\"value\" placeholder=\"type something\" />
    <button type=\"submit\">Save</button>
  </form>

  <p>
    <a href=\"state-php.php\">Refresh</a>
    <a href=\"state-php.php?action=clear\">Clear</a>
  </p>

  <hr/>
  <p><small>Cookie name: <code>$cookieName</code></small></p>
</body>
</html>";
?>
