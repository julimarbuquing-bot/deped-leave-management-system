# DepEd SDO Ilocos Sur Leave Management System

A PHP + MySQL workflow-based web application for managing CSC Form No. 6 leave applications, approval routing, electronic signatures, notifications, and reporting.

## Features

- Role-based access: Administrator, Applicant, School Head, ASDS/Division Approver, Office Approver, HR/Personnel Administrator
- Workflow-based application processing
- Department organization structure: schools and offices
- Leave credit tracking and deduction after final approval
- Electronic Form 6 with leave date calculation
- Application status tracking and timeline
- Approval routing based on user assignment
- E-signature and audit trail
- Email notifications and SMTP configuration
- PDF/Print view support
- QR verification page
- Reports, import/export, and backup tools
- Demo data and installation guide

## Project structure

- `config/` – database and app configuration
- `includes/` – common helpers and security functions
- `admin/` – administrator dashboard and management pages
- `applicant/` – applicant forms and tracking pages
- `approver/` – approver review and action pages
- `assets/` – CSS and JavaScript files
- `database/` – SQL schema and seed data
- `uploads/` – uploaded documents

## Requirements

- XAMPP with PHP 7.x/8.x and MySQL/MariaDB
- Apache + MySQL enabled
- Browser support: modern Chrome/Edge/Firefox

## Installation

1. Install XAMPP.
2. Start Apache and MySQL.
3. Copy this project into `C:\xampp\htdocs\leave_system`.
4. Create MySQL database: `depedsdois_leave`.
5. Import `database/leave_system.sql`.
6. Update database credentials in `config/db.php` if needed.
7. Open `http://localhost/leave_system/`.
8. Login using demo account from the seed data.

## Demo accounts

The seeded data includes sample users and roles:

- Administrator: `admin` / `password`
- School Head: `schoolhead` / `password`
- School Personnel: `schoolpersonnel` / `password`
- ASDS: `asds` / `password`
- Office Approver: `officeapprover` / `password`
- HR Personnel: `hradmin` / `password`

All demo accounts require a password change after installation as required.

## Notes

- Passwords are intentionally stored in plain text to satisfy the project requirement.
- The system is designed to run locally with local database and optional SMTP.
- Email sending is implemented through a simple SMTP wrapper when configured.
- PDF generation prefers TCPDF if available, and otherwise renders a print-ready HTML view.

## License

This project is intended for local institutional deployment and demonstration.
