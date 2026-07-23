<?php

namespace App\Support;

use Illuminate\Support\Facades\Config;

class MailConfigurator
{
    public static function apply(): void
    {
        self::normalizeFromAddress();

        $port = (int) Config::get('mail.mailers.smtp.port', 587);
        $encryption = strtolower((string) Config::get('mail.mailers.smtp.encryption', 'tls'));

        if (! Config::get('mail.mailers.smtp.scheme')) {
            Config::set(
                'mail.mailers.smtp.scheme',
                ($port === 465 || $encryption === 'ssl') ? 'smtps' : 'smtp'
            );
        }

        if (! Config::get('mail.mailers.smtp.local_domain')) {
            $domain = env('MAIL_EHLO_DOMAIN');
            if (! $domain) {
                $from = (string) Config::get('mail.from.address', '');
                if (str_contains($from, '@')) {
                    $domain = substr(strrchr($from, '@'), 1) ?: null;
                }
            }
            if ($domain) {
                Config::set('mail.mailers.smtp.local_domain', $domain);
            }
        }

        if (Config::get('mail.mailers.smtp.timeout') === null) {
            Config::set('mail.mailers.smtp.timeout', (int) env('MAIL_TIMEOUT', 30));
        }

        Config::set(
            'mail.mailers.smtp.verify_peer',
            filter_var(env('MAIL_SSL_VERIFY', true), FILTER_VALIDATE_BOOLEAN)
        );
    }

    private static function normalizeFromAddress(): void
    {
        $from = trim((string) Config::get('mail.from.address', ''), " \t\n\r\0\x0B\"'");
        if ($from !== '') {
            Config::set('mail.from.address', $from);
        }

        $username = trim((string) Config::get('mail.mailers.smtp.username', ''), " \t\n\r\0\x0B\"'");
        if ($username !== '') {
            Config::set('mail.mailers.smtp.username', $username);
        }
    }
}
