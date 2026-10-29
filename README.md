# CampusFix - College Complaint Management System

A web-based facility and maintenance complaint management system built using PHP and MySQL. CampusFix enables students to report campus issues (electrical, plumbing, infrastructure, etc.), allows administrators to review, assign, and monitor tasks under SLA deadlines, and empowers staff members to resolve assigned complaints with photo/document proofs.

---

## Features

- **Role-Based Authentication & Access Control**:
  - **Student**: Submit complaints with photo/document attachments, select campus/building/spot, view status timeline, and reopen resolved/closed complaints if issues persist.
  - **Staff (Maintenance / Department)**: View assigned complaints, update progress (`Assigned` -> `In Progress` -> `Resolved`), and attach resolution proof files.
  - **Administrator**: Comprehensive dashboard with KPI stats, complaint assignment, staff account approvals, reopened complaint reviews, category management, and campus/building/spot management.
- **Complaint Tracking**:
  - Track complaint status and history without requiring login using the public tracking page and unique Complaint ID (e.g., `CMP_001`).
- **SLA & Escalation Monitoring**:
  - Automatic SLA warning calculation (unassigned > 6 hours) and breach alerts (assigned unresolved > 42 hours).
  - Dedicated escalated reports panel for administrators.
- **Dynamic Cascading Location Selectors**:
  - AJAX-powered Campus -> Building -> Spot selection with custom "Other" options.
- **Theme Toggle**:
  - Dark mode and light mode switcher persisted with cookies.

---

## Technologies Used

- **Backend**: PHP (PHP 7.4+ / PHP 8.x) with PDO MySQL
- **Database**: MySQL / MariaDB
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5, jQuery 3.6, Chart.js
- **Environment**: Standard Apache/MySQL stack (e.g., XAMPP, WampServer, or LAMP)

---

## Project Structure

```text
college-complaint-system/
├── api/
│   ├── get_buildings.php      # JSON endpoint for dynamic building dropdown
│   ├── get_complaint.php      # JSON endpoint for complaint details
│   ├── get_forwarded.php      # JSON endpoint for SLA-breached complaints
│   ├── get_spots.php          # JSON endpoint for dynamic spot dropdown
│   ├── track_complaint.php    # JSON endpoint for public tracking lookup
│   └── update_status.php      # Status transition handler
├── assets/
│   ├── css/
│   │   └── style.css          # Custom styling and dark mode rules
│   └── js/
│       └── main.js            # Client-side validation and UI utilities
├── config/
│   ├── db.example.php         # Sample database connection config
│   └── db.php                 # Local database connection (git-ignored)
├── database/
│   └── db.sql                 # Complete database schema and initial seed data
├── includes/
│   ├── footer.php             # Shared HTML footer and script imports
│   ├── functions.php          # Helpers: RBAC, SLA calculations, transitions, timeAgo
│   └── header.php             # Shared navbar, session indicators, and theme script
├── uploads/
│   ├── proofs/                # Uploaded student complaint evidence
│   └── resolutions/           # Uploaded staff resolution evidence
├── approve_staff.php          # Admin approval for registered staff accounts
├── create_complaint.php       # Student complaint submission form
├── dashboard_admin.php        # Administrator overview, stats, and assignments
├── dashboard_staff.php        # Staff task overview and status updates
├── dashboard_student.php      # Student personal complaints list
├── escalated_reports.php      # SLA violation reports
├── index.php                  # Entry point / routing redirect
├── login.php                  # User sign-in page
├── logout.php                 # Secure session destruction
├── manage_areas.php           # Admin campus, building, and spot management
├── manage_categories.php      # Admin complaint category management
├── register.php               # Student and Staff registration
├── track_complaint.php        # Public complaint tracking page
├── view_complaint.php         # Detailed complaint view, timeline, and actions
├── .gitignore                 # Git ignore rules for uploads, configs, and system files
└── README.md                  # Project documentation
```

---

## Requirements

- **PHP**: 7.4 or higher (tested on PHP 8.2) with `pdo_mysql` enabled.
- **Database**: MySQL 5.7+ or MariaDB 10.4+.
- **Web Server**: Apache (via XAMPP, WAMP, or standalone).

---

## Installation and Setup

### 1. Start Apache and MySQL
Open the **XAMPP Control Panel** (or your preferred local server) and start both **Apache** and **MySQL**.

### 2. Place Project in Web Root
Copy or move the `college-complaint-system` folder into your web server's document root:
- On Windows (XAMPP): `C:\xampp\htdocs\college-complaint-system`
- On Linux: `/var/www/html/college-complaint-system`

### 3. Set Up the Database
1. Open your web browser and navigate to **phpMyAdmin** (`http://localhost/phpmyadmin`).
2. Create a new database named `campusfix` (using `utf8mb4_general_ci` collation).
3. Select the `campusfix` database and click the **Import** tab.
4. Choose the file located at `database/db.sql` inside the project folder and click **Import**.

### 4. Configure Database Credentials
1. In the `config/` directory, ensure `db.php` exists (you can copy `config/db.example.php` to `config/db.php` if setting up fresh).
2. Set your local database credentials in `config/db.php`:
   ```php
   $host = 'localhost';
   $db_name = 'campusfix';
   $username = 'root';
   $password = ''; // Default in XAMPP is blank
   ```

### 5. Access the Application
Open your browser and navigate to:
```text
http://localhost/college-complaint-system/
```
You will be directed to the login page.

---

## Default Roles & Test Accounts

The included `database/db.sql` provides pre-configured accounts:

| Role | Email | Password | Notes |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@campusfix.com` | `admin123` | Full administrative control |
| **Staff** | `amit123@gmail.com` | `password` | Approved staff member |
| **Staff** | `jignesh@gmail.com` | `password` | Approved staff member |
| **Student** | `valavaibhav315@gmail.com` | `password` | Registered student |

> **Note**: You can also register a new Student or Staff account from the [Register page](http://localhost/college-complaint-system/register.php). Newly registered Staff accounts require Administrator approval under **Approve Staff** before they can log in.

---

## Complaint Status Lifecycle

1. **Submitted**: Initial state after student complaint submission.
2. **Verified**: Admin reviews and verifies the validity of the complaint.
3. **Assigned**: Admin assigns the complaint to a specific staff member.
4. **In Progress**: Staff member begins resolution work.
5. **Resolved**: Staff member marks the issue resolved and uploads proof.
6. **Closed**: Admin confirms and closes the complaint ticket.
7. **Reopened**: If unsatisfied, the student can request a reopen, prompting admin review.

---

## Security Best Practices Included

- **Prepared Statements**: All database operations use PDO prepared statements to defend against SQL injection.
- **Password Hashing**: Secure hashing via PHP's `password_hash()` and verification using `password_verify()`.
- **Role-Based Protection**: All internal views enforce strict session-based role validation (`Admin`, `Staff`, `Student`).
- **File Upload Safeguards**: Whitelist checks for file extensions (`.jpg`, `.jpeg`, `.png`, `.pdf`) and strict 2MB file size limits.
- **HTML Output Escaping**: User inputs are escaped with `htmlspecialchars()` to prevent XSS attacks.
- **Git Hygiene**: Environment variables and database credentials are excluded from version control via `.gitignore`.

---

## Limitations

- Email and SMS notifications are not connected to an external SMTP/gateway service and are logged internally.
- File uploads are stored locally in the filesystem under `uploads/` rather than on cloud storage (e.g., AWS S3).
