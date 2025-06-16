<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HomeSection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'show_title',
        'type',
        'status',
        'limit',
        'can_show_more',
        'show_more_path',
        'orders',
        'is_active',
        'data'
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function getShowMorePathAttribute()
    {
        return $this->can_show_more ? $this->show_more_path : null;
    }


    protected function data(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => json_decode($value, true),
            set: fn($value) => json_encode($value),
        );
    }
}
