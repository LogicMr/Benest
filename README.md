# BENEST

BENEST is a modular PHP/MySQL project management workspace for small software teams.

## Run locally with XAMPP

1. Start Apache and MySQL in XAMPP.
2. Import `database/schema.sql` in phpMyAdmin. The schema creates the `benest` database and sample Phase 1 data.
3. Open `http://localhost/benest%20management/`.
4. Sign in with `admin@benest.local` and `password`.

If the database was imported before the demo password fix, run `database/fix-demo-password.sql` in phpMyAdmin, then sign in again.

The default database connection is `127.0.0.1`, database `benest`, user `root`, and an empty password. BENEST displays financial values in Tanzanian shillings (`TZS`). Override these with `BENEST_DB_HOST`, `BENEST_DB_NAME`, `BENEST_DB_USER`, and `BENEST_DB_PASS` environment variables when needed.

## Current foundation

Phase 1 includes session authentication, password hashing, CSRF protection, role-aware guards, dashboard analytics, clients, projects, milestones, tasks, Kanban, calendar, activity logs, responsive navigation, connected detail views, and TZS currency formatting. The remaining roadmap modules can reuse the same PDO, schema, and layout contracts.

Never put SMTP passwords, API keys, or database credentials in ordinary project fields. Keep secrets in server environment variables or a protected configuration store.
