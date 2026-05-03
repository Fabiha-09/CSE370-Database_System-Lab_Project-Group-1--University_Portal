# BracU University Portal
**CSE370 Project — Full Stack PHP/MySQL Web Application**

---

## 🚀 Setup Instructions

### Requirements
- PHP 7.4+
- MySQL 5.7+ or MariaDB
- Apache/Nginx (XAMPP, WAMP, MAMP, or Laragon recommended)

---

### Step 1: Import the Database

1. Open **phpMyAdmin** (or MySQL command line)
2. Create a new database: `university_portal`
3. Import the file: `database.sql`

**OR via terminal:**
```bash
mysql -u root -p < database.sql
```

---

### Step 2: Configure Database Connection

Edit `includes/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // your MySQL username
define('DB_PASS', '');           // your MySQL password
define('DB_NAME', 'university_portal');
```

---

### Step 3: Start the Project

Place the `university_portal/` folder inside:
- XAMPP: `C:/xampp/htdocs/`
- WAMP:  `C:/wamp64/www/`
- MAMP:  `/Applications/MAMP/htdocs/`

Then visit: **http://localhost/university_portal/**

---

## 🔑 Demo Login

Use any Student ID as both username and password:

| Student ID | Name | Department |
|------------|------|------------|
| 22301001 | Faiyaz Ahmed | CSE |
| 22301002 | Fabiha Islam | CSE |
| 21201001 | Rakib Hossain | CSE |
| 22101001 | Sadia Begum | BBS |

Password = Student ID (e.g., `22301001` / `22301001`)

---

## 📋 Features Implemented

| # | Feature | Type |
|---|---------|------|
| 01 | Smart Course Recommendation System | ✨ Unique |
| 02 | Student Performance Analytics Dashboard | ✨ Unique |
| 03 | Department Statistics Dashboard | ✨ Unique |
| 04 | Real-Time Lab Availability Checker | ✨ Unique |
| 05 | Campus Facility Complaint System | ✨ Unique |
| 06 | Student Notes Resource Library | ✨ Unique |
| 07 | Reward Points System | ✨ Unique |
| 08 | Credit Limit Checker (auto-enforced in enrollment) | Basic |
| 09 | Course Enrollment System | Basic |
| 10 | Classroom Capacity Checker (auto-enforced) | Basic |
| 11 | Student Grade Sheet System | Basic |
| 12 | Exam Schedule System | Basic |
| 13 | Semester Fee Payment Status | Basic |
| 14 | Personal Academic Routine | Basic |

---

## 📁 Project Structure

```
university_portal/
├── index.php              # Login page
├── register.php           # Registration page
├── logout.php             # Logout
├── database.sql           # Full database with sample data
├── assets/
│   └── css/style.css      # Main stylesheet
├── includes/
│   ├── db.php             # Database connection
│   ├── auth.php           # Session/auth helpers
│   └── sidebar.php        # Navigation sidebar
└── pages/
    ├── dashboard.php      # Home dashboard
    ├── profile.php        # Student profile
    ├── enrollment.php     # Course enrollment (F08, F09, F10)
    ├── grades.php         # Grade sheet (F11)
    ├── exam_schedule.php  # Exam schedule (F12)
    ├── routine.php        # Academic routine (F14)
    ├── recommendation.php # Smart recommendations (F01)
    ├── analytics.php      # Analytics + dept stats (F02, F03)
    ├── lab_availability.php # Lab checker (F04)
    ├── complaint.php      # Complaint system (F05)
    ├── notes.php          # Notes library (F06)
    ├── reward_points.php  # Reward leaderboard (F07)
    ├── fees.php           # Semester fees (F13)
    └── department_stats.php # Department stats (F03)
```
