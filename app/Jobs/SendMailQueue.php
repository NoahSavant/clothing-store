<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;

class SendMailQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $emailAddress;

    protected $mail;

    public function __construct($emailAddress, $mail)
    {
        $this->emailAddress = $emailAddress;
        $this->mail = $mail;
    }

    public function handle(): void
    {
        Mail::to($this->emailAddress)->send($this->mail);
    }
}
