<?php

namespace Tests\Feature;

use App\Models\UserModel;
use App\Services\LeagueService;
use Database\Seeders\CountrySeeder;
use Database\Seeders\DivisionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LeagueServiceTest extends TestCase
{
    use RefreshDatabase;

    private int $countryId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CountrySeeder::class);
        $this->seed(DivisionSeeder::class);
        $this->countryId = (int) DB::table('country')->value('id');
    }

    private function makeUser(int $division, int $weeklyPoints): UserModel
    {
        return UserModel::forceCreate([
            'name' => 'u' . uniqid(),
            'score' => 1000, // de por vida; no debe tocarse
            'weekly_points' => $weeklyPoints,
            'coins' => 0,
            'lives' => 5,
            'streak' => 0,
            'streak_freezes' => 0,
            'last_milestone' => 0,
            'times_ranked_first' => 0,
            'division_id' => $division,
            'country_id' => $this->countryId,
        ]);
    }

    public function test_top_promotes_bottom_relegates_champion_and_reset(): void
    {
        // 10 usuarios en la división 2 con puntos 100,90,...,10.
        $users = [];
        for ($i = 0; $i < 10; $i++) {
            $users[$i] = $this->makeUser(2, 100 - $i * 10);
        }

        $summary = app(LeagueService::class)->rollover();

        // 20% sube y 20% baja => 2 y 2.
        $this->assertSame(2, $summary['promoted']);
        $this->assertSame(2, $summary['relegated']);
        $this->assertSame(1, $summary['champions']);

        // Top 2 (100, 90) ascienden a la división 3.
        $this->assertSame(3, (int) $users[0]->fresh()->division_id);
        $this->assertSame(3, (int) $users[1]->fresh()->division_id);
        // Fondo 2 (20, 10) descienden a la división 1.
        $this->assertSame(1, (int) $users[8]->fresh()->division_id);
        $this->assertSame(1, (int) $users[9]->fresh()->division_id);
        // El medio se queda.
        $this->assertSame(2, (int) $users[5]->fresh()->division_id);

        // El #1 suma un title; el score de por vida no se toca.
        $this->assertSame(1, (int) $users[0]->fresh()->times_ranked_first);
        $this->assertSame(1000, (int) $users[0]->fresh()->score);

        // Todos los weekly_points se reinician.
        $this->assertSame(0, (int) UserModel::sum('weekly_points'));
    }

    public function test_inactive_top_division_does_not_promote_but_resets(): void
    {
        // Un usuario solo, en la división más baja, sin puntos: no asciende ni
        // desciende, pero sus weekly_points se reinician.
        $u = $this->makeUser(1, 0);

        $summary = app(LeagueService::class)->rollover();

        $this->assertSame(0, $summary['promoted']);
        $this->assertSame(1, (int) $u->fresh()->division_id);
        $this->assertSame(0, (int) $u->fresh()->times_ranked_first); // sin puntos, no es campeón
    }
}
