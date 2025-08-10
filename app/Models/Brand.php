<?php

namespace App\Models;

use App\Services\LanguageService;
use App\Traits\LanguageTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Brand extends Model
{
    use HasFactory, SoftDeletes, LanguageTrait, HasTranslations;

    protected $fillable = [
        'name',
        'image',
        'availability',
        'orders',
    ];

    protected $casts = [
        'availability' => 'boolean',
    ];

    public $translatable = ['name'];

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => $this->getAllTranslations('name'),
        );
    }

    // image is a url
    protected $appends = ['image_url'];
    public function getImageUrlAttribute()
    {
        $local = LanguageService::getLocale();
        if (empty($this->image) || is_null($this->image) || !isset($this->image)) {
            return \App\Services\AvatarService::generateAvatar($this->name[$local], 256, 'random', 0);
        }

        return url('storage/' . $this->image);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'brand_products', 'brand_id', 'product_id');
    }

    public function brandProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'brand_products');
    }

    public function seo(): MorphOne
    {
        return $this->morphOne(Seo::class, 'seoable');
    }
}
