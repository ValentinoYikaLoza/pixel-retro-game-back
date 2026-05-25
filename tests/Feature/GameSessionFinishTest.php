<?php

namespace Tests\Feature;

use App\Exceptions\ApiException;
use App\Models\GameModel;
use App\Models\GameSessionModel;
use App\Models\UserModel;
use App\Services\GameSessionService;
use Database\Seeders\CountrySeeder;
use Database\Seeders\DivisionSeeder;
use Database\Seeders\GameLevelSeeder;
use Database\Seeders\GameSeeder;
use Database\Seeders\MissionTypeSeeder;
use Database\Seeders\RewardSeeder;
use Database\Seeders\StatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GameSessionFinishTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CountrySeeder::class);
        $this->seed(DivisionSeeder::class);
        $this->seed(GameSeeder::class);
        $this->seed(GameLevelSeeder::class); // niveles de Snake
        $this->seed(StatusSeeder::class);
        $this->seed(MissionTypeSeeder::class);
        $this->seed(RewardSeeder::class);
    }

    private function makeUser(): UserModel
    {
        return UserModel::forceCreate([
            'name' => 'u' . uniqid(),
            'score' => 0,
            'weekly_points' => 0,
            'coins' => 0,
            'lives' => 5,
            'streak' => 0,
            'streak_freezes' => 0,
            'last_milestone' => 0,
            'times_ranked_first' => 0,
            'division_id' => 1,
            'country_id' => (int) DB::table('country')->value('id'),
        ]);
    }

    public function test_implausible_result_is_rejected(): void
    {
        $user = $this->makeUser();
        $svc = app(GameSessionService::class);
        $session = $svc->start($user->id, 'snake', 1);

        // Sin tiempo transcurrido en el servidor (started_at = ahora): comer 50
        // comidas es imposible => debe rechazarse.
        $this->expectException(ApiException::class);
        $svc->finish($user->id, $session['session_id'], 9999, 50, 60000);
    }

    public function test_finish_accrues_score_weekly_points_and_is_idempotent(): void
    {
        $user = $this->makeUser();
        $svc = app(GameSessionService::class);
        $session = $svc->start($user->id, 'snake', 1);

        // Simula 60 s de partida (el servidor recorta la duración al tiempo real).
        GameSessionModel::where('id', $session['session_id'])
            ->update(['started_at' => now()->subSeconds(60)]);

        $svc->finish($user->id, $session['session_id'], 10, 5, 60000);

        $fresh = $user->fresh();
        $this->assertSame(10, (int) $fresh->score, 'score de por vida');
        $this->assertSame(10, (int) $fresh->weekly_points, 'puntos de liga');
        $this->assertSame(1, (int) $fresh->coins, '10 puntos => 1 moneda');

        // Cerrar otra vez no vuelve a otorgar (idempotente por sesión).
        $svc->finish($user->id, $session['session_id'], 10, 5, 60000);
        $this->assertSame(10, (int) $user->fresh()->score);
        $this->assertSame(10, (int) $user->fresh()->weekly_points);
    }

    public function test_infinite_mode_uses_separate_record_and_no_level_clear(): void
    {
        $user = $this->makeUser();
        $svc = app(GameSessionService::class);

        // level 0 = infinito: no requiere nivel y usa la config base del juego.
        $session = $svc->start($user->id, 'snake', 0);
        $this->assertSame(0, (int) $session['level']);
        $this->assertSame(0, (int) $session['target_score']);

        GameSessionModel::where('id', $session['session_id'])
            ->update(['started_at' => now()->subSeconds(60)]);

        $result = $svc->finish($user->id, $session['session_id'], 10, 5, 60000);

        // No hay "nivel superado" en infinito.
        $this->assertFalse($result['level_cleared'] ?? false);

        // El récord va al infinite_high_score, NO al high_score de niveles.
        $stat = \App\Models\UserGameStatModel::where('user_id', $user->id)
            ->where('game_id', \App\Models\GameModel::SNAKE)->first();
        $this->assertSame(10, (int) $stat->infinite_high_score);
        $this->assertSame(0, (int) $stat->high_score);
    }
}
