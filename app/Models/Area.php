<?php

namespace App\Models;

use App\Models\City;
use App\Traits\LanguageTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Area extends Model
{
    use HasFactory, SoftDeletes, HasTranslations, LanguageTrait;

    public $translatable = ['name'];

    protected $fillable = [
        'name',
        'place_id',
        'city_id',
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => $this->getAllTranslations('name'),
        );
    }
}
