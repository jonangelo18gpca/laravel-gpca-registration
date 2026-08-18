@extends('admin.events.transactions.invoices.master')

@section('content')
    <div class="logo">
        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('/assets/images/invoice_logo.png'))) }}"
            alt="gpca-logo">
    </div>

    <table class="invoice-intro">
        <tr>
            <td>
                <h1>Tax Invoice</h1>
            </td>
        </tr>
        <tr>
            <td class="invoice-intro-left">
                <p>{{ $companyName }}</p>
                <p>{{ $companyAddress }}</p>
                <p>{{ $companyCity }}</p>
                <p>{{ $companyCountry }}</p>
            </td>
            <td class="invoice-intro-right">
                <table align="right">
                    <tr>
                        <td class="keys">
                            <p>Invoice Date</p>
                            <p>Date of Supply</p>
                            <p>Invoice No</p>
                            <p>Reference No</p>
                            <p>Payment Terms</p>
                            <p class="gpca-trn">GPCA TRN</p>
                        </td>
                        <td class="values">
                            <p>: {{ $invoiceDate }}</p>
                            <p>: {{ $invoiceDate }}</p>
                            <p>: {{ $invoiceNumber }}</p>
                            <p>: {{ $bookRefNumber }}</p>
                            <p>: Immediate Payment</p>
                            <p class="gpca-trn">: 100316599800003</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="invoice-body">
        <tr class="tr-header">
            <td class="first-col">Description</td>
            <td class="second-col">Qty</td>
            <td class="third-col">Unit Price</td>
            <td class="fourth-col">Discount</td>
            <td class="fifth-col">Net <br> Amount <br> (USD)</td>
        </tr>
        @php
            $count = 1;
        @endphp

        @foreach ($invoiceDetails as $delegatInvoiceDetail)
            @if ($count == 1)
                <tr class="tr-description">
                    <td class="first-col">
                        <p><strong>{{ $invoiceDescription }}</strong></p>
                    </td>
                    <td class="second-col">&nbsp;</td>
                    <td class="third-col">&nbsp;</td>
                    <td class="fourth-col">&nbsp;</td>
                    <td class="fifth-col">&nbsp;</td>
                </tr>
            @endif


            <tr class="tr-delegates">
                <td class="first-col">
                    <p>{{ $delegatInvoiceDetail['delegateDescription'] }}</p>
                    <ol>
                        @foreach ($delegatInvoiceDetail['delegateNames'] as $name)
                            <li>{{ $name }}</li>
                        @endforeach
                    </ol>
                </td>
                <td class="second-col">
                    <p>{{ $delegatInvoiceDetail['quantity'] }}</p>
                </td>
                <td class="third-col">
                    <p>$ {{ number_format($delegatInvoiceDetail['totalUnitPrice'], 2, '.', ',') }}</p>
                </td>
                <td class="fourth-col">
                    <p>$ {{ number_format($delegatInvoiceDetail['totalDiscount'], 2, '.', ',') }}</p>
                </td>
                <td class="fifth-col">
                    <p>$ {{ number_format($delegatInvoiceDetail['totalNetAmount'], 2, '.', ',') }}</p>
                </td>
            </tr>


            @if (count($invoiceDetails) == $count)
                <tr class="tr-note">
                    <td class="first-col">
                        <p><em>(Note: Please quote the invoice number during payment and ensure that all bank charges and
                                withholding taxes (if any) should be borne by the sender to avoid underpayments)</em></p>
                    </td>
                    <td class="second-col">&nbsp;</td>
                    <td class="third-col">&nbsp;</td>
                    <td class="fourth-col">&nbsp;</td>
                    <td class="fifth-col">&nbsp;</td>
                </tr>
            @endif

            @php
                $count++;
            @endphp
        @endforeach

        {{-- INVOICE FOOTER --}}
        <tr class="tr-totals totals-first-row">
            <td class="first-col" rowspan="5">
                <table>
                    <tr>
                        <td class="total-amount-words">
                            <p><strong>Total Amount USD: {{ $total_amount_string }} Only</strong></p>
                        </td>
                    </tr>
                    <tr>
                        <td class="exchange-rate">
                            <p><strong>Exchange rate: 1 USD = 3.675 AED</strong></p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="payment-instruction">
                                <p style="text-decoration: underline;">Payment Instruction</p>
                                <p>Please remit to:</p>
                                <p>Mashreq Bank</p>
                                <p>Riqa Branch, Deira, P.O. Box 5511, Dubai</p>
                                <p>USD Acct No. {{ $bankDetails['accountNumber'] }}</p>
                                <p>IBAN No. {{ $bankDetails['ibanNumber'] }}</p>
                                <p>Swift Code BOMLAEAD</p>
                                <p>In favor of: Gulf Petrochemicals & Chemicals Association</p>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td colspan="3" class="second-col">
                <p>Total before VAT </p>
            </td>
            <td class="third-col">
                <p>$ {{ number_format($net_amount, 2, '.', ',') }}</p>
            </td>
        </tr>

        <tr class="tr-totals">
            <td colspan="3" class="second-col">
                <p>VAT {{ $eventVat }}%</p>
            </td>
            <td class="third-col">
                <p>$ {{ number_format($vat_price, 2, '.', ',') }}</p>
            </td>
        </tr>

        <tr class="tr-totals">
            <td colspan="3" class="second-col">
                <p>Total</p>
            </td>
            <td class="third-col">
                <p>$ {{ number_format($total_amount, 2, '.', ',') }}</p>
            </td>
        </tr>

        <tr class="tr-totals tr-totals-unpaid">
            <td colspan="3">&nbsp;</td>
            <td>&nbsp;</td>
        </tr>

        <tr class="tr-totals tr-totals-unpaid">
            <td colspan="3">&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
    </table>

    {{-- <div class="terms-and-condition">
        <p>Terms and Conditions</p>
        <ol>
            <li>Cancellations must be made in writing within 15 days of receiving the invoice. Cancellations received after this deadline will not be accepted, and the full invoice amount must be settled.</li>
            <li>If a delegate is unable to attend, a substitute delegate can be nominated at no additional cost. Please notify us in writing via email at forumregistration@gpca.org.ae with the full name, job title, email address, and phone number of both the registered and substitute delegates.</li>
            <li>If you have registered within the early bird time frame, payment must be completed before the deadline. Otherwise, the registration fee will automatically reflect the standard rate. </li>
            <li>Refund Policy
                <p class="inside-li">4.1 If a delegate cancels their registration 31 days or more before the event, they will receive a refund of 75% of the paid registration fee.</p>
                <p class="inside-li">4.2 If a delegate cancels their registration less than 31 days before the event, no refund will be issued.</p>
            </li>
        </ol>
    </div> --}}


       <div class="terms-and-condition">
       <p>Terms and Conditions</p>
       <ol>
           <li>Cancellations must be made in writing within 15 days of receiving the invoice. Cancellations received after this deadline will not be accepted, and the full invoice amount must be settled.</li>
           <li>If a delegate is unable to attend, a substitute delegate can be nominated at no additional cost. Please notify us in writing via email at forumregistration@gpca.org.ae with the full name, job title, email address, and phone number of both the registered and substitute delegates.</li>
           <li>If a delegate is unable to attend due to a Force Majeure Event (including but not limited to war, armed conflict, government-imposed travel restrictions, or other circumstances beyond their reasonable control), GPCA may issue a non-refundable credit note, subject to written notification and supporting evidence.  Notification should be provided at least 30 days prior to the event, where reasonably possible. The credit note will be valid for one year from the original event date (inclusive of the respective event if the dates are scheduled for beyond the 12 months)</li>
            <li>If you have registered within the early bird time frame, payment must be completed before the deadline. Otherwise, the registration fee will automatically reflect the standard rate.</li>
         
           <li>Refund Policy
               <p class="inside-li">5.1	If delegate/s cancelled their registration 31 days before the event, they will get a refund of 75% on the amount paid for the registration fee.</p>
               <p class="inside-li">5.2	If delegate/s cancelled their registration less than 31 days before the event, NO refund will be given.</p>
           </li>
       </ol>
   </div>

    <p style="text-align: center; font-size: 11px; margin-top: 30px"><em>This is an autogenerated invoice no signature
            required.</em></p>

   <table class="invoice-main-footer">
       <tr>
            <td class="left">
                <img src="data:image/PNG;base64,{{ base64_encode(file_get_contents(public_path('/assets/images/invoice_footer_left.PNG'))) }}" alt="invoice-footer" width="200">
            </td>
            <td class="right">
                <img src="data:image/PNG;base64,{{ base64_encode(file_get_contents(public_path('/assets/images/invoice_footer_right.PNG'))) }}" alt="invoice-footer" width="150">
            </td>
       </tr>
   </table>
@endsection
