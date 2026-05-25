<?php

namespace Tests\Feature;

use App\Models\DailyMissionModel;
use App\Models\GameModel;
use App\Models\MissionTypeModel;
use App\Models\RewardModel;
use App\Models\StatusModel;
use App\Models\UserDailyMissionModel;
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

class MissionRewardTest extends TestCase
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

    public function test_completing_a_mission_grants_its_reward_coins(): void
    {
        $user = $this->makeUser();

        $mission = DailyMissionModel::forceCreate([
            'description' => 'Obtén 100 puntos en Snake',
            'total_value' => 100,
            'game_id' => GameModel::SNAKE,
            'mission_type_id' => MissionTypeModel::POINTS,
            'reward_id' => RewardModel::BRONZE,
        ]);

        $um = UserDailyMissionModel::forceCreate([
            'current_value' => 0,
            'completed_at' => null,
            'user_id' => $user->id,
            'daily_mission_id' => $mission->id,
            'status_id' => StatusModel::PENDING,
            'period_key' => now('UTC')->format('Y-m-d'), // del período actual: no se reasigna
        ]);

        // Jugó Snake con 150 puntos (>= 100): completa la misión.
        app(MissionService::class)->advanceForGame($user->id, GameModel::SNAKE, 150);

        $um->refresh();
        $this->assertSame(StatusModel::COMPLETED, (int) $um->status_id);
        $this->assertSame(100, (int) $um->current_value); // tope en total_value
        $this->assertNotNull($um->completed_at);

        // Recompensa Bronze = 50 monedas, otorgada una vez.
        $this->assertSame(50, (int) $user->fresh()->coins);
    }

    public function test_reward_is_not_granted_twice(): void
    {
        $user = $this->makeUser();
        $mission = DailyMissionModel::forceCreate([
            'description' => 'Obtén 100 puntos en Snake',
            'total_value' => 100,
            'game_id' => GameModel::SNAKE,
            'mission_type_id' => MissionTypeModel::POINTS,
            'reward_id' => RewardModel::BRONZE,
        ]);
        UserDailyMissionModel::forceCreate([
            'current_value' => 0,
            'completed_at' => null,
            'user_id' => $user->id,
            'daily_mission_id' => $mission->id,
            'status_id' => StatusModel::PENDING,
            'period_key' => now('UTC')->format('Y-m-d'),
        ]);

        $service = app(MissionService::class);
        $service->advanceForGame($user->id, GameModel::SNAKE, 150); // completa + recompensa
        $service->advanceForGame($user->id, GameModel::SNAKE, 150); // ya completada: nada

        // Solo se otorgó una vez (50, no 100).
        $this->assertSame(50, (int) $user->fresh()->coins);
    }
}
