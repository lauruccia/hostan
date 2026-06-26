<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyServicesPdf extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public readonly string $pdfContent,
        public readonly string $filename,
        public readonly Carbon $carbon,
        public readonly int    $totalCount,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $dateLabel = $this->carbon->locale('it')->isoFormat('dddd D MMMM YYYY');

        return new Envelope(
            subject: "📋 Servizi del {$dateLabel} — Riepilogo giornaliero Hostand",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-services-pdf',
            with: [
                'date'       => $this->carbon,
                'totalCount' => $this->totalCount,
                'filename'   => $this->filename,
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
        return [
            Attachment::fromData(
                fn () => $this->pdfContent,
                $this->filename
            )->withMime('application/pdf'),
        ];
    }
}
