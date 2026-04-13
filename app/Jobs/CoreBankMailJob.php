<?php

namespace App\Jobs;

use App\Mail\CoreBankMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class CoreBankMailJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $email,
        public string $name,
        public string $subject,
        public string $content
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->email)->send(
            new CoreBankMail(
                subjectLine: $this->subject,
                content: $this->content,
                name: $this->name
            )
        );
    }
}