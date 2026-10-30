# Workshop Registration Admin

- The Workshop Registrations table does not display PRC numbers.
- The list header shows the remaining capacity for each workshop and the registered count.
- Select a row or use **View registration** to open the right-side slide-over.
- The slide-over uses **Registrant** and **Workshops** tabs.
- The **Workshops** tab shows one card for each selected workshop, including schedule, venue, and capacity.
- Use **Edit** to correct a registration and **Delete** to remove a cancellation.
- Excel export still includes PRC number and the complete workshop selection for authorized administrators.
- Public workshop registration requires an active Annual Convention registration (`Pending` or `Approved`). PSA members are matched by PSA ID; non-members are matched by the PRC number from their annual guest registration.
- PSA member identity and member type are read from the `members` table, and existing workshop registrations block duplicate PSA ID or PRC submissions.
- After deploying database imports, run `php artisan migrate --force`; the registration repair migration restores missing Filament record IDs and supports non-member PSA IDs.
- The Workshop Registrations table includes **Resend confirmation** for records with an email address. It sends the same selected-workshop confirmation again and reports SMTP failures in the Filament notification and application log.
