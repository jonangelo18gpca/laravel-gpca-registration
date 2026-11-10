<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class EmailBroadcast extends Mailable
{
    use Queueable, SerializesModels;

    public $details;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        if ($this->details['eventYear'] == '2023') {
            if ($this->details['eventCategory'] == "AFV") {
                $subject = '17ᵗʰ Annual GPCA Forum - Registration & Badge Information';
            } else {
                $subject = '17ᵗʰ Annual GPCA Forum - Registration & Badge Information';
            }
        } else if ($this->details['eventYear'] == '2024') {
            $subject = '18ᵗʰ Annual GPCA Forum - Registration & Badge Information';
        } else if ($this->details['eventYear'] == '2025') {
            if ($this->details['badgeCategory'] == "youth-forum" || $this->details['badgeCategory'] == "youth-council") {
                // $subject = 'Welcome to the GPCA Youth Forum 2025 | Your Guide to the Youth Zone';
                // $subject = '7 Days to Go | Your Youth Zone Schedule & WhatsApp Link';
                // $subject = 'Two days to go. Be in the Youth Zone daily';
                $subject = 'Tomorrow we start. Begin in the Youth Zone';
            } else {
                $subject = '19ᵗʰ Annual GPCA Forum - Registration & Badge Information';
            }
        } else if ($this->details['eventYear'] == '2026') {
            $subject = '20ᵗʰ Annual GPCA Forum - Registration & Badge Information';
        } else {

            $subject = 'Annual GPCA Forum - Registration & Badge Information';
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
    // public function content()
    // {
    //     if ($this->details['eventYear'] == '2023') {
    //         if ($this->details['eventCategory'] == "AF") {
    //             if ($this->details['registrationStatus'] == "confirmed") {
    //                 return new Content(
    //                     markdown: 'emails.2023.af.confirm-email-broadcast',
    //                 );
    //             } else {
    //                 return new Content(
    //                     markdown: 'emails.2023.af.pending-email-broadcast',
    //                 );
    //             }
    //         } else if ($this->details['eventCategory'] == "AFV") {
    //             if ($this->details['registrationStatus'] == "confirmed") {
    //                 return new Content(
    //                     markdown: 'emails.2023.visitor.confirm-email-broadcast',
    //                 );
    //             } else {
    //                 return new Content(
    //                     markdown: 'emails.2023.visitor.pending-email-broadcast',
    //                 );
    //             }
    //         } else if ($this->details['eventYear'] == '2026') {
    //             if ($this->details['eventCategory'] == "AF") {
    //                 if ($this->details['registrationStatus'] == "confirmed") {
    //                     return new Content(
    //                         markdown: 'emails.2026.af.confirm-email-broadcast',
    //                     );
    //                 } else {
    //                     return new Content(
    //                         markdown: 'emails.2026.af.pending-email-broadcast',
    //                     );
    //                 }
    //             } else {
    //                 return new Content(
    //                     markdown: 'emails.email-broadcast',
    //                 );
    //             }
    //         } else {
    //             return new Content(
    //                 markdown: 'emails.email-broadcast',
    //             );
    //         }
    //     } else if ($this->details['eventYear'] == '2024') {
    //         if ($this->details['eventCategory'] == "AF") {
    //             if ($this->details['registrationStatus'] == "confirmed") {
    //                 return new Content(
    //                     markdown: 'emails.2024.af.confirm-email-broadcast',
    //                 );
    //             } else {
    //                 return new Content(
    //                     markdown: 'emails.2024.af.pending-email-broadcast',
    //                 );
    //             }
    //         } else {
    //             return new Content(
    //                 markdown: 'emails.email-broadcast',
    //             );
    //         }
    //     } else if ($this->details['eventYear'] == '2025') {
    //         if ($this->details['eventCategory'] == "AF") {
    //             if ($this->details['badgeCategory'] == "youth-forum" || $this->details['badgeCategory'] == "youth-council") {
    //                 if ($this->details['registrationStatus'] == "confirmed") {
    //                     return new Content(
    //                         // markdown: 'emails.2025.af.youth.broadcast.1.confirm-email-broadcast',
    //                         // markdown: 'emails.2025.af.youth.broadcast.2.confirm-email-broadcast',
    //                         // markdown: 'emails.2025.af.youth.broadcast.3.confirm-email-broadcast',
    //                         markdown: 'emails.2025.af.youth.broadcast.4.confirm-email-broadcast',
    //                     );
    //                 } else {
    //                     return new Content(
    //                         // markdown: 'emails.2025.af.youth.broadcast.1.pending-email-broadcast',
    //                         // markdown: 'emails.2025.af.youth.broadcast.2.pending-email-broadcast',
    //                         // markdown: 'emails.2025.af.youth.broadcast.3.pending-email-broadcast',
    //                         markdown: 'emails.2025.af.youth.broadcast.4.pending-email-broadcast',
    //                     );
    //                 }
    //             } else {
    //                 if ($this->details['registrationStatus'] == "confirmed") {
    //                     return new Content(
    //                         markdown: 'emails.2025.af.confirm-email-broadcast',
    //                     );
    //                 } else {
    //                     return new Content(
    //                         markdown: 'emails.2025.af.pending-email-broadcast',
    //                     );
    //                 }
    //             }
    //         } else {
    //             return new Content(
    //                 markdown: 'emails.email-broadcast',
    //             );
    //         }
    //     } else {
    //         return new Content(
    //             markdown: 'emails.email-broadcast',
    //         );
    //     }
    // }


    public function content()
{
    if ($this->details['eventYear'] == '2023') {
        if ($this->details['eventCategory'] == "AF") {
            if ($this->details['registrationStatus'] == "confirmed") {
                return new Content(markdown: 'emails.2023.af.confirm-email-broadcast');
            } else {
                return new Content(markdown: 'emails.2023.af.pending-email-broadcast');
            }
        } else if ($this->details['eventCategory'] == "AFV") {
            if ($this->details['registrationStatus'] == "confirmed") {
                return new Content(markdown: 'emails.2023.visitor.confirm-email-broadcast');
            } else {
                return new Content(markdown: 'emails.2023.visitor.pending-email-broadcast');
            }
        } else {
            return new Content(markdown: 'emails.email-broadcast');
        }
    } else if ($this->details['eventYear'] == '2024') {
        if ($this->details['eventCategory'] == "AF") {
            if ($this->details['registrationStatus'] == "confirmed") {
                return new Content(markdown: 'emails.2024.af.confirm-email-broadcast');
            } else {
                return new Content(markdown: 'emails.2024.af.pending-email-broadcast');
            }
        } else {
            return new Content(markdown: 'emails.email-broadcast');
        }
    } else if ($this->details['eventYear'] == '2025') {
        if ($this->details['eventCategory'] == "AF") {
            if ($this->details['badgeCategory'] == "youth-forum" || $this->details['badgeCategory'] == "youth-council") {
                if ($this->details['registrationStatus'] == "confirmed") {
                    return new Content(markdown: 'emails.2025.af.youth.broadcast.4.confirm-email-broadcast');
                } else {
                    return new Content(markdown: 'emails.2025.af.youth.broadcast.4.pending-email-broadcast');
                }
            } else {
                if ($this->details['registrationStatus'] == "confirmed") {
                    return new Content(markdown: 'emails.2025.af.confirm-email-broadcast');
                } else {
                    return new Content(markdown: 'emails.2025.af.pending-email-broadcast');
                }
            }
        } else {
            return new Content(markdown: 'emails.email-broadcast');
        }
    } else if ($this->details['eventYear'] == '2026') {
        if ($this->details['eventCategory'] == "AF") {
            if ($this->details['registrationStatus'] == "confirmed") {
                return new Content(markdown: 'emails.2026.af.confirm-email-broadcast');
            } else {
                return new Content(markdown: 'emails.2026.af.pending-email-broadcast');
            }
        } else {
            return new Content(markdown: 'emails.email-broadcast');
        }
    } else {
        return new Content(markdown: 'emails.email-broadcast');
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
