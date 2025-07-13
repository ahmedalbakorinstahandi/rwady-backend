# نظام كاش المستخدم المحسن

## المشكلة السابقة
كان النظام يستخدم مفتاح كاش واحد `'current_user'` لجميع المستخدمين، مما يؤدي إلى:
- مشاركة بيانات المستخدمين في نفس الكاش
- مشاكل في الأمان والخصوصية
- عدم دقة البيانات المعروضة

## الحل الجديد

### 1. مفتاح كاش فريد لكل مستخدم
```php
// المفتاح الجديد: current_user_{user_id}
$cacheKey = 'current_user_' . $user->id;
```

### 2. دالة `User::auth()` المحسنة
```php
public static function auth()
{
    if (!Auth::guard('sanctum')->check()) {
        return null;
    }

    $user = Auth::guard('sanctum')->user();
    $cacheKey = 'current_user_' . $user->id;

    return cache()->remember($cacheKey, 60, function () use ($user) {
        return User::where('id', $user->id)->first();
    });
}
```

### 3. دوال مسح الكاش
```php
// مسح كاش المستخدم الحالي
User::clearAuthCache();

// مسح كاش مستخدم محدد
User::clearUserCache($userId);

// مسح كاش المستخدم الحالي
User::clearUserCache();
```

### 4. UserCacheTrait
Trait جديد لتسهيل إدارة الكاش:

```php
use App\Traits\UserCacheTrait;

class YourService
{
    use UserCacheTrait;

    public function someMethod()
    {
        // الحصول على المستخدم الحالي
        $user = $this->getCurrentUser();

        // إنشاء مفتاح كاش للمستخدم الحالي
        $cacheKey = $this->getUserCacheKey('some_data');

        // حفظ بيانات في الكاش للمستخدم الحالي
        $data = $this->rememberForUser('some_data', function () {
            return $this->expensiveOperation();
        }, 60);

        // مسح كاش محدد للمستخدم الحالي
        $this->forgetUserCache('some_data');

        // مسح جميع كاش المستخدم الحالي
        $this->clearAllUserCache();
    }
}
```

### 5. Middleware لمسح الكاش تلقائياً
```php
// ClearUserCacheMiddleware
// يمسح كاش المستخدم تلقائياً بعد كل طلب
```

## كيفية الاستخدام

### في Controllers
```php
use App\Traits\UserCacheTrait;

class YourController extends Controller
{
    use UserCacheTrait;

    public function index()
    {
        $user = $this->getCurrentUser();
        $cacheKey = $this->getUserCacheKey('data');
        
        return cache()->remember($cacheKey, 60, function () {
            return $this->getData();
        });
    }
}
```

### في Services
```php
use App\Traits\UserCacheTrait;

class YourService
{
    use UserCacheTrait;

    public function getData()
    {
        return $this->rememberForUser('data', function () {
            return $this->expensiveOperation();
        }, 60);
    }
}
```

### في Resources
```php
use App\Traits\UserCacheTrait;

class YourResource extends JsonResource
{
    use UserCacheTrait;

    public function toArray($request)
    {
        $user = $this->getCurrentUser();
        // استخدم البيانات حسب المستخدم
    }
}
```

## المميزات

1. **أمان محسن**: كل مستخدم له كاش منفصل
2. **أداء أفضل**: تقليل استعلامات قاعدة البيانات
3. **سهولة الاستخدام**: Trait جاهز للاستخدام
4. **مسح تلقائي**: Middleware يمسح الكاش بعد كل طلب
5. **مرونة**: إمكانية مسح كاش محدد أو جميع الكاش

## ملاحظات مهمة

- الكاش يتم مسحه تلقائياً بعد كل طلب
- كل مستخدم له كاش منفصل تماماً
- يمكن تخصيص مدة الكاش حسب الحاجة
- النظام يدعم المستخدمين الضيوف (guest users) 