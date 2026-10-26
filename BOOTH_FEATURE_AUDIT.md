# Booth Feature Audit

**Scope:** public Booth check-in flow, Livewire component, Booth/attendance models, QR generation, Filament admin surfaces, routing, responsive UI, and related runtime configuration.

**Change policy:** application source code was not changed. This file is the only deliverable created by the audit.

## Executive verdict

The basic check-in path works locally, but the feature is **not ready for an untrusted public event audience** without fixing the identity/privacy model and event scoping.

Most serious issue: a public user can submit a guessed four-digit PSA ID. A valid ID is accepted as proof of identity, creates an attendance record, and returns the member's name, PSA ID, chapter, and booth journey. The local database contains 6,785 four-character member IDs, so the current input is an enumerable identifier rather than an authentication factor.

## Test environment and method

- Local URL: `http://127.0.0.1:8000`
- Booth exercised: generated code `SUITE1BP` (`BD PHILIPPINES`, `Suite 1`)
- Browser viewports: desktop `1280x900`; mobile `390x844`
- Runtime: Laravel 12.63, PHP 8.5.11, Livewire 4.3
- Database observations: 1 Booth, 6,785 Members, member IDs all length 4, one pre-existing attendance record
- Commands/checks run:
  - `php artisan route:list --path=booth`
  - `php artisan test`
  - PHP syntax checks on Booth route/model/service/table files
  - HTTP probes for valid, lowercase, and invalid Booth codes
  - Chromium UI checks for empty, invalid, valid, duplicate, mobile, and accessibility states
- Test data cleanup: a temporary check-in for a second local test member was removed. Final attendance count was restored to 1; the second member had no attendance for Booth 1.

## Positive checks

| Check | Result |
|---|---|
| Valid Booth URL | `200 OK`; page rendered with the expected booth |
| Lowercase generated code | `200 OK`; component normalizes it to uppercase |
| Unknown Booth code | `404`; no page rendered |
| Empty PSA ID | Inline validation: `Please enter your PSA ID.` |
| Unknown PSA ID | Inline validation: `PSA ID not found. Please check and try again.` |
| New valid member | Attendance created and success state displayed |
| Duplicate scan | Existing attendance was not duplicated; `Already checked in` displayed |
| Mobile horizontal overflow | None observed; document width stayed `390px` |
| Booth PHP syntax | All checked files passed `php -l` |
| Unauthenticated admin Booth URL | Redirected to `/admin/login` with `302` |

## Findings and improvement suggestions

### F-01 — Critical: PSA ID is both public identity proof and personal-data lookup

**Evidence**

- `resources/views/components/booth/booth-scan.blade.php:33-66` validates only `required|string|max:50`, finds a member by the submitted primary key, and calls `BoothAttendance::firstOrCreate()`.
- `resources/views/components/booth/booth-scan.blade.php:38-50` rate-limits only failed lookups by IP. Successful member IDs do not consume the failure limit.
- The UI requires four characters, while the server does not enforce an exact four-digit format.
- The local database contains 6,785 member IDs, all four characters long, ranging from `0001` through `6790` in the current data.
- Browser test with a valid local member ID returned the member name, PSA ID, chapter, and complete booth progress. An invalid ID produced a distinguishable error.

**Impact**

An attacker can enumerate member IDs, disclose personal/member information and visit history, and create fraudulent attendance records for valid IDs. The same issue permits a person who knows another member's ID to check in as that member. IP-only failure throttling does not prevent distributed guessing and does not limit valid-ID submissions.

**Suggestion**

Do not use a short sequential PSA ID as public authentication. Prefer an authenticated member session, a signed short-lived member token, or a member QR token with server-side verification. Return a generic failure response, rate-limit every verification attempt by multiple dimensions (IP, token/session, and event), and avoid returning full member identity plus all visit history on a public page. Add an append-only audit trail for accepted scans and reject inactive/non-eligible member statuses if the business rule requires it.

### F-02 — High: attendance and progress are not scoped to an event

**Evidence**

- `database/migrations/2026_10_20_110942_create_booths_table.php` has no event identifier.
- `database/migrations/2026_10_20_112541_create_booth_attendances_table.php` keys attendance only by `member_id_no` and `booth_id`.
- `resources/views/components/booth/booth-scan.blade.php:81-90` loads all attendance for the verified member and all Booth rows ordered by primary key.

**Impact**

**[INFERENCE]** If Booth records from multiple conventions or campaigns remain in the database, a scan in one event will show visits from other events and can mark the wrong completion percentage. A member can also be prevented from recording a later event's same Booth identity because the unique key has no event dimension.

**Suggestion**

Create an explicit event/campaign model, include the event in the QR URL and attendance key, and filter every lookup, progress query, admin table, and report by the active event. Use a unique constraint such as `(event_id, member_id_no, booth_id)`.

### F-03 — High operational risk: generated QR URLs currently point to localhost

**Evidence**

- `.env:5` sets `APP_URL=http://127.0.0.1:8000`.
- `app/Support/BoothQr.php:11-15` generates scan URLs with the named route.
- Runtime evaluation of `BoothQr::scanUrl('SUITE1BP')` returned `http://127.0.0.1:8000/booth/SUITE1BP`.

**Impact**

Any QR generated while this configuration is active points a phone back to the phone's own localhost, not to the deployed PSA site. Printed QR codes can therefore be unusable at the venue.

**Suggestion**

Set `APP_URL` from the production canonical HTTPS host, fail QR generation when the host is local/private, and verify the decoded URL before printing. Keep separate environment-specific QR assets and regenerate existing codes after correcting the canonical URL.

### F-04 — High for an administrative compromise: QR filename path traversal is not constrained

**Evidence**

- `app/Filament/Resources/Booths/Schemas/BoothForm.php:52-58` permits an administrator to edit `booth_generated_code` with no character whitelist.
- `app/Support/BoothQr.php:18-30` interpolates that value into `public/booths-qr/{CODE}.svg`.
- `app/Support/BoothQr.php:43-50` accepts any stored path beginning with `booths-qr/` for deletion; a traversal value beginning with that prefix is not rejected.

**Impact**

**[INFERENCE]** A compromised or insufficiently trusted admin could supply path separators or `..` segments and cause QR generation/deletion outside the intended QR directory. The suffix limits the direct file type to `.svg`, but arbitrary file writes/deletes in reachable paths are still an integrity risk.

**Suggestion**

Make generated codes server-controlled and restrict them to a safe character set such as uppercase letters, digits, and hyphens. Store a generated identifier rather than using user input as a filesystem path. Resolve and verify the final path is inside the QR directory before every write/delete; reject all traversal, separators, control characters, and URL syntax.

### F-05 — Medium: duplicate Booth route leaves dead and unsafe code

**Evidence**

- `routes/web.php:84-91` declares `GET /booth/{booth_code}` twice with the same route name `booth.scan`.
- `php artisan route:list --path=booth` and router inspection show only the second closure is active, with `web` middleware.
- The first closure reflects the route parameter directly into a response string instead of rendering escaped view output.
- `routes/web.php:8` imports `App\\Livewire\\BoothScan`, which is not used by the active route.

**Impact**

The duplicate definition creates maintenance ambiguity and makes it easy to change the active behavior accidentally. If the raw string closure becomes active later, its direct reflection is an avoidable output-injection risk.

**Suggestion**

Keep one named route, point it directly to the intended view/controller, remove the dead closure, and remove the unused import. Add a route test asserting the expected view and 404 behavior for unknown generated codes.

### F-06 — Medium: QR regeneration action writes the wrong intermediate filename

**Evidence**

- `app/Filament/Resources/Booths/Tables/BoothsTable.php:47-54` calls `BoothQr::generate($record->booth_code)`.
- `app/Support/BoothQr.php:23` uses its argument as the SVG filename.
- The public scan page resolves `booth_generated_code`, not the display `booth_code`.
- `app/Models/Booth.php:68-87` later regenerates the QR from `booth_generated_code` during model saving.

**Impact**

The admin action can create an orphaned QR file named from the display code before the model hook writes the canonical generated-code file. This leaves stale public assets and makes QR storage harder to audit.

**Suggestion**

Use one canonical QR-generation path based on `booth_generated_code`; do not generate a display-code file as an intermediate side effect. Add cleanup tests for regenerate, rename, and delete operations.

### F-07 — Medium: editing a generated code invalidates already printed QR codes

**Evidence**

- `app/Models/Booth.php:79-86` deletes the old QR file when the generated code changes.
- `resources/views/components/booth/booth-scan.blade.php:27-31` resolves only the current generated code.
- There is no alias/redirect table for old generated codes.

**Impact**

A previously printed QR continues to contain the old URL, but that code no longer resolves to the Booth after an edit. Existing signage can therefore become a 404 without an explicit warning.

**Suggestion**

Make the public Booth identifier immutable after first publication, or retain an old-code alias that redirects to the current Booth. Require an explicit reprint/deprecation workflow before invalidating a code.

### F-08 — Medium: admin attendance CRUD conflicts with the intended scan-only audit trail

**Evidence**

- The table comment in `app/Filament/Resources/BoothAttendances/Tables/BoothAttendancesTable.php:157-164` says row actions replace edit/delete with a reset action.
- `app/Filament/Resources/BoothAttendances/BoothAttendanceResource.php:42-48` still registers create and edit routes.
- `app/Filament/Resources/BoothAttendances/Pages/ListBoothAttendances.php:13-17` exposes a create action.
- `app/Filament/Resources/BoothAttendances/Pages/EditBoothAttendance.php:13-17` exposes deletion.
- `app/Filament/Resources/BoothAttendances/Schemas/BoothAttendanceForm.php:15-21` accepts raw member and Booth identifiers.
- No Booth-specific policy or role check was found in the audited `app` tree; the panel provider visibly applies authentication middleware, but no finer-grained authorization was found.

**Impact**

An authenticated panel user can potentially create, edit, or delete scan history outside the public check-in flow. This weakens attendance integrity and creates a mismatch between the table UI and direct resource URLs.

**Suggestion**

Choose one policy: make attendance append-only and expose only a controlled correction/reset action with a reason and audit log, or explicitly support administrative corrections with strict role/policy checks. Remove unused create/edit routes if they are not part of the product. Use relationship-backed Booth/member selectors rather than raw IDs.

### F-09 — Medium: test suite is red and contains no Booth coverage

**Evidence**

- `tests/Feature/ExampleTest.php` only tests `GET /`.
- `tests/Pest.php:14-16` has `RefreshDatabase` commented out.
- `php artisan test` result: 1 passed, 1 failed. The feature test returned `500` because the SQLite test database lacked `gallery_images` while the home page mounted the gallery component.
- No Booth-specific tests were present in the test tree.

**Impact**

The baseline suite cannot prove the application boots in its configured test environment and does not protect the Booth route, validation, idempotency, authorization, event boundary, or privacy behavior.

**Suggestion**

Fix test database setup/migrations first. Add isolated Booth feature tests for valid/invalid/duplicate scans, rate limits, unknown Booth codes, event isolation, member eligibility, and public-data minimization. Keep a regression test for the existing home-page failure.

### F-10 — Medium UI defect: fixed mobile navbar overlaps the Booth heading after submission

**Evidence**

At `390x844`, after submitting a valid ID:

- browser `scrollY`: `127`
- fixed navbar bounds: `top=0`, `bottom=65`
- Booth `h1` bounds: `top=49`, `bottom=85`

The screenshot showed the `BD PHILIPPINES` heading partly under the fixed navbar. The document had no horizontal overflow, so this is a vertical scroll/reflow problem rather than a width problem.

**Suggestion**

Reserve fixed-header space in the page layout, restore the component scroll position after Livewire state changes, or avoid retaining a scroll offset that places the heading underneath the header. Recheck both initial and result states at common phone widths.

### F-11 — Low/Medium accessibility defect: form semantics are incomplete

**Evidence**

- `resources/views/components/booth/booth-scan.blade.php:196-200` uses `type="numeric"`; the browser reports the actual input property as `text`, with no `inputmode`.
- The same input sets `aria-describedby="member-id-help"`, but no element with that ID exists, both before and after validation.
- The mobile navbar toggle in `resources/views/components/partials/navbar.blade.php:68-74` has no accessible name, `aria-expanded`, or `aria-controls`.

**Suggestion**

Use `type="text"` with `inputmode="numeric"` and an appropriate pattern, add a real stable error/help element with the referenced ID, and expose the mobile menu state through an accessible button name and ARIA state. Add automated accessibility checks for the form and navigation.

### F-12 — Low: no “scan another booth” action after a result

**Evidence**

The result branch in `resources/views/components/booth/booth-scan.blade.php:265-525` replaces the form with the success/already state and progress list. There is no reset button, next-Booth link, or scan-again action.

**Impact**

Attendees must reload the page or scan another QR externally before the next check-in. This is especially awkward on kiosk/shared-device workflows.

**Suggestion**

Add a clear `Scan another booth`/`Check in another member` action. Preserve privacy by clearing the verified member state and member input before returning to the form.

### F-13 — Low/Medium scalability risk: every result renders every Booth

**Evidence**

`progress()` performs `Booth::orderBy('booth_id')->get()` and the view renders the entire collection. There is no event filter, pagination, or result-size guard.

**Impact**

**[INFERENCE]** A large event will produce a long mobile page and increasingly large Livewire responses for every successful scan. Primary-key order also becomes an unreliable substitute for an explicit event/display order after deletes or imports.

**Suggestion**

Scope to the active event, store an explicit display order, and use a compact summary plus paginated/searchable booth list for large events.

### F-14 — Low security hardening: unpinned third-party scripts and missing response hardening headers

**Evidence**

- `resources/views/layouts/app.blade.php:8-9` loads `https://unpkg.com/lucide@latest` and jsDelivr `jsQR` from a CDN without Subresource Integrity.
- The observed local Booth response exposed `X-Powered-By: PHP/8.5.11` and did not include visible CSP, `X-Content-Type-Options`, frame-ancestor/frame options, or Referrer-Policy headers.

**Suggestion**

Pin exact dependency versions, self-host or add SRI with a restrictive CSP, remove version disclosure where appropriate, and configure security headers at the deployment edge. Recheck production headers separately because the local server may differ.

### F-15 — Critical global security observation: plaintext SMTP credential in the working `.env`

**Evidence**

`.env:51-58` contains SMTP account configuration including a password. `.env` is ignored by Git in this working tree, but the credential is still present on disk and may have been shared or exposed outside Git.

**Suggestion**

Rotate the SMTP credential immediately, audit access/logs, move secrets to a managed secret store or deployment environment, and verify that the credential has not entered source control, backups, screenshots, or logs. The value is intentionally not reproduced in this report.

## Recommended remediation order

1. **Before public launch:** replace PSA-ID-only verification; stop exposing full member/progress data to unauthenticated callers; add event scoping.
2. **Before printing/reprinting QR codes:** correct the canonical HTTPS `APP_URL`, validate QR targets, enforce safe immutable Booth identifiers, and remove path-based user input.
3. **Protect the admin surface:** add role/policy authorization and make attendance corrections auditable; remove contradictory CRUD routes.
4. **Stabilize quality gates:** repair the test database setup and add Booth feature/security regression tests.
5. **Fix the user experience:** mobile fixed-header overlap, accessible form/menu semantics, and a scan-again action.
6. **Harden deployment:** rotate the SMTP credential, pin/self-host CDN assets, and configure security headers.
