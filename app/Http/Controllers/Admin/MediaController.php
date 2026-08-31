<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $assets = MediaAsset::query()
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($x) => $x
                ->where('name','like','%'.$request->q.'%')
                ->orWhere('alt_text','like','%'.$request->q.'%')
                ->orWhere('caption','like','%'.$request->q.'%')))
            ->when($request->filled('type'), function ($q) use ($request) {
                if ($request->type === 'image') $q->where('mime_type','like','image/%');
                if ($request->type === 'other') $q->where('mime_type','not like','image/%');
            })
            ->latest()
            ->paginate(32)
            ->withQueryString();

        $summary = [
            'all' => MediaAsset::count(),
            'images' => MediaAsset::where('mime_type','like','image/%')->count(),
            'bytes' => (int) MediaAsset::sum('size_bytes'),
        ];

        return view('admin.media.index', compact('assets','summary'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'files' => ['required','array','max:20'],
            'files.*' => ['file','max:20480'],
            'alt_text' => ['nullable','string','max:255'],
        ]);

        foreach ($request->file('files', []) as $file) {
            if (!$file || !$file->isValid()) continue;

            $path = $file->store('media/' . now()->format('Y/m'), 'public');

            MediaAsset::create([
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'disk' => 'public',
                'mime_type' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
                'alt_text' => $data['alt_text'] ?? null,
                'uploaded_by' => auth()->id(),
            ]);
        }

        return back()->with('success','Media uploaded.');
    }

    public function update(Request $request, MediaAsset $media)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'alt_text' => ['nullable','string','max:255'],
            'caption' => ['nullable','string','max:500'],
            'replacement' => ['nullable','file','max:20480'],
        ]);

        if ($request->hasFile('replacement') && $request->file('replacement')->isValid()) {
            $replacement = $request->file('replacement');
            $newPath = $replacement->store('media/' . now()->format('Y/m'), $media->disk ?: 'public');

            if (Storage::disk($media->disk ?: 'public')->exists($media->path)) {
                Storage::disk($media->disk ?: 'public')->delete($media->path);
            }

            $data['path'] = $newPath;
            $data['mime_type'] = $replacement->getMimeType();
            $data['size_bytes'] = $replacement->getSize();
        }

        unset($data['replacement']);
        $media->update($data);

        return back()->with('success','Media asset updated.');
    }

    public function destroy(MediaAsset $media)
    {
        if (Storage::disk($media->disk ?: 'public')->exists($media->path)) {
            Storage::disk($media->disk ?: 'public')->delete($media->path);
        }

        $media->delete();

        return back()->with('success','Media deleted.');
    }
}
