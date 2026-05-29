<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Publication;
use App\Models\Document;
use App\Models\Project;
use App\Models\MenuItem;
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
use League\Flysystem\Filesystem;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ── Limitation de débit (Rate Limiting) ─────────────────────────────────

        /**
         * Protection anti brute-force sur la route de connexion.
         * Limite : 5 tentatives par minute par adresse IP.
         */
        RateLimiter::for('auth-login', function (\Illuminate\Http\Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        /**
         * Limitation des uploads de documents Drive.
         * Limite : 10 uploads par heure par utilisateur authentifié (ou par IP si non authentifié).
         */
        RateLimiter::for('drive-upload', function (\Illuminate\Http\Request $request) {
            return Limit::perHour(10)->by($request->user()?->id ?: $request->ip());
        });

        // Register the Microsoft Azure Socialite driver under the name 'microsoft'
        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('microsoft', \SocialiteProviders\Azure\Provider::class);
        });

        // Auto-assign users to their filière group on create/update
        User::observe(UserObserver::class);

        // Polymorphic morph map for social features
        Relation::morphMap([
            'publications' => Publication::class,
            'documents'    => Document::class,
            'projects'     => Project::class,
            'menu-items'   => MenuItem::class,
        ]);

        // Register Azure Blob Storage disk driver (league/flysystem-azure-blob-storage)
        Storage::extend('azure', function ($app, $config) {
            $connectionString = env('AZURE_STORAGE_CONNECTION_STRING')
                ?? sprintf(
                    'DefaultEndpointsProtocol=https;AccountName=%s;AccountKey=%s;EndpointSuffix=core.windows.net',
                    $config['account'] ?? $config['name'] ?? '',
                    $config['key'] ?? ''
                );

            $client  = BlobRestProxy::createBlobService($connectionString);
            $adapter = new AzureBlobStorageAdapter(
                $client,
                $config['container'],
                $config['prefix'] ?? ''
            );

            return new FilesystemAdapter(
                new Filesystem($adapter),
                $adapter,
                $config
            );
        });
    }
}
