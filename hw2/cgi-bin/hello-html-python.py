#!/usr/bin/env python3
import os, datetime

print("Content-Type: text/html; charset=utf-8")
print()

now = datetime.datetime.utcnow().isoformat()
ip = os.environ.get("REMOTE_ADDR", "unknown")

print(f"""<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Python Hello HTML</title>
</head>
<body>
  <h1>Hello World from Python!</h1>
  <p><b>Language:</b> Python</p>
  <p><b>Today's date is</b> {now}</p>
  <p><b>Your IP:</b> {ip}</p>
</body>
</html>
""")
