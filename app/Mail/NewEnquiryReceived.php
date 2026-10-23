<?php

namespace App\Mail;

use App\Models\Enquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewEnquiryReceived extends Mailable
{
    use Queueable;

    public function __construct(public Enquiry $enquiry) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New website enquiry — '.$this->enquiry->enquiry_type->label(),
            replyTo: $this->enquiry->email ? [new Address($this->enquiry->email, $this->enquiry->name)] : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.new-enquiry',
            with: ['adminUrl' => route('admin.enquiries.show', $this->enquiry)],
        );
    }
}
