# Bulk QR sending

The registrations list has **Bulk Send QR**. It selects up to the requested limit from registrations that are:

- `Approved` status
- `RM`, `LM`, or `TM` membership
- older `created_at` first, then lower `id`
- not already recorded in `registration_qr_sends`
- not already reserved by another queued or running bulk batch

The default limit is 50 and the allowed range is 1–500. The monitoring page shows queued, processing, sent, failed, and skipped recipients. The first approved member registration excluded by the limit is highlighted and included in the CSV export as `not_selected_limit`.

The database queue worker must be running:

```bash
php artisan queue:work
```

Invalid or missing addresses, missing member records, missing generated QR/ID cards, QR generation errors, and SMTP rejections are recorded per recipient. A recipient with no generated QR/ID card is marked failed and no email is sent. A generic SMTP connection cannot detect a delayed bounce after the provider accepts a message; automatic delayed-bounce tracking requires a provider webhook or bounce mailbox integration.

After deploying, preserve the exact case of `app/Mail/Registrationqrcode.php`, then rebuild and clear Laravel state:

```bash
composer dump-autoload --optimize
php artisan optimize:clear
php artisan queue:restart
```

The admin dashboard includes a **QR Delivery Overview** count of unique registrations with a successful QR send record; resends do not inflate the user count.
