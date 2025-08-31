<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MissionModel extends Model
{
    public $timestamps = false;

    use HasFactory;

    protected $table = 'mission';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'description',
        'total_points',
        'current_points',
    ];

    protected $casts = [
        'name' => 'string',
        'description' => 'string',
        'total_points' => 'integer',
        'current_points' => 'integer',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function frequency()
    {
        return $this->belongsTo(FrequencyModel::class, 'frequency_id');
    }

    public function reward()
    {
        return $this->belongsTo(RewardModel::class, 'reward_id');
    }
}
