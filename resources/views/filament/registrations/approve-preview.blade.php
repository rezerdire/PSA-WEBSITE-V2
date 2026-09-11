S<div class="space-y-3 text-sm">
    <div><span class="font-semibold">To:</span> {{ $member?->mem_email_address ?? 'No email on file' }}</div>
    <div><span class="font-semibold">Subject:</span> Your PSA Membership ID Card</div>
    <div class="border rounded-lg p-3 bg-gray-50">
        Hello {{ $member?->full_name ?? $registration->full_name }},<br><br>
        Your PSA membership registration has been approved. Your official membership ID card, including your QR code, is attached to this email.
    </div>
</div>