#!/usr/bin/env node

const host = process.env.HTTP_HOST || "";
const ip = process.env.REMOTE_ADDR || "unknown";
const time = new Date().toISOString();

const payload = {
  title: "Hello, Node!",
  heading: "Hello, Node!",
  message: "Hello World from Austin (Node.js)!",
  time,
  ipAddress: ip,
  host
};

process.stdout.write("Content-Type: application/json; charset=utf-8\r\n\r\n");
process.stdout.write(JSON.stringify(payload, null, 2));
process.stdout.write("\n");
