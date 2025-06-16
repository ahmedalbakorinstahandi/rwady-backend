<?php






namespace App\Models;

use App\Traits\LanguageTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\LaravelPackageTools\Concerns\Package\HasTranslations;

class Seo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'seo';

    protected $fillable = [
        'seoable_type',
        'seoable_id',
        'meta_title',
        'meta_description',
        'keywords',
        'image'
    ];




    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }
}






// namespace App\Models;

// use App\Traits\LanguageTrait;
// use Illuminate\Database\Eloquent\Casts\Attribute;
// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Relations\MorphTo;
// use Illuminate\Database\Eloquent\SoftDeletes;
// use Spatie\LaravelPackageTools\Concerns\Package\HasTranslations;

// class Seo extends Model
// {
//     use HasFactory, SoftDeletes, LanguageTrait, HasTranslations;

//     protected $table = 'seo';

//     protected $fillable = [
//         'seoable_type',
//         'seoable_id',
//         'meta_title',
//         'meta_description',
//         'keywords',
//         'image'
//     ];

//     public $translatable = ['meta_title', 'meta_description'];

//     protected function metaTitle(): Attribute
//     {
//         return Attribute::make(
//             get: fn(string $value) => $this->getAllTranslations('meta_title'),
//         );
//     }

//     protected function metaDescription(): Attribute
//     {
//         return Attribute::make(
//             get: fn(string $value) => $this->getAllTranslations('meta_description'),
//         );
//     }


//     public function seoable(): MorphTo
//     {
//         return $this->morphTo();
//     }
// }
