<?php

use App\Http\Controllers\Storefront\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemap-pages.xml', [SitemapController::class, 'pages'])->name('sitemap.pages');
Route::get('/sitemap-products.xml', [SitemapController::class, 'products'])->name('sitemap.products');
Route::get('/sitemap-collections.xml', [SitemapController::class, 'collections'])->name('sitemap.collections');
Route::get('/sitemap-journal.xml', [SitemapController::class, 'journal'])->name('sitemap.journal');
