<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RelatedProductItem extends Model
{
    use SoftDeletes;

    protected $table = 'related_product_items';

    protected $fillable = [
        'product_id',
        'related_product_id'
    ];
}
