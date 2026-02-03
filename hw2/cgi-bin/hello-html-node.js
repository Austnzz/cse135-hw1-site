#!/usr/bin/env node

function h(s) {
  return String(s ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#39;");
}

const host = process.env.HTTP_HOST || "";
const ip = process.env.REMOTE_ADDR || "unknown";
const time = new Date().toISOString();

process.stdout.write("Content-Type: text/html; charset=utf-8\r\n\r\n");
process.stdout.write(`<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Hello HTML (Node)</title>
</head>
<body>
  <h1>Hello World from Austin (Node.js)!</h1>
  <p><b>Language:</b> Node.js</p>
  <p><b>Time (UTC):</b> ${h(time)}</p>
  <p><b>Your IP:</b> ${h(ip)}</p>
  <p><b>Host:</b> ${h(host)}</p>
</body>
</html>
`);
