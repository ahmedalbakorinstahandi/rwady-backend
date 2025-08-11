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

        $token = request()->bearerToken();
        if (!$token) {
            return null;
        }

        $cacheKey = 'request_user_' . $token;
        
        // Get user from cache (stored by SetLocaleMiddleware)
        return cache()->get($cacheKey);
    }



    public static function clearUserCache($userId = null)
    {
        if ($userId) {
            // Clear cache for specific user by finding their active tokens
            // This is useful when user data is updated
            $user = User::find($userId);
            if ($user) {
                // Clear any cached user data
                cache()->forget('current_user_' . $userId);
            }
        }
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public static function notificationsUnreadCount()
    {
        $user = User::auth();
        if ($user) {
            return  Notification::where('user_id', $user->id)->whereNull('read_at')->count();
        } else {
            return  Notification::whereNull('user_id')->count();
        }
    }
}
