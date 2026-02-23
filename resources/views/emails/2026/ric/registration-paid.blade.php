{{-- <x-mail::message>
    <p class="sub">Dear {{ $details['name'] }},</p>

    <p class="sub" style="margin-top: 15px;">Thank you for registering to attend the <a
            href="{{ $details['eventLink'] }}" target="_blank">{{ $details['eventName'] }}</a>, taking place from
        <strong>18-19 May 2026</strong> {{ $details['eventLocation'] }}. By registering as a delegate, you are subject
        to the terms and conditions outlined in the invoice.
    </p>

    <p class="sub" style="margin-top: 15px;"><strong>
            We are pleased to confirm that your registration has been confirmed. Please find below summary of your
            booking confirmation.
        </strong></p>

    <p class="title" style="margin-top: 20px;">Your registration details as follows:</p>
    <p class="sub">Full name: {{ $details['name'] }}</p>
    <p class="sub">Job title: {{ $details['jobTitle'] }}</p>
    <p class="sub">Company name: {{ $details['companyName'] }}</p>
    @if ($sendInvoice)
        <p class="sub">Amount paid: $ {{ number_format($details['amountPaid'], 2, '.', ',') }}</p>
    @endif
    <p class="sub">Transaction ID: {{ $details['transactionId'] }}</p>

    @if ($sendInvoice)
        <br>
        <x-mail::button :url="$details['invoiceLink']" color="registration">
            Download invoice
        </x-mail::button>
    @endif

    <p class="sub" style="margin-top: 15px;">To request updates to your registration details, kindly contact <a
            href="mailto:jovelyn@gpca.org.ae">jovelyn@gpca.org.ae</a>on or before 17<sup>th</sup> April to ensure your
        badge information is accurate.</p>

    <p class="sub" style="margin-top: 20px; text-decoration: underline;"><strong>Collection of badges</strong></p>
    <p class="sub" style="margin-top: 5px;">Upon arrival, please proceed to the registration desk located in the
        Foyer to collect your event badge. Kindly present your ID or email confirmation for verification.</p>

    <p class="sub" style="margin-top: 20px; text-decoration: underline;"><strong>Visa Inquiries</strong></p>
    <p class="sub" style="margin-top: 5px;">For any visa related inquiries, please contact our designated travel
        partner at Cozmo Travel. You may reach <strong>John Uytiongco</strong> at <a
            href="mailto:juytiongco@cozmotravel.com">juytiongco@cozmotravel.com</a> or call +971 4 406 5802.</p>


    <p class="sub" style="margin-top: 20px; text-decoration: underline;"><strong>Hotel Booking</strong></p>
    <p class="sub" style="margin-top: 5px;">For hotel accommodation, please click the booking <a
            href="https://www.marriott.com/event-reservations/reservation-link.mi?id=1771313552687&key=GRP&app=resvlink&_branch_match_id=1552210776338362721&_branch_referrer=H4sIAAAAAAAAA8soKSkottLXTywo0MtNLCrKzC8p0UvOz9UvSi3OyczLtgdK2ALZZSCOWmaKraG5uaGxobGpqZGZhbladmqlrXtQgFpdUWpaKlB3Xnp8UlF%2BeXFqka1rSnoqAChM6UVeAAAA">link</a>
        to secure the special hotel rate at the Le Meridien, Al Khobar, Saudi Arabia. </p>

    <p class="sub" style="margin-top: 5px;">If you have any questions or need assistance with the booking, kindly
        coordinate with <strong>Adnan Ahmed</strong>
        <a href="mailto:adnan.ahmed2@lemeridien.com">adnan.ahmed2@lemeridien.com</a>or call + 966 504940516.
    </p>


    <p class="sub" style="margin-top: 20px;">For any event-related queries, please reach out to the following team
        members:</p>

    <p class="sub" style="margin-top: 10px;"><strong>Sponsorship, Exhibition, and Delegate Inquiries: </strong></p>

    <ul class="event-list">
        <li style="margin-top: 5px;">Salman Khan and Jerry Rodrigues</li>
        <li>Email: <a href="mailto:salman@gpca.org.ae">salman@gpca.org.ae</a>, <a
                href="mailto:jerry@gpca.org.ae">jerry@gpca.org.ae</a></li>
        <li>Telephone: +971 4 451 0666 ext 103 & 106</li>
    </ul>

    <p class="sub" style="margin-top: 20px;">Stay updated on upcoming GPCA events and industry news by following our
        <a href="https://www.linkedin.com/company/gulf-petrochemicals-and-chemicals-association-gpca-/">LinkedIn
            Page</a>. You can also connect with us on our official social media accounts: <a
            href="https://twitter.com/GulfPetChem">Twitter</a>, <a
            href="https://www.instagram.com/gulfpetchem/">Instagram</a>, <a
            href="https://www.facebook.com/GulfPetChem?fref=ts">Facebook</a>, and <a
            href="https://www.youtube.com/user/GPCAorg">YouTube</a>.
    </p>

    <p class="sub" style="margin-top: 20px;">Best Regards,</p>
    <p class="sub">GPCA Team</p>
</x-mail::message> --}}
<x-mail::message>

<p style="margin:0 0 12px 0; font-size:18px; line-height:1.6;">
Dear {{ $details['name'] }},
</p>

<p style="margin:0 0 12px 0; font-size:18px; line-height:1.6;">
Thank you for registering to attend the 
<a href="{{ $details['eventLink'] }}" target="_blank">{{ $details['eventName'] }}</a> 
taking place from 18–19 May 2026 at the {{ $details['eventLocation'] }}.
</p>

<p style="margin:0 0 24px 0; font-size:18px; line-height:1.6;">
We are pleased to confirm that your registration has been confirmed. Please find below a summary of your booking confirmation.
</p>

<p style="margin:0 0 8px 0; font-weight:600; font-size:20px; line-height:1.4; color:#3D4852;">
Your registration details are as follows:
</p>

<p style="margin:0 0 24px 0; font-size:18px; line-height:1.6;">
<strong>Full Name:</strong> {{ $details['name'] }}<br>
<strong>Job Title:</strong> {{ $details['jobTitle'] }}<br>
<strong>Company Name:</strong> {{ $details['companyName'] }}<br>
@if ($sendInvoice)
<strong>Amount Paid:</strong> ${{ number_format($details['amountPaid'], 2, '.', ',') }}<br>
@endif
<strong>Transaction ID:</strong> {{ $details['transactionId'] }}
</p>

@if ($sendInvoice)
<x-mail::button :url="$details['invoiceLink']" color="registration">
Download Invoice
</x-mail::button>
@endif

<p style="margin:24px 0 24px 0; font-size:18px; line-height:1.6;">
To request updates to your registration details, kindly contact 
<a href="mailto:jovelyn@gpca.org.ae">jovelyn@gpca.org.ae</a> 
on or before 17<sup>th</sup> April to ensure your badge information is accurate.
</p>

<p style="margin:0 0 6px 0; font-weight:600; font-size:18px;">
Collection of Badges
</p>

<p style="margin:0 0 24px 0; font-size:18px; line-height:1.6;">
Upon arrival, please proceed to the registration desk located in the Foyer to collect your event badge. Kindly present your ID or email confirmation for verification.
</p>

<p style="margin:0 0 6px 0; font-weight:600; font-size:18px;">
Visa Inquiries
</p>

<p style="margin:0 0 24px 0; font-size:18px; line-height:1.6;">
For any visa-related inquiries, please contact our designated travel partner at Cozmo Travel. You may reach 
<strong>John Uytiongco</strong> at 
<a href="mailto:juytiongco@cozmotravel.com">juytiongco@cozmotravel.com</a> 
or call +971 4 406 5802.
</p>

<p style="margin:0 0 6px 0; font-weight:600; font-size:18px;">
Hotel Booking
</p>

<p style="margin:0 0 24px 0; font-size:18px; line-height:1.6;">
For hotel accommodation, please click the booking 
<a href="https://www.marriott.com/event-reservations/reservation-link.mi?id=1771313552687&key=GRP&app=resvlink&_branch_match_id=1552210776338362721&_branch_referrer=H4sIAAAAAAAAA8soKSkottLXTywo0MtNLCrKzC8p0UvSi3OyczLtgdK2ALZZSCOWmaKraG5uaGxobGpqZGZhbladmqlrXtQgFpdUWpaKlB3Xnp8UlF%2BeXFqka1rSnoqAChM6UVeAAAA">
link
</a>
to secure the special hotel rate at the Le Meridien, Al Khobar, Saudi Arabia.
</p>

<p style="margin:0 0 24px 0; font-size:18px; line-height:1.6;">
If you have any questions or need assistance with the booking, kindly coordinate with 
<a href="mailto:adnan.ahmed2@lemeridien.com">adnan.ahmed2@lemeridien.com</a> 
or call +966 504940516.
</p>

<p style="margin:0 0 12px 0; font-size:18px; line-height:1.6;">
For any event-related queries, please reach out to the following team members:
</p>

<p style="margin:0 0 6px 0; font-weight:600; font-size:18px;">
Sponsorship, Exhibition, and Delegate Inquiries
</p>

<p style="margin:0 0 24px 0; font-size:18px; line-height:1.6;">
• Salman Khan and Jerry Rodrigues<br>
• Email: 
<a href="mailto:salman@gpca.org.ae">salman@gpca.org.ae</a>, 
<a href="mailto:jerry@gpca.org.ae">jerry@gpca.org.ae</a><br>
• Telephone: +971 4 451 0666 ext 103 & 106
</p>

<p style="margin:0 0 24px 0; font-size:18px; line-height:1.6;">
Stay updated on upcoming GPCA events and industry news by following our 
<a href="https://www.linkedin.com/company/gulf-petrochemicals-and-chemicals-association-gpca-/">
LinkedIn Page
</a>. You can also connect with us on our official social media accounts: 
<a href="https://twitter.com/GulfPetChem">Twitter</a>, 
<a href="https://www.instagram.com/gulfpetchem/">Instagram</a>, 
<a href="https://www.facebook.com/GulfPetChem?fref=ts">Facebook</a>, and 
<a href="https://www.youtube.com/user/GPCAorg">YouTube</a>.
</p>

<p style="margin:0 0 16px 0; font-size:18px; line-height:1.6;">
Thank you, and we look forward to welcoming you in Saudi Arabia for the 8th GPCA Research & Innovation Conference.
</p>

<p style="margin:0; font-size:18px; line-height:1.6;">
Best regards,<br>
<strong>GPCA Team</strong>
</p>

</x-mail::message>