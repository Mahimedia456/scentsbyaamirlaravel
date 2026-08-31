<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Storefront\StorefrontCatalogController;

Route::get('/media/{path}', [\App\Http\Controllers\Storefront\StorefrontMediaController::class, 'show'])->where('path', '.*')->name('store.media');

Route::get('/', [StorefrontCatalogController::class, 'home'])->name('home');
Route::get('/shop', [StorefrontCatalogController::class, 'shop'])->name('shop');
Route::get('/collections', [StorefrontCatalogController::class, 'collections'])->name('collections');
Route::get('/collections/{slug}', [StorefrontCatalogController::class, 'collection'])->name('collections.show');
Route::get('/product/{slug}', [StorefrontCatalogController::class, 'product'])->name('product.show');

Route::get('/search', [\App\Http\Controllers\Storefront\DiscoveryController::class, 'search'])->name('search');
Route::get('/fragrance-finder', [\App\Http\Controllers\Storefront\DiscoveryController::class, 'finder'])->name('finder');
Route::view('/ingredients', 'store.ingredients')->name('ingredients');

Route::get('/ingredients/{slug}', function (string $slug) {
    abort_unless(config("storefront.ingredients.$slug"), 404);
    return view('store.ingredient-detail', compact('slug'));
})->name('ingredients.show');

Route::get('/journal', [\App\Http\Controllers\Storefront\ContentController::class, 'journal'])->name('journal');
Route::get('/journal/{slug}', [\App\Http\Controllers\Storefront\ContentController::class, 'journalPost'])->name('journal.show');

Route::view('/wishlist', 'store.wishlist')->name('wishlist');
Route::middleware('guest:customer')->group(function () {
    Route::get('/account/login', [\App\Http\Controllers\Storefront\CustomerAuthController::class, 'login'])->name('customer.login');
    Route::post('/account/login', [\App\Http\Controllers\Storefront\CustomerAuthController::class, 'authenticate'])->name('customer.login.store');
    Route::get('/account/register', [\App\Http\Controllers\Storefront\CustomerAuthController::class, 'register'])->name('customer.register');
    Route::post('/account/register', [\App\Http\Controllers\Storefront\CustomerAuthController::class, 'store'])->name('customer.register.store');
});
Route::middleware('customer')->group(function () {
    Route::get('/account', [\App\Http\Controllers\Storefront\AccountController::class, 'index'])->name('account');
    Route::put('/account', [\App\Http\Controllers\Storefront\AccountController::class, 'update'])->name('account.update');
    Route::post('/account/address', [\App\Http\Controllers\Storefront\AccountController::class, 'address'])->name('account.address');
    Route::get('/account/orders', [\App\Http\Controllers\Storefront\AccountController::class, 'orders'])->name('orders');
    Route::get('/account/orders/{order}', [\App\Http\Controllers\Storefront\AccountController::class, 'order'])->name('orders.show');
    Route::get('/account/notifications', [\App\Http\Controllers\Storefront\AccountController::class, 'notifications'])->name('notifications');
    Route::post('/account/notifications/{notification}/read', [\App\Http\Controllers\Storefront\AccountController::class, 'readNotification'])->name('notifications.read');
    Route::get('/checkout', [\App\Http\Controllers\Storefront\CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout', [\App\Http\Controllers\Storefront\CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/success/{order}', [\App\Http\Controllers\Storefront\CheckoutController::class, 'success'])->name('checkout.success');
    Route::post('/account/logout', [\App\Http\Controllers\Storefront\CustomerAuthController::class, 'logout'])->name('customer.logout');
});
Route::view('/gifting', 'store.gifting')->name('gifting');
Route::view('/services', 'store.services')->name('services');

Route::get('/contact', [\App\Http\Controllers\Storefront\OperationsController::class, 'contact'])->name('contact');
Route::post('/contact', [\App\Http\Controllers\Storefront\OperationsController::class, 'contactStore'])->middleware('throttle:10,1')->name('contact.store');
Route::post('/newsletter', [\App\Http\Controllers\Storefront\OperationsController::class, 'newsletter'])->middleware('throttle:10,1')->name('newsletter.store');

Route::get('/shipping', fn(\App\Services\StorefrontContentService $content) => ($page=$content->page('shipping')) ? view('store.info-page',compact('page')) : abort(404))->name('shipping');

Route::get('/returns', fn(\App\Services\StorefrontContentService $content) => ($page=$content->page('returns')) ? view('store.info-page',compact('page')) : abort(404))->name('returns');

Route::get('/track-order', [\App\Http\Controllers\Storefront\OperationsController::class, 'track'])->name('track-order');
Route::post('/track-order', [\App\Http\Controllers\Storefront\OperationsController::class, 'trackStore'])->middleware('throttle:15,1')->name('track-order.store');

Route::get('/gift-wrapping', fn(\App\Services\StorefrontContentService $content) => ($page=$content->page('gift-wrapping')) ? view('store.info-page',compact('page')) : abort(404))->name('gift-wrapping');

Route::get('/personalized-message', fn(\App\Services\StorefrontContentService $content) => ($page=$content->page('personalized-message')) ? view('store.info-page',compact('page')) : abort(404))->name('personalized-message');

Route::get('/privacy', fn(\App\Services\StorefrontContentService $content) => ($page=$content->page('privacy')) ? view('store.info-page',compact('page')) : abort(404))->name('privacy');

Route::get('/terms', fn(\App\Services\StorefrontContentService $content) => ($page=$content->page('terms')) ? view('store.info-page',compact('page')) : abort(404))->name('terms');

Route::get('/cookies', fn(\App\Services\StorefrontContentService $content) => ($page=$content->page('cookies')) ? view('store.info-page',compact('page')) : abort(404))->name('cookies');

Route::get('/accessibility', fn(\App\Services\StorefrontContentService $content) => ($page=$content->page('accessibility')) ? view('store.info-page',compact('page')) : abort(404))->name('accessibility');

Route::get('/social/{platform}', function (string $platform) {
    abort_unless(in_array($platform, ['instagram', 'facebook', 'tiktok'], true), 404);
    return view('store.social', compact('platform'));
})->name('social');


/*
|--------------------------------------------------------------------------
| Admin Panel
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [\App\Http\Controllers\Admin\AuthController::class, 'create'])->name('login');
        Route::post('/login', [\App\Http\Controllers\Admin\AuthController::class, 'store'])->name('login.store');
    });

    Route::middleware('admin')->group(function () {
        Route::get('/', fn () => redirect()->route('admin.dashboard'));
        Route::get('/dashboard', \App\Http\Controllers\Admin\DashboardController::class)->name('dashboard');

        // Phase 02 — Catalog
        Route::resource('products', \App\Http\Controllers\Admin\ProductController::class)->except('show');
        Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class)->except('show');
        Route::resource('collections', \App\Http\Controllers\Admin\CollectionController::class)->except('show');

        // Phase 03 — Orders & Customers
        Route::resource('orders', \App\Http\Controllers\Admin\OrderController::class)->only(['index','show','update']);
        Route::post('/orders/{order}/payment/approve', [\App\Http\Controllers\Admin\OrderController::class, 'approvePayment'])->name('orders.payment.approve');
        Route::post('/orders/{order}/payment/reject', [\App\Http\Controllers\Admin\OrderController::class, 'rejectPayment'])->name('orders.payment.reject');
        Route::get('/orders/{order}/payment/receipt', [\App\Http\Controllers\Admin\OrderController::class, 'receipt'])->name('orders.payment.receipt');
        Route::resource('customers', \App\Http\Controllers\Admin\CustomerController::class);

        // Phase 04 — Inventory & Promotions
        Route::get('/inventory', [\App\Http\Controllers\Admin\InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory/adjust', [\App\Http\Controllers\Admin\InventoryController::class, 'adjust'])->name('inventory.adjust');
        Route::resource('coupons', \App\Http\Controllers\Admin\CouponController::class)->except('show');

        // Phase 05 — CMS, Journal & Navigation
        Route::resource('pages', \App\Http\Controllers\Admin\PageController::class)->except('show');
        Route::resource('journal-posts', \App\Http\Controllers\Admin\JournalPostController::class)->except('show');
        Route::resource('navigations', \App\Http\Controllers\Admin\NavigationController::class)->except('show');


        // Phase 06 — Media + SEO
        Route::get('/media', [\App\Http\Controllers\Admin\MediaController::class, 'index'])->name('media.index');
        Route::post('/media', [\App\Http\Controllers\Admin\MediaController::class, 'store'])->name('media.store');
        Route::delete('/media/{media}', [\App\Http\Controllers\Admin\MediaController::class, 'destroy'])->name('media.destroy');
        Route::get('/seo', [\App\Http\Controllers\Admin\SeoController::class, 'index'])->name('seo.index');
        Route::post('/seo/redirects', [\App\Http\Controllers\Admin\SeoController::class, 'store'])->name('seo.store');
        Route::delete('/seo/redirects/{redirect}', [\App\Http\Controllers\Admin\SeoController::class, 'destroy'])->name('seo.destroy');

        // Phase 07 — Settings + Shipping + Payments
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
        Route::get('/shipping', [\App\Http\Controllers\Admin\ShippingController::class, 'index'])->name('shipping.index');
        Route::post('/shipping', [\App\Http\Controllers\Admin\ShippingController::class, 'store'])->name('shipping.store');
        Route::delete('/shipping/{zone}', [\App\Http\Controllers\Admin\ShippingController::class, 'destroy'])->name('shipping.destroy');
        Route::get('/payments', [\App\Http\Controllers\Admin\PaymentMethodController::class, 'index'])->name('payments.index');
        Route::put('/payments/{payment}', [\App\Http\Controllers\Admin\PaymentMethodController::class, 'update'])->name('payments.update');

        // Phase 08 — Analytics + Roles + Audit
        Route::get('/analytics', [\App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/admin-users', [\App\Http\Controllers\Admin\AdminUserController::class, 'index'])->name('admin-users.index');
        Route::post('/admin-users', [\App\Http\Controllers\Admin\AdminUserController::class, 'store'])->name('admin-users.store');
        Route::get('/audit', [\App\Http\Controllers\Admin\AuditController::class, 'index'])->name('audit.index');

        // Storefront Integration Phase 10 — Customer Care + Newsletter
        Route::get('/contact-inquiries', [\App\Http\Controllers\Admin\ContactInquiryController::class, 'index'])->name('contact-inquiries.index');
        Route::get('/contact-inquiries/{inquiry}', [\App\Http\Controllers\Admin\ContactInquiryController::class, 'show'])->name('contact-inquiries.show');
        Route::put('/contact-inquiries/{inquiry}', [\App\Http\Controllers\Admin\ContactInquiryController::class, 'update'])->name('contact-inquiries.update');
        Route::get('/newsletter', [\App\Http\Controllers\Admin\NewsletterController::class, 'index'])->name('newsletter.index');
        Route::put('/newsletter/{subscriber}', [\App\Http\Controllers\Admin\NewsletterController::class, 'update'])->name('newsletter.update');

        // Phase 09 — One-time WooCommerce migration center
        Route::get('/woocommerce-import', [\App\Http\Controllers\Admin\WooCommerceImportController::class, 'index'])->name('woocommerce.index');
        Route::post('/woocommerce-import/test', [\App\Http\Controllers\Admin\WooCommerceImportController::class, 'test'])->name('woocommerce.test');
        Route::post('/woocommerce-import', [\App\Http\Controllers\Admin\WooCommerceImportController::class, 'store'])->name('woocommerce.store');

        Route::post('/logout', [\App\Http\Controllers\Admin\AuthController::class, 'destroy'])->name('logout');
    });
});
