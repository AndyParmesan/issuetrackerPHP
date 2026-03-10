# Issue Tracker — Castles Technology Enterprise v2.0

A web-based issue and bug tracking system designed for software development teams. Built with PHP, MySQL, and vanilla JavaScript.
By AndyParmesan & Nikkowiii.

---

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [User Roles & Permissions](#user-roles--permissions)
- [Installation](#installation)
- [Project Structure](#project-structure)
- [Database Schema](#database-schema)
- [API Endpoints](#api-endpoints)
- [Issue Workflow](#issue-workflow)
- [Usage Guide](#usage-guide)
- [Configuration](#configuration)
- [License](#license)

---

## 🔍 Overview

**Issue Tracker** is an internal system used by Castles Technology to manage software development tasks, track bugs, and coordinate user stories across development teams.

The system supports:
- Creating and managing issues/bugs
- User story management with Agile workflows
- File attachments
- Comment discussions
- Bulk import via Excel
- Reporting and analytics
- Role-based access control

---

## ✨ Features

| Feature | Description |
|---------|-------------|
| **Dashboard** | Real-time statistics with interactive charts (issues by priority, by dashboard) |
| **Issue Management** | Create, view, edit, delete issues with full metadata tracking |
| **User Stories** | Agile-style user stories with acceptance criteria, story points, sprints |
| **Comments** | Threaded discussions on each issue/story |
| **Attachments** | Upload up to 5 files per issue (images, PDFs, documents) |
| **Import XLSX** | Bulk import issues from Excel spreadsheets |
| **Export XLSX** | Export issues to styled Excel files |
| **Reports** | Generate reports by date range and status |
| **Maintenance** | Admin-only panel for managing users and particulars |
| **Authentication** | Secure login with role-based access |

---

## 🛠 Technology Stack

| Layer | Technology |
|-------|------------|
| **Frontend** | HTML5, CSS3, Vanilla JavaScript |
| **Backend** | PHP 7.x / 8.x |
| **Database** | MySQL (via PDO) |
| **External Libraries** | Chart.js (charts), XLSX-JS-Style (Excel import/export) |
| **Server** | Apache (XAMPP) |

---

## 👥 User Roles & Permissions

| Role | Permissions |
|------|-------------|
| **Admin** | Full access: create/edit/delete issues, manage users, import XLSX, view reports, maintenance panel |
| **Reporter** | Create issues and user stories, view all issues, add comments |
| **Developer** | View issues, update assigned issues, view reports |
| **Staff** | General access (configurable) |

---

## 📦 Installation

### Prerequisites
- XAMPP (or any Apache + PHP + MySQL stack)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser (Chrome, Firefox, Edge)

### Setup Steps

1. **Start XAMPP**
   - Start Apache and MySQL services in XAMPP Control Panel

2. **Create Database**
   - Open phpMyAdmin (`http://localhost/phpmyadmin`)
   - Create a new database named `issuetracker`
   - Import the SQL schema files from the `config/` folder

3. **Configure Database**
   - Edit `config/database.php` if needed (default: localhost, root, no password)

4. **Deploy Files**
   - Place the project folder in `htdocs/`:
     ```
     c:/xampp/htdocs/issuetracker/
     ```

5. **Access the Application**
   - Open `http://localhost/issuetracker/` in your browser

6. **Default Login** (if configured)
   - Check `config/master_users.sql` for default admin credentials
   - Or create a user via the Maintenance panel (admin only)

---

## 📂 Project Structure

```
issuetracker/
├── index.html              # Main application (SPA)
├── login.html              # Login page
├── Castles_Technology_logo.png
├── DEPLOY_CHECKLIST.txt
├── api/                    # PHP API endpoints
│   ├── login.php
│   ├── get_issues.php
│   ├── create_issue.php
│   ├── update_issue.php
│   ├── delete_issue.php
│   ├── get_issue_detail.php
│   ├── get_stats.php
│   ├── comments.php
│   ├── add_comment.php
│   ├── delete_comment.php
│   ├── attachments.php
│   ├── download_attachment.php
│   ├── delete_attachment.php
│   ├── get_users.php
│   ├── get_users_full.php
│   ├── create_user_maint.php
│   ├── update_user_maint.php
│   ├── delete_user_maint.php
│   ├── get_particulars.php
│   ├── create_particular.php
│   ├── update_particular.php
│   ├── delete_particular.php
│   ├── create_report.php
│   ├── get_reports.php
│   ├── download_report.php
│   ├── export_issues_excel.php
│   ├── import_xlsx.php
│   └── import_csv.php
├── config/                 # Configuration & DB scripts
│   ├── database.php
│   ├── issuetracker_queries.sql
│   ├── master_users.sql
│   ├── migrate_to_v2.sql
│   ├── add_login.sql
│   └── fix_users.sql
└── uploads/                # Uploaded attachments (auto-created)
```

---

## 🗄 Database Schema

### Main Tables

**`issues`**
| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Auto-increment ID |
| title | VARCHAR(255) | Issue/story title |
| dashboard | VARCHAR(100) | Dashboard/category |
| particular_id | INT | FK to particulars |
| module | VARCHAR(100) | Module name |
| description | TEXT | Full description |
| state | VARCHAR(50) | Current state |
| status | VARCHAR(50) | Progress status |
| priority | VARCHAR(20) | Priority level |
| story_points | INT | Agile story points |
| area_path | VARCHAR(100) | Team/department |
| iteration_path | VARCHAR(50) | Sprint assignment |
| acceptance_criteria | TEXT | User story acceptance criteria |
| issued_by | INT | FK to users (creator) |
| assigned_to | INT | FK to users (assignee) |
| date_identified | DATE | Date issue was found |
| source | VARCHAR(50) | Origin (Manual/XLSX) |
| created_at | DATETIME | Creation timestamp |
| updated_at | DATETIME | Last update timestamp |

**`users`**
| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Auto-increment ID |
| username | VARCHAR(50) | Login username |
| password | VARCHAR(32) | MD5 hash |
| name | VARCHAR(100) | Full name |
| email | VARCHAR(100) | Email address |
| role | VARCHAR(20) | User role |
| particular_id | INT | FK to particulars |
| isActive | TINYINT | Active status |

**`particulars`**
| Column | Type | Description |
|--------|------|-------------|
| particular_id | INT (PK) | Auto-increment ID |
| name | VARCHAR(100) | Particular name |
| isActive | TINYINT | Active status |

**`comments`**
| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Auto-increment ID |
| issue_id | INT | FK to issues |
| user_id | INT | FK to users |
| author | VARCHAR(100) | Author name |
| comment | TEXT | Comment text |
| created_at | DATETIME | Creation timestamp |

**`attachments`**
| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Auto-increment ID |
| issue_id | INT | FK to issues |
| file_name | VARCHAR(255) | Stored filename |
| original_name | VARCHAR(255) | Original filename |
| file_type | VARCHAR(100) | MIME type |
| file_size | INT | Size in bytes |
| uploaded_at | DATETIME | Upload timestamp |

**`reports`**
| Column | Type | Description |
|--------|------|-------------|
| id | INT (PK) | Auto-increment ID |
| date_range | VARCHAR(50) | Date range filter |
| status_filter | VARCHAR(50) | Status filter |
| total_issues | INT | Total count |
| new_count | INT | New issues count |
| bug_count | INT | Bug count |
| open_count | INT | Open count |
| in_progress_count | INT | In progress count |
| resolved_count | INT | Resolved count |
| generated_at | DATETIME | Report generation time |

---

## 🌐 API Endpoints

### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/login.php` | Authenticate user |

### Issues
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/get_issues.php` | List all issues (with filters) |
| GET | `/api/get_issue_detail.php?id={id}` | Get single issue details |
| POST | `/api/create_issue.php` | Create new issue or user story |
| POST | `/api/update_issue.php?id={id}` | Update issue |
| GET | `/api/delete_issue.php?id={id}` | Delete issue |

### Comments
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/comments.php?issueId={id}` | Get comments for issue |
| POST | `/api/add_comment.php` | Add comment |
| GET | `/api/delete_comment.php?id={id}` | Delete comment |

### Attachments
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/attachments.php?issueId={id}` | Get attachments |
| POST | `/api/attachments.php?issueId={id}` | Upload attachment |
| GET | `/api/download_attachment.php?id={id}` | Download attachment |
| GET | `/api/delete_attachment.php?issueId={id}&attachmentId={id}` | Delete attachment |

### Users & Maintenance
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/get_users.php` | List users |
| GET | `/api/get_users_full.php` | List users with details |
| POST | `/api/create_user_maint.php` | Create user (maintenance) |
| POST | `/api/update_user_maint.php` | Update user (maintenance) |
| GET | `/api/delete_user_maint.php?id={id}` | Delete user |

### Particulars
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/get_particulars.php` | List particulars |
| POST | `/api/create_particular.php` | Create particular |
| POST | `/api/update_particular.php` | Update particular |
| GET | `/api/delete_particular.php?id={id}` | Delete particular |

### Reports & Analytics
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/get_stats.php` | Get dashboard statistics |
| POST | `/api/create_report.php` | Generate report |
| GET | `/api/get_reports.php` | List reports |
| GET | `/api/download_report.php?id={id}` | Download report CSV |

### Import/Export
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/export_issues_excel.php` | Export to Excel |
| POST | `/api/import_xlsx.php` | Import from XLSX |
| POST | `/api/import_csv.php` | Import from CSV |

---

## 🔄 Issue Workflow

### Legacy Issue States
```
New → In Progress → Fixed → Resolved
   ↘ Open → ... (cycle)
```

### User Story States (Agile)
```
Draft → For Review → Approved → In Development → For Testing → QA Failed → For UAT → Ready for Deployment → Deployed → Closed
```

### Priority Levels
| Priority | Label | Description |
|----------|-------|-------------|
| 1-Urgent | 🔴 Urgent | System outage, data loss |
| 2-Critical | 🔴 Critical | Blocks dev or production |
| 3-High | 🟠 High | Major functionality affected |
| 4-Medium | 🟡 Medium | Moderate impact |
| 5-Low | 🟢 Low | Minor / cosmetic |

### Story Points
| Points | Estimated Time |
|--------|-----------------|
| 1 | 1–2 hours |
| 2 | 3–4 hours |
| 3 | 5–8 hours |
| 5 | 8–16 hours |
| 8 | 24–32 hours |
| 13 | 40–56 hours |

---

## 📖 Usage Guide

### Login
1. Navigate to `http://localhost/issuetracker/`
2. Enter username and password
3. Click Login

### Creating an Issue
1. Click **New Issue** in the sidebar
2. Fill in the required fields:
   - Particular (department/project)
   - Module
   - Description
   - State (New/Bug/Open)
   - Priority
3. Click **Create Issue**

### Creating a User Story
1. Click **User Story** in the sidebar
2. Fill in all required fields:
   - Title
   - State (Draft, For Review, Approved, etc.)
   - Description
   - Acceptance Criteria
   - Story Points
   - Sprint (Iteration Path)
3. Click **Create User Story**

### Viewing & Editing Issues
1. Click on any issue in the list
2. The detail panel shows full information
3. Add comments in the Discussion section
4. Upload attachments (max 5)
5. Edit or Delete using the buttons (role permitting)

### Importing Issues (Admin)
1. Click **Import XLSX** in the sidebar
2. Drop or select an Excel file (.xlsx)
3. Click **Import File**
4. Review the results

### Generating Reports
1. Click **Reports** in the sidebar
2. Select date range and status filter
3. Click **Generate Report**
4. Download as CSV

### Maintenance (Admin)
1. Click **Maintenance** in the sidebar
2. Manage **Users**: Add, edit, delete users
3. Manage **Particulars**: Add, edit, delete departments/projects

---

## ⚙ Configuration

### Database Connection
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'issuetracker');
define('DB_USER', 'root');
define('DB_PASS', '');       // Your MySQL password
define('DB_PORT', 3306);
```

### Logo
Replace `Castles_Technology_logo.png` with your company logo.

### Session Timeout
Modify `index.html` JavaScript section:
```javascript
// Session is stored in localStorage
// Add session expiry logic as needed
```

---

## 📄 License

**Issue Tracker** is an internal system developed by **Castles Technology**.

All rights reserved © 2026 Castles Technology

---

## 🆘 Troubleshooting

| Issue | Solution |
|-------|----------|
| "Cannot connect to server" | Ensure XAMPP Apache and MySQL are running |
| Login fails | Check database connection; verify user exists in `users` table |
| Upload fails | Check `uploads/` folder permissions; verify file size < 10MB |
| Import errors | Ensure Excel format matches the required column order |
| Charts not loading | Check console for JavaScript errors; verify Chart.js CDN loads |

---

## 📞 Support

For internal support, contact your system administrator or IT department.

---

*Last updated: 2026*

