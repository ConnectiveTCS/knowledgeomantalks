<?php

namespace App\Http\Controllers;

use App\Models\InteractiveActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class InteractiveActivityController extends Controller
{
    //
    public function index()
    {
        // Fetch all interactive activities
        $activities = InteractiveActivity::all();

        return view('interactive_activities.index', compact('activities'));
    }
    public function create()
    {
        // Show the form to create a new interactive activity
        return view('interactive_activities.create');
    }
    public function store(Request $request)
    {
        // Validate and store the new interactive activity
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'type' => 'nullable|string|max:255',
        ]);

        $activity = new InteractiveActivity();
        $activity->user_id = Auth::user()->id;
        $activity->slug = Str::slug($request->title);
        $activity->description = $request->description;
        $activity->image = $request->file('image')->store('images', 'public');
        $activity->type = $request->type;
        $activity->save();

        return redirect()->route('interactive_activities.index')->with('success', 'Interactive activity created successfully.');
    }
    public function show($id)
    {
        // Show a specific interactive activity
        $activity = InteractiveActivity::findOrFail($id);

        return view('interactive_activities.show', compact('activity'));
    }
    public function edit($id)
    {
        // Show the form to edit an existing interactive activity
        $activity = InteractiveActivity::findOrFail($id);

        return view('interactive_activities.edit', compact('activity'));
    }
    public function update(Request $request, $id)
    {
        // Validate and update the interactive activity
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'type' => 'nullable|string|max:255',
        ]);

        $activity = InteractiveActivity::findOrFail($id);
        $activity->title = $request->title;
        $activity->description = $request->description;
        if ($request->hasFile('image')) {
            $activity->image = $request->file('image')->store('images', 'public');
        }
        $activity->type = $request->type;
        $activity->save();

        return redirect()->route('interactive_activities.index')->with('success', 'Interactive activity updated successfully.');
    }
    public function destroy($id)
    {
        // Delete the interactive activity
        $activity = InteractiveActivity::findOrFail($id);
        $activity->delete();

        return redirect()->route('interactive_activities.index')->with('success', 'Interactive activity deleted successfully.');
    }
}
