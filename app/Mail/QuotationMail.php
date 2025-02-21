<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuotationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $quote; 

    public function __construct($quote)
    {
        $this->quote = $quote;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Here is your Quotation From PMFC.',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $quoteData = json_decode($this->quote->quote, true); // Decode the quote field

        return new Content(
            view: 'emails.quote',
            with: [
              'quote' => $this->quote, // Pass the quote variable correctly
              'total' => $quoteData['total'] ?? 0, // Pass the total to the view
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
