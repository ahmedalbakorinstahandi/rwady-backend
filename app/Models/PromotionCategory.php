<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionCategory extends Model
{
    protected $fillable = [
        'promotion_id',
        'category_id',
    ];
    
    protected $table = 'promotion_categories';

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotion_id');
    }
}
