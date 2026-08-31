<?php
namespace App\Http\Middleware;
use App\Models\SeoRedirect; use Closure; use Illuminate\Http\Request; use Illuminate\Support\Facades\Schema;
class StorefrontSeoRedirects { public function handle(Request $request, Closure $next){if(!$request->is('admin*') && !$request->is('api*') && $request->isMethod('GET') && Schema::hasTable('seo_redirects')){$path='/'.ltrim($request->path(),'/');$r=SeoRedirect::where('from_path',$path)->first();if($r && $r->to_path!==$path)return redirect($r->to_path,(int)($r->status_code ?: 301));}return $next($request);} }
