<?php

namespace App\Mail;

use App\Enums\AccessTypes;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class RegistrationFree extends Mailable
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
        if ($this->details['eventYear'] == '2025') {
            if ($this->details['eventCategory'] == "AF") {
                $subject = 'Pending Registration for the ' . $this->details['eventName'] . ' (FOC)';
            } else {
                $subject = 'Pending Registration confirmation for the ' . $this->details['eventName'];
            }
        } else {
            $subject = 'Pending Registration confirmation for the ' . $this->details['eventName'];
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
                    markdown: 'emails.2023.af.registration-free',
                );
            } else if ($this->details['eventCategory'] == "ANC") {
                return new Content(
                    markdown: 'emails.2023.anc.registration-free',
                );
            } else if ($this->details['eventCategory'] == "IPAW") {
                return new Content(
                    markdown: 'emails.2023.ipaw.registration-free',
                );
            } else if ($this->details['eventCategory'] == "PC") {
                return new Content(
                    markdown: 'emails.2023.pc.registration-free',
                );
            } else if ($this->details['eventCategory'] == "RCC") {
                return new Content(
                    markdown: 'emails.2023.rcc.registration-free',
                );
            } else if ($this->details['eventCategory'] == "SCC") {
                return new Content(
                    markdown: 'emails.2023.scc.registration-free',
                );
            } else if ($this->details['eventCategory'] == "PSW") {
                return new Content(
                    markdown: 'emails.2023.psw.registration-free',
                );
            } else if ($this->details['eventCategory'] == "DAW") {
                return new Content(
                    markdown: 'emails.2023.daw.registration-free',
                );
            } else if ($this->details['eventCategory'] == "AFV") {
                return new Content(
                    markdown: 'emails.2023.visitor.registration-free',
                );
            } else {
                return new Content(
                    markdown: 'emails.registration-free',
                );
            }
        } else if ($this->details['eventYear'] == '2024') {
            if ($this->details['eventCategory'] == "GLF") {
                return new Content(
                    markdown: 'emails.2024.glf.registration-free',
                );
            } else if ($this->details['eventCategory'] == "RCCW1") {
                return new Content(
                    markdown: 'emails.2024.rccw1.registration-free',
                );
            } else if ($this->details['eventCategory'] == "PC") {
                return new Content(
                    markdown: 'emails.2024.pc.registration-free',
                );
            } else if ($this->details['eventCategory'] == "SCC") {
                return new Content(
                    markdown: 'emails.2024.scc.registration-free',
                );
            } else if ($this->details['eventCategory'] == "DFCLW1") {
                return new Content(
                    markdown: 'emails.2024.dfclw1.registration-free',
                );
            } else if ($this->details['eventCategory'] == "ANC") {
                if ($this->details['accessType'] == AccessTypes::CONFERENCE_ONLY->value) {
                    return new Content(
                        markdown: 'emails.2024.anc.co.registration-free',
                    );
                } else if ($this->details['accessType'] == AccessTypes::WORKSHOP_ONLY->value) {
                    return new Content(
                        markdown: 'emails.2024.anc.wo.registration-free',
                    );
                } else {
                    return new Content(
                        markdown: 'emails.2024.anc.registration-free',
                    );
                }
            } else if ($this->details['eventCategory'] == "PSC") {
                if ($this->details['accessType'] == AccessTypes::CONFERENCE_ONLY->value) {
                    return new Content(
                        markdown: 'emails.2024.psc.co.registration-free',
                    );
                } else if ($this->details['accessType'] == AccessTypes::WORKSHOP_ONLY->value) {
                    return new Content(
                        markdown: 'emails.2024.psc.wo.registration-free',
                    );
                } else {
                    return new Content(
                        markdown: 'emails.2024.psc.registration-free',
                    );
                }
            } else if ($this->details['eventCategory'] == "AF") {
                return new Content(
                    markdown: 'emails.2024.af.registration-free',
                );
            } else if ($this->details['eventCategory'] == "IPAW") {
                return new Content(
                    markdown: 'emails.2024.ipaw.registration-free',
                );
            } else if ($this->details['eventCategory'] == "CAIPW1") {
                return new Content(
                    markdown: 'emails.2024.caipw1.registration-free',
                );
            } else {
                return new Content(
                    markdown: 'emails.registration-free',
                );
            }
        } else if ($this->details['eventYear'] == '2025') {
            if ($this->details['eventCategory'] == "GLF") {
                return new Content(
                    markdown: 'emails.2025.glf.registration-free',
                );
            } else if ($this->details['eventCategory'] == "RCW") {
                return new Content(
                    markdown: 'emails.2025.rcw.registration-free',
                );
            } else if ($this->details['eventCategory'] == "PC") {
                return new Content(
                    markdown: 'emails.2025.pc.registration-free',
                );
            } else if ($this->details['eventCategory'] == "SCC") {
                if ($this->details['accessType'] == AccessTypes::CONFERENCE_ONLY->value) {
                    return new Content(
                        markdown: 'emails.2025.scc.co.registration-free',
                    );
                } else if ($this->details['accessType'] == AccessTypes::WORKSHOP_ONLY->value) {
                    return new Content(
                        markdown: 'emails.2025.scc.wo.registration-free',
                    );
                } else {
                    return new Content(
                        markdown: 'emails.2025.scc.registration-free',
                    );
                }
            } else if ($this->details['eventCategory'] == "ANC") {
                if ($this->details['accessType'] == AccessTypes::CONFERENCE_ONLY->value) {
                    return new Content(
                        markdown: 'emails.2025.anc.co.registration-free',
                    );
                } else if ($this->details['accessType'] == AccessTypes::WORKSHOP_ONLY->value) {
                    return new Content(
                        markdown: 'emails.2025.anc.wo.registration-free',
                    );
                } else {
                    return new Content(
                        markdown: 'emails.2025.anc.registration-free',
                    );
                }
            } else if ($this->details['eventCategory'] == "RCC") {
                if ($this->details['accessType'] == AccessTypes::CONFERENCE_ONLY->value) {
                    return new Content(
                        markdown: 'emails.2025.rcc.co.registration-free',
                    );
                } else if ($this->details['accessType'] == AccessTypes::WORKSHOP_ONLY->value) {
                    return new Content(
                        markdown: 'emails.2025.rcc.wo.registration-free',
                    );
                } else {
                    return new Content(
                        markdown: 'emails.2025.rcc.registration-free',
                    );
                }
            } else if ($this->details['eventCategory'] == "PSW") {
                return new Content(
                    markdown: 'emails.2025.psw.registration-free',
                );
            } else if ($this->details['eventCategory'] == "PSTW") {
                return new Content(
                    markdown: 'emails.2025.pstw.registration-free',
                );
            } else if ($this->details['eventCategory'] == "AF") {
                return new Content(
                    markdown: 'emails.2025.af.registration-free',
                );
            } else if ($this->details['eventCategory'] == "IPAW") {
                return new Content(
                    markdown: 'emails.2025.ipaw.registration-free',
                );
            } else {
                return new Content(
                    markdown: 'emails.registration-free',
                );
            }
        } else if ($this->details['eventYear'] == '2026') {

            if ($this->details['eventCategory'] == "RIC") {
                return new Content(
                    markdown: 'emails.2026.ric.registration-free',
                );
            }
        } else {
            return new Content(
                markdown: 'emails.registration-free',
            );
        }
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
