<?php

use Illuminate\Support\Facades\Route;
use Webkul\Shop\Http\Controllers\CompareController;
use Webkul\Shop\Http\Controllers\HomeController;
use Webkul\Shop\Http\Controllers\Designer3DController;
use Webkul\Shop\Http\Controllers\PageController;
use Webkul\Shop\Http\Controllers\ProductController;
use Webkul\Shop\Http\Controllers\ProductsCategoriesProxyController;
use Webkul\Shop\Http\Controllers\SearchController;
use Webkul\Shop\Http\Controllers\SubscriptionController;
use Webkul\Shop\Http\Controllers\ContactController;
use Spatie\Honeypot\ProtectAgainstSpam;

Route::group(['middleware' => ['locale', 'theme', 'currency']], function () {

    /**
     * Fallback route.
     */
    Route::fallback(ProductsCategoriesProxyController::class.'@index')
        ->name('shop.product_or_category.index')
        ->middleware('cacheResponse');

    /**
     * Store front home.
     */
    Route::get('/', [HomeController::class, 'index'])
        ->name('shop.home.index')
        ->middleware(['cacheResponse']);

    /**
     * CMS pages.
     */
    Route::get('page/{slug}', [PageController::class, 'view'])
        ->name('shop.cms.page')
        ->middleware(['cacheResponse']);

    /**
     * Store front home.
     */
    Route::get('/designer3d', [Designer3DController::class, 'index'])
        ->name('shop.designer3d.index')
        ->middleware(['cacheResponse']);

    /**
     * Store front search.
     */
    Route::get('search', [SearchController::class, 'index'])
        ->name('shop.search.index')
        ->middleware('cacheResponse');

    Route::post('search/upload', [SearchController::class, 'upload'])->name('shop.search.upload');

    /**
     * Subscription routes.
     */
    Route::controller(SubscriptionController::class)->group(function () {
        Route::post('subscription', 'store')->name('shop.subscription.store');

        Route::get('subscription/{token}', 'destroy')->name('shop.subscription.destroy');
    });

    /**
     * Contact routes.
     */
    Route::controller(ContactController::class)->group(function () {
        Route::post('contact', 'store')->name('shop.contact.store')->middleware(ProtectAgainstSpam::class);;
    });

    /**
     * Compare products
     */
    Route::get('compare', [CompareController::class, 'index'])
        ->name('shop.compare.index')
        ->middleware('cacheResponse');

    /**
     * Downloadable products
     */
    Route::controller(ProductController::class)->group(function () {
        Route::get('downloadable/download-sample/{type}/{id}', 'downloadSample')->name('shop.downloadable.download_sample');

        Route::get('product/{id}/{attribute_id}', 'download')->defaults('_config', [
            'view' => 'shop.products.index',
        ])->name('shop.product.file.download');
    });
});
