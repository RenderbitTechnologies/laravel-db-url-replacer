<?php

namespace Renderbit\DbUrlReplacer;

use Illuminate\Support\ServiceProvider;
use Renderbit\DbUrlReplacer\Commands\ReplaceUrlInDatabase;

class DbUrlReplacerServiceProvider extends ServiceProvider
{
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ReplaceUrlInDatabase::class,
            ]);
        }
    }
}
