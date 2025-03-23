<?php

namespace RfSchubert\CredentialDetector\Laravel\Providers;

use Illuminate\Support\ServiceProvider;
use RfSchubert\CredentialDetector\Detector;
use GuzzleHttp\Client;

/**
 * Service Provider para integração com Laravel.
 */
class CredentialDetectorServiceProvider extends ServiceProvider
{
    /**
     * Registra os serviços.
     *
     * @return void
     */
    public function register()
    {
        // Mesclar configuração
        $this->mergeConfigFrom(
            __DIR__.'/../config/credential-detector.php',
            'credential-detector'
        );

        // Registrar o detector como singleton
        $this->app->singleton('credential-detector', function ($app) {
            $config = $app['config']->get('credential-detector');
            
            return new Detector(
                $config['confidence_threshold'] ?? 0.7,
                $config['patterns'] ?? null,
                $config['preload_model'] ?? false
            );
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publicar configuração
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/credential-detector.php' => config_path('credential-detector.php'),
            ], 'config');
        }
    }
} 