#!/usr/bin/env python3
import os, json, datetime

now = datetime.datetime.utcnow().isoformat()
ip = os.environ.get("REMOTE_ADDR", "unknown")

data = {
    "title": "Hello, Python!",
    "heading": "Hello, Python!",
    "message": "Hello World from Python!",
    "time": now,
    "ipAddress": ip
}

print("Content-Type: application/json; charset=utf-8")
print()
print(json.dumps(data, indent=2))
