# CSE 135 Grader Guide

## Site Access

Reporting platform:
https://reporting.austinchoi-135.site
https://reporting.austinchoi-135.site/reports

Because the reporting host is protected by Basic Auth, you will first need the site-level credentials below before using the in-app login system.

## Basic Auth Credentials

Username:
grader

Password:
grader_password

## App Login Credentials

### Super Admin
Username:
superadmin

Password:
grader_password

### Analyst
Username:
analyst1

Password:
grader_password

### Viewer
Username:
viewer1

Password:
grader_password

## Recommended Grading Scenario

### Scenario 1: Super Admin Flow

Step 1  
Open the reporting platform and pass the Basic Auth prompt using the grader credentials.

Step 2  
Log in as the super admin account.

Step 3  
Open the Reports page and review the dashboard overview:
- KPI cards
- grouped event chart
- recent event table

Step 4  
Open the Saved Reports page and review the curated report cards.

Step 5  
Open each saved report:
- behavior overview
- error overview
- performance overview

Step 6  
On the behavior report, review the extra visualization work:
- behavior over time
- recent session summaries

Step 7  
Use the Export as PDF action on one of the saved reports and confirm the export flow works.

Step 8  
Open the User Management page and confirm that the super admin can access the administrative route.

### Scenario 2: Viewer Access Restriction

Step 1  
Log out.

Step 2  
Log in as the viewer account.

Step 3  
Open Saved Reports and verify that the viewer can review published saved reports.

Step 4  
Attempt to open the admin users route:
`/admin/users`

Expected result:
- the viewer should receive a styled 403 forbidden page

### Scenario 3: Fallback Checks

Step 1  
Visit a fake route such as:
`/not-a-real-page`

Expected result:
- styled 404 page

Step 2  
Visit a fake report slug such as:
`/saved-reports/not-real-report`

Expected result:
- styled 404 page

Step 3  
Visit a fake export file such as:
`/download-export/not-real-file.pdf`

Expected result:
- styled 404 page

## Main Features to Look For

### Authentication and Authorization
- working login system backed by the database
- three user levels
- super admin can access admin page
- viewer is restricted from admin page
- role-based access is enforced server-side

### Reporting Layer
- saved reports act as the viewer-facing reporting surface
- three report categories are present
- reports include charts, supporting tables, and commentary
- behavior report includes extra visualization work

### Export System
- reports can be exported to PDF
- export is generated server-side
- download route serves generated file

### UI and Presentation
- consistent visual structure across major pages
- dashboard overview
- saved report card layout
- polished report detail layout
- styled 403 and 404 fallbacks

## Known Concerns and Architecture Notes

### 1. Live-server editing history
A large portion of the project was developed directly on the live server rather than through a fully clean repo-first deployment workflow. The deployed version is the source of truth for the final project state.

### 2. Dataset size affects some visualizations
Some of the newer visualizations depend on the amount and spread of collected analytics data. For example, time-bucket charts may look more dramatic or more sparse depending on how concentrated the captured sample data is.

### 3. Session-summary feature is a lightweight interpretation
The recent session summaries on the behavior report are intended as a lightweight behavioral view rather than a full session replay system. This was a deliberate choice to keep the application lighter and more practical.

### 4. Export quality is functional but not fully polished
The PDF export works and is integrated into the report flow, but the visual formatting of exported PDFs could still be improved further if more time were available.

### 5. Administrative surface is read-focused
The user management page is currently a review page rather than a full CRUD user administration tool. It still demonstrates authorization, role visibility, and account review, but it is intentionally limited in scope.

### 6. Error handling is much stronger than earlier phases, but still not perfect
Major fallback states are now styled and routed through the app, including 403, 404, missing slugs, missing export files, and export failure. That said, there may still be edge cases that could be refined further with more time.