<?php

namespace App\Mail;

use App\Enums\AccessTypes;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;

class RegistrationUnpaid extends Mailable
{
    use Queueable, SerializesModels;

    public $details;
    public $sendInvoice;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($details, $sendInvoice = true)
    {
        $this->details = $details;
        $this->sendInvoice = $sendInvoice;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        if ($this->details['eventYear'] == '2023') {
            if ($this->details['eventCategory'] == "RCCA") {
                $subject = 'Outstanding payment for your entry submission on the ' . $this->details['eventName'];
            } else if ($this->details['eventCategory'] == "AFS") {
                $subject = 'Your Exclusive Spouse Program Experience at the 17th Annual GPCA Forum';
            } else {
                $subject = 'Outstanding payment for your ' . $this->details['eventName'] . ' registration';
            }
        } else if ($this->details['eventYear'] == '2024') {
            if ($this->details['eventCategory'] == "SCEA") {
                $subject = 'Outstanding payment for your entry submission on the ' . $this->details['eventName'];
            } else {
                $subject = 'Outstanding payment for your ' . $this->details['eventName'] . ' registration';
            }
        } else if ($this->details['eventYear'] == '2025') {
            if ($this->details['eventCategory'] == "SCEA") {
                $subject = 'Outstanding payment for your entry submission on the ' . $this->details['eventName'];
            } else if ($this->details['eventCategory'] == "SCC") {
                if ($this->details['accessType'] == AccessTypes::CONFERENCE_ONLY->value) {
                    $subject = 'Pending Registration for the ' . $this->details['eventName'];
                } else if ($this->details['accessType'] == AccessTypes::WORKSHOP_ONLY->value) {
                    $subject = 'Pending Registration for the GULF SQAS Driving Supply Chain Sustainability Workshop ';
                } else {
                    $subject = 'Pending Registration for the ' . $this->details['eventName'];
                }
            } else if ($this->details['eventCategory'] == "ANC") {
                if ($this->details['accessType'] == AccessTypes::CONFERENCE_ONLY->value) {
                    $subject = 'Pending Registration for the ' . $this->details['eventName'];
                } else if ($this->details['accessType'] == AccessTypes::WORKSHOP_ONLY->value) {
                    $subject = 'Pending Registration for the 3rd Operational Excellence Workshop';
                } else {
                    $subject = 'Pending Registration for the ' . $this->details['eventName'];
                }
            } else if ($this->details['eventCategory'] == "RCC") {
                $subject = 'Pending Registration for the ' . $this->details['eventName'];
            } else if ($this->details['eventCategory'] == "AF") {
                $subject = 'Outstanding payment for your ' . $this->details['eventName'] . " Registration (UNPAID)";
            } else if ($this->details['eventCategory'] == "RCCA") {
                $subject = 'Outstanding payment for your entry submission on the ' . $this->details['eventName'];
            } else {
                $subject = 'Outstanding payment for your ' . $this->details['eventName'] . ' registration';
            }
        } else {
            $subject = 'Outstanding payment for your ' . $this->details['eventName'] . ' registration';
        }

        return new Envelope(
            from: new Address('forumregistration@gpca.org.ae', 'GPCA Events Registration'),
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        if ($this->details['eventYear'] == '2023') {
            if ($this->details['eventCategory'] == "AF") {
                return new Content(
                    markdown: 'emails.2023.af.registration-unpaid',
                );
            } else if ($this->details['eventCategory'] == "AFS") {
                return new Content(
                    markdown: 'emails.2023.spouse.registration-unpaid',
                );
            } else if ($this->details['eventCategory'] == "AFV") {
                return new Content(
                    markdown: 'emails.2023.visitor.registration-unpaid',
                );
            } else if ($this->details['eventCategory'] == "ANC") {
                return new Content(
                    markdown: 'emails.2023.anc.registration-unpaid',
                );
            } else if ($this->details['eventCategory'] == "IPAW") {
                return new Content(
                    markdown: 'emails.2023.ipaw.registration-unpaid',
                );
            } else if ($this->details['eventCategory'] == "PC") {
                return new Content(
                    markdown: 'emails.2023.pc.registration-unpaid',
                );
            } else if ($this->details['eventCategory'] == "RCC") {
                return new Content(
                    markdown: 'emails.2023.rcc.registration-unpaid',
                );
            } else if ($this->details['eventCategory'] == "RCCA") {
                return new Content(
                    markdown: 'emails.2023.rcca.registration-unpaid',
                );
            } else if ($this->details['eventCategory'] == "SCC") {
                return new Content(
                    markdown: 'emails.2023.scc.registration-unpaid',
                );
            } else if ($this->details['eventCategory'] == "PSW") {
                return new Content(
                    markdown: 'emails.2023.psw.registration-unpaid',
                );
            } else if ($this->details['eventCategory'] == "DAW") {
                return new Content(
                    markdown: 'emails.2023.daw.registration-unpaid',
                );
            } else if ($this->details['eventCategory'] == "DFCLW1") {
                return new Content(
                    markdown: 'emails.2024.dfclw1.registration-unpaid',
                );
            } else {
                return new Content(
                    markdown: 'emails.registration-unpaid',
                );
            }
        } else if ($this->details['eventYear'] == '2024') {
            if ($this->details['eventCategory'] == "GLF") {
                return new Content(
                    markdown: 'emails.2024.glf.registration-unpaid',
                );
            } else if ($this->details['eventCategory'] == "RCCW1") {
                return new Content(
                    markdown: 'emails.2024.rccw1.registration-unpaid',
                );
            } else if ($this->details['eventCategory'] == "PC") {
                return new Content(
                    markdown: 'emails.2024.pc.registration-unpaid',
                );
            } else if ($this->details['eventCategory'] == "SCC") {
                return new Content(
                    markdown: 'emails.2024.scc.registration-unpaid',
                );
            } else if ($this->details['eventCategory'] == "SCEA") {
                return new Content(
                    markdown: 'emails.2024.scea.registration-unpaid',
                );
            } else if ($this->details['eventCategory'] == "ANC") {
                if ($this->details['accessType'] == AccessTypes::CONFERENCE_ONLY->value) {
                    return new Content(
                        markdown: 'emails.2024.anc.co.registration-unpaid',
                    );
                } else if ($this->details['accessType'] == AccessTypes::WORKSHOP_ONLY->value) {
                    return new Content(
                        markdown: 'emails.2024.anc.wo.registration-unpaid',
                    );
                } else {
                    return new Content(
                        markdown: 'emails.2024.anc.registration-unpaid',
                    );
                }
            } else if ($this->details['eventCategory'] == "PSC") {
                if ($this->details['accessType'] == AccessTypes::CONFERENCE_ONLY->value) {
                    return new Content(
                        markdown: 'emails.2024.psc.co.registration-unpaid',
                    );
                } else if ($this->details['accessType'] == AccessTypes::WORKSHOP_ONLY->value) {
                    return new Content(
                        markdown: 'emails.2024.psc.wo.registration-unpaid',
                    );
                } else {
                    return new Content(
                        markdown: 'emails.2024.psc.registration-unpaid',
                    );
                }
            } else if ($this->details['eventCategory'] == "AF") {
                return new Content(
                    markdown: 'emails.2024.af.registration-unpaid',
                );
            } else if ($this->details['eventCategory'] == "IPAW") {
                return new Content(
                    markdown: 'emails.2024.ipaw.registration-unpaid',
                );
            } else if ($this->details['eventCategory'] == "CAIPW1") {
                return new Content(
                    markdown: 'emails.2024.caipw1.registration-unpaid',
                );
            } else {
                return new Content(
                    markdown: 'emails.registration-unpaid',
                );
            }
        } else if ($this->details['eventYear'] == '2025') {
            if ($this->details['eventCategory'] == "RCW") {
                return new Content(
                    markdown: 'emails.2025.rcw.registration-unpaid',
                );
            } else if ($this->details['eventCategory'] == "SCEA") {
                return new Content(
                    markdown: 'emails.2025.scea.registration-unpaid',
                );
            } else if ($this->details['eventCategory'] == "PC") {
                return new Content(
                    markdown: 'emails.2025.pc.registration-unpaid',
                );
            } else if ($this->details['eventCategory'] == "SCC") {
                if ($this->details['accessType'] == AccessTypes::CONFERENCE_ONLY->value) {
                    return new Content(
                        markdown: 'emails.2025.scc.co.registration-unpaid',
                    );
                } else if ($this->details['accessType'] == AccessTypes::WORKSHOP_ONLY->value) {
                    return new Content(
                        markdown: 'emails.2025.scc.wo.registration-unpaid',
                    );
                } else {
                    return new Content(
                        markdown: 'emails.2025.scc.registration-unpaid',
                    );
                }
            } else if ($this->details['eventCategory'] == "ANC") {
                if ($this->details['accessType'] == AccessTypes::CONFERENCE_ONLY->value) {
                    return new Content(
                        markdown: 'emails.2025.anc.co.registration-unpaid',
                    );
                } else if ($this->details['accessType'] == AccessTypes::WORKSHOP_ONLY->value) {
                    return new Content(
                        markdown: 'emails.2025.anc.wo.registration-unpaid',
                    );
                } else {
                    return new Content(
                        markdown: 'emails.2025.anc.registration-unpaid',
                    );
                }
            } else if ($this->details['eventCategory'] == "RCC") {
                if ($this->details['accessType'] == AccessTypes::CONFERENCE_ONLY->value) {
                    return new Content(
                        markdown: 'emails.2025.rcc.co.registration-unpaid',
                    );
                } else if ($this->details['accessType'] == AccessTypes::WORKSHOP_ONLY->value) {
                    return new Content(
                        markdown: 'emails.2025.rcc.wo.registration-unpaid',
                    );
                } else {
                    return new Content(
                        markdown: 'emails.2025.rcc.registration-unpaid',
                    );
                }
            } else if ($this->details['eventCategory'] == "PSW") {
                return new Content(
                    markdown: 'emails.2025.psw.registration-unpaid',
                );
            } else if ($this->details['eventCategory'] == "PSTW") {
                return new Content(
                    markdown: 'emails.2025.pstw.registration-unpaid',
                );
            } else if ($this->details['eventCategory'] == "AF") {
                return new Content(
                    markdown: 'emails.2025.af.registration-unpaid',
                );
            } else if ($this->details['eventCategory'] == "RCCA") {
                return new Content(
                    markdown: 'emails.2025.rcca.registration-unpaid',
                );
            } else if ($this->details['eventCategory'] == "IPAW") {
                return new Content(
                    markdown: 'emails.2025.ipaw.registration-unpaid',
                );
            } else {
                return new Content(
                    markdown: 'emails.registration-unpaid',
                );
            }
        } else if ($this->details['eventYear'] == '2026') {

            if ($this->details['eventCategory'] == "RIC") {
                return new Content(
                    markdown: 'emails.2026.ric.registration-unpaid',
                );
            }
        } else {
            return new Content(
                markdown: 'emails.registration-unpaid',
            );
        }
    }


    /**
     * Get the attachments for the message.
     *
     * @return array
     */

    public function attachments(): array
    {
        return [];
    }
}
