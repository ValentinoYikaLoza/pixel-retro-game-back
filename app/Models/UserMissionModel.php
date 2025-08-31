<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMissionModel extends Model
{
    public $timestamps = false;

    use HasFactory;

    protected $table = 'user_mission';
    protected $primaryKey = 'id';

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }

    public function mission()
    {
        return $this->belongsTo(MissionModel::class, 'mission_id');
    }

    public function status()
    {
        return $this->belongsTo(StatusModel::class, 'status_id');
    }
}
