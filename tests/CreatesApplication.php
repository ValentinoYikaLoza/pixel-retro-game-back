<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        // Tests siempre sobre sqlite en memoria, sin importar las variables de
        // entorno reales del contenedor (Docker fija DB_DATABASE y eso pisa a
        // phpunit). Así los tests son autocontenidos y no tocan la BD real.
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'broadcasting.default' => 'null',
        ]);
        $app->make('db')->purge('sqlite');

        return $app;
    }
}
