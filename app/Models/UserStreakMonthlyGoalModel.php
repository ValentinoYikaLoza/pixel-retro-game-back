<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStreakMonthlyGoalModel extends Model
{
    public $timestamps = false;

    protected $table = 'user_streak_monthly_goal';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'streak_monthly_goal_id',
        'period_key',
        'claimed_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'streak_monthly_goal_id' => 'integer',
        'claimed_at' => 'datetime',
    ];
}
