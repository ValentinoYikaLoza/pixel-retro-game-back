<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveShopModel extends Model
{
    public $timestamps = false;

    use HasFactory;

    protected $table = 'live_shop';
    protected $primaryKey = 'id';
    protected $fillable = [
        'quantity',
        'price',
        'type_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'float',
        'type_id' => 'integer',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
