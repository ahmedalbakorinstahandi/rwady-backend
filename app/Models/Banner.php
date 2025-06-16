<?php

namespace App\Models;

use App\Traits\LanguageTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Banner extends Model
{
    use HasFactory, SoftDeletes, LanguageTrait, HasTranslations;

    protected $fillable = [
        'title',
        'description',
        'button_text',
        'image',
        'is_popup',
        'link',
        'start_date',
        'end_date',
        'availability'
    ];

    protected $casts = [
        'is_popup' => 'boolean',
        'availability' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public $translatable = ['title', 'description', 'button_text'];

    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => $this->getAllTranslations('title'),
        );
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => $this->getAllTranslations('description'),
        );
    }

    protected function buttonText(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => $this->getAllTranslations('button_text'),
        );
    }

    // image is a url
    protected $appends = ['image_url'];
    public function getImageUrlAttribute()
    {
        return url($this->image);
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'model');
    }
}
