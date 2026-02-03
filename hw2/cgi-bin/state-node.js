#!/usr/bin/env node
'use strict';

function parseCookies(cookieHeader) {
  const out = {};
  if (!cookieHeader) return out;
  cookieHeader.split(';').forEach(part => {
    const [k, ...rest] = part.trim().split('=');
    if (!k) return;
    out[k] = rest.join('=');
  });
  return out;
}

function htmlEscape(s) {
  return String(s)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

const qs = require('node:querystring');
const now = new Date().toISOString();

const method = process.env.REQUEST_METHOD || 'GET';
const host = process.env.HTTP_HOST || 'unknown';
const ip = process.env.REMOTE_ADDR || 'unknown';

const query = qs.parse(process.env.QUERY_STRING || '');
const action = query.action || 'view';

const cookies = parseCookies(process.env.HTTP_COOKIE || '');
const cookieName = 'hw2_state_node';
const savedValue = cookies[cookieName] ? decodeURIComponent(cookies[cookieName]) : '';

let setCookieHeader = '';

if (action === 'set') {
  const value = (query.value ?? '').toString();

  setCookieHeader =
    `${cookieName}=${encodeURIComponent(value)}; Max-Age=86400; Path=/; Secure; SameSite=Lax`;
} else if (action === 'clear') {
  setCookieHeader =
    `${cookieName}=; Max-Age=0; Path=/; Secure; SameSite=Lax`;
}


process.stdout.write('Content-Type: text/html; charset=utf-8\r\n');
if (setCookieHeader) {
  process.stdout.write(`Set-Cookie: ${setCookieHeader}\r\n`);
}
process.stdout.write('\r\n');


process.stdout.write(`<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>State (Node.js)</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 24px; }
    .box { background:#f7f7f7; padding: 12px; border-left: 4px solid #ccc; max-width: 900px; }
    input, button { padding: 8px; margin-top: 6px; }
    form { margin-top: 16px; }
    code { background:#f4f4f4; padding: 2px 6px; }
    a { margin-right: 12px; }
  </style>
</head>
<body>
  <h1>State Demo (Node.js)</h1>

  <div class="box">
    <ul>
      <li><b>Host:</b> ${htmlEscape(host)}</li>
      <li><b>Time (UTC):</b> ${htmlEscape(now)}</li>
      <li><b>Your IP:</b> ${htmlEscape(ip)}</li>
      <li><b>Method:</b> ${htmlEscape(method)}</li>
      <li><b>Cookie name:</b> <code>${cookieName}</code></li>
    </ul>

    <p><b>Saved value:</b> <code>${htmlEscape(savedValue || '(none)')}</code></p>

    <p>
      <a href="/hw2/cgi-bin/state-node.js">Refresh</a>
      <a href="/hw2/cgi-bin/state-node.js?action=clear">Clear</a>
    </p>

    <form action="/hw2/cgi-bin/state-node.js" method="GET">
      <input type="hidden" name="action" value="set" />
      <label>
        Value to save:
        <input type="text" name="value" placeholder="type something..." />
      </label>
      <button type="submit">Save</button>
    </form>
  </div>

  <p style="margin-top:18px;">
    This demo saves state using a <b>server-set cookie</b>. The browser stores it and sends it back on future requests.
  </p>

</body>
</html>
`);
