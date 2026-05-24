<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCheckInModel extends Model
{
    public $timestamps = false;

    protected $table = 'user_check_in';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'checked_in_on',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'checked_in_on' => 'date',
    ];
}
