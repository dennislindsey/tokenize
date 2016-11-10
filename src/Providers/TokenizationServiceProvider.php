<?php
/**
 * Class TokenizationServiceProvider
 *
 * @date   11/10/16
 * @author dennis
 */

namespace DennisLindsey\Tokenize\Providers;

use Illuminate\Support\ServiceProvider;

class TokenizationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/tokenization.php' => app()->basePath() . '/config/tokenization.php',
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/tokenization.php', 'tokenization'
        );
    }
}
