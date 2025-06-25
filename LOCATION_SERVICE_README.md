# Location Service - خدمة الموقع

## المشكلة الأصلية
كان الكود الأصلي لا يستخرج البيانات بشكل صحيح من Google Maps API بسبب طريقة الوصول الخاطئة إلى `address_components`.

## الحل المطبق

### 1. إصلاح استخراج البيانات
- تم إصلاح طريقة استخراج البيانات من `address_components`
- إضافة دالة مساعدة `$findComponent` للبحث عن المكونات حسب النوع
- معالجة أفضل للحالات التي قد لا تتوفر فيها بعض البيانات

### 2. تحسينات إضافية
- إضافة معالجة الأخطاء (try-catch)
- التحقق من صحة الإحداثيات
- إضافة logging لتتبع العمليات
- التحقق من صحة استجابة API
- إضافة دالة اختبار للاتصال بـ API

## كيفية الاستخدام

### 1. اختبار مع إحداثيات بغداد
```php
$result = LocationService::getLocationData(33.310122, 44.368598);
```

### 2. اختبار عبر API Routes
```bash
# اختبار مع إحداثيات بغداد
GET /api/test/location

# اختبار مع إحداثيات مخصصة
POST /api/test/location/custom
{
    "latitude": 33.310122,
    "longitude": 44.368598
}

# اختبار الاتصال بـ API
GET /api/test/location/api-connection
```

### 3. استخدام في الكود
```php
use App\Services\LocationService;

$locationData = LocationService::getLocationData($latitude, $longitude);

if ($locationData) {
    $address = $locationData['address'];
    $city = $locationData['city'];
    $country = $locationData['country'];
    $state = $locationData['state'];
    $postalCode = $locationData['postal_code'];
    $addressSecondary = $locationData['address_secondary'];
}
```

## البيانات المُرجعة
```json
{
    "address": "العنوان الكامل",
    "city": "بغداد",
    "country": "العراق",
    "postal_code": "10001",
    "address_secondary": "المنطقة الفرعية",
    "state": "محافظة بغداد",
    "latitude": "33.310122",
    "longitude": "44.368598"
}
```

## ملاحظات مهمة
1. تأكد من أن Google Maps API Key صالح ومفعل
2. الإحداثيات يجب أن تكون أرقام صحيحة
3. اللغة تُحدد من header `Accept-Language`
4. في حالة فشل الاستعلام، سيتم إرجاع `null`

## إصلاحات المشاكل
- ✅ إصلاح استخراج البيانات من `address_components`
- ✅ إضافة معالجة الأخطاء
- ✅ تحسين التعامل مع البيانات الفارغة
- ✅ إضافة logging للتتبع
- ✅ إضافة validation للإحداثيات 