<?php

namespace App\Services;

use App\Mail\CoreBankMail;
use App\Models\Message;
use Exception;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    protected int $maxAttempts = 3;

    // ================= EMAIL =================
    public function sendEmail($customer, $subject, $content)
    {
        $log = Message::create([
            'customer_id' => $customer->id,
            'channel' => 'email',
            'recipient' => $customer->email,
            'subject' => $subject,
            'message' => $content,
            'status' => 'pending',
        ]);

        try {
            Mail::to($customer->email)->send(
                new CoreBankMail(
                    subjectLine: $subject,
                    name: $customer->first_name,
                    content: $content
                )
            );

            $log->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

        } catch (Exception $e) {

            // increment attempts safely
            $log->increment('attempts');

            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }

        return $log;
    }

    // ================= SMS =================
    public function sendSms($customer, $message)
    {
        $log = Message::create([
            'customer_id' => $customer->id,
            'channel' => 'sms',
            'recipient' => $customer->phone,
            'message' => $message,
            'status' => 'pending',
        ]);

        try {
            // 🔥 Plug your SMS API here later

            $log->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

        } catch (Exception $e) {

            $log->increment('attempts');

            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }

        return $log;
    }

    // ================= RETRY FAILED =================
    public function retryFailed()
    {
        Message::where('status', 'failed')
            ->where('attempts', '<', $this->maxAttempts)
            ->get()
            ->each(function ($msg) {

                if ($msg->channel === 'email') {

                    $customer = (object)[
                        'id' => $msg->customer_id,
                        'email' => $msg->recipient,
                        'first_name' => 'Customer'
                    ];

                    $this->sendEmail(
                        $customer,
                        $msg->subject,
                        $msg->message
                    );
                }

                if ($msg->channel === 'sms') {

                    $customer = (object)[
                        'id' => $msg->customer_id,
                        'phone' => $msg->recipient
                    ];

                    $this->sendSms(
                        $customer,
                        $msg->message
                    );
                }
            });
    }
}