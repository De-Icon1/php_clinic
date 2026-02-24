# Copilot / AI Agent Instructions for this Repository

Purpose: help AI coding agents become productive quickly in this PHP hospital management app.

- **Run locally**: this is a PHP/MySQL app intended for XAMPP. Import `hospital.sql` (root) into MySQL, start Apache+MySQL, then update DB credentials in `assets/inc/config.php` if needed. Default config currently uses `root` / empty password and DB name `Hospital`.

- **PHP versions**: App targets PHP 5.6 — 7.4 (see top-level project info). Avoid using PHP 8-only features unless you update compatibility across includes.

- **Key entry points & flow**:
  - `index.php`: login flow. It includes `assets/inc/config.php` and `assets/inc/functions.php` and maps `his_docs.doc_dept` to department dashboards (e.g., `admin_dashboard.php`, `nursing_dashboard.php`, `pharmacy_dashboard.php`, `doc/doctor_dashboard.php`). Use it to understand routing decisions.
  - Dashboards and feature pages live at repository root (many `his_admin_*` pages) and output HTML directly — this is a multi-page, procedural PHP app (no framework).

- **Important files to reference**:
  - `assets/inc/config.php` — DB connection; change here for local dev.
  - `assets/inc/functions.php` — shared helpers used by many pages (read before changing behavior).
  - `hospital.sql` — DB schema / seed data. Use this to understand table names and columns (e.g., `his_docs`, `pharmacy`, `drug`).
  - `scripts/` and `setup_*.php` — DB & initial data setup scripts used by the project; mimic these if scripting changes.

- **Conventions & patterns to preserve**:
  - Procedural PHP with include-based composition (pages include config + functions). Avoid moving logic into classes without updating all includes.
  - Session-driven auth: pages expect `$_SESSION['doc_id']`, `$_SESSION['doc_number']`, and optionally `$_SESSION['campus_id']` / `working_location` set at login. Preserve session keys when changing auth flow.
  - Password hashing uses `sha1(md5($pwd))` (legacy). When migrating, keep compatibility or provide an opt-in migration path in DB so existing users still authenticate.
  - DB usage: mixture of prepared statements and raw `mysqli_query` calls. If refactoring, preserve SQL parameterization and fix raw queries to avoid SQL injection.

- **Quick examples**:
  - Login check (from `index.php`): prepared statement against `his_docs` with fields `doc_number`, `doc_pwd`, `doc_dept` → redirects to department dashboards.
  - Opening stock logic: functions `pharmacyopeningstock()` and `storeopeningstock()` in `index.php` copy current inventory rows into `pharmacy_stock` / `store_stock` when a new date appears.

- **Testing / debugging**:
  - No automated tests are present. Use XAMPP + browser to exercise pages. Tail `error_log` and enable `display_errors` in `php.ini` for rapid debugging but keep off in commits.
  - For quick DB checks, open `hospital.sql` and run targeted SELECTs in phpMyAdmin or MySQL CLI.

- **When changing DB schema**:
  - Update `hospital.sql` and any `setup_*.php` scripts in `scripts/`. Search the codebase for table/column names before renaming columns (many files reference them directly).

- **Security notes for contributors** (documented to avoid accidental regressions):
  - Input sanitization is inconsistent — prefer prepared statements and `htmlspecialchars()` on output.
  - Password storage is legacy — if you change hashing, add a migration path.

If anything above is unclear or you want the file to include extra examples (e.g., a short checklist for refactors or a list of the most-edit-heavy pages), tell me which area to expand. 
---

## Refactor Checklist (use before changing core behavior)

- **Read `index.php` and `assets/inc/functions.php` first**: these define session keys, login redirects, and many helper functions used across pages.
- **Update `hospital.sql` and `scripts/*` when changing schema**: add a migration SQL and update any `setup_*.php` that seeds data.
- **Preserve session keys**: keep `$_SESSION['doc_id']`, `$_SESSION['doc_number']`, and optionally `$_SESSION['campus_id']` / `working_location` unless you update every consumer.
- **Keep legacy password compatibility**: existing passwords use `sha1(md5($pwd))`. If you migrate hashing, provide a dual-check/migration path.
- **Prefer prepared statements**: convert raw `mysqli_query` to prepared statements when touching DB code; run targeted manual tests after each change.
- **Search for hard-coded table/column names**: many files reference columns directly (e.g., `his_docs.doc_dept`, `drug.quantity`). Use a repo-wide search before renaming.
- **Minimal UI changes**: pages output HTML directly; keep markup changes localized to the edited file to avoid layout regressions.

## Top Edit Hotspots (start here for feature work)

1. `assets/inc/config.php` — DB credentials and connection object `$mysqli` (global). Changing this affects the whole app.
2. `assets/inc/functions.php` — common utilities and logging (`log_action()`), input sanitizers, and helpers.
3. `index.php` — login flow, session population, and startup routines (`pharmacyopeningstock`, `storeopeningstock`).
4. `hospital.sql` — canonical schema and seed data; update with migrations.
5. `drug_operations.php` — CRUD for `drug` inventory.
6. `drug_csv_handler.php` — CSV import/export used for bulk pharma updates.
7. `dispense_pharmacy.php` — dispensing workflow; affects pharmacy stock tables.
8. `pharmacy_order.php` — order creation and stock change points.
9. `phar_receipt.php` — receipt generation / printing logic tied to transactions.
10. `pharmacy_dashboard.php` & `his_admin_dashboard.php` — entry points and navigation for admin/pharmacy flows.
11. `doc/doctor_dashboard.php` — doctor-facing workflows and links to clinical pages.
12. `pharmacy_report.php` & `pharmacyview_report.php` — reporting SQL; edits affect performance.
13. `storeview_report.php` & `storestockview_report.php` — store inventory reporting.
14. `get_drug.php`, `get_price.php`, `get_cart.php` — small AJAX endpoints used by many forms.
15. `scripts/setup_drugs.php`, `scripts/setup_consumables.php` — setup scripts for initial data.
16. `setup_drugs.php` — separate setup file for drugs present at repo root.
17. `assets/js/swal.js` — client-side alerts used across pages (keep consistent UX).
18. `drug_csv_handler.php` — bulk import logic; validate inputs before changing.
19. `remove_drug.php`, `update_drug.php` — deletion & update endpoints for inventory.
20. `his_admin_manage_pharmaceuticals.php` — admin UI for pharmaceuticals management.

If you'd like, I can also:
- produce a ranked list of the top 20 files with line ranges to inspect first, or
- add a short
