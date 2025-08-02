<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TopicController extends Controller
{
    //
    // CRUD for signed in users with speaker role
    public function index()
    {
        $topics = Topic::all();
        return view('topics.index', compact('topics'));
    }
    public function create()
    {
        return view('topics.create');
    }
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:topics',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|max:2048',
        ]);

        $topic = new Topic();
        $topic->foreign_id = Auth::user()->id;
        $topic->title = $request->input('title');
        $topic->slug = $request->input('slug');
        $topic->description = $request->input('description');

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images', 'public');
            $topic->image = $path;
        }

        $topic->save();

        return redirect()->route('topics.index')->with('success', 'Topic created successfully.');
    }
    public function show(Topic $topic)
    {
        return view('topics.show', compact('topic'));
    }
    public function edit(Topic $topic)
    {
        return view('topics.edit', compact('topic'));
    }
    public function update(Request $request, Topic $topic)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:topics,slug,' . $topic->id,
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|max:2048',
        ]);

        $topic->title = $request->input('title');
        $topic->slug = $request->input('slug');
        $topic->description = $request->input('description');

        if ($request->hasFile('image')) {
            // Delete old image
            if ($topic->image) {
                Storage::disk('public')->delete($topic->image);
            }
            // Store new image
            $path = $request->file('image')->store('images', 'public');
            $topic->image = $path;
        }

        $topic->save();

        return redirect()->route('topics.index')->with('success', 'Topic updated successfully.');
    }
    public function destroy(Topic $topic)
    {
        // Delete image
        if ($topic->image) {
            Storage::disk('public')->delete($topic->image);
        }
        $topic->delete();

        return redirect()->route('topics.index')->with('success', 'Topic deleted successfully.');
    }
}
