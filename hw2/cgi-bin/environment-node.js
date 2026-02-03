#!/usr/bin/env node

function esc(s) {
  return String(s)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#39;");
}

const keys = Object.keys(process.env).sort();

let html = `<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Node.js Environment Variables</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 24px; }
    ul { line-height: 1.5; }
    code { background:#f4f4f4; padding:2px 6px; }
  </style>
</head>
<body>
  <h1>Node.js Environment Variables</h1>
  <hr>
  <ul>
`;

for (const k of keys) {
  html += `    <li><code>${esc(k)}</code> = ${esc(process.env[k])}</li>\n`;
}

html += `  </ul>
</body>
</html>
`;

process.stdout.write("Content-Type: text/html; charset=utf-8\r\n\r\n");
process.stdout.write(html);
