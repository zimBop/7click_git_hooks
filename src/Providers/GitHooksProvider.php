<?php

namespace Zimbop\GitHooks\Providers;

use Illuminate\Support\ServiceProvider;
use Zimbop\GitHooks\Commands\SendPrePushNotify;

class GitHooksProvider extends ServiceProvider
{
    const CONFIG_PATH = __DIR__ . '/../../config/git_hooks.php';
    const HUSKY_PATH = __DIR__ . '/../../husky';

    public function boot()
    {
        $this->publishes([
            self::HUSKY_PATH => base_path('.husky'),
        ], 'git-hooks');

        chmod(base_path('.husky') . '/pre-push', 0755);

        $this->publishes([
            self::CONFIG_PATH => config_path('git_hooks.php'),
        ], 'git-hooks-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                SendPrePushNotify::class,
            ]);
        }
    }
}
