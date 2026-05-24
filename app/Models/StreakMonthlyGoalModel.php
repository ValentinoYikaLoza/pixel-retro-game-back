<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StreakMonthlyGoalModel extends Model
{
    public $timestamps = false;

    protected $table = 'streak_monthly_goal';
    protected $primaryKey = 'id';

    protected $fillable = [
        'days_required',
        'reward_coins',
        'reward_freezes',
        'reward_exp',
    ];

    protected $casts = [
        'days_required' => 'integer',
        'reward_coins' => 'integer',
        'reward_freezes' => 'integer',
        'reward_exp' => 'integer',
    ];
}
