#!/usr/bin/env python3
import os, sys, json, datetime
from urllib.parse import parse_qs

def get_client_ip():
    xff = os.environ.get("HTTP_X_FORWARDED_FOR", "")
    if xff:
        return xff.split(",")[0].strip()
    return os.environ.get("REMOTE_ADDR", "unknown")

def read_body():
    try:
        length = int(os.environ.get("CONTENT_LENGTH", "0"))
    except ValueError:
        length = 0
    if length > 0:
        return sys.stdin.read(length)
    return ""

method = os.environ.get("REQUEST_METHOD", "GET")
content_type = os.environ.get("CONTENT_TYPE", "")
query_string = os.environ.get("QUERY_STRING", "")
raw_body = read_body()

parsed = {}
parse_error = None

try:
    if method == "GET":
        parsed = parse_qs(query_string, keep_blank_values=True)
    else:
        if "application/json" in content_type:
            parsed = json.loads(raw_body) if raw_body.strip() else {}
        else:
            parsed = parse_qs(raw_body, keep_blank_values=True)
except Exception as e:
    parse_error = str(e)

now = datetime.datetime.utcnow().isoformat()
host = os.environ.get("HTTP_HOST", "unknown-host")
ua = os.environ.get("HTTP_USER_AGENT", "unknown")
ip = get_client_ip()

print("Content-Type: text/html; charset=utf-8")
print()

def esc(s):
    return (s or "").replace("&","&amp;").replace("<","&lt;").replace(">","&gt;")

print(f"""<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Python Echo</title>
  <style>
    body {{ font-family: Arial, sans-serif; margin: 24px; }}
    code, pre {{ background:#f4f4f4; padding: 8px; display:block; overflow:auto; }}
  </style>
</head>
<body>
  <h1>Python Echo</h1>

  <h2>Request Info</h2>
  <ul>
    <li><b>Host:</b> {esc(host)}</li>
    <li><b>Time (UTC):</b> {esc(now)}</li>
    <li><b>Client IP:</b> {esc(ip)}</li>
    <li><b>User-Agent:</b> {esc(ua)}</li>
    <li><b>Method:</b> {esc(method)}</li>
    <li><b>Content-Type:</b> {esc(content_type)}</li>
  </ul>

  <h2>Raw Data</h2>
  <h3>Query String</h3>
  <pre>{esc(query_string)}</pre>

  <h3>Body</h3>
  <pre>{esc(raw_body)}</pre>

  <h2>Parsed Data</h2>
""")

if parse_error:
    print(f"<p><b>Parse Error:</b> {esc(parse_error)}</p>")

print("<pre>")
print(esc(json.dumps(parsed, indent=2, sort_keys=True, default=str)))
print("</pre>")

print("""
</body>
</html>
""")
