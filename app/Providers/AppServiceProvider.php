<?php
namespace App\Providers;
use App\Services\StorefrontContentService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider {
 public function register():void{}
 public function boot():void{View::composer(['components.house.header','components.house.footer'],function($view){try{$content=app(StorefrontContentService::class);$view->with('cmsHeaderNavigation',$content->navigation('main_header'))->with('cmsFooterNavigation',$content->navigation('footer'));}catch(\Throwable){$view->with('cmsHeaderNavigation',collect())->with('cmsFooterNavigation',collect());}});}
}
