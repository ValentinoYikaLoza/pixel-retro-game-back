<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusModel extends Model
{
    const PENDING = 1;
    const IN_PROGRESS = 2;
    const COMPLETED = 3;

    public $timestamps = false;

    use HasFactory;

    protected $table = 'status';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'name' => 'string',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
