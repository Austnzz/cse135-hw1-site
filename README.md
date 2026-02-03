# CSE 135

# HW1: Client Side Basics, Site and Server Configuration

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


# HW2 Main Page (Required Links)
- **Site:** https://austinchoi-135.site
- Under Homework 2 on the homepage, there are links to:
  - All 15 CGI demo programs (3 languages × 5 endpoints)
  - The instructor-provided Perl CGI demo proof page

## HW2 File Locations

All Homework 2 work lives under the hw2/ folder.

- **My implemented CGI demos:**
  - Location: `hw2/cgi-bin/`
  - These contain the required endpoints (`hello-html-*`, `hello-json-*`, `environment-*`, `echo-*`, `state-*`) for the 3 languages I chose.

- **Instructor-provided Perl CGI demo code:**
  - Location: `hw2/perl/cgi-bin/`
  - These are the sample Perl programs downloaded from the course site and deployed to my server for Part 1.

- **Echo form page (required UI):**
  - Location: `hw2/echo.html`


## CGI Demos Implemented (3 languages)
I implemented the required CGI endpoints in Python, PHP, and Node.js:

Each language includes:
- `hello-html`
- `hello-json`
- `environment`
- `echo`
- `state`

## Instructor Perl CGI Demo
I also deployed the instructor-provided Perl CGI sample code and verified it runs on my domain.
- Link: `https://austinchoi-135.site/hw2/cgi-bin/perl-hello-html-world.pl`

## Third-Party Analytics

### Approach 1 — Google Analytics
- Google Analytics is installed on the homepage in index.html

### Approach 2 — LogRocket
- LogRocket is installed and verified, also in index.html

### Approach 3 — Free Choice: Microsoft Clarity
For the free choice analytics requirement, I decided to try Microsoft Clarity because it sits in an interesting middle ground between traditional aggregated analytics like Google Analytics and full session-replay tools like LogRocket. I wanted something that could give me more of a behavior-like insight with things like heatmaps, scroll depth, click patterns, and recordings without requiring a heavy set of compelxity or a paid plan just to see meaningful results.

When I started evaluating options, I first looked at privacy-focused, lightweight tools so things in the more simple pageview dashboard category. Those were appealing because they’re easy to set up and avoid tracking baggage, but they felt too similar to Google Analytics so they would have mostly repeated the same kind of repeated metrics I already had. I also considered replay style tools that are closer to LogRocket, but many of them either require payment to unlock key features or overlap too much with what LogRocket already demonstrates.

Clarity stood out because:
- The install is straightforward (single script tag)
- It provides heatmaps + session recordings that feel different from GA’s aggregated reporting

Implementation wise, I added the Clarity tracking script to my homepage and verified in DevTools that requests to Clarity were being sent during navigation and interaction. In other words, from the site’s point of view the integration is active and transmitting data.

One limitation I ran into is that Clarity’s web dashboard did not reliably move from the “Getting Started” state to showing recordings/heatmaps during my testing window, even after generating traffic and confirming network calls. This made it harder to immediately validate the reporting UI compared to GA and LogRocket. Despite that, I still chose Clarity because it represents a genuinely different analytics category than the other two approaches, and the client-side instrumentation was lightweight and easy to integrate.
