<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'allow_null',
        'is_setting',
    ];

    protected $casts = [
        'key' => 'string',
        'allow_null' => 'boolean',
        'is_setting' => 'boolean',
        'value' => 'string',
    ];
} 