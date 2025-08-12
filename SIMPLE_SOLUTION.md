# حل بسيط للمنتجات المشابهة

## المشكلة
- المنتج يتبع لعدة أقسام
- تريد منتجات من نفس القسم الأدنى (الأقل مستوى)
- لا تريد منتجات من الأقسام الرئيسية العامة

## الحل البسيط

### 1. دالة جديدة في Product: `getSameLevelProductsAttribute()`

```php
public function getSameLevelProductsAttribute()
{
    // Get all categories this product belongs to
    $productCategories = $this->categories()->with('children')->get();
    
    if ($productCategories->isEmpty()) {
        return collect();
    }
    
    // Find the lowest level categories (categories with no children)
    $lowestCategories = collect();
    
    foreach ($productCategories as $category) {
        if ($category->children->isEmpty()) {
            $lowestCategories->push($category);
        }
    }
    
    if ($lowestCategories->isEmpty()) {
        return collect();
    }
    
    $lowestCategoryIds = $lowestCategories->pluck('id');
    
    // Get products from the same lowest level categories
    $query = Product::query()
        ->where('id', '!=', $this->id)
        ->whereHas('categories', function($q) use ($lowestCategoryIds) {
            $q->whereIn('category_id', $lowestCategoryIds);
        })
        ->with(['media', 'colors']);
    
    $limit = $this->related_category_limit ?: 10;
    return $query->inRandomOrder()
        ->limit($limit)
        ->get();
}
```

### 2. ProductResource مبسط

```php
'related_products' => $this->whenLoaded('categories', function () {
    try {
        // First: Get manually related products
        $manualRelatedProducts = collect($this->relatedProducts ?? []);
        
        // Second: Get products from same lowest level categories
        $sameLevelProducts = collect($this->sameLevelProducts ?? []);
        
        // Merge both collections
        $merged = $manualRelatedProducts->merge($sameLevelProducts);
        
        // Remove duplicates and apply limit
        $limit = $this->related_category_limit ?: 10;
        $merged = $merged->unique('id')->take($limit);
        
        return ProductResource::collection($merged);
    } catch (\Exception $e) {
        return ProductResource::collection(collect([]));
    }
}, []),
```

## مثال عملي

### المنتج: DELONGHI DOUBLE WALL ESPRESSO GLASSES

**الأقسام المرتبطة:**
1. **العلامات التجارية** (parent_id: null) - قسم رئيسي ❌
2. **DELONGHI - ديلونجي** (parent_id: 1) - قسم فرعي ✅
3. **ماكينات تحضير القهوة** (parent_id: null) - قسم رئيسي ❌

**النتيجة:** 
- سيتم جلب منتجات من قسم "DELONGHI - ديلونجي" فقط
- لأنه القسم الوحيد الذي لا يحتوي على أقسام فرعية

## المميزات

✅ **بسيط**: كود واضح ومفهوم  
✅ **دقيق**: منتجات من نفس المستوى بالضبط  
✅ **سريع**: لا يوجد تعقيد أو استعلامات متكررة  
✅ **مرن**: يعمل مع أي عدد من الأقسام  

## النتيجة

الآن `related_products` سيجلب منتجات من نفس القسم الأدنى فقط، مما يضمن دقة عالية في التوصيات! 🎯
