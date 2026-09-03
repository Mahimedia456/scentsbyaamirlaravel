<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JournalPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class JournalPostController extends Controller
{
    public function index(Request $request)
    {
        $posts = JournalPost::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = '%' . $request->q . '%';
                $query->where(function ($nested) use ($search) {
                    $nested->where('title', 'like', $search)
                        ->orWhere('slug', 'like', $search)
                        ->orWhere('source_url', 'like', $search);
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
        $data = $this->validated($request);
        $post = JournalPost::create($data);
        $this->syncFeaturedImage($request, $post);

        return redirect()
            ->route('admin.journal-posts.edit', $post)
            ->with('success', 'Journal post created.');
    }

    public function update(Request $request, JournalPost $journal_post)
    {
        $journal_post->update($this->validated($request, $journal_post->id));
        $this->syncFeaturedImage($request, $journal_post);

        return redirect()
            ->route('admin.journal-posts.edit', $journal_post)
            ->with('success', 'Journal post updated.');
    }

    public function duplicate(JournalPost $journal_post)
    {
        $copy = $journal_post->replicate();
        $copy->title = $journal_post->title . ' Copy';
        $copy->slug = Str::slug($copy->title) . '-' . Str::lower(Str::random(4));
        $copy->status = 'draft';
        $copy->published_at = null;
        $copy->wordpress_id = null;
        $copy->source_url = null;
        $copy->author_name = null;
        $copy->wordpress_categories = null;
        $copy->wordpress_tags = null;
        $copy->wordpress_modified_at = null;
        $copy->imported_at = null;
        $copy->save();

        return redirect()
            ->route('admin.journal-posts.edit', $copy)
            ->with('success', 'Journal post duplicated as draft.');
    }

    public function destroy(JournalPost $journal_post)
    {
        $this->deleteManagedImage($journal_post->featured_image_path);
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
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'meta_title' => ['nullable', 'string', 'max:190'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'published_at' => ['nullable', 'date'],
            'category_names' => ['nullable', 'string', 'max:1500'],
            'tag_names' => ['nullable', 'string', 'max:3000'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'remove_featured_image' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        if (array_key_exists('category_names', $data)) {
            $data['wordpress_categories'] = $this->termsFromCommaList($data['category_names']);
            unset($data['category_names']);
        }

        if (array_key_exists('tag_names', $data)) {
            $data['wordpress_tags'] = $this->termsFromCommaList($data['tag_names']);
            unset($data['tag_names']);
        }

        unset($data['featured_image'], $data['remove_featured_image']);

        return $data;
    }

    private function syncFeaturedImage(Request $request, JournalPost $post): void
    {
        $remove = $request->boolean('remove_featured_image');
        $upload = $request->file('featured_image');

        if ($remove || $upload) {
            $this->deleteManagedImage($post->featured_image_path);
            $post->featured_image_path = null;
        }

        if ($upload && $upload->isValid()) {
            $base = Str::slug(pathinfo($upload->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'featured';
            $extension = strtolower($upload->getClientOriginalExtension() ?: 'jpg');
            $filename = $base . '-' . Str::lower(Str::random(10)) . '.' . $extension;
            $directory = 'journal/' . ($post->slug ?: ('post-' . $post->id));
            $post->featured_image_path = $upload->storeAs($directory, $filename, 'public');
        }

        if ($post->isDirty('featured_image_path')) {
            $post->save();
        }
    }

    private function deleteManagedImage(?string $path): void
    {
        if (!$path || Str::startsWith($path, ['http://', 'https://', '/'])) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function termsFromCommaList(?string $value): array
    {
        return collect(explode(',', (string) $value))
            ->map(fn ($name) => trim($name))
            ->filter()
            ->unique(fn ($name) => mb_strtolower($name))
            ->values()
            ->map(fn ($name) => [
                'id' => null,
                'name' => $name,
                'slug' => Str::slug($name),
                'link' => null,
            ])
            ->all();
    }
}
