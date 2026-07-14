<x-mail::message>
<p class="sub">Dear {{ $details['name'] }},</p>

<p class="sub" style="margin-top: 15px;">Thank you for registering to attend the <a href="{{ $details['eventLink'] }}" target="_blank">{{ $details['eventName'] }}</a>, taking place from 29 September - 01 October 2026 at the Jubail University City, Saudi Arabia.</p>

<p class="sub" style="margin-top: 15px; color: red;">Please note that by completing your registration as a delegate, you acknowledge and agree that your participation is subject to the terms and conditions outlined in our invoice. Your registration <strong><i>will only be confirmed upon receipt of payment.</i></strong> To avoid any inconvenience during onsite badge collection, please settle your outstanding payment or contact <a href="mailto:analee@gpca.org.ae">analee@gpca.org.ae</a> for any assistance.</p>

<p class="title" style="margin-top: 20px;">Your registration details as follows:</p>
<p class="sub">Full name: {{  $details['name'] }}</p>
<p class="sub">Job title: {{  $details['jobTitle'] }}</p>
<p class="sub">Company name: {{  $details['companyName'] }}</p>
@if ($sendInvoice)
<p class="sub">Amount paid: $ {{ number_format($details['amountPaid'], 2, '.', ',') }}</p>
@endif
<p class="sub">Transaction ID: {{  $details['transactionId'] }}</p>

@if ($sendInvoice)
<br>
<x-mail::button :url="$details['invoiceLink']" color="registration">
Download invoice
</x-mail::button>
@endif

<p class="sub" style="margin-top: 15px;">To request any updates to your registration details, kindly contact <a href="mailto:jovelyn@gpca.org.ae">jovelyn@gpca.org.ae</a> on or before  28<sup>th</sup> August to ensure your badge information is accurate.</p>

<p class="sub" style="margin-top: 20px; text-decoration: underline;"><strong>Collection of badges</strong></p>
<p class="sub" style="margin-top: 5px;">Upon arrival, please proceed to the registration desk located in the Foyer to collect your event badge. Kindly present your ID or email confirmation for verification.</p>

<p class="sub" style="margin-top: 20px; text-decoration: underline;"><strong>Visa Inquiries</strong></p>
<p class="sub" style="margin-top: 5px;">For any visa related inquiries, please contact our designated travel partner at Cozmo Travel. You may reach John Uytiongco at <a href="mailto:juytiongco@cozmotravel.com">juytiongco@cozmotravel.com</a> or call +971 4 406 5802. </p>

<p class="sub" style="margin-top: 20px; text-decoration: underline;"><strong>Hotel Booking</strong></p>
<p class="sub" style="margin-top: 5px;">For hotel accommodation, please click the booking <a href="https://www.marriott.com/event-reservations/reservation-link.mi?id=1783493640009&key=GRP&app=resvlink&_branch_match_id=1593528681424671850&_branch_referrer=H4sIAAAAAAAAA8soKSkottLXTywo0MtNLCrKzC8p0UvOz9UvSi0uy0wtN7IHytiCODmZedlqmSm2huYWxiaWxmYmBgYGlmrZqZW27kEBanVFqWmpQO156fFJRfnlxalFtq4p6akAvU7LkF8AAAA%3D"></a> link to secure the special hotel rate at the Courtyard by Marriot Jubail.</p>
<p class="sub" style="margin-top: 10px;">If you have any questions or need assistance with the booking, kindly coordinate with Iftikhar Ahmed at <a href="mailto:iftikhar.ahmed@marriott.com">iftikhar.ahmed@marriott.com</a> or call +966 50 579 6752.</p>

<p class="sub" style="margin-top: 20px; text-decoration: underline;"><strong>Event-related Inquiries</strong></p>
<p class="sub" style="margin-top: 20px;">For sponsorship, exhibition and delegate inquiries, please contact the team members listed below for assistance:</p>

{{-- <p class="sub" style="margin-top: 10px;"><strong>Sponsorship, Exhibition, and Delegate Inquiries: </strong></p> --}}

<ul class="event-list">
    <li style="margin-top: 5px;">Salman Khan and Jerry Rodrigues</li>
    <li>Email: <a href="mailto:salman@gpca.org.ae">salman@gpca.org.ae</a>, <a href="mailto:jerry@gpca.org.ae">jerry@gpca.org.ae</a></li>
    <li>Telephone: +971 4 451 0666 ext 103 & 106</li>
</ul>

<p class="sub" style="margin-top: 20px;">Stay updated on upcoming GPCA events and industry news by following our <a href="https://www.linkedin.com/company/gulf-petrochemicals-and-chemicals-association-gpca-/">LinkedIn Page</a>. You can also connect with us on our official social media accounts: <a href="https://twitter.com/GulfPetChem">Twitter</a>, <a href="https://www.instagram.com/gulfpetchem/">Instagram</a>, <a href="https://www.facebook.com/GulfPetChem?fref=ts">Facebook</a>, and <a href="https://www.youtube.com/user/GPCAorg">YouTube</a>. </p>

<p class="sub" style="margin-top: 20px;">Thank you, and we look forward to welcoming you in Saudi Arabia for the 7<sup>th</sup> GPCA Responsible Care Conference. </p>


<p class="sub" style="margin-top: 20px;">Kind Regards,</p>
<p class="sub">GPCA Team</p>
</x-mail::message>