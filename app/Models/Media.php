<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'product_color_id'
    ];

    protected $casts = [
        'source' => 'string',
        'orders' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('order', function ($query) {
            $query->orderBy('orders', 'asc');
        });
    }


    // path is a url
    protected $appends = ['url'];
    public function getUrlAttribute()
    {
        if ($this->source == 'file') {
            return url('storage/' . $this->path);
        } else {
            return $this->path;
        }
    }


    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
