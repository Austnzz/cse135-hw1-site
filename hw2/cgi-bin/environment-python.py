#!/usr/bin/env python3
import os
import html

print("Content-Type: text/html; charset=utf-8")
print()

print("""<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Python Environment Variables</title>
</head>
<body>
  <h1>Python Environment Variables</h1>
  <hr>
  <ul>
""")

for k in sorted(os.environ.keys()):
    v = os.environ.get(k, "")

    if k == "SERVER_SIGNATURE":
        print(f"    <li>{html.escape(k)} = </li>")
        if v.strip():
            print(f"    {v}")
        else:
            print("    ") 
    else:
        print(f"    <li>{html.escape(k)} = {html.escape(v)}</li>")

print("""  </ul>
</body>
</html>
""")
