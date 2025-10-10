<?php

namespace App\Console\Commands;

use App\Events\TimeUpdated;
use Illuminate\Console\Command;

class BroadcastTime extends Command
{
    protected $signature = 'broadcast:time';
    protected $description = 'Emite el tiempo restante actualizado cada segundo.';

    public function handle()
    {
        // Enviamos el evento cada segundo en un bucle infinito
        $this->info('Iniciando emisión de TimeUpdated cada segundo...');

        while (true) {
            broadcast(new TimeUpdated());
            sleep(1);
        }
    }
}
