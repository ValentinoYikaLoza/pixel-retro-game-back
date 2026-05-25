<?php

namespace Tests\Feature;

use App\Models\DailyMissionModel;
use App\Models\GameModel;
use App\Models\MissionTypeModel;
use App\Models\RewardModel;
use App\Models\StatusModel;
use App\Models\UserDailyMissionModel;
use App\Models\UserGameStatModel;
use App\Models\UserModel;
use App\Services\MissionService;
use Database\Seeders\CountrySeeder;
use Database\Seeders\DivisionSeeder;
use Database\Seeders\GameSeeder;
use Database\Seeders\MissionTypeSeeder;
use Database\Seeders\RewardSeeder;
use Database\Seeders\StatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MissionRolloverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CountrySeeder::class);
        $this->seed(DivisionSeeder::class);
        $this->seed(GameSeeder::class);
        $this->seed(MissionTypeSeeder::class);
        $this->seed(RewardSeeder::class);
        $this->seed(StatusSeeder::class);
    }

    private function makeUser(): UserModel
    {
        return UserModel::forceCreate([
            'name' => 'u' . uniqid(),
            'score' => 0, 'weekly_points' => 0, 'coins' => 0, 'lives' => 5,
            'streak' => 0, 'streak_freezes' => 0, 'last_milestone' => 0,
            'times_ranked_first' => 0, 'division_id' => 1,
            'country_id' => (int) DB::table('country')->value('id'),
        ]);
    }

    private function dailyMission(int $game, int $type, int $value): DailyMissionModel
    {
        return DailyMissionModel::forceCreate([
            'description' => 'm' . uniqid(),
            'total_value' => $value,
            'game_id' => $game,
            'mission_type_id' => $type,
            'reward_id' => RewardModel::BRONZE,
            'active' => true,
        ]);
    }

    public function test_single_game_score_completes_only_with_one_qualifying_game(): void
    {
        $user = $this->makeUser();
        $mission = $this->dailyMission(GameModel::SNAKE, MissionTypeModel::SINGLE_GAME_SCORE, 500);
        $um = UserDailyMissionModel::forceCreate([
            'current_value' => 0, 'completed_at' => null, 'user_id' => $user->id,
            'daily_mission_id' => $mission->id, 'status_id' => StatusModel::PENDING,
            'period_key' => now('UTC')->format('Y-m-d'),
        ]);

        $svc = app(MissionService::class);
        // Una partida por debajo del umbral: no avanza (no acumula).
        $svc->advanceForGame($user->id, GameModel::SNAKE, 300);
        $this->assertSame(0, (int) $um->fresh()->current_value);
        $this->assertSame(StatusModel::PENDING, (int) $um->fresh()->status_id);

        // Una partida que alcanza el umbral: completa + recompensa.
        $svc->advanceForGame($user->id, GameModel::SNAKE, 600);
        $this->assertSame(StatusModel::COMPLETED, (int) $um->fresh()->status_id);
        $this->assertSame(50, (int) $user->fresh()->coins);
    }

    public function test_rollover_prefers_a_game_the_user_plays(): void
    {
        $user = $this->makeUser();

        // Pool activo: misiones para SNAKE y TETRIS.
        $this->dailyMission(GameModel::SNAKE, MissionTypeModel::POINTS, 600);
        $this->dailyMission(GameModel::TETRIS, MissionTypeModel::POINTS, 600);

        // El usuario solo juega SNAKE.
        UserGameStatModel::forceCreate([
            'user_id' => $user->id, 'game_id' => GameModel::SNAKE,
            'high_score' => 100, 'total_games' => 10, 'total_score' => 500,
            'total_food' => 0, 'last_played_at' => now(),
        ]);

        // Misión diaria VENCIDA (período viejo) apuntando a TETRIS.
        $tetris = $this->dailyMission(GameModel::TETRIS, MissionTypeModel::MATCHES, 5);
        $um = UserDailyMissionModel::forceCreate([
            'current_value' => 3, 'completed_at' => null, 'user_id' => $user->id,
            'daily_mission_id' => $tetris->id, 'status_id' => StatusModel::IN_PROGRESS,
            'period_key' => '2000-01-01', // vencida => se reasigna
        ]);

        // listForUser dispara el rollover.
        app(MissionService::class)->listForUser($user->id);

        $reassigned = $um->fresh()->load('dailyMission');
        $this->assertSame(GameModel::SNAKE, (int) $reassigned->dailyMission->game_id, 'debe preferir SNAKE (lo que juega)');
        $this->assertSame(0, (int) $reassigned->current_value, 'progreso reiniciado');
        $this->assertSame(StatusModel::PENDING, (int) $reassigned->status_id);
    }
}
