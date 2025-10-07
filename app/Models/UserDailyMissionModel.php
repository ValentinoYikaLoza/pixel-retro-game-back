<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDailyMissionModel extends Model
{
    public $timestamps = false;

    use HasFactory;

    protected $table = 'user_daily_mission';
    protected $primaryKey = 'id';

    protected $fillable = [
        'current_value',
        'completed_at',
    ];

    protected $casts = [
        'current_value' => 'integer',
        'completed_at' => 'datetime',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }

    public function dailyMission()
    {
        return $this->belongsTo(DailyMissionModel::class, 'daily_mission_id');
    }

    public function status()
    {
        return $this->belongsTo(StatusModel::class, 'status_id');
    }
}
