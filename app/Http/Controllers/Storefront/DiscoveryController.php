<?php
namespace App\Http\Controllers\Storefront;
use App\Http\Controllers\Controller;
use App\Services\StorefrontCatalogService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class DiscoveryController extends Controller {
 public function search(Request $request, StorefrontCatalogService $catalog){
  $q=trim((string)$request->query('q','')); $products=$catalog->allProducts();
  $results=$q==='' ? collect() : $products->filter(function($p)use($q){$hay=implode(' ',[$p['name']??'',$p['family']??'',$p['description']??'',$p['story']??'',is_array($p['notes']??null)?implode(' ',$p['notes']):($p['notes']??''),$p['category']['name']??'']);return Str::contains(Str::lower($hay),Str::lower($q));})->values();
  return view('store.search',compact('q','results'));
 }
 public function finder(Request $request, StorefrontCatalogService $catalog){
  $answers=['mood'=>$request->query('mood'),'intensity'=>$request->query('intensity'),'occasion'=>$request->query('occasion')]; $recommendations=collect();
  if(array_filter($answers)){$terms=$this->terms($answers);$recommendations=$catalog->allProducts()->map(function($p)use($terms){$hay=Str::lower(implode(' ',[$p['name']??'',$p['family']??'',$p['description']??'',$p['story']??'',is_array($p['notes']??null)?implode(' ',$p['notes']):($p['notes']??''),$p['wear']??'']));$score=collect($terms)->sum(fn($t)=>Str::contains($hay,Str::lower($t))?2:0)+(($p['is_featured']??false)?1:0);return ['product'=>$p,'score'=>$score];})->sortByDesc('score')->take(4)->pluck('product')->values();}
  return view('store.fragrance-finder',compact('answers','recommendations'));
 }
 private function terms(array $a):array{$map=['Quiet'=>['musk','skin','soft','cedar'],'Magnetic'=>['oud','amber','resin','spice'],'Fresh'=>['citrus','neroli','fresh','light'],'Warm'=>['amber','vanilla','wood','warm'],'Dark'=>['oud','dark','smoke','resin'],'Celebratory'=>['floral','bright','spice'],'Soft'=>['skin','musk','soft'],'Moderate'=>['wood','floral','balanced'],'Strong'=>['oud','amber','resin'],'Everyday'=>['fresh','skin','musk','cedar'],'Evening'=>['oud','amber','dark','resin'],'Formal'=>['wood','oud','floral'],'Gifting'=>['signature','featured','floral','musk']];$out=[];foreach($a as $v)if($v&&isset($map[$v]))$out=array_merge($out,$map[$v]);return array_unique($out);}
}
