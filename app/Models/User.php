<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    protected $fillable = [
        'name',
        'phone',
        'avatar',
        'status',
        'role',
        'otp',
        'otp_expire_at',
        'is_verified',
        'language',
    ];

    protected $hidden = [
        'otp',
        'otp_expire_at',
    ];
    


    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin()
    {
        return $this->role == 'admin';
    }

    public function isCustomer()
    {
        return $this->role == 'customer';
    }

    public function getAvatarAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }

    public function couponUsages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    // povit
    public function favorites()
    {
        return $this->belongsToMany(Product::class, 'user_favorites', 'user_id', 'product_id');
    }

    public static function auth()
    {
        // Check if user is authenticated
        if (!Auth::guard('sanctum')->check()) {
            return null;
        }

        $user = Auth::guard('sanctum')->user();
        $cacheKey = 'current_user_' . $user->id;

        // Use cache to avoid repeated database queries
        return cache()->remember($cacheKey, 60, function () use ($user) {
            return User::where('id', $user->id)->first();
        });
    }

    public static function clearAuthCache()
    {
        if (Auth::guard('sanctum')->check()) {
            $user = Auth::guard('sanctum')->user();
            $cacheKey = 'current_user_' . $user->id;
            cache()->forget($cacheKey);
        }
    }

    public static function clearUserCache($userId = null)
    {
        if ($userId) {
            $cacheKey = 'current_user_' . $userId;
            cache()->forget($cacheKey);
        } else {
            // Clear current user cache
            self::clearAuthCache();
        }
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
}
