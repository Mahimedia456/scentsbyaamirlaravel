<?php
namespace App\Services;

use App\Models\JournalPost;
use App\Models\Navigation;
use App\Models\Page;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class StorefrontContentService
{
    public function page(string $slug): ?array
    {
        if (Schema::hasTable('pages')) {
            $page = Page::where('slug',$slug)->where('status','published')->where(fn($q)=>$q->whereNull('published_at')->orWhere('published_at','<=',now()))->first();
            if ($page) return ['source'=>'db','title'=>$page->title,'slug'=>$page->slug,'eyebrow'=>'House Information','intro'=>$page->meta_description ?: Str::limit(strip_tags($page->content ?? ''),180),'content'=>$page->content,'meta_title'=>$page->meta_title,'meta_description'=>$page->meta_description,'theme'=>'light'];
        }
        $fallback = config("store-pages.$slug");
        return $fallback ? array_merge($fallback,['source'=>'config','slug'=>$slug,'meta_title'=>null,'meta_description'=>$fallback['intro'] ?? null]) : null;
    }

    public function journal()
    {
        if (Schema::hasTable('journal_posts')) {
            $posts=JournalPost::where('status','published')->where(fn($q)=>$q->whereNull('published_at')->orWhere('published_at','<=',now()))->latest('published_at')->latest()->get();
            if ($posts->isNotEmpty()) return $posts;
        }
        return collect(config('storefront.journal',[]))->map(fn($p,$slug)=>(object)array_merge($p,['slug'=>$slug,'featured_image_path'=>$p['image'] ?? null,'eyebrow'=>$p['category'] ?? 'Journal','published_at'=>null,'content'=>implode("\n\n",$p['body'] ?? [])]));
    }

    public function journalPost(string $slug)
    {
        return $this->journal()->first(fn($p)=>$p->slug===$slug);
    }

    public function navigation(string $key)
    {
        if (!Schema::hasTable('navigations')) return collect();
        $nav=Navigation::with(['items'=>fn($q)=>$q->where('is_active',true)])->where('key',$key)->where('is_active',true)->first();
        return $nav?->items ?? collect();
    }
}
