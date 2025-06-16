<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'path',
        'type',
        'source',
        'orders',
        'product_id',
        'product_color_id',
    ];


    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
