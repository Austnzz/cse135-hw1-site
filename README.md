# CSE 135 Analytics Reporting Platform

## Project Overview

This project is a lightweight analytics reporting platform built for CSE 135. It extends the earlier project milestones into a more complete reporting system with authentication, authorization, saved reports, data visualization, export support, and administrative access control.

The system collects analytics data from a test site, stores it in PostgreSQL, exposes reporting data through server-side logic, and presents curated reports through a role-based dashboard.

The project is designed to stay lightweight and practical. It uses server-rendered PHP, small amounts of JavaScript, and a limited number of dependencies so that the application remains responsive and easy to grade.

## Important Access Note

The root of the reporting domain, `https://reporting.austinchoi-135.site`, does not serve the main HW5 dashboard directly.

For grading and normal use of the final project, please start from one of these application routes instead:

- `https://reporting.austinchoi-135.site/login`
- `https://reporting.austinchoi-135.site/reports`
- `https://reporting.austinchoi-135.site/saved-reports`

The reporting platform is routed through the application entry points above. Visiting only the bare domain root may show an older placeholder page and does not reflect the actual final HW5 reporting experience.

## Important Repository Note

The main HW5 and final-project implementation is located in the `reporting.austinchoi-135.site/` directory in this repository.

This repository originally started from earlier assignment work, so the repo root still contains older site files and earlier project structure. The final reporting platform work for HW5 is concentrated in the `reporting.austinchoi-135.site/` folder.

For grading, the deployed reporting platform and the source code in `reporting.austinchoi-135.site/` are the most relevant parts of the final project.

## Deployed Sites

Main site:
https://austinchoi-135.site

Test site:
https://test.austinchoi-135.site

Collector:
https://collector.austinchoi-135.site

Reporting platform:
https://reporting.austinchoi-135.site
https://reporting.austinchoi-135.site/login

## Repository

Repo link:
https://github.com/Austnzz/cse135-hw1-site

Note: most of the live work for this project was done directly on the server, so the deployed version is the main source of truth for the final state of the project.

## High-Level Architecture

The project is hosted on a DigitalOcean Ubuntu droplet.

Public-facing stack:
- nginx on ports 80 and 443
- Apache behind nginx on 127.0.0.1:8080

This setup was used so the public-facing server header could be controlled and presented as CSE135 Server.

Major parts of the project:
- main website and earlier homework content
- test site that loads analytics collection code
- collector service for logging analytics events
- PostgreSQL database for analytics and reporting data
- reporting platform for dashboards, saved reports, exports, and admin views

## Technology Stack

Server and hosting:
- Ubuntu 24.04
- DigitalOcean
- nginx
- Apache

Backend:
- PHP
- PostgreSQL
- PDO for database access

Frontend:
- HTML
- CSS
- small amounts of JavaScript
- Chart.js for charts

Export:
- Dompdf for PDF generation

## Main Features

### Authentication and Authorization

The reporting platform includes a working application login system backed by a users table in PostgreSQL.

There are three role levels:
- super admin
- analyst
- viewer

Role behavior:
- super admin can access all reporting pages and user management
- analyst can access reporting pages
- viewer can only access saved reports that are published

There is also host-level Basic Auth protecting the reporting host separately from the app login.

### Reporting Dashboard

The reports overview page gives a summary-first dashboard view with:
- KPI cards
- grouped event chart
- recent event table
- links into saved reports

### Saved Reports

The saved reports page acts as the viewer-facing reporting layer of the platform. Reports are presented as curated report cards rather than raw data output.

Each saved report includes:
- report summary
- metadata
- KPI cards
- visualization
- analyst commentary
- supporting table
- export action

Current report categories:
- behavior
- errors
- performance

### Data Visualizations

The platform includes multiple chart-based and report-style visualizations.

Examples:
- grouped event counts by event type
- error counts by page
- performance events by page
- behavior over time
- KPI summary cards
- recent session summaries for behavior reports

The recent session summary section was added as a more unique behavioral analytics feature. It is not full replay, but it does give a compact session-level summary without the weight and complexity of true session playback.

### Export System

Saved reports can be exported as PDFs.

Export flow:
- user opens a saved report
- user clicks export
- server generates a PDF using Dompdf
- file is saved under storage/exports
- file becomes available through a download route

### Administrative View

The platform includes a super-admin-only user management page that displays:
- username
- display name
- role
- allowed sections
- created timestamp
- last login

### Error and Contingency Handling

The platform includes styled fallback pages and route handling for:
- 403 forbidden
- 404 not found
- missing report slugs
- missing export files
- export failure fallback
- no-script notice
- empty-state handling on major pages

## Data Collection and Storage

Analytics data is collected from the test site through collector.js and sent to the collector endpoint.

The collector stores events in PostgreSQL. Reporting pages then query and shape this data on the server side before rendering it into the dashboard or saved report pages.

Important data categories include:
- behavior-related events
- error events
- performance events

## Performance Considerations

One of the design goals of this project was to stay reasonably performant and avoid a heavyweight frontend.

Performance decisions made in this project:
- server-rendered PHP instead of a large SPA framework
- one shared CSS file
- Chart.js only on pages that actually need charts
- grouped and filtered SQL queries instead of sending raw logs to the browser
- limited recent-event tables instead of huge unbounded tables
- server-side PDF generation only when requested
- no large JavaScript bundles before getting to the data

## AI Usage

AI was used during development as a support tool for:
- generating draft code more quickly
- helping rewrite repetitive code structures
- helping think through routing, fallback states, and report presentation

My main observation is that AI was helpful when I already knew the direction I wanted and needed help moving faster. It was especially useful for speeding up boilerplate code, improving phrasing, reorganizing pages, and exploring multiple design options quickly.

At the same time, a few recurring problems were:
- lack of full project context
- suggestions that did not match the exact live codebase
- occasional hallucinations
- recommending changes that sounded good in theory but did not fit the real server setup
- giving code that still needed careful review and adjustment

Because of that, AI was most useful as an assistant. It did save me a lot of time, but it still required manual checking, manual testing, and a good understanding of the project structure. In practice, the best results came from using AI for faster iteration while still verifying changes directly on the live project.

## What I Learned

This project helped reinforce several ideas:
- server-side shaping is often cleaner than pushing raw data to the browser
- small and intentional dashboards are better than overly crowded ones
- access control should be visible in both backend logic and the UI
- error handling and fallback states matter more once an app starts to feel real
- export and reporting features are more useful when they are integrated into a clear workflow
- AI can help with speed, but context and validation still matter a lot

## Roadmap and Future Improvements

If more time were available, I would like to improve the project further in the following ways:

### Better report authoring
Right now the saved reports are seeded and read-focused. A next step would be creating, editing, and publishing reports through the UI.

### Richer behavioral analytics
The recent session summaries were a good step, but I think future works could include:
- more path-flow summaries
- transition analysis between pages
- better funnel-style views
- more meaningful segmentation of behavior events

### More refined performance analytics
The current performance reporting is useful, but it could be extended with:
- clearer timing breakdowns
- more page-level comparison views
- more trend summaries over time

### Better export presentation
The export system works, but the PDF layout could be refined further with stronger report-specific formatting.

### Repository and deployment workflow
More of the project could be moved into a cleaner repo-driven deployment workflow rather than relying so heavily on live-server editing.