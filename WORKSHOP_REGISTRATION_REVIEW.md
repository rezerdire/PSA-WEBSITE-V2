# Workshop Registration Review

## Verdict

**Score: 6/10 — functional local prototype; not release-ready yet.**

The main registration path works: the page loads, workshop capacity is shown, PSA member lookup can populate the form, validation rejects invalid input, and a valid registration is persisted. The largest release blockers are a stale PSA ID display after a failed lookup recovery, incomplete workshop information, weak automated coverage, and data-integrity/security hardening.

## Scope and test setup

- Route tested: `GET /CME/Workshop`
- Application: local Laravel server at `http://127.0.0.1:8000`
- Database: local MySQL configured by `.env`
- Browser sizes: desktop `1440x1000`; mobile `390x844`
- Database state after testing: `workshop_registrations` returned to **0 records**. A temporary smoke registration was created and removed after the success-path test.

## Test results

| Scenario | Result | Evidence |
|---|---:|---|
| Workshop page loads | PASS | Browser received the page with HTTP 200 and rendered the form. |
| Desktop layout | PASS | Form, capacity cards, PSA checker, footer, and RA details rendered without visible overflow. |
| Mobile layout | PASS | Navigation collapsed to a hamburger; form fields stacked correctly at 390px wide. |
| Search member by last name | PASS | Search returned a seeded member result; selecting it populated name, member type, and PRC number. |
| Search member by email | PASS | Email search returned the matching seeded member and selection populated the registration form. |
| Verify a valid PSA ID | PASS | A valid seeded ID populated the member details. |
| Verify an unknown PSA ID | PASS | `PSA ID not found.` was shown. |
| Empty form submission | PASS | Required-field messages appeared for the missing fields. |
| Invalid phone number | PASS | Server-side validation returned `Please enter a valid phone number.` |
| Invalid email format | PARTIAL | The browser's native `type="email"` constraint prevented the Livewire request, so the custom server-side email message was not shown. |
| Workshop selection | PASS | RA, POCUS, and Airway could be selected; RA displayed its program/session/station panel. |
| Valid registration | PASS | A temporary valid registration reached `Registration Complete` and created one database row; the row was removed afterward. |
| Capacity display | PASS | All three workshops showed `0 / 60 registered` and `60 slots left` during the test. Full-capacity and concurrent-submission behavior were not stress-tested through the browser. |
| Production build | PASS | `npm run build` completed successfully. |

## Findings and improvements

### P1 — Fix the stale PSA ID after lookup recovery

**Observed sequence:**

1. Enter an unknown manual ID (`9999`) and click **Verify**.
2. Use the member checker to select a valid member.
3. The name/member type/PRC fields update to the valid member, but the read-only PSA ID field still visually shows the old `9999` value.

The server-side member state is updated, but the DOM value of the reused input is stale. This can make a registrant believe the wrong member was selected.

**Relevant code:** `resources/views/components/workshop/workshop-registration.blade.php:99-118, 258-274`

**Fix:** reset `psaIdInput` when a valid member is selected and force a distinct DOM key when switching between the editable PSA ID input and the read-only selected-member input. Add a regression test for invalid lookup -> valid checker selection.

### P1 — Make PSA ID uniqueness database-enforced

The component checks for an existing PSA ID at `resources/views/components/workshop/workshop-registration.blade.php:171-174`, but the check happens before the registration lock. The migration only makes `prc_number` unique; `psa_id` is nullable and has no unique index.

Two concurrent submissions for the same member with different PRC numbers can pass the application-level check and create duplicate member registrations.

**Fix:** add a unique database index for non-null PSA IDs, or enforce the duplicate check inside the same protected transaction/lock as the insert. Keep the PRC unique constraint as the second line of defense.

### P1 — Add workshop-specific automated tests

There are currently no workshop tests. The only feature test is the default homepage test in `tests/Feature/ExampleTest.php`.

Add tests covering:

- GET `/CME/Workshop` returns 200.
- Valid and invalid PSA ID lookup.
- Member auto-fill and clear behavior.
- Required fields, email, phone, and workshop validation.
- Duplicate PSA ID and duplicate PRC rejection.
- One workshop per registration.
- Capacity limit and full-workshop behavior.
- Successful persistence and success state.
- The stale-ID recovery scenario above.

Use factories/fixtures so tests do not depend on the live MySQL member database.

### P1 — Repair the baseline feature test database setup

`php artisan test` produced **1 passing and 1 failing test**. The failing test is `tests/Feature/ExampleTest.php`; the homepage request returned 500 because the SQLite in-memory test database had no `gallery_images` table.

This failure is outside the workshop route, but it means the project's default automated suite is not green and can hide regressions. Either migrate the required gallery tables in the test database or change the test fixture so the homepage does not query missing tables.

### P2 — Provide complete information for every workshop

`WORKSHOP_DETAILS` contains a full program/session/station panel only for RA. POCUS and Airway have empty `program`, `session`, `venue`, and `stations` values, so selecting either option shows no schedule, venue, or preparation information.

Add, for each workshop:

- Date and time
- Venue
- Faculty/speakers
- Program or station structure
- Required equipment or preparation
- Any eligibility/prerequisite information

### P2 — Improve the completion state

The success state only says that the slot was recorded. It does not show the selected workshop, schedule, venue, registration/reference number, payment instructions, cancellation policy, or whether a confirmation email was sent.

Show a concise confirmation summary and a stable reference number. If email confirmation is part of the business process, send it and display its status; otherwise state the next step clearly.

### P2 — Decide whether member fields are editable

After member lookup, last name, first name, middle name, and PRC number remain editable even though they are auto-filled from the member database. The component comment describes these as auto-filled member data, but the template only makes PSA ID and member type read-only.

Either:

- Make database-derived identity fields read-only and provide a clear correction path; or
- Label them as editable and explicitly define which submitted values are authoritative.

### P2 — Improve validation consistency

The email input is `type="email"`, so malformed email text is stopped by browser-native constraint validation before Livewire calls `submit()`. The server has a custom email validation message, but that message is not reached in this case.

Choose one consistent experience:

- Keep native validation and add matching `required`/constraint attributes and accessible error handling; or
- Use `novalidate` and let Livewire own all validation messages.

Also add `aria-invalid` and `aria-describedby` when a field has an error.

### P2 — Improve accessibility

The labels are visually present but do not have matching `for`/`id` associations. The checker toggle and search tabs do not expose `aria-expanded`, `aria-controls`, or selected-tab state. Validation messages are not announced through an `aria-live` region.

Add explicit input IDs, associated labels, fieldset/legend markup for workshop radios, accessible toggle/tab state, keyboard/focus verification, and announced validation results.

### P2 — Add privacy and operational messaging

The form collects PSA ID, PRC number, hospital, email, and phone number but presents no privacy notice, consent language, retention statement, or support contact. Add the minimum notice required by the organization's privacy process before public launch.

### P0 before deployment — Remove secrets from application configuration

The local `.env` contains an SMTP credential and has `APP_DEBUG=true`. Do not commit or deploy those values. Rotate the SMTP credential if the file has ever been shared or committed, use environment/secret management in deployment, and set production debug mode to false.

## Recommended order

1. Fix the stale PSA ID display and add its regression test.
2. Enforce unique PSA IDs at the database boundary and test concurrent/duplicate behavior.
3. Add workshop feature tests and repair the SQLite test migrations so `php artisan test` is green.
4. Complete POCUS/Airway schedule and venue data.
5. Add a useful confirmation summary/reference number.
6. Apply accessibility and privacy improvements.
7. Rotate credentials and verify production configuration before deployment.

## Verification commands

```text
php artisan test          # currently fails: missing gallery_images in SQLite test DB
npm run build             # passes
php artisan route:list --path=CME
php artisan migrate:status
```
