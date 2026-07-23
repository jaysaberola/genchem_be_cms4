<?php

namespace App\Providers;

use App\Support\MailConfigurator;
use Illuminate\Mail\MailManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use  Illuminate\Support\Facades\Schema;

use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Collection::macro('paginate', function($perPage, $total = null, $page = null, $pageName = 'page'){

            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

            return new LengthAwarePaginator(

                $this->forPage($page, $perPage),

                $total ?: $this->count(),

                $perPage,

                $page,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                ]
            );
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        MailConfigurator::apply();
        $this->registerSmtpTransport();

        Paginator::useBootstrap();
        Schema::defaultStringLength(191);
    }

    private function registerSmtpTransport(): void
    {
        $this->app->afterResolving(MailManager::class, function (MailManager $manager) {
            $manager->extend('smtp', function (array $config) {
                $factory = new EsmtpTransportFactory();

                $scheme = $config['scheme'] ?? 'smtp';
                $transport = $factory->create(new Dsn(
                    $scheme,
                    $config['host'],
                    $config['username'] ?? null,
                    $config['password'] ?? null,
                    $config['port'] ?? null,
                ));

                $stream = $transport->getStream();
                if ($stream instanceof SocketStream) {
                    if (! empty($config['timeout'])) {
                        $stream->setTimeout((float) $config['timeout']);
                    }

                    if (! filter_var($config['verify_peer'] ?? true, FILTER_VALIDATE_BOOLEAN)) {
                        $stream->setStreamOptions([
                            'ssl' => [
                                'verify_peer' => false,
                                'verify_peer_name' => false,
                                'allow_self_signed' => true,
                            ],
                        ]);
                    }
                }

                return $transport;
            });
        });
    }
}
