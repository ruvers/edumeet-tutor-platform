# EduMeet — Peer Tutoring Platform

Web platform where students request tutoring sessions, tutors manage lessons, and admins oversee users and reports.

**Stack:** PHP, MySQL, HTML/CSS/JavaScript

## Features

- Student, tutor, and admin roles
- Tutor discovery, lesson requests, ratings, and reviews
- Admin dashboards for users and moderation
- Secure password hashing with PHP `password_hash`

## Setup

1. Create a MySQL database named `tutor_system`
2. Import `schema.sql`
3. Copy `db.example.php` to `db.php` and set your database credentials
4. Serve the folder with Apache/XAMPP or PHP's built-in server:

```bash
php -S localhost:8000
```

## Demo accounts

After importing the schema, log in with the seeded demo users (see `schema.sql`). Default local setup uses XAMPP/MAMP with `root` and no password.
