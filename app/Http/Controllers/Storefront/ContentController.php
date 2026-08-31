<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\StorefrontContentService;

class ContentController extends Controller
{
    public function about(StorefrontContentService $content)
    {
        $page = $content->page('about');

        return view('store.about', compact('page'));
    }

    public function page(string $slug, StorefrontContentService $content)
    {
        $page = $content->page($slug);
        abort_unless($page, 404);

        return view('store.info-page', compact('page'));
    }

    public function journal(StorefrontContentService $content)
    {
        $posts = $content->journal();

        return view('store.journal', compact('posts'));
    }

    public function journalPost(string $slug, StorefrontContentService $content)
    {
        $article = $content->journalPost($slug);
        abort_unless($article, 404);

        return view('store.journal-detail', compact('article'));
    }
}
