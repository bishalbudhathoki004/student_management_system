# Student Management System

Simple PHP + MySQL student management system with admin and student login.

## Run locally

1. Import `database/student_management_system.sql` into MySQL.
2. Update DB settings in `config/database.php` if needed.
3. Serve project from PHP web server root.
   - Example: `php -S localhost:8000`
4. Open `http://localhost:8000/login.php`.

## Refactored structure

- `config/database.php` - centralized DB connection helper (`get_db_connection`).
- `includes/bootstrap.php` - shared app bootstrap for session + helpers + model loading.
- `includes/helpers.php` - reusable helpers for sanitization, redirects, flash messages, auth checks, and validation.
- `models/StudentModel.php` - grouped student-related DB operations (list/create/update/delete/reset password).
- `templates/admin/` - shared admin layout header/footer.
- `templates/students/` - reusable student table and student form fields partial.
- `assets/css/admin.css` - shared admin CRUD page styles.
- `admin/` - CRUD handlers/pages now call helpers/model/templates instead of repeating logic.

## Notes

- Existing student CRUD flows are preserved: list, create, edit/update, delete.
- Password reset flow is preserved and still shows generated credentials after reset.
