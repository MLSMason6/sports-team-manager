# 🏃‍♂️ Sports Team Manager – Admin Log System

A secure PHP/MySQL-based dashboard that tracks, audits, and manages actions within a sports management app.  
Super Admins can view system logs, add notes, and edit details — all in real-time with smart live updates.

---

## 🚀 Features

### 🧾 Audit Logging
- Logs every key admin and user action.
- Records time, actor, and action details.
- Filter by user, action keyword, or date range.

### 💬 Real-Time Notes
- Comment threads on each log entry.
- Chat-style UI with user avatars.
- Updates in real time (auto-refresh every 5s).
- Smart polling: pauses when tab inactive, resumes instantly.

### 📝 Editable Details
- Modify audit log details directly via modal.
- AJAX-powered updates (no page reloads).

### 📊 Tools for Admins
- CSV export for audit data.
- Pagination for large logs.
- Role-based access (only Super Admins can view/edit logs).

---

## 🧱 Tech Stack

| Layer | Tools |
|-------|-------|
| **Frontend** | HTML5, CSS3, JavaScript (vanilla) |
| **Backend** | PHP 8+, PDO (secure database access) |
| **Database** | MySQL / MariaDB |
| **Version Control** | Git + GitHub |
| **Environment** | XAMPP / WAMP / LAMP |

---

## ⚙️ Setup Instructions

1. Clone the repository:
   ```bash
   git clone https://github.com/YOUR_USERNAME/sports-manager-admin.git
