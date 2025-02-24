<?php

namespace Combizera\WpMigration;

use Combizera\WpMigration\Console\MigrateWpXmlCommand;
use Illuminate\Support\ServiceProvider;

class WpMigrationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->commands([
            MigrateWpXmlCommand::class,
        ]);
    }

    public function boot(): void
    {
        if (!$this->app->runningInConsole()) {
            \Log::info('WpMigrationServiceProvider running 🔥');
        }
    }
}
