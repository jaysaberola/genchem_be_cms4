<?php

namespace App\Console\Commands;

use App\Mail\InquiryAdminMail;
use App\Models\EmailRecipient;
use App\Helpers\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestContactMailCommand extends Command
{
    protected $signature = 'contact:test-mail {email? : Recipient to test (defaults to first email recipient)}';

    protected $description = 'Send a test contact notification email to verify SMTP settings';

    public function handle(): int
    {
        $to = $this->argument('email');

        if (! $to) {
            $to = EmailRecipient::query()->value('email') ?? Setting::info()->email ?? null;
        }

        if (! $to || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->error('No valid recipient. Add an Email Recipient in CMS or pass an email argument.');
            return self::FAILURE;
        }

        $setting = Setting::info();
        $client = [
            'name'    => 'Test User',
            'email'   => config('mail.from.address', 'test@example.com'),
            'contact' => '09123456789',
            'subject' => 'Contact Us — Test',
            'message' => 'This is a test message from contact:test-mail.',
        ];
        $adminInfo = (object) ['name' => 'Admin', 'email' => $to];

        $this->info('Mail driver: ' . config('mail.default'));
        $this->info('Mail scheme: ' . (config('mail.mailers.smtp.scheme') ?: '(auto)'));
        $this->info('Mail host: ' . config('mail.mailers.smtp.host'));
        $this->info('Mail port: ' . config('mail.mailers.smtp.port'));
        $this->info('From: ' . config('mail.from.address'));
        $this->info('Sending test to: ' . $to);

        try {
            Mail::to($to)->send(new InquiryAdminMail($setting, $client, $adminInfo));
            $this->info('Test email sent successfully.');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
