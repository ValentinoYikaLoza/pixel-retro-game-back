<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserModel extends Model
{
    public $timestamps = false;

    use HasFactory;

    protected $table = 'user';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'score',
        'weekly_points',
        'coins',
        'lives',
        'streak',
        'last_streak_date',
        'streak_freezes',
        'last_milestone',
        'times_ranked_first'
    ];

    protected $casts = [
        'name' => 'string',
        'score' => 'integer',
        'weekly_points' => 'integer',
        'coins' => 'integer',
        'lives' => 'integer',
        'streak' => 'integer',
        'last_streak_date' => 'date',
        'streak_freezes' => 'integer',
        'last_milestone' => 'integer',
        'times_ranked_first' => 'integer',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function division()
    {
        return $this->belongsTo(DivisionModel::class, 'division_id');
    }

    public function country()
    {
        return $this->belongsTo(CountryModel::class, 'country_id');
    }
}
