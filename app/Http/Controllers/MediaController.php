<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    //
    public function index()
    {
        $media = Media::paginate(15);
        // Logic to list media items
        return view('media.index', compact('media'));
    }

    public function create()
    {
        // Logic to show form for creating new media item
        return view('media.create');
    }
    public function store(Request $request)
    {
        // Logic to store new media item
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:media,slug',
            'type' => 'nullable|string',
            'url' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mp3', // Adjust mime types as needed
            'thumbnail' => 'nullable|file|mimes:jpg,jpeg,png,gif', // Adjust mime types as needed
            'description' => 'nullable|string',
        ]);
        // generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['title']);
        }  

        // get type from file extension
        if ($request->file('url')) {
            $extension = $request->file('url')->getClientOriginalExtension();
            $data['type'] = match ($extension) {
                'jpg', 'jpeg', 'png', 'gif' => 'image',
                'mp4' => 'video',
                'mp3' => 'audio',
                default => 'other',
            };
        } else {
            $data['type'] = 'other';
        }

        // Create media item logic here
        $data = Media::create([
            'title' => $request->input('title'),
            'slug' => $data['slug'],
            'type' => $data['type'],
            'url' => $request->hasFile('url') ? $request->file('url')->store('media', 'public') : null,
            'thumbnail' => $request->hasFile('thumbnail') ? $request->file('thumbnail')->store('thumbnails', 'public') : null,
            'description' => $data['description'],
        ]);

        return redirect()->route('media.index')->with('success', 'Media item created successfully.');
    }
    public function show(Media $media)
    {
        // Logic to show a single media item
        return view('media.show', compact('media'));
    }
    public function edit(Media $media)
    {
        // Logic to show form for editing media item
        return view('media.edit', compact('media'));
    }
    public function update(Request $request, Media $media)
    {
        // Logic to update media item
        $request->validate([
            'title' => 'required|string|max:255',
            // 'slug' => 'required|string|unique:media,slug,' . $media->id,
            // 'type' => 'nullable|string',
            'url' => 'nullable|url',
            // 'thumbnail' => 'nullable|url',
            'description' => 'nullable|string',
        ]);

        // generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($request['title']);
        }


        // get type from file extension
        if ($request->file('url')) {
            $extension = $request->file('url')->getClientOriginalExtension();
            $data['type'] = match ($extension) {
                'jpg', 'jpeg', 'png', 'gif' => 'image',
                'mp4' => 'video',
                'mp3' => 'audio',
                default => 'other',
            };
        } else {
            $data['type'] = 'other';
        }

        $media->update([
            'title' => $request->input('title'),
            // 'slug' => $request['slug'],
            // 'type' => $request['type'],
            // 'url' => $request->hasFile('url') ? $request->file('url')->store('media', 'public') : null,
            // 'thumbnail' => $request->hasFile('thumbnail') ? $request->file('thumbnail')->store('thumbnails', 'public') : null,
            'description' => $request['description'],
        ]);

        return redirect()->route('media.index')->with('success', 'Media item updated successfully.');
    }
    public function destroy(Media $media)
    {
        // Logic to delete media item
        $media->delete();

        return redirect()->route('media.index')->with('success', 'Media item deleted successfully.');
    }

    // Bulk creation
    public function bulkCreate()
    {
        // Logic to show form for bulk creation of media items
        return view('media.bulk.create');
    }

    public function bulkStore(Request $request)
    {
        $data = $request->validate([
            'media_files'   => 'required|array',
            'media_files.*' => 'file|mimes:jpg,jpeg,png,gif,mp4,mp3',
            'description'   => 'nullable|string',
        ]);

        foreach ($request->file('media_files') as $file) {
            $ext  = $file->getClientOriginalExtension();
            $type = match ($ext) {
                'jpg', 'jpeg', 'png', 'gif' => 'image',
                'mp4'                    => 'video',
                'mp3'                    => 'audio',
                default                  => 'other',
            };
            $path = $file->store('media', 'public');
            Media::create([
                'title'       => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'slug'        => \Illuminate\Support\Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)),
                'type'        => $type,
                'url'         => $path,
                'thumbnail'   => null,
                'description' => $data['description'] ?? null,
            ]);
        }

        return redirect()->route('media.index')
            ->with('success', 'Media items created successfully.');
    }

    public function bulkEdit()
    {
        // Logic to show form for bulk editing of media items
        return view('media.bulk_edit');
    }
    public function bulkUpdate(Request $request)
    {
        // Logic to update multiple media items
        $data = $request->validate([
            'media.*.id' => 'required|exists:media,id',
            'media.*.title' => 'required|string|max:255',
            'media.*.slug' => 'required|string|unique:media,slug,' . $request->input('media.*.id') . ',id',
            'media.*.type' => 'nullable|string',
            'media.*.url' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mp3', // Adjust mime types as needed
            'media.*.thumbnail' => 'nullable|file|mimes:jpg,jpeg,png,gif', // Adjust mime types as needed
            'media.*.description' => 'nullable|string',
        ]);

        foreach ($data['media'] as $item) {
            $mediaItem = Media::findOrFail($item['id']);
            if (empty($item['slug'])) {
                $item['slug'] = \Illuminate\Support\Str::slug($item['title']);
            }
            if (isset($item['url'])) {
                $extension = $item['url']->getClientOriginalExtension();
                $item['type'] = match ($extension) {
                    'jpg', 'jpeg', 'png', 'gif' => 'image',
                    'mp4' => 'video',
                    'mp3' => 'audio',
                    default => 'other',
                };
            } else {
                $item['type'] = 'other';
            }

            $mediaItem->update([
                'title' => $item['title'],
                'slug' => $item['slug'],
                'type' => $item['type'],
                'url' => isset($item['url']) ? $item['url']->store('media', 'public') : $mediaItem->url,
                'thumbnail' => isset($item['thumbnail']) ? $item['thumbnail']->store('thumbnails', 'public') : $mediaItem->thumbnail,
                'description' => $item['description'],
            ]);
        }

        return redirect()->route('media.index')->with('success', 'Media items updated successfully.');
    }
    public function bulkDelete(Request $request)
    {
        // Logic to delete multiple media items
        $data = $request->validate([
            'media.*.id' => 'required|exists:media,id',
        ]);
        foreach ($data['media'] as $item) {
            $mediaItem = Media::findOrFail($item['id']);
            $mediaItem->delete();
        }
        return redirect()->route('media.index')->with('success', 'Media items deleted successfully.');
    }
}
