# CSE 135 — HW1: Client Side Basics, Site and Server Configuration

## Team
- **Member 1:** Austin Choi

## Droplet / Server Info
- **Provider:** DigitalOcean
- **Droplet Name:** cse135-hw1
- **Public IP:** 143.110.137.113
- **Domain:** https://austinchoi-135.site
- **Collector:** https://collector.austinchoi-135.site
- **Reporting:** https://reporting.austinchoi-135.site

---

## Grader Access (SSH)
ssh grader@143.110.137.113
password: grader_password

### Homepage + Member Pages
- **Homepage:** https://austinchoi-135.site
- **My member page:** https://austinchoi-135.site/members/austinchoi.html

### Required Site Files
- **favicon:** https://austinchoi-135.site/favicon.ico  
- **robots.txt:** https://austinchoi-135.site/robots.txt

### Other Required Files
- **PHP page:** https://austinchoi-135.site/hw1/hello.php  
- **GoAccess report:** https://austinchoi-135.site/hw1/report.htm

## Password-Protected Area (Basic Auth)
I enabled Apache Basic Authentication so the grader can log into the protected area.

## Udser name and Password to Access Site
**Username:** grader
**password:** grader_password

## GitHub Auto-Deploy Setup
**Goal:** When I push to `main`, the website updates automatically on the DigitalOcean droplet.

### Repo
- **GitHub Repo:** https://github.com/Austnzz/cse135-hw1-site

### How deployment works (what I set up)
1. On the droplet, I created a bare Git repo: `/var/repo/site.git`
2. I added a post receive hook that runs after every push to `main`
3. The hook checks out the latest `main` branch into the Apache DocumentRoot: `/var/www/austinchoi-135.site`

### post-receive hook logic
- If the pushed ref is `refs/heads/main`:
  - `git checkout -f main` into `/var/www/austinchoi-135.site`
  - `chown -R deploy:www-data` on the deployed files
  - set directory perms to 755 and file perms to 644

## Compression
I enabled Apache compression so the server can send smaller versions of text files (HTML/CSS/JS) to the browser.  
This reduces download size and usually makes the page load faster.
Modules:
- `deflate`
- `filter`
- `headers`

### What changed after compression
Before compression, the browser downloaded the full raw text of the page files.  
After compression, the server sends a compressed version instead, so:

- Transferred size goes down
- Content-Encoding: gzip appears in response headers
- The browser automatically decompresses it, so the page still looks the same


## Obscuring Server Identity

### Goal
Change the HTTP response header from something like:
- `Server: CSE135 Server`

### What I did
Apache normally controls its own Server header, and it is intentionally hard to fully overwrite it.
To reliably set a custom Server header, I configured Nginx as the public-facing server and used it as a reverse proxy in front of Apache.



