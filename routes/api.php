<?php

use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', HealthController::class)->name('api.v1.health');

    // Catalog API
    Route::get('/catalog/products', [\App\Http\Controllers\Api\V1\CatalogController::class, 'products']);
    Route::get('/catalog/products/{slug}', [\App\Http\Controllers\Api\V1\CatalogController::class, 'product']);
    Route::get('/catalog/categories', [\App\Http\Controllers\Api\V1\CatalogController::class, 'categories']);
    Route::get('/catalog/collections', [\App\Http\Controllers\Api\V1\CatalogController::class, 'collections']);

    // Promotions & CMS API for storefront integration
    Route::post('/promotions/validate', [\App\Http\Controllers\Api\V1\PromotionController::class, 'validateCode']);
    Route::get('/content/pages/{slug}', [\App\Http\Controllers\Api\V1\ContentController::class, 'page']);
    Route::get('/content/journal', [\App\Http\Controllers\Api\V1\ContentController::class, 'journal']);
    Route::get('/content/journal/{slug}', [\App\Http\Controllers\Api\V1\ContentController::class, 'journalShow']);
    Route::get('/content/navigation/{key}', [\App\Http\Controllers\Api\V1\ContentController::class, 'navigation']);
    Route::get('/store/config', \App\Http\Controllers\Api\V1\StoreConfigController::class);

    // Storefront Integration Phase 02 — live cart/wishlist validation
    Route::post('/store/cart/validate', [\App\Http\Controllers\Api\V1\StorefrontCommerceController::class, 'validateCart']);
    Route::post('/store/wishlist/resolve', [\App\Http\Controllers\Api\V1\StorefrontCommerceController::class, 'resolveWishlist']);
});
