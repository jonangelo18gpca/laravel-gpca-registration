<div class="mb-4">
    @if ($event->category == 'SCC' && $event->year == '2025')
        <div class="text-2xl text-registrationPrimaryColor font-semibold">Gulf SQAS Workshop Pass</div>
         @elseif ($event->category == 'RCC' && $event->year == '2026')
        <div class="text-2xl text-registrationPrimaryColor font-semibold">Workshop & Process Safety by Frontline pass</div>
    @else
        <div class="text-2xl text-registrationPrimaryColor font-semibold">Workshop only</div>
    @endif

    @if ($event->category == 'ANC' && $event->year == '2024')
        <p class="text-lg font-semibold mt-2">Delegate pass includes:</p>
        <div class="grid grid-cols-2 gap-5 mt-2">
            <ul class="list-disc col-span-1 ml-5">
                <li>Access to Operational Excellence Workshops on 10 September</li>
                <li>A site visit to Estidamah Center on 10 September</li>
                <li>Access to Luncheon and Networking breaks on 10 September</li>
            </ul>
            <ul class="list-disc col-span-1 ml-5">
                <li>Delegate bag and Stationery</li>
                <li>Access to event networking app</li>
            </ul>
        </div>
    @endif

    @if ($event->category == 'PSC' && $event->year == '2024')
        <p class="text-lg font-semibold mt-2">Delegate pass includes:</p>
        <div class="grid grid-cols-2 gap-5 mt-2">
            <ul class="list-disc col-span-1 ml-5">
                <li>3 full-day workshops on 7<sup>th</sup> October</li>
                <li>Networking break on 7<sup>th</sup> October</li>
            </ul>
            <ul class="list-disc col-span-1 ml-5">
                <li>First-of-its-kind program, “Process Safety by Frontlines”</li>
                <li>Networking reception on 7<sup>th</sup> October</li>
            </ul>
        </div>
    @endif

    @if ($event->category == 'SCC' && $event->year == '2025')
        <p class="text-lg font-semibold mt-2">Delegate pass includes:</p>
        <div class="grid grid-cols-2 gap-5 mt-2">
            <ul class="list-disc col-span-1 ml-5">
                <li>Access to Gulf SQAS Workshop on 26 May</li>
                <li>Networking breaks on 26 May</li>
            </ul>
        </div>
    @endif
</div>
<table class="w-full bg-registrationPrimaryColor text-white text-center" cellspacing="1" cellpadding="2">
    <thead>
        <tr>
            <td class="py-2 font-bold text-lg">Pass category</td>
            @if ($finalWoEbEndDate != null)
                <td class="py-2 font-bold text-lg">
                    <span>Early bird rate <br> <span class="font-normal text-base">(valid until
                            {{ $finalWoEbEndDate }})</span></span>
                </td>
            @endif
            <td class="py-2 font-bold text-lg">
                @if ($finalWoEbEndDate != null)
                    <span>Standard rate <br> <span class="font-normal text-base">(starting
                            {{ $finalWoStdStartDate }})</span></span>
                @else
                    <span>Standard rate</span>
                @endif
            </td>
        </tr>
    </thead>
    <tbody>
        @if ($event->wo_eb_full_member_rate != null || $event->wo_std_full_member_rate != null)
            <tr>
                <td class="text-black">
                    <div class="bg-white py-2 font-bold ml-1">
                        Full member
                    </div>
                </td>
                @if ($finalWoEbEndDate != null)
                    <td class="text-black">
                        <div class="bg-white py-2">
                            $ {{ number_format($event->wo_eb_full_member_rate, 2, '.', ',') }}
                            {{ $event->event_vat == 0 ? '' : '+VAT (' . $event->event_vat . '%)' }}
                        </div>
                    </td>
                @endif
                <td class="text-black">
                    <div class="bg-white py-2 mr-1">
                        $ {{ number_format($event->wo_std_full_member_rate, 2, '.', ',') }}
                        {{ $event->event_vat == 0 ? '' : '+VAT (' . $event->event_vat . '%)' }}
                    </div>
                </td>
            </tr>
        @endif
        <tr>
            <td class="text-black">
                <div class="bg-white py-2 font-bold ml-1">
                    Member
                </div>
            </td>
            @if ($finalWoEbEndDate != null)
                <td class="text-black">
                    <div class="bg-white py-2">
                        $ {{ number_format($event->wo_eb_member_rate, 2, '.', ',') }}
                        {{ $event->event_vat == 0 ? '' : '+VAT (' . $event->event_vat . '%)' }}
                    </div>
                </td>
            @endif
            <td class="text-black">
                <div class="bg-white py-2 mr-1">
                    $ {{ number_format($event->wo_std_member_rate, 2, '.', ',') }}
                    {{ $event->event_vat == 0 ? '' : '+VAT (' . $event->event_vat . '%)' }}
                </div>
            </td>
        </tr>
        <tr>
            <td class="text-black">
                <div class="bg-white py-2 font-bold mb-1 ml-1">
                    Non-member
                </div>
            </td>
            @if ($finalWoEbEndDate != null)
                <td class="text-black">
                    <div class="bg-white py-2 mb-1">
                        $ {{ number_format($event->wo_eb_nmember_rate, 2, '.', ',') }}
                        {{ $event->event_vat == 0 ? '' : '+VAT (' . $event->event_vat . '%)' }}
                    </div>
                </td>
            @endif
            <td class="text-black">
                <div class="bg-white py-2 mb-1 mr-1">
                    $ {{ number_format($event->wo_std_nmember_rate, 2, '.', ',') }}
                    {{ $event->event_vat == 0 ? '' : '+VAT (' . $event->event_vat . '%)' }}
                </div>
            </td>
        </tr>
    </tbody>
</table>
