<?php

namespace App\Models;

use App\Traits\LanguageTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class HomeSection extends Model
{
    use SoftDeletes, LanguageTrait, HasTranslations;

    public $translatable = ['title'];

    protected $fillable = [
        'title',
        'show_title',
        'type',
        'item_id',
        'status',
        'limit',
        'can_show_more',
        'show_more_path',
        'orders',
        'availability',
        'data'
    ];

    protected $casts = [
        'data' => 'array',
        'availability' => 'boolean',
        'show_title' => 'boolean',
        'can_show_more' => 'boolean',
        'show_more_path' => 'string',
        'limit' => 'integer',
        'orders' => 'integer',
        'item_id' => 'integer',
        'status' => 'string',
        'type' => 'string',
    ];


    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => $this->getAllTranslations('title'),
        );
    }
    public function getShowMorePathAttribute()
    {
        return $this->can_show_more ? $this->attributes['show_more_path'] : null;
    }


    protected function data(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => json_decode($value, true),
            set: fn($value) => json_encode($value),
        );
    }
}
