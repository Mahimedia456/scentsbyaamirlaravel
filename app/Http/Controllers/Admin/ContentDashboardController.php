<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JournalPost;
use App\Models\MediaAsset;
use App\Models\Navigation;
use App\Models\Page;
use Illuminate\View\View;

class ContentDashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'pages' => Page::count(),
            'published_pages' => Page::where('status','published')->count(),
            'journal' => JournalPost::count(),
            'published_journal' => JournalPost::where('status','published')->count(),
            'media' => MediaAsset::count(),
            'navigations' => Navigation::count(),
        ];

        $recentPages = Page::latest('updated_at')->limit(5)->get();
        $recentPosts = JournalPost::latest('updated_at')->limit(5)->get();
        $recentMedia = MediaAsset::latest()->limit(8)->get();

        return view('admin.content.index', compact('stats','recentPages','recentPosts','recentMedia'));
    }
}
