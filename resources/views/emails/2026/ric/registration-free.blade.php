<x-mail::message>

<div style="font-size:14px; line-height:1.6; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">

<p style="margin:0 0 12px 0; font-size:14px;">
Dear {{ $details['name'] }},
</p>

<p style="margin:0 0 12px 0; font-size:14px;">
Thank you for registering to attend 
<a href="{{ $details['eventLink'] }}" target="_blank">{{ $details['eventName'] }}</a>, 
taking place from <strong>18–19 May 2026</strong> at the 
<strong>{{ $details['eventLocation'] }}</strong>.
</p>

<p style="margin:0 0 12px 0; font-size:14px;">
By registering as a delegate, your participation is subject to the terms and conditions outlined in your invoice.
</p>

<p style="margin:0 0 24px 0; font-size:14px;">
Please note that your registration is subject to confirmation by one of our team members. We will review the details provided to ensure accuracy for your badge arrangements.
</p>

<p style="margin:0 0 8px 0; font-weight:600; font-size:14px; color:#3D4852">
Your registration details as follows:
</p>

<p style="margin:0 0 24px 0; font-size:14px;">
<strong>Full name:</strong> {{ $details['name'] }}<br>
<strong>Job title:</strong> {{ $details['jobTitle'] }}<br>
<strong>Company name:</strong> {{ $details['companyName'] }}<br>
<strong>Amount paid:</strong> $ {{ number_format($details['amountPaid'], 2, '.', ',') }}<br>
<strong>Transaction ID:</strong> {{ $details['transactionId'] }}<br>
</p>

@if ($sendInvoice)
<x-mail::button :url="$details['invoiceLink']" color="registration">
Download Invoice
</x-mail::button>
@endif

<p style="margin:24px 0 24px 0; font-size:14px;">
To request updates on your registration details, contact 
<a href="mailto:jovelyn@gpca.org.ae">jovelyn@gpca.org.ae</a> 
on or before <strong>17<sup>th</sup> April</strong> to ensure your badge information is accurate.
</p>

<p style="margin:0 0 6px 0; font-weight:600; font-size:14px;">Collection of Badges</p>

<p style="margin:0 0 24px 0; font-size:14px;">
Upon arrival, please proceed to the registration desk located in the Foyer to collect your event badge. Kindly present your ID or email confirmation for verification.
</p>

<p style="margin:0 0 6px 0; font-weight:600; font-size:14px;">Visa Inquiries</p>

<p style="margin:0 0 24px 0; font-size:14px;">
For any visa related inquiries, please contact our designated travel partner at Cozmo Travel. You may reach John Uytiongco at <a href="mailto:juytiongco@cozmotravel.com">juytiongco@cozmotravel.com</a> or call +971 4 406 5802.
</p>

<p style="margin:0 0 6px 0; font-weight:600; font-size:14px;">Hotel Booking</p>

<p style="margin:0 0 24px 0; font-size:14px;">
For hotel accommodation, please click the booking 
<a href="https://www.marriott.com/event-reservations/reservation-link.mi?id=1771313552687&key=GRP&app=resvlink&_branch_match_id=1552210776338362721&_branch_referrer=H4sIAAAAAAAAA8soKSkottLXTywo0MtNLCrKzC8p0UvOz9UvSi3OyczLtgdK2ALZZSCOWmaKraG5uaGxobGpqZGZhbladmqlrXtQgFpdUWpaKlB3Xnp8UlF%2BeXFqka1rSnoqAChM6UVeAAAA">
link
</a>
to secure the special hotel rate at the Le Meridien, Al Khobar, Saudi Arabia.
</p>

<p style="margin:0 0 12px 0; font-size:14px;">
If you have any questions or need assistance with the booking, kindly coordinate with 
<strong>Adnan Ahmed</strong> at 
<a href="mailto:adnan.ahmed2@lemeridien.com">adnan.ahmed2@lemeridien.com</a> 
or call +966 504940516.
</p>

<p style="margin:0 0 12px 0; font-size:14px;">For any event-related queries, please reach out to the following team members:</p>

<p style="margin:0 0 6px 0; font-weight:600; font-size:14px;">Sponsorship, Exhibition, and Delegate Inquiries</p>

<p style="margin:0 0 24px 0; font-size:14px;">
• Salman Khan and Jerry Rodrigues<br>
• Email: 
<a href="mailto:salman@gpca.org.ae">salman@gpca.org.ae</a>, 
<a href="mailto:jerry@gpca.org.ae">jerry@gpca.org.ae</a><br>
• Telephone: +971 4 451 0666 ext 103 & 106
</p>

<p style="margin:0 0 24px 0; font-size:14px;">
Stay updated on upcoming GPCA events and industry news by following our 
<a href="https://www.linkedin.com/company/gulf-petrochemicals-and-chemicals-association-gpca-/">
LinkedIn Page
</a>. You can also connect with us on our official social media accounts: 
<a href="https://twitter.com/GulfPetChem">Twitter</a>, 
<a href="https://www.instagram.com/gulfpetchem/">Instagram</a>, 
<a href="https://www.facebook.com/GulfPetChem?fref=ts">Facebook</a>, and 
<a href="https://www.youtube.com/user/GPCAorg">YouTube</a>.
</p>

<p style="margin:0 0 16px 0; font-size:14px;">
Thank you, and we look forward to welcoming you in Saudi Arabia for the <strong>8th GPCA Research & Innovation Conference</strong>.
</p>

<p style="margin:0; font-size:14px;">
Best Regards,<br>
<strong>GPCA Team</strong>
</p>

</div>

</x-mail::message>