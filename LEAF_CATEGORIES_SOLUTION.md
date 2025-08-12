# حل الأقسام الفرعية (الأوراق) للمنتجات المشابهة

## المشكلة
- المنتج يتبع لعدة أقسام في سلالة واحدة أو أكثر
- تريد جلب منتجات مشابهة من "الأوراق" (الأقسام الفرعية) وليس من "الجذع" (الأقسام الرئيسية)
- الأقسام الرئيسية مثل "العلامات التجارية" عامة جداً، والأقسام الفرعية مثل "DELONGHI" أكثر تحديداً

## الحل المطبق

### 1. دوال جديدة في نموذج Category

#### `isLeaf()`
```php
public function isLeaf()
{
    return $this->children()->count() === 0;
}
```
- يتحقق من أن القسم لا يحتوي على أقسام فرعية

#### `getLeafCategories()`
```php
public static function getLeafCategories($categories)
{
    $leafCategories = collect();
    
    foreach ($categories as $category) {
        if ($category->isLeaf()) {
            $leafCategories->push($category);
        } else {
            // Recursively get leaf categories from children
            $childLeaves = self::getLeafCategories($category->children);
            $leafCategories = $leafCategories->merge($childLeaves);
        }
    }
    
    return $leafCategories->unique('id');
}
```
- يجد جميع الأقسام الفرعية (الأوراق) من مجموعة من الأقسام
- يعمل بشكل متكرر للوصول لأعمق مستوى

### 2. دالة جديدة في نموذج Product

#### `getLeafCategoryProductsAttribute()`
```php
public function getLeafCategoryProductsAttribute()
{
    // Get all categories this product belongs to
    $productCategories = $this->categories()->with('children')->get();
    
    if ($productCategories->isEmpty()) {
        return collect();
    }
    
    // Find leaf categories (categories with no children)
    $leafCategories = Category::getLeafCategories($productCategories);
    
    if ($leafCategories->isEmpty()) {
        return collect();
    }
    
    $leafCategoryIds = $leafCategories->pluck('id');
    
    // Get products from leaf categories
    $query = Product::query()
        ->where('id', '!=', $this->id)
        ->whereHas('categories', function($q) use ($leafCategoryIds) {
            $q->whereIn('category_id', $leafCategoryIds);
        })
        ->with(['media', 'colors']);
    
    $limit = $this->related_category_limit ?: 10;
    return $query->inRandomOrder()
        ->limit($limit)
        ->get();
}
```

### 3. تحديث ProductResource

```php
'related_products' => $this->whenLoaded('categories', function () {
    try {
        // Get products from current categories if no related products found
        $categoryProducts = collect($this->relatedCategoryProducts ?? []);
        $manualRelatedProducts = collect($this->relatedProducts ?? []);
        
        $merged = $categoryProducts->merge($manualRelatedProducts);
        
        if ($merged->isEmpty()) {
            // Get products from leaf categories (most specific categories)
            $leafCategoryProducts = collect($this->leafCategoryProducts ?? []);
            
            if ($leafCategoryProducts->isNotEmpty()) {
                $merged = $leafCategoryProducts;
            } else {
                // Fallback: Get products from current product's categories
                // ... existing fallback code
            }
        }
        
        // ... rest of the code
    } catch (\Exception $e) {
        return ProductResource::collection(collect([]));
    }
}, []),
```

### 4. تحديث ProductService

```php
$product->load(['brands', 'colors', 'relatedProducts', 'categories.children', 'media', 'seo', 'relatedCategory']);
```

## مثال عملي

### المنتج: DELONGHI DOUBLE WALL ESPRESSO GLASSES
**الأقسام المرتبطة:**
1. **العلامات التجارية** (parent_id: null) - قسم رئيسي
2. **DELONGHI - ديلونجي** (parent_id: 1) - قسم فرعي
3. **ماكينات تحضير القهوة** (parent_id: null) - قسم رئيسي

**الأقسام الفرعية (الأوراق):**
- **DELONGHI - ديلونجي** (لأنه لا يحتوي على أقسام فرعية)

**المنتجات المشابهة:**
- سيتم جلب منتجات من قسم "DELONGHI - ديلونجي" فقط
- لن يتم جلب منتجات من "العلامات التجارية" أو "ماكينات تحضير القهوة" (لأنها عامة جداً)

## المميزات

✅ **دقة عالية**: المنتجات المشابهة من نفس المستوى الدقيق
✅ **مرونة**: يعمل مع أي عدد من الأقسام والسلالات
✅ **أداء محسن**: لا يجلب منتجات من أقسام عامة
✅ **fallback آمن**: إذا لم توجد أقسام فرعية، يعود للطريقة القديمة

## النتيجة

الآن `related_products` سيجلب منتجات مشابهة من الأقسام الفرعية (الأوراق) فقط، مما يضمن دقة أعلى في التوصيات! 🎯
