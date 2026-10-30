<x-mail::message>
# Workshop registration confirmed

Hello {{ $name }},

Thank you for registering. Your selected workshop slots have been recorded.

<x-mail::panel>
@foreach ($workshops as $workshop)
**Workshop:** {{ $workshop['label'] }}

@if ($workshop['session'])
**Schedule:** {{ $workshop['session'] }}
@endif

@if ($workshop['venue'])
**Venue:** {{ $workshop['venue'] }}
@endif

@if (!$loop->last)
---
@endif
@endforeach
</x-mail::panel>

## Registration details

**Email:** {{ $registration->email }}

For further inquiries, please contact us at **psainc_sec@yahoo.com**.

Regards,

PSA Secretariat
</x-mail::message>