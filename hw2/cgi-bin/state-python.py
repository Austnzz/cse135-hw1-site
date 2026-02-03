#!/usr/bin/env python3
import os
import json
import datetime
from http import cookies
from urllib.parse import parse_qs, unquote_plus

def esc(s: str) -> str:
    return (s.replace("&", "&amp;")
             .replace("<", "&lt;")
             .replace(">", "&gt;")
             .replace('"', "&quot;")
             .replace("'", "&#39;"))

def get_params():
    qs = os.environ.get("QUERY_STRING", "")
    return parse_qs(qs)

def load_state():
    jar = cookies.SimpleCookie()
    jar.load(os.environ.get("HTTP_COOKIE", ""))

    if "hw2_state" in jar:
        try:
            return json.loads(jar["hw2_state"].value)
        except Exception:
            return {"value": ""}
    return {"value": ""}

def make_set_cookie(state_obj):
    jar = cookies.SimpleCookie()
    jar["hw2_state"] = json.dumps(state_obj)
    jar["hw2_state"]["path"] = "/"
    jar["hw2_state"]["max-age"] = 86400

    return "Set-Cookie: " + jar["hw2_state"].OutputString()

def main():
    now = datetime.datetime.utcnow().isoformat()
    host = os.environ.get("HTTP_HOST", "unknown-host")
    ip = os.environ.get("REMOTE_ADDR", "unknown-ip")

    params = get_params()
    action = (params.get("action", [""])[0]).lower()
    value = params.get("value", [""])[0]

    state = load_state()

    set_cookie_header = None
    if action == "set":
        state["value"] = value
        set_cookie_header = make_set_cookie(state)
    elif action == "clear":
        state["value"] = ""
        set_cookie_header = make_set_cookie(state)

    # ---- HEADERS ----
    print("Content-Type: text/html; charset=utf-8")
    if set_cookie_header:
        print(set_cookie_header)
    print()

    stored = state.get("value", "")

    print(f"""<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>State (Python)</title>
  <style>
    body {{ font-family: Arial, sans-serif; margin: 24px; }}
    .box {{ background:#f7f7f7; padding: 12px; border-left: 4px solid #ccc; max-width: 800px; }}
    input, button {{ padding: 8px; margin-top: 6px; }}
    form {{ margin-top: 16px; }}
    code {{ background:#f4f4f4; padding: 2px 6px; }}
  </style>
</head>
<body>
  <h1>State Demo (Python)</h1>

  <div class="box">
    <ul>
      <li><b>Host:</b> {esc(host)}</li>
      <li><b>Time (UTC):</b> {esc(now)}</li>
      <li><b>Your IP:</b> {esc(ip)}</li>
      <li><b>Storage:</b> Cookie (<code>hw2_state</code>)</li>
    </ul>

    <p><b>Currently saved value:</b> <code>{esc(stored)}</code></p>
  </div>

  <h2>Set a value</h2>
  <form method="GET" action="/hw2/cgi-bin/state-python.py">
    <input type="hidden" name="action" value="set">
    <input type="text" name="value" placeholder="Type something to store" style="width: 360px;" required>
    <button type="submit">Save</button>
  </form>

  <h2>Clear saved value</h2>
  <form method="GET" action="/hw2/cgi-bin/state-python.py">
    <input type="hidden" name="action" value="clear">
    <button type="submit">Clear</button>
  </form>

  <p style="margin-top:24px;">
    Save a value, refresh, it should persist. Then clear it.
  </p>
</body>
</html>""")

if __name__ == "__main__":
    main()
