<?php

namespace App\Models;

use App\Traits\LanguageTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class FeaturedSection extends Model
{
    use HasFactory, SoftDeletes, LanguageTrait, HasTranslations;

    protected $fillable = [
        'name',
        'image',
        'link',
        'start_date',
        'end_date',
        'availability',
    ];

    protected $casts = [
        'availability' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public $translatable = ['name'];

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => $this->getAllTranslations('name'),
        );
    }

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->image ? url($this->image) : null;
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'model');
    }
}
