<?php

use App\Http\Controllers\Api\V1\CatalogController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function(){
    Route::get('/products',[CatalogController::class,'products']);
    Route::get('/products/{slug}',[CatalogController::class,'product']);
    Route::get('/categories',[CatalogController::class,'categories']);
    Route::get('/collections',[CatalogController::class,'collections']);
});
