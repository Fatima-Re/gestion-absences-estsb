# AI Coding Guidelines for Gestion Absences PFE

## Project Overview
This is a Laravel 12 application for managing student absences in an educational institution. It supports three user roles: admin, teacher, and student, with role-based access control.

## Architecture
- **MVC Structure**: Standard Laravel with controllers organized by role (`app/Http/Controllers/{Admin,Teacher,Student}`)
- **Database**: MySQL with migrations defining tables for users, teachers, students, groups, modules, course_sessions, absences, justifications
- **User Model**: Single `User` model with `role` enum ('admin', 'teacher', 'student'); extended by `teachers` and `students` tables via foreign keys
- **Relationships**: Many-to-many between groups/students, modules/teachers, groups/modules via pivot tables
- **Frontend**: Vite + Tailwind CSS, views in `resources/views/` organized by role subdirs

## Key Components
- **Absence Management**: Recorded per `course_sessions` (teaching instances), linked to students
- **Justifications**: Students can submit justifications for absences, reviewed by teachers/admins
- **Attendance Records**: Separate from absences, possibly for real-time tracking
- **Notifications**: System for alerts (email/queue-based)

## Development Workflow
- **Setup**: Run `composer run setup` (installs deps, copies .env, generates key, migrates, builds assets)
- **Development**: Use `composer run dev` to concurrently run server, queue worker, logs, and Vite dev server
- **Testing**: `composer run test` for PHPUnit tests
- **Build**: `npm run build` for production assets

## Conventions
- **Routes**: Define in `routes/web.php` with role-based middleware (e.g., `middleware('role:teacher')`)
- **Models**: Use Eloquent relationships; e.g., `User` hasOne `Teacher` or `Student` based on role
- **Controllers**: Group by role; use resource controllers for CRUD operations
- **Views**: Blade templates in role-specific folders; extend `layouts.app` with Tailwind classes
- **Migrations**: Follow Laravel naming; use foreign keys with `constrained()` for relationships
- **Seeders**: Use factories for test data; DatabaseSeeder creates default users

## Examples
- **User Role Check**: `if ($user->role === 'teacher') { // teacher-specific logic }`
- **Relationship Access**: `$teacher = $user->teacher; $modules = $teacher->modules;`
- **Absence Creation**: `Absence::create(['student_id' => $student->id, 'course_session_id' => $session->id, 'status' => 'unjustified']);`
- **Middleware Usage**: Protect routes with custom role middleware in `app/Http/Middleware/`

Focus on implementing role-based features, ensuring data integrity across related tables, and using Laravel's queue system for notifications.