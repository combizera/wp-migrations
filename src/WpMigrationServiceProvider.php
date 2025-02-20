<?php

namespace Combizera\WpMigration;

use Combizera\WpMigration\Console\MigrateWpXmlCommand;
use Illuminate\Support\ServiceProvider;

class WpMigrationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            MigrateWpXmlCommand::class,
        ]);
    }


    public function boot()
    {
        // Teste para ver se o provider foi carregado
        \Log::info('WpMigrationServiceProvider carregado com sucesso!');
    }
}
