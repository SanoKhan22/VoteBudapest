# VoteBudapest - Participatory Budgeting Platform

<div align="center">

![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![Chart.js](https://img.shields.io/badge/Chart.js-4.4-FF6384?style=for-the-badge&logo=chart.js&logoColor=white)

**Full-stack web app for democratic community budget voting**

🎓 Academic Project @ ELTE |

</div>

---

## 🖼️ Screenshots

<div align="center">
<img src="screenshots/homepage.png" alt="Homepage" width="800"/>
<p><em>Homepage with real-time voting interface and progress tracking</em></p>

<img src="screenshots/analytics.png" alt="Analytics" width="800"/>
<p><em>Admin dashboard with Chart.js visualizations</em></p>

<img src="screenshots/submit-project.png" alt="Submit Form" width="800"/>
<p><em>Multi-step project submission form with validation</em></p>
</div>

---

## 🚀 What It Does

Citizens submit community project proposals → Admin reviews & approves → Community votes democratically → Data analytics track engagement

**Key Features:**
- ✅ AJAX-powered voting without page reload
- ✅ Smart constraints: max 3 votes/category, 14-day voting window
- ✅ Admin workflow: approve/reject/request-rework with feedback
- ✅ Real-time analytics with interactive Chart.js visualizations
- ✅ Secure authentication with bcrypt + session management
- ✅ Responsive Bootstrap 5 UI with modern design

---

## 💡 Technical Skills Showcased

**Backend:** PHP 8.1+ (vanilla), MySQL with PDO, session auth, prepared statements  
**Frontend:** HTML5/CSS3, Bootstrap 5, Vanilla JavaScript (ES6+), AJAX/Fetch API  
**Security:** SQL injection prevention, XSS protection, password hashing, input validation  
**Database:** Schema design, foreign keys, constraints, indexes, normalization  
**Tools:** Git, Chart.js, RESTful patterns, MVC architecture

---

## 📊 Quick Stats

| Metric | Value |
|--------|-------|
| Lines of Code | 3,500+ |
| PHP Files | 30+ |
| Database Tables | 6 |
| Dev Time | ~60 hours |

---

## ⚡ Quick Start

```bash
# Clone & setup
git clone https://github.com/yourusername/VoteBudapest.git
cd VoteBudapest
mysql -u root -p < sql/schema.sql

# Configure
cp config/database.example.php config/database.php
# Edit with your credentials

# Run
php -S localhost:8000

# Login: admin / admin
```

---

## 🎯 Key Implementations

**AJAX Voting:** Real-time updates without page reload, progress bars, toast notifications  
**Smart Validation:** Budapest postal codes, password complexity, email format, character limits  
**Admin Dashboard:** Project lifecycle management, feedback system, approval workflow  
**Data Visualization:** Bar/Pie/Doughnut charts showing votes, status, category distribution  
**Security:** PDO prepared statements, bcrypt hashing, XSS sanitization, CSRF protection

---

## 📂 Architecture

```
MVC-Inspired Structure:
├── index.php (Homepage)
├── pages/ (Views: login, register, projects, admin)
├── actions/ (Controllers: vote, auth, admin operations)
├── includes/ (Reusable: session, functions, header/footer)
├── config/ (Database, constants)
├── assets/ (CSS, JS with AJAX voting)
└── sql/ (Database schema)
```

---

<div align="center">

**Ehsanullah Ehsanullah** | ELTE Faculty of Informatics | Neptun: igbv99

Built for Web Programming Course • Academic Year 2024/2025

![Made with PHP](https://img.shields.io/badge/Made%20with-PHP-777BB4?style=flat-square&logo=php)
![Database](https://img.shields.io/badge/Database-MySQL-4479A1?style=flat-square&logo=mysql)
![Frontend](https://img.shields.io/badge/Frontend-Bootstrap-7952B3?style=flat-square&logo=bootstrap)

</div>
