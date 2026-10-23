<x-mail::message>
# Welcome to the Zytecs Research team portal

Hello {{ \App\Support\MarkdownText::escape($recipientName) }},

You have been added as a team member. In the portal you will see the orders assigned to you, the brief and instructions, and you can upload your guidance and feedback materials for review.

<x-mail::button :url="$setPasswordUrl">
Set your password
</x-mail::button>

This link expires in {{ $expiresMinutes }} minutes. If it expires, use "Forgot password" on the sign-in page with this email address.

Please keep all client work confidential.

Zytecs Research
</x-mail::message>
