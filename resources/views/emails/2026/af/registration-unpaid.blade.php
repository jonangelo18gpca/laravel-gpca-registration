<x-mail::message>

<div>

<p class="sub" style="margin-top: 15px;">Dear {{ $details['name'] }},</p>

<p class="sub" style="margin-top: 15px;">Thank you for registering to attend the <a
        href="{{ $details['eventLink'] }}" target="_blank">{{ $details['eventName'] }}</a>, taking place from
    {{ $details['eventDates'] }} at the {{ $details['eventLocation'] }}. By registering as a delegate, you are
    subject to the terms and conditions outlined in the invoice.</p>

<p class="sub" style="margin-top: 15px; color: red;">Your registration is confirmed; however, payment is required
    to collect your badge onsite. To complete your payment, kindly reach out to our finance team at <a
        href="mailto:analee@gpca.org.ae">analee@gpca.org.ae</a> for assistance. If you register at the early bird
    rate but do not complete the payment by the cutoff date, the fee will be adjusted to the standard rate.</p>

<p class="sub" style="margin-top: 15px;">Your registration details as follows:</p>

<p class="sub" style="margin-top: 20px;"><strong>Delegate Information</strong></p>

<p class="sub">Full name: {{ $details['name'] }}</p>
<p class="sub">Job title: {{ $details['jobTitle'] }}</p>
<p class="sub">Company name: {{ $details['companyName'] }}</p>

@if ($sendInvoice)
<p class="sub">Amount paid: $ {{ number_format($details['amountPaid'], 2, '.', ',') }}</p>
@endif

<p class="sub">Transaction ID: {{ $details['transactionId'] }}</p>

@if ($sendInvoice)

<br>

<x-mail::button :url="$details['invoiceLink']">
Download invoice
</x-mail::button>

@endif

<p class="sub" style="margin-top: 15px;">To request any updates on your registration details, kindly contact <a
        href="mailto:jovelyn@gpca.org.ae">jovelyn@gpca.org.ae</a> before 30th October to ensure your badge
    information is accurate.</p>

<p class="sub" style="margin-top: 20px;"><strong>Collection of badges</strong></p>

<p class="sub" style="margin-top: 5px;">Upon your arrival, please proceed to the registration desk located in the
    foyer to collect your event badge. Kindly present your ID or email confirmation for verification.</p>


    <p class="sub" style="margin-top: 20px;"><strong>Hotel Accommodation</strong></p>

<p class="sub" style="margin-top: 5px;">To avail of the special hotel rates, please click <a href="https://www.gpcaforum.com/travel-and-accommodation-2/" target="_blank">here</a> to view the list of available partner hotels.</p>

<p class="sub" style="margin-top: 5px;">When making your accommodation inquiry or reservation, kindly indicate that you are attending the <a href="http://www.gpcaforum.com">20<sup>th</sup> Annual GPCA Forum</a> to ensure that the applicable special rates are extended to you.</p>


<p class="sub" style="margin-top: 15px;">For any event related queries, please feel free to reach out to the
    following team members:</p>

<p class="sub" style="margin-top: 15px;"><strong>Sponsorship, Exhibition, and Delegate Inquiries:</strong></p>

<p class="sub" style="margin-top: 5px;">Salman Khan and Jerry Rodrigues</p>

<p class="sub">
Email:
<a href="mailto:salman@gpca.org.ae">salman@gpca.org.ae</a>
<a href="mailto:jerry@gpca.org.ae">jerry@gpca.org.ae</a>
</p>

<p class="sub">Telephone: +971 4 451 0666 ext 103 & 106</p>

<p class="sub" style="margin-top: 15px;">Stay updated on upcoming GPCA events and industry news by following our
<a href="https://www.linkedin.com/company/gulf-petrochemicals-and-chemicals-association-gpca-/">LinkedIn Page</a>.
You can also connect with us on our official social media accounts:
<a href="https://twitter.com/GulfPetChem">Twitter</a>,
<a href="https://www.instagram.com/gulfpetchem/">Instagram</a>,
<a href="https://www.facebook.com/GulfPetChem?fref=ts">Facebook</a>,
and <a href="https://www.youtube.com/user/GPCAorg">YouTube</a>.
</p>

<p class="sub" style="margin-top: 15px;">Thank you, and we look forward to welcoming you in Dubai for the
{{ $details['eventName'] }}.</p>

<p class="sub" style="margin-top: 15px;">Best regards,</p>
<p class="sub">GPCA Team</p>

</div>

{{-- 
@php
    $description = "Join {$details['eventName']} in Dubai on {$details['eventDates']}";
    $shareUrl = $details['eventLink'];
    $shareText = urlencode($description);


echo <<<HTML
<br><br>


<div style="text-align:center;">

<p style="font-size:12px; color:#5f615f;">
<strong>Let others know you’re attending</strong>
</p>

<table border="0" cellpadding="0" cellspacing="0" align="center">
<tr>

<td>
<a href="https://www.linkedin.com/sharing/share-offsite/?url={$shareUrl}">
<img src="https://gpcachem.org/ie-images/ln_header.png" width="30" style="display:block; border:0;">
</a>
</td>

<td width="10"></td>

<td>
<a href="https://x.com/intent/tweet?text={$shareText}&url={$shareUrl}">
<img src="https://gpcachem.org/ie-images/x_header.png" width="30" style="display:block; border:0;">
</a>
</td>

<td width="10"></td>

<td>
<a href="https://www.facebook.com/sharer/sharer.php?u={$shareUrl}">
<img src="https://gpcachem.org/ie-images/fb_header.png" width="30" style="display:block; border:0;">
</a>
</td>

</tr>
</table>

</div>
HTML;
@endphp --}}

</x-mail::message>