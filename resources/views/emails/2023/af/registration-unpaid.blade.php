<x-mail::message>
    <p class="sub">Dear {{ $details['name'] }},</p>

    <p class="sub" style="margin-top: 15px;">Greetings from GPCA!</p>

    <p class="sub" style="margin-top: 15px;">Thank you for registering for the <a href="{{ $details['eventLink'] }}"
            target="_blank">{{ $details['eventName'] }}</a>, which will be held from <strong>4-6 December 2023</strong>,
        at the {{ $details['eventLocation'] }}. Please note that the opening ceremony of the forum will now commence on
        <strong>3<sup>rd</sup> December</strong> from <strong>16:00-18:30</strong>. The opening day will be inaugurated
        by the Energy Minister of Qatar and Saudi Arabia, with SABIC’s CEO delivering the welcome remarks. The GPCA
        Legacy Awards ceremony and inauguration of the exhibition will also take place on the same day, followed by a
        networking reception at the Fountain Area, Qatar National Convention Centre. To view the updated agenda, please
        click <a href="https://www.gpcaforum.com/annual-forum/#program" target="_blank">here</a> or refer to the below
        schedule. </p>

    <p class="subtitle" style="margin-top: 15px; text-decoration: underline;">3 December 2023</p>

    <table class="af-table" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td width="20%">16:00-16:10</td>
            <td width="80%"><strong>Opening ceremony</strong></td>
        </tr>

        <tr>
            <td width="20%">16:10-16:20</td>
            <td width="80%"><strong>Welcome remarks</strong> <br> <strong>Eng. Abdulrahman Al-Fageeh</strong>,
                <em>CEO</em>, <strong>SABIC</strong> and <em>Chairman</em>, <strong>GPCA</strong></td>
        </tr>

        <tr>
            <td width="20%">16:20-16:30</td>
            <td width="80%"><strong>Ministerial address</strong> <br> <strong>H.E. Saad Sherida Al-Kaabi</strong>,
                <em>Minister of State for Energy Affairs</em> and <em>President</em> and <em>CEO</em>,
                <strong>QatarEnergy</strong></td>
        </tr>

        <tr>
            <td width="20%">16:30-16:40</td>
            <td width="80%"><strong>Ministerial address</strong> <br> <strong>H.R.H Prince Abdulaziz Bin Salman
                    Al-Saud</strong>, <em>Minister of Energy</em>, <strong>Saudi Arabia</strong></td>
        </tr>

        <tr>
            <td width="20%">16:40-16:50</td>
            <td width="80%"><strong>Incoming Host Address: 18<sup>th</sup> Annual GPCA Forum announcement
                    (TBC)</strong> <br> <strong>H.E. Salim bin Nasser bin Said Al Aufi</strong>, <em>Minister of Energy
                    and Minerals</em>, <strong>Oman</strong></td>
        </tr>

        <tr>
            <td width="20%">16:50-17:30</td>
            <td width="80%"><strong>Al-Rowad: GPCA Legacy Awards ceremony</strong></td>
        </tr>

        <tr>
            <td width="20%">17:30-18:30</td>
            <td width="80%"><strong>Exhibition inauguration</strong></td>
        </tr>

        <tr>
            <td width="20%">18:30 onwards</td>
            <td width="80%"><strong>Networking reception</strong></td>
        </tr>
    </table>

    <p class="sub" style="margin-top: 15px; color: red;">Kindly note that your registration is not yet confirmed.
        Please settle your payment through bank transfer prior to the event to avoid any inconvenience onsite</p>

    <p class="title" style="margin-top: 20px;">Delegate Information:</p>
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

    <p class="title" style="margin-top: 20px;">Badge Collection:</p>
    <p class="sub">Delegates can start collecting their badges on <strong>3<sup>rd</sup> December 2023</strong>, from
        <strong>10:00am</strong> onwards located at the Spider Foyer on Level 1.</p>

    <p class="title" style="margin-top: 20px;">VISA INFORMATION:</p>
    <p class="sub">For travelers with passports issued by India, Pakistan, Iran, Thailand, and Ukraine, please reach
        out to Cozmo Travel https://www.gpcaforum.com/travel-accomodation/ for visa assistance if you have booked your
        accommodation through them.</p>

    <p class="sub" style="margin-top: 15px;">For other non-GCC nationals applying for a visa and who have booked
        their accommodation through our travel partner should contact Cozmo Travel to obtain a hotel confirmation
        letter, a requirement for your visa applications.</p>

    <p class="sub" style="margin-top: 15px;">We look forward to your participation in the event and hope that your
        experience at the 17<sup>th</sup> Annual GPCA Forum will be both enriching and insightful. Should you have any
        further inquiries or require additional information, please do not hesitate to reach out to us.</p>

    <p class="sub" style="margin-top: 15px;">Thank you once again for your registration, and we look forward to
        welcoming you to this exceptional event!</p>

    <p class="sub" style="margin-top: 15px;">Best regards,</p>
    <p class="sub">GPCA Team</p>
</x-mail::message>
