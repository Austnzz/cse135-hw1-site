#!/usr/bin/env node

const fs = require("fs");
const querystring = require("querystring");

function esc(s) {
  return String(s ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#39;");
}

const method = process.env.REQUEST_METHOD || "GET";
const host = process.env.HTTP_HOST || process.env.SERVER_NAME || "unknown-host";
const ip = process.env.HTTP_X_FORWARDED_FOR
  ? process.env.HTTP_X_FORWARDED_FOR.split(",")[0].trim()
  : (process.env.REMOTE_ADDR || "unknown-ip");
const ua = process.env.HTTP_USER_AGENT || "unknown";
const contentType = (process.env.CONTENT_TYPE || "").split(";")[0].trim();
const qsRaw = process.env.QUERY_STRING || "";
const bodyRaw = fs.readFileSync(0, "utf8");

let parsed = {};
let parseNote = "";

try {
  if (method === "GET") {
    parsed = querystring.parse(qsRaw);
    parseNote = "Parsed from query string (GET).";
  } else {
    if (contentType === "application/json") {
      parsed = bodyRaw ? JSON.parse(bodyRaw) : {};
      parseNote = "Parsed JSON body.";
    } else if (contentType === "application/x-www-form-urlencoded") {
      parsed = querystring.parse(bodyRaw);
      parseNote = "Parsed x-www-form-urlencoded body.";
    } else {
      
      parsed = bodyRaw ? querystring.parse(bodyRaw) : {};
      parseNote = "Unknown content-type; treated body as form-encoded.";
    }
  }
} catch (e) {
  parsed = { error: "Failed to parse body", message: String(e) };
  parseNote = "Parse error.";
}

const now = new Date().toISOString();

const html = `<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Echo (Node.js)</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 24px; }
    code, pre { background:#f4f4f4; padding: 8px; display:block; overflow:auto; }
  </style>
</head>
<body>
  <h1>Echo (Node.js)</h1>

  <h2>Request Info</h2>
  <ul>
    <li><b>Host:</b> ${esc(host)}</li>
    <li><b>Time (UTC):</b> ${esc(now)}</li>
    <li><b>Client IP:</b> ${esc(ip)}</li>
    <li><b>User-Agent:</b> ${esc(ua)}</li>
    <li><b>Method:</b> ${esc(method)}</li>
    <li><b>Content-Type:</b> ${esc(contentType)}</li>
  </ul>

  <h2>Raw Data</h2>
  <h3>Query String</h3>
  <pre>${esc(qsRaw)}</pre>

  <h3>Body</h3>
  <pre>${esc(bodyRaw)}</pre>

  <h2>Parsed Data</h2>
  <p>${esc(parseNote)}</p>
  <pre>${esc(JSON.stringify(parsed, null, 2))}</pre>
</body>
</html>
`;

process.stdout.write("Content-Type: text/html; charset=utf-8\r\n\r\n");
process.stdout.write(html);
