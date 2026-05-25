<?php

namespace App\Console\Commands;

use App\Services\LeagueService;
use Illuminate\Console\Command;

class LeagueRolloverCommand extends Command
{
    protected $signature = 'leagues:rollover';

    protected $description = 'Cierra la semana de liga: asciende/desciende divisiones y reinicia los puntos semanales';

    public function handle(LeagueService $leagues): int
    {
        $summary = $leagues->rollover();

        $this->info(sprintf(
            'Liga: %d ascensos, %d descensos, %d campeones.',
            $summary['promoted'],
            $summary['relegated'],
            $summary['champions'],
        ));

        return self::SUCCESS;
    }
}
