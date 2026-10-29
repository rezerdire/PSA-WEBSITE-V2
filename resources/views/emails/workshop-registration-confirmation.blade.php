<x-mail::message>
# Workshop registration confirmed

Hello {{ $name }},

Thank you for registering. Your workshop slot has been recorded.

<x-mail::panel>

**Workshop:** {{ $workshop }}

@if ($session)
**Schedule:** {{ $session }}
@endif

@if ($venue)
**Venue:** {{ $venue }}
@endif
</x-mail::panel>

## Registration details

For further inquiries, please contact us at **psainc_sec@yahoo.com**.

Regards,

PSA Secretariat
</x-mail::message>