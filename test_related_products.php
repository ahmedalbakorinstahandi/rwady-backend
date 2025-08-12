<?php

require_once 'vendor/autoload.php';

use App\Models\Product;
use App\Http\Resources\ProductResource;

// Test related products functionality
echo "Testing Related Products...\n";

// Get a product with all relationships loaded
$product = Product::with([
    'brands', 
    'colors', 
    'relatedProducts', 
    'categories', 
    'media', 
    'seo', 
    'relatedCategory'
])->first();

if ($product) {
    echo "Product found: {$product->name}\n";
    echo "Product ID: {$product->id}\n";
    echo "Related Category ID: " . ($product->related_category_id ?? 'null') . "\n";
    echo "Related Category Limit: " . ($product->related_category_limit ?? 'null') . "\n";
    
    // Test relatedCategoryProducts accessor
    $categoryProducts = $product->relatedCategoryProducts;
    echo "Category Products Count: " . $categoryProducts->count() . "\n";
    
    // Test relatedProducts relationship
    $manualRelated = $product->relatedProducts;
    echo "Manual Related Count: " . $manualRelated->count() . "\n";
    
    // Test categories relationship
    $categories = $product->categories;
    echo "Categories Count: " . $categories->count() . "\n";
    
    if ($categories->isNotEmpty()) {
        echo "Category IDs: " . $categories->pluck('id')->implode(', ') . "\n";
    }
    
    // Create resource
    $resource = new ProductResource($product);
    $data = $resource->toArray(request());
    
    echo "Related Products in Resource: " . count($data['related_products'] ?? []) . "\n";
    
    if (isset($data['related_products']) && count($data['related_products']) > 0) {
        echo "First Related Product: " . $data['related_products'][0]['name'] ?? 'N/A' . "\n";
    }
    
} else {
    echo "No products found in database\n";
}
