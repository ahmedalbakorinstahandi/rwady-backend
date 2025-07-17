<?php

namespace App\Models;

use App\Models\User;
use App\Traits\LanguageTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Notification extends Model
{
    use SoftDeletes, HasTranslations, LanguageTrait;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'notificationable_id',
        'notificationable_type',
        'read_at',
        'metadata',
    ];

    protected $casts = [
        'read_at'          => 'datetime',
        'metadata'         => 'array',
        'notificationable_id' => 'integer',
        'notificationable_type' => 'string',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
        'deleted_at'       => 'datetime',
    ];

    public $translatable = [
        'title',
        'message',
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function getIsReadAttribute(): bool
    {
        return !is_null($this->read_at);
    }

    // // notificationable polymorphic relationship
    // public function notificationable()
    // {
    //     return $this->morphTo()->withTrashed();
    // }


    protected function title(): Attribute
    {
        return Attribute::make(
            get: function (string $value) {
                $raw = $this->getRawOriginal('title');

                if (is_string($raw)) {
                    $raw = json_decode($raw, true);
                }

                if (is_array($raw) && isset($raw['cu'])) {
                    return $raw['cu'];
                }

                return $value;
            }
        );
    }
    protected function message(): Attribute
    {
        return Attribute::make(
            get: function (string $value) {
                $raw = $this->getRawOriginal('message');

                if (is_string($raw)) {
                    $raw = json_decode($raw, true);
                }

                if (is_array($raw) && isset($raw['cu'])) {
                    return $raw['cu'];
                }

                return $value;
            }
        );
    }
}
