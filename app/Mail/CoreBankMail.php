<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CoreBankMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectLine,
        public string $content,
        public ?string $name = null,
        public array $files = []
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.bank-template',
            with: [
                'name' => $this->name,
                'content' => $this->content,
            ]
        );
    }

    public function attachments(): array
    {
        return collect($this->files)
            ->map(fn($file) => Attachment::fromPath($file))
            ->toArray();
    }
}