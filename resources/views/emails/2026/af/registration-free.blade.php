<x-mail::message>
<div>

<p class="sub" style="margin-top: 15px;">Dear {{ $details['name'] }},</p>

<p class="sub" style="margin-top: 15px;">Thank you for registering to attend the <a
        href="{{ $details['eventLink'] }}" target="_blank">{{ $details['eventName'] }}</a>, taking place from
    {{ $details['eventDates'] }} at the {{ $details['eventLocation'] }}. By registering as a delegate, you are
    subject to the terms and conditions outlined in the invoice.</p>

<p class="sub" style="margin-top: 15px;"><strong>Please note that your registration is subject to confirmation from one of our team members. We will review the registration details you've provided to ensure we have the accurate information to make the necessary badge arrangements.</strong></p>

<p class="sub" style="margin-top: 15px;"><strong>Your registration details as follows:</strong></p>

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
    <a href="https://www.linkedin.com/company/gulf-petrochemicals-and-chemicals-association-gpca-/">LinkedIn
        Page</a>. You can also connect with us on our official social media accounts:
    <a href="https://twitter.com/GulfPetChem">Twitter</a>,
    <a href="https://www.instagram.com/gulfpetchem/">Instagram</a>,
    <a href="https://www.facebook.com/GulfPetChem?fref=ts">Facebook</a>, and
    <a href="https://www.youtube.com/user/GPCAorg">YouTube</a>.
</p>

<p class="sub" style="margin-top: 15px;">Thank you, and we look forward to welcoming you in Dubai for the
    {{ $details['eventName'] }}.</p>

<p class="sub" style="margin-top: 15px;">Best regards,</p>
<p class="sub">GPCA Team</p>

</div>
</x-mail::message>