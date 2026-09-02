<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JournalPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class JournalPostController extends Controller
{
    public function index(Request $request)
    {
        $posts = JournalPost::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->q.'%';
                $query->where(function ($nested) use ($term) {
                    $nested->where('title', 'like', $term)
                        ->orWhere('slug', 'like', $term)
                        ->orWhere('wordpress_url', 'like', $term);
                });
            })
            ->latest('published_at')
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.journal-posts.index', compact('posts'));
    }

    public function create()
    {
        return view('admin.journal-posts.form', ['post' => new JournalPost]);
    }

    public function edit(JournalPost $journal_post)
    {
        return view('admin.journal-posts.form', ['post' => $journal_post]);
    }

    public function store(Request $request)
    {
        JournalPost::create($this->validated($request));
        return redirect()->route('admin.journal-posts.index')->with('success', 'Journal post created.');
    }

    public function update(Request $request, JournalPost $journal_post)
    {
        $journal_post->update($this->validated($request, $journal_post->id));
        return redirect()->route('admin.journal-posts.index')->with('success', 'Journal post updated.');
    }

    public function duplicate(JournalPost $journal_post)
    {
        $copy = $journal_post->replicate();
        $copy->title = $journal_post->title.' Copy';
        $copy->slug = Str::slug($copy->title).'-'.Str::lower(Str::random(4));
        $copy->status = 'draft';
        $copy->source = 'manual';
        $copy->wordpress_id = null;
        $copy->wordpress_url = null;
        $copy->wordpress_modified_at = null;
        $copy->imported_at = null;
        $copy->published_at = null;
        $copy->save();

        return redirect()->route('admin.journal-posts.edit', $copy)->with('success', 'Journal post duplicated as draft.');
    }

    public function destroy(JournalPost $journal_post)
    {
        $journal_post->delete();
        return back()->with('success', 'Journal post deleted.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:200', Rule::unique('journal_posts', 'slug')->ignore($id)],
            'eyebrow' => ['nullable', 'string', 'max:120'],
            'excerpt' => ['nullable', 'string', 'max:1500'],
            'content' => ['nullable', 'string'],
            'featured_image_path' => ['nullable', 'string', 'max:700'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'meta_title' => ['nullable', 'string', 'max:190'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'canonical_url' => ['nullable', 'string', 'max:700'],
            'og_title' => ['nullable', 'string', 'max:190'],
            'og_description' => ['nullable', 'string', 'max:1000'],
            'og_image_path' => ['nullable', 'string', 'max:700'],
            'author_name' => ['nullable', 'string', 'max:190'],
            'categories_text' => ['nullable', 'string', 'max:1500'],
            'tags_text' => ['nullable', 'string', 'max:1500'],
            'published_at' => ['nullable', 'date'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        $data['categories'] = $this->csv($data['categories_text'] ?? null);
        $data['tags'] = $this->csv($data['tags_text'] ?? null);
        unset($data['categories_text'], $data['tags_text']);

        if ($data['status'] === 'published' && ! $data['published_at']) {
            $data['published_at'] = now();
        }

        return $data;
    }

    private function csv(?string $value): array
    {
        return collect(explode(',', (string) $value))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->unique(fn ($item) => mb_strtolower($item))
            ->values()
            ->all();
    }
}
