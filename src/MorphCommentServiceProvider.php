<?php

namespace WalkerChiu\MorphComment;

use Illuminate\Support\ServiceProvider;

class MorphCommentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfig();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config files
        $this->publishes([
           __DIR__ .'/config/morph-comment.php' => config_path('wk-morph-comment.php'),
        ], 'config');

        // Publish migration files
        $from = __DIR__ .'/database/migrations/';
        $to   = database_path('migrations') .'/';
        $this->publishes([
            $from .'create_wk_morph_comment_table.php'
                => $to .date('Y_m_d_His', time()) .'_create_wk_morph_comment_table.php'
        ], 'migrations');

        $this->loadTranslationsFrom(__DIR__.'/translations', 'php-morph-comment');
        $this->publishes([
            __DIR__.'/translations' => resource_path('lang/vendor/php-morph-comment'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                config('wk-morph-comment.command.cleaner')
            ]);
        }

        config('wk-core.class.morph-comment.comment')::observe(config('wk-core.class.morph-comment.commentObserver'));
        config('wk-core.class.morph-comment.commentLang')::observe(config('wk-core.class.morph-comment.commentLangObserver'));
    }

    /**
     * Register the blade directives
     *
     * @return void
     */
    private function bladeDirectives()
    {
    }

    /**
     * Merges user's and package's configs.
     *
     * @return void
     */
    private function mergeConfig()
    {
        if (!config()->has('wk-morph-comment')) {
            $this->mergeConfigFrom(
                __DIR__ .'/config/morph-comment.php', 'wk-morph-comment'
            );
        }

        $this->mergeConfigFrom(
            __DIR__ .'/config/morph-comment.php', 'morph-comment'
        );
    }

    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param String  $path
     * @param String  $key
     * @return void
     */
    protected function mergeConfigFrom($path, $key)
    {
        if (
            !(
                $this->app instanceof CachesConfiguration
                && $this->app->configurationIsCached()
            )
        ) {
            $config = $this->app->make('config');
            $content = $config->get($key, []);

            $config->set($key, array_merge(
                require $path, $content
            ));
        }
    }
}
