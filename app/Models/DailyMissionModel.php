<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyMissionModel extends Model
{
    public $timestamps = false;

    use HasFactory;

    protected $table = 'daily_mission';
    protected $primaryKey = 'id';
    protected $fillable = [
        'description',
        'total_value',
    ];

    protected $casts = [
        'description' => 'string',
        'total_value' => 'integer',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function game()
    {
        return $this->belongsTo(GameModel::class, 'game_id');
    }

    public function missionType()
    {
        return $this->belongsTo(MissionTypeModel::class, 'mission_type_id');
    }

    public function reward()
    {
        return $this->belongsTo(RewardModel::class, 'reward_id');
    }
}
