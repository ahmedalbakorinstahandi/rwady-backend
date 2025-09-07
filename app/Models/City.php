<?php

namespace App\Models;

use App\Models\Country;
use App\Traits\LanguageTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class City extends Model
{
    use HasFactory, SoftDeletes, HasTranslations, LanguageTrait;

    public $translatable = ['name'];

    protected $fillable = [
        'name',
        'place_id',
        'country_id',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function areas()
    {
        return $this->hasMany(Area::class);
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => $this->getAllTranslations('name'),
        );
    }
}
