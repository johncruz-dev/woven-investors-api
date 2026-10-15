# Woven Education

Laravel education LMS with role-based access for students, teachers, counselors, and administrators.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

**Accounts (password: `password`):**

| Email | Role |
|-------|------|
| `admin@example.com` | Administrator |
| `teacher@example.com` | Teacher / Instructor |
| `student@example.com` | Student (Alex) |
| `jordan@example.com` | Student (Jordan) |
| `sam@example.com` | Student (Sam) |
| `counselor@example.com` | Counselor (support) |

Seeded demo video lessons appear under **Schedule**. Students can open a lesson and click **Confirm I’ll attend**.

## User management & roles

### Students
- Create/manage profile (`/education/profile`)
- Enroll in courses
- View lessons & assignments
- Submit homework
- Track learning progress
- Message teachers

### Teachers / Instructors
- Create courses
- Upload learning materials
- Manage course students
- Create assessments
- Grade assignments
- Monitor student performance

### Administrators
- Manage users & permissions (`/education/admin/users`)
- Configure platform settings
- Generate reports
- Manage payments/subscriptions
- Maintain security

## Key URLs

| Path | Purpose |
|------|---------|
| `/education` | Dashboard |
| `/education/courses` | Courses |
| `/education/messages` | Messaging |
| `/education/admin/users` | Admin panel |

## Tests

```bash
php artisan test
```
