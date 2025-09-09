# Accreditation System — Detailed Task List & Roadmap

> **Purpose:** Complete, step-by-step developer roadmap and task list to build an Accreditation System with roles & permissions, file upload+Google Drive integration, access-request workflows, detailed security checklist, admin appearance controls, and PWA/offline support.

---

## Table of contents

1. Overview & assumptions
2. Project prerequisites
3. Development phases (high level)
4. Step‑by‑step tasks (phase by phase)

   * Core & infra
   * Authentication & security
   * Roles & permissions
   * Core modules (College, Academic Year, Area, Parameter, Parameter Content)
   * Accreditation workflows (tagging, accreditors, requests)
   * SWOT, Ranking, Reports
   * Admin settings & theme
   * File handling / Google Drive integration
   * Logging & audit
   * Testing, QA & deployment
5. Permission matrix (roles × features)
6. Single File Policy & migration/seeder guidance
7. Developer checklist & acceptance criteria
8. Appendix: API endpoints & sample DB schema notes

---

## 1. Overview & assumptions

* Stack assumed: **Laravel 12+** (or similar MVC framework) with **Bootstrap 5.3** for UI.
* Use **spatie/laravel-permission** or equivalent for roles/permissions but tasks include custom permission checks for special behaviors (request-access vs edit).
* Use **Google Drive Link Only** for storage + access request flows.
* Mobile-first responsive design, PWA support, and maroon branding theme.
* Database-driven configuration (no environment-driven features for runtime settings).

---

## 2. Project prerequisites

* PHP 8.2+, Composer, Node.js (for assets), MySQL.
* Google Drive Link No API (for Drive integration tasks).
* SMTP account for email/notifications.

---

## 3. Development phases (high level)

1. **Project skeleton & infra** — repo, CI, DB connections, base UI, PWA scaffolding.
2. **Auth & security** — auth, 2FA, password hashing (argon2id), security headers.
3. **Roles & permissions** — roles seed, permission definitions, middleware.
4. **Core modules** — College, Academic Year, Area, Parameter, Parameter Content (files).
5. **Accreditation workflows** — tagging, assignments, access request system.
6. **Analytics & reporting** — SWOT, area ranking, dashboards.
7. **Admin settings & theming** — theme management, brand assets, SMTP.
8. **Testing & audits** — unit/integration/E2E tests, security checks.
9. **Deployment & training documentation** — release, user manuals.

---

## 4. Step‑by‑step tasks (phase by phase)

### Phase 1 — Core & infra

* [x] Initialize Laravel project.
* [x] Install Bootstrap 5.3 and set up main layout (mobile-responsiveness).
* [x] Add PWA scaffolding (manifest.json, service worker, offline page).
* [x] Configure database connection and DB user with least privileges.
* [x] Create initial migrations (single-file policy — see section 6).
* [x] Seed initial admin user and roles (single-seeder file).
* [x] Set up CI to run `php artisan test` and `npm run build`.
* [x] **Create layout structure with two main folders:**
  * [x] **Admin Layout** (`resources/views/admin/`) - Full administrative interface with complete system control
  * [x] **User Layout** (`resources/views/user/`) - Role-based interface where different user roles (dean, accreditor_lead, accreditor_member, chairperson, faculty, overall_coordinator) access different CRUD features based on their permissions
  * [x] Implement role-based navigation and feature visibility in user layout
  * [x] Create shared components between admin and user layouts for consistency

### Phase 2 — Authentication & security

* [x] Implement authentication scaffold (Laravel Breeze / Fortify) with Argon2id hashing.
* [x] Implement session security (secure cookies, httpOnly, sameSite).
* [x] CSRF protection enabled by default — add audit to ensure forms include tokens.
* [x] Security headers via middleware: X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Content-Security-Policy (CSP) baseline.
* [x] Rate limiting on auth endpoints and file requests.
* [x] Implement 2FA (Google Authenticator) with user opt-in functionality.
* [x] XSS protection: encode outputs; sanitize user-submitted text.
* [x] Use prepared statements / query builder to avoid SQL injection.

### Phase 3 — Roles & permissions

**Task list:**

* [x] Install spatie/laravel-permission.
* [x] Create `roles` table seed (single file) with: `admin`, `dean`, `accreditor_lead`, `accreditor_member`, `chairperson`, `faculty`, `overall_coordinator`.
* [x] Create `permissions` seed (single file) for granular actions per module (see permission matrix).
* [x] Implement RBAC middleware (`role`, `permission`, and `can-request-access` variations).
* [x] UI admin page to manage user roles and assign permissions (database-driven form).
* [x] Build audit of role changes (who changed what, when).

### Phase 4 — Core modules

Create each module with RESTful controllers, policies, validations, tests.

**Layout Implementation Requirements:**
* [x] Implement admin-side views in `resources/views/admin/` with full CRUD capabilities
* [x] Implement user-side views in `resources/views/user/` with role-based CRUD restrictions
* [x] Create role-based middleware to route users to appropriate layout (admin vs user)
* [x] Ensure consistent UI/UX between admin and user layouts while maintaining different permission levels

#### 4.1 College Input

* [x] DB model & migration: `colleges` (name, code, address, contact, coordinator\_id, meta JSON).
* [x] CRUD API + UI pages (list, create, edit, view, delete).
* [x] Assign overall coordinator & link to users.
* [x] Validation rules & tests.

#### 4.2 Academic Year

* [x] Model & migration: `academic_years` (label, start\_date, end\_date, active boolean).
* [x] CRUD UI, default active year logic.

#### 4.3 Area Level Input

* [x] Model & migration: `areas` (code, title, description, parent\_area\_id optional).
* [x] Link area → college and academic\_year where applicable.
* [x] CRUD pages and permission checks.

#### 4.4 Parameter Input

* [x] Model & migration: `parameters` (area\_id, code, name, description, weight, required boolean).
* [x] Admin UI for parameter templates and bulk import/export.

#### 4.5 Parameter Content Input (with file uploads)

* [x] Model & migration: `parameter_contents` (parameter\_id, uploaded\_by, title, description, file\_storage\_type, external\_drive\_id, tags, visibility, approved boolean).
* [x] File store abstraction: local storage + Google Drive adapter.
* [x] Implement upload UI (multiple files), with client & server validation (file size & type restrictions set in Admin Settings).
* [x] Ownership rules:

  * Faculty: can upload, edit, delete **their own** parameter contents.
  * Chairperson: can upload missing or required files (for college they represent) but should be logged.
  * Accreditor (Lead & Member): can **view** files if tagged to accreditation. If not tagged, must request access.
  * Admin: full control (view/edit/delete).
* [x] Tagging system to tag a uploaded file to specific accreditation or user.
* [x] Implement preview (PDF/image) and download endpoints with permission checks.

### Phase 5 — Accreditation workflows & access requests

* [x] Model: `accreditations` (college\_id, academic\_year\_id, status, assigned\_lead\_id, assigned\_members JSON).
* [x] Tagging flow: `accreditation_tags` linking accreditations → parameter\_contents.
* [x] Overall coordinator UI to tag colleges and assign accreditors.
* [x] Accreditors view: read-only dashboard for assigned accreditations.
* [x] Request Access flow:

  * [x] Button `Request Access` on file view for users without permission.
  * [x] Create `access_requests` model (file\_id, requester\_id, reason, status, approver\_id, expires\_at).
  * [x] Notification: email + in-app to file owner and admin/approver.
  * [x] Approve/Reject UI with logging and optional temporary share link generation (expires).
* [x] Approval rules: Owner or Admin can approve; approval generates grant and logs.

### Phase 6 — SWOT Analysis & Area Ranking

* [x] SWOT model `swot_entries` (college\_id, area\_id, type: S/W/O/T, description, created\_by).
* [x] CRUD UI for Chairperson and Faculty to add their SWOT items.
* [x] Accreditor review queue for SWOT items.
* [x] Area ranking algorithm:

  * [x] Define ranking inputs (parameter completion %, quality score, accreditor rating).
  * [x] Create `area_rankings` table or view that computes and stores rank snapshots.
  * [x] Weekly or on-demand ranking job (Queue + Cron).
* [x] Exportable ranking & SWOT reports (PDF/CSV).

### Phase 7 — Admin settings & appearance

(Features already required — implement in admin settings UI)

* [x] Theme Management: change primary maroon color (via DB stored Bootstrap variables), preview, and publish.
* [x] Logo & favicon upload + versioning.
* [x] SMTP settings UI + test email.
* [x] 2FA management panel + backup codes UI.
* [x] Maintenance mode with whitelist and message editor.
* [x] File upload policy manager (allowed types, max size, retention policy).
* [x] Dark/light toggle stored in DB per user / system default.

### Phase 8 — File handling & Google Drive integration

* [ ] Implement storage adapter pattern: `local`, `s3` (optional), `gdrive`.
* [ ] Google Drive tasks:

  * [ ] OAuth2 service account or OAuth consent for user-owned Drive depending on policy.
  * [ ] Save `drive_file_id` and permission share links in `parameter_contents`.
  * [ ] Implement function to create a permission request to Drive (request access flow maps to Drive share request when file is in external Drive).
  * [ ] If Drive file is not shared to requester, `Request Access` should trigger email to Drive owner with one-click share link (or create a comment request on Drive if using sharing APIs).
* [ ] Secure proxy endpoint for Drive file downloads (do not expose raw Drive links publicly) — validate permission before streaming.

### Phase 9 — Activity logs & auditing

* [ ] Global `activity_logs` table (user\_id, model\_type, model\_id, action, meta JSON, ip\_address, user\_agent, created\_at).
* [ ] Log events:

  * File uploads/downloads, edits, deletes
  * Access requests and approvals
  * Role/permission changes
  * Accreditation tagging actions
  * Login successes/failures
* [ ] Admin UI for searching/filters and export.

### Phase 10 — Testing, QA & deployment

* [ ] Unit tests for models, policies, and permission logic.
* [ ] Integration tests for file access and request flows (including Drive mocks).
* [ ] End-to-end tests (Cypress / Playwright) for key user journeys by role.
* [ ] Penetration checks for file upload & XSS injection vectors.
* [ ] Deployment checklist and rollback plan.

---

## 5. Permission matrix (concise)

> Legend: V = View, C = Create, E = Edit, D = Delete, R = Request access (to files not tagged)

| Feature / Role                    |          Admin | Dean | Accreditor Lead | Accreditor Member |       Chairperson |       Faculty |  Overall Coord |
| --------------------------------- | -------------: | ---: | --------------: | ----------------: | ----------------: | ------------: | -------------: |
| College CRUD                      |          C/E/D |    V |               V |                 V |                 V | V (view only) |          C/E/V |
| Academic Year                     |          C/E/D |    V |               V |                 V |                 V |             V |              V |
| Area Level                        |          C/E/D |    V |               V |                 V |               C/E |             V |              V |
| Parameter                         |          C/E/D |    V |               V |                 V |                 V |             V |              V |
| Parameter Content (own)           |          C/E/D |    V |   V (if tagged) |     V (if tagged) | C/E (for college) |         C/E/D |              V |
| Parameter Content (others)        |          C/E/D |    V |               R |                 R |                 R |             - |              V |
| Request Access                    | Approve/Reject |    - |          Create |            Create |            Create |        Create | Approve/Assign |
| Tag Colleges / Assign Accreditors |            C/E |    V |               V |                 V |                 - |             - |            C/E |
| SWOT                              |          C/E/D |    V |               V |                 V |               C/E |         C/E/D |              V |
| Area Ranking                      |            C/E |    V |               V |                 V |                 V |             V |              V |
| Admin Settings                    |          C/E/D |    - |               - |                 - |                 - |             - |              - |

> Note: implement per-feature permissions (e.g., `parameter_contents.view`, `parameter_contents.request`, `parameter_contents.edit.own`) for fine control.

---

## 6. Single File Policy & migrations/seeders guidance

* **Migrations:** Each migration **must** be contained in a single file that creates/updates related objects. Keep migrations atomic and reversible. Example: one migration file creates `colleges` table.
* **Seeders:** Consolidate seed data into single files per domain area (e.g., `RolesPermissionsSeeder.php`, `InitialAdminSeeder.php`).
* **Settings files:** Consolidate configuration defaults into `settings.php` DB-driven seeds rather than multiple small files.
* **Directory structure:** Keep minimal files — group related controllers into subfolders, consolidate helpers.

---

## 7. Developer checklist & acceptance criteria

* [ ] All role-based access control paths covered by unit/integration tests.
* [ ] Access requests generate an email & in-app notification, stored in DB.
* [ ] Drive-based files are never exposed without permission; downloads go through secured endpoint.
* [ ] Audit logs show at least: actor, action, target, timestamp, IP.
* [ ] PWA works offline for read-only cached views and upload queue for offline submissions.
* [ ] Admin settings cover theme, branding, SMTP, file restrictions, 2FA.
* [ ] All migrations & seeders are single-file per domain policy.

---

## 8. Appendix: Suggested API endpoints & DB notes

**Sample endpoints** (RESTful)

* `GET /api/colleges`
* `POST /api/colleges`
* `PUT /api/colleges/{id}`
* `DELETE /api/colleges/{id}`
* `GET /api/parameters/{parameter_id}/contents`
* `POST /api/parameters/{parameter_id}/contents` (multipart upload)
* `POST /api/contents/{id}/request-access`
* `POST /api/contents/{id}/approve-access`
* `GET /api/accreditations/{id}/report`

**DB notes** (high-level)

* `users`, `roles`, `permissions`
* `colleges`, `academic_years`, `areas`, `parameters`, `parameter_contents`
* `accreditations`, `accreditation_tags`
* `swot_entries`, `area_rankings`
* `access_requests`, `activity_logs`

---

## Final notes

* Start with a **minimum viable product** (MVP): auth, roles, college + parameter CRUD, file uploads local, request flow, basic dashboards.
* Iterate: add Drive integration, PWA offline sync, advanced ranking algorithm, heavy reporting later.
* Keep the Single File Policy in mind for migrations and seeders to maintain repository hygiene.

---

*Document generated: Accreditation-System-Roadmap.md*
