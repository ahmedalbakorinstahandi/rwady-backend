# نظام الوسائط الموحد للمنتجات

## نظرة عامة
تم تحديث نظام الوسائط في المنتجات ليدعم مصفوفة واحدة تحتوي على الصور والفيديوهات مع التمييز التلقائي بينهما.

## كيفية الاستخدام

### إنشاء منتج جديد
```json
{
  "name": {
    "ar": "اسم المنتج",
    "en": "Product Name"
  },
  "price": 100.00,
  "media": [
    "/uploads/images/product1.jpg",
    "/uploads/images/product2.png", 
    "https://www.youtube.com/watch?v=example",
    "https://vimeo.com/123456789",
    "/uploads/images/product3.jpg"
  ]
}
```

### تحديث منتج موجود
```json
{
  "name": {
    "ar": "اسم المنتج المحدث",
    "en": "Updated Product Name"
  },
  "media": [
    "/uploads/images/new-image.jpg",
    "https://www.youtube.com/watch?v=new-video"
  ]
}
```

## التمييز التلقائي

### الصور
- أي رابط لا يحتوي على `https` يعتبر صورة
- يتم حفظها مع `type: 'image'` و `source: 'file'`

### الفيديوهات  
- أي رابط يحتوي على `https` يعتبر فيديو
- يتم حفظها مع `type: 'video'` و `source: 'link'`

## أمثلة على الروابط المدعومة

### صور
```
/uploads/images/product.jpg
/public/storage/products/image.png
data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQ...
```

### فيديوهات
```
https://www.youtube.com/watch?v=dQw4w9WgXcQ
https://vimeo.com/123456789
https://example.com/video.mp4
https://drive.google.com/file/d/example/view
```

## ملاحظات مهمة

1. **التحديث**: عند تحديث الوسائط، يتم حذف جميع الوسائط القديمة وإنشاء الجديدة
2. **الترتيب**: يتم حفظ ترتيب العناصر كما هو في المصفوفة
3. **التحقق**: يتم التحقق من صحة الروابط في Request Validation
4. **الأداء**: النظام محسن للتعامل مع كميات كبيرة من الوسائط

## التحديثات المطلوبة

### في Frontend
- تحديث النماذج لاستخدام `media` بدلاً من `images` و `videos` منفصلين
- إضافة منطق للتمييز البصري بين الصور والفيديوهات في الواجهة

### في API Documentation
- تحديث التوثيق ليعكس التغييرات الجديدة
- إضافة أمثلة للاستخدام الصحيح 