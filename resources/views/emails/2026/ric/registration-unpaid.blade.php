{{-- <x-mail::message>
    <p class="sub">Dear {{ $details['name'] }},</p>

    <p class="sub" style="margin-top: 15px;">Thank you for registering to attend the <a
            href="{{ $details['eventLink'] }}" target="_blank">{{ $details['eventName'] }}</a>, 18-19 May 2026 at the
        {{ $details['eventLocation'] }}.</p>

    <p class="sub" style="margin-top: 15px; color: red;">Please be advised that your registration is not yet confirmed.
        To avoid any inconvenience during onsite badge collection, please settle your payment or contact our finance
        team at <a href="mailto:analee@gpca.org.ae">analee@gpca.org.ae</a>.</p>


    <p class="sub" style="margin-top: 5px; color: red;">
        If you register at the early bird rate but do not settle your payment on or before the early bird deadline date,
        your registration fee will be adjusted to the standard rate.</p>


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

    <p class="sub" style="margin-top: 15px;">To request updates on your registration details, contact <a
            href="mailto:jovelyn@gpca.org.ae">jovelyn@gpca.org.ae</a> on or before 17<sup>th</sup> April to ensure your
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

Dear {{ $details['name'] }},

Thank you for registering to attend the 
<a href="{{ $details['eventLink'] }}" target="_blank">
{{ $details['eventName'] }}
</a> taking place from 18–19 May 2026 at the {{ $details['eventLocation'] }}.

<p style="color:#C00000;">
Please be advised that your registration is not yet confirmed. To avoid any inconvenience during onsite badge collection, please settle your payment or contact our finance team at 
<a href="mailto:analee@gpca.org.ae" style="color:#C00000;">analee@gpca.org.ae</a>.
</p>

<p style="color:#C00000;">
If you register at the early bird rate but do not settle your payment on or before the early bird deadline date, your registration fee will be adjusted to the standard rate.
</p>

Your registration details are as follows:

• <strong>Full Name:</strong> {{ $details['name'] }}  
• <strong>Job Title:</strong> {{ $details['jobTitle'] }}  
• <strong>Company Name:</strong> {{ $details['companyName'] }}  
@if ($sendInvoice)
• <strong>Amount Paid:</strong> ${{ number_format($details['amountPaid'], 2, '.', ',') }}  
@endif
• <strong>Transaction ID:</strong> {{ $details['transactionId'] }}

@if ($sendInvoice)

<x-mail::button :url="$details['invoiceLink']" color="registration">
Download Invoice
</x-mail::button>

@endif

<br/>
To request updates to your registration details, kindly contact 
<a href="mailto:jovelyn@gpca.org.ae">jovelyn@gpca.org.ae</a> 
on or before 17<sup>th</sup> April to ensure your badge information is accurate.

<br/>
<br/>
<strong>Collection of Badges</strong>

Upon arrival, please proceed to the registration desk located in the Foyer to collect your event badge. Kindly present your ID or email confirmation for verification.

<strong>Visa Inquiries</strong>

For any visa related inquiries, please contact our designated travel partner at Cozmo Travel. You may reach 
<strong>John Uytiongco</strong> at 
<a href="mailto:juytiongco@cozmotravel.com">juytiongco@cozmotravel.com</a> 
or call +971 4 406 5802.

<strong>Hotel Booking</strong>

For hotel accommodation, please click the booking 
<a href="https://www.marriott.com/event-reservations/reservation-link.mi?id=1771313552687&key=GRP&app=resvlink&_branch_match_id=1552210776338362721&_branch_referrer=H4sIAAAAAAAAA8soKSkottLXTywo0MtNLCrKzC8p0UvOz9UvSi3OyczLtgdK2ALZZSCOWmaKraG5uaGxobGpqZGZhbladmqlrXtQgFpdUWpaKlB3Xnp8UlF%2BeXFqka1rSnoqAChM6UVeAAAA">
link
</a>
to secure the special hotel rate at the Le Meridien, Al Khobar, Saudi Arabia.

If you have any questions or need assistance with the booking, kindly coordinate with 
<a href="mailto:adnan.ahmed2@lemeridien.com">adnan.ahmed2@lemeridien.com</a> 
or call +966 504940516.

For any event-related queries, please reach out to the following team members:

Sponsorship, Exhibition, and Delegate Inquiries:  
• Salman Khan and Jerry Rodrigues  
• Email: 
<a href="mailto:salman@gpca.org.ae">salman@gpca.org.ae</a>, 
<a href="mailto:jerry@gpca.org.ae">jerry@gpca.org.ae</a>  
• Telephone: +971 4 451 0666 ext 103 & 106

Stay updated on upcoming GPCA events and industry news by following our 
<a href="https://www.linkedin.com/company/gulf-petrochemicals-and-chemicals-association-gpca-/">
LinkedIn Page
</a>. You can also connect with us on our official social media accounts: 
<a href="https://twitter.com/GulfPetChem">Twitter</a>, 
<a href="https://www.instagram.com/gulfpetchem/">Instagram</a>, 
<a href="https://www.facebook.com/GulfPetChem?fref=ts">Facebook</a>, and 
<a href="https://www.youtube.com/user/GPCAorg">YouTube</a>.

Thank you, and we look forward to welcoming you in Saudi Arabia for the 8th GPCA Research & Innovation Conference.

Best regards,  
GPCA Team

</x-mail::message>