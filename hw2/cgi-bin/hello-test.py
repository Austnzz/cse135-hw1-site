#!/usr/bin/env python3

import datetime
import os

print("Content-Type: text/html; charset=utf-8")
print()  # blank line required between headers and body

ip = os.environ.get("REMOTE_ADDR", "unknown")
now = datetime.datetime.now().isoformat()

print(f"""<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Python CGI Test</title>
</head>
<body>
  <h1>Python CGI Works</h1>
  <p>Time: {now}</p>
  <p>Your IP: {ip}</p>
</body>
</html>
""")

