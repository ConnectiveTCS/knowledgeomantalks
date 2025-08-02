<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use League\Csv\Writer;
use SplTempFileObject;

class EventController extends Controller
{
    //
    public function index(Request $request)
    {
        $query = Event::query();

        // Apply search filter if provided
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('organizer', 'like', "%{$search}%");
            });
        }

        // Apply category filter if provided
        if ($request->filled('category')) {
            $query->where('category', 'like', '%' . $request->input('category') . '%');
        }

        // Apply status filter if provided
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Apply visibility filter if provided
        if ($request->filled('visibility')) {
            $query->where('visibility', $request->input('visibility'));
        }

        $events = $query->get();

        // Return the events view
        return view('events.index', compact('events'));
    }

    public function show($id)
    {
        // Fetch a single event by its ID
        $event = Event::findOrFail($id);

        // Return the event view
        return view('events.show', compact('event'));
    }

    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'required|string|max:255',
            'organizer' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:20',
            'website' => 'nullable|string|max:255',
            'social_media' => 'nullable|string|max:255',
            'photo' => 'nullable|image|max:60000',
            'category' => 'nullable|string|max:255',
            'tags' => 'nullable|string|max:255',
            'status' => 'nullable|string|',
            'visibility' => 'nullable|string|',
            'accessibility' => 'nullable|string|',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        // Add the user_id to the validated data - Fixed method
        $validated['user_id'] = Auth::check() ? Auth::id() : 1; // Default to user 1 if not authenticated

        // Handle photo upload if provided
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('events', 'public');
            $validated['photo'] = $path;
        }

        // Create the speaker
        $event = Event::create($validated);

        // Check if the action is save_and_new
        if ($request->input('action') === 'save_and_new') {
            return redirect()->route('events.create')
                ->with('success', 'Event created successfully. You can now add another.');
        }

        // Default redirect to index page
        return redirect()->route('events.index')
            ->with('success', 'Event created successfully.');
    }

    public function create()
    {
        return view('events.create');
    }

    public function edit(Event $event)
    {
        return view('events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        // Validate and update the event
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'required|string|max:255',
            'organizer' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:20',
            'website' => 'nullable|url|max:255',
            'social_media' => 'nullable|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'category' => 'nullable|string|max:255',
            'tags' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:draft,published',
            'visibility' => 'nullable|string|in:public,private',
            'accessibility' => 'nullable|string|in:none,limited,full',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $event->update($validatedData);

        return redirect()->route('events.index')->with('success', 'Event updated successfully');
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('events.index')->with('success', 'Event deleted successfully');
    }

    public function search(Request $request)
    {
        // Validate the search query
        $request->validate([
            'query' => 'required|string|max:255',
        ]);

        // Fetch events matching the search query
        $events = Event::where('name', 'like', '%' . $request->query . '%')
            ->orWhere('location', 'like', '%' . $request->query . '%')
            ->orWhere('organizer', 'like', '%' . $request->query . '%')
            ->get();

        // Return the view with the search results
        return view('events.index', compact('events'));
    }

    public function filter(Request $request)
    {
        // Validate the filter criteria
        $request->validate([
            'category' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:draft,published',
            'visibility' => 'nullable|string|in:public,private',
        ]);

        // Fetch events matching the filter criteria
        $events = Event::when($request->category, function ($query) use ($request) {
            return $query->where('category', 'like', '%' . $request->category . '%');
        })->when($request->status, function ($query) use ($request) {
            return $query->where('status', $request->status);
        })->when($request->visibility, function ($query) use ($request) {
            return $query->where('visibility', $request->visibility);
        })->get();

        // Return the view with the filtered events
        return view('events.index', compact('events'));
    }

    public function uploadPhoto(Request $request)
    {
        // Validate the uploaded photo
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Store the photo and get the path
        $path = $request->file('photo')->store('events', 'public');

        // Return the photo URL
        return response()->json(['url' => asset('storage/' . $path)]);
    }

    public function downloadPhoto($id)
    {
        // Fetch the event by ID
        $event = Event::findOrFail($id);

        // Check if the photo exists
        if (!$event->photo) {
            return redirect()->back()->with('error', 'Photo not found.');
        }

        // Return the photo as a download
        return response()->download(storage_path('app/public/' . $event->photo));
    }

    public function deletePhoto($id)
    {
        // Fetch the event by ID
        $event = Event::findOrFail($id);

        // Delete the photo from storage
        if ($event->photo) {
            Storage::disk('public')->delete($event->photo);
            $event->photo = null;
            $event->save();
        }

        // Redirect to the events index with a success message
        return redirect()->route('events.index')->with('success', 'Photo deleted successfully.');
    }

    public function getEventById($id)
    {
        // Fetch the event by ID
        $event = Event::findOrFail($id);

        // Return the event data as JSON
        return response()->json($event);
    }

    public function getEventsByCategory($category)
    {
        // Fetch events by category
        $events = Event::where('category', 'like', '%' . $category . '%')->get();

        // Return the events data as JSON
        return response()->json($events);
    }

    public function getEventsByStatus($status)
    {
        // Fetch events by status
        $events = Event::where('status', $status)->get();

        // Return the events data as JSON
        return response()->json($events);
    }

    public function getEventsByLocation($location)
    {
        // Fetch events by location
        $events = Event::where('location', 'like', '%' . $location . '%')->get();

        // Return the events data as JSON
        return response()->json($events);
    }

    public function getEventsByOrganizer($organizer)
    {
        // Fetch events by organizer
        $events = Event::where('organizer', 'like', '%' . $organizer . '%')->get();

        // Return the events data as JSON
        return response()->json($events);
    }

    /**
     * Import events from CSV file
     */
    public function import(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');

        // Get file contents
        $fileContents = file_get_contents($file->getRealPath());
        $rows = array_map('str_getcsv', explode("\n", $fileContents));

        // Get headers
        $headers = array_shift($rows);

        $importCount = 0;
        $errorCount = 0;

        foreach ($rows as $row) {
            // Skip empty rows
            if (empty($row[0])) continue;

            // Map CSV columns to database fields
            $data = array_combine($headers, $row);

            // Skip if name is empty
            if (empty($data['name'])) continue;

            // Check if event already exists
            $existingEvent = Event::where('name', $data['name'])
                ->where('start_date', $data['start_date'] ?? '')
                ->first();

            if (!$existingEvent) {
                try {
                    // Now create the event
                    Event::create([
                        'name' => $data['name'],
                        'description' => $data['description'] ?? '',
                        'start_date' => $data['start_date'] ?? null,
                        'end_date' => $data['end_date'] ?? null,
                        'location' => $data['location'] ?? '',
                        'organizer' => $data['organizer'] ?? '',
                        'contact_email' => $data['contact_email'] ?? '',
                        'contact_phone' => $data['contact_phone'] ?? '',
                        'website' => $data['website'] ?? null,
                        'social_media' => $data['social_media'] ?? null,
                        'photo' => $data['photo'] ?? null,
                        'category' => $data['category'] ?? null,
                        'tags' => $data['tags'] ?? null,
                        'status' => $data['status'] ?? 'draft',
                        'visibility' => $data['visibility'] ?? 'public',
                        'accessibility' => $data['accessibility'] ?? 'none',
                        'latitude' => $data['latitude'] ?? null,
                        'longitude' => $data['longitude'] ?? null,
                    ]);

                    $importCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                }
            }
        }

        return redirect()->route('events.index')
            ->with('success', "Imported {$importCount} events successfully. {$errorCount} errors occurred.");
    }

    /**
     * Export events to CSV file
     */
    public function export(Request $request)
    {
        // Build query with optional filters
        $query = Event::query();

        // Apply filters if provided
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('organizer', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', 'like', '%' . $request->input('category') . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Get all events based on filters
        $events = $query->get();

        // Create CSV writer
        $csv = Writer::createFromFileObject(new SplTempFileObject());

        // Add header row
        $csv->insertOne([
            'name',
            'description',
            'start_date',
            'end_date',
            'location',
            'organizer',
            'contact_email',
            'contact_phone',
            'website',
            'social_media',
            'photo',
            'category',
            'tags',
            'status',
            'visibility',
            'accessibility',
            'latitude',
            'longitude',
        ]);

        // Add data rows
        foreach ($events as $event) {
            $csv->insertOne([
                $event->name,
                $event->description,
                $event->start_date,
                $event->end_date,
                $event->location,
                $event->organizer,
                $event->contact_email,
                $event->contact_phone,
                $event->website,
                $event->social_media,
                $event->photo ? asset('storage/' . $event->photo) : null,
                $event->category,
                $event->tags,
                $event->status,
                $event->visibility,
                $event->accessibility,
                $event->latitude,
                $event->longitude,
            ]);
        }

        // Set the appropriate headers for download
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="events.csv"',
        ];

        // Return the CSV file as a download
        return response((string) $csv, 200, $headers);
    }

    /**
     * Download a template CSV file for event import
     */
    public function downloadTemplate()
    {
        // Create CSV writer
        $csv = Writer::createFromFileObject(new SplTempFileObject());

        // Add header row
        $csv->insertOne([
            'name',
            'description',
            'start_date',
            'end_date',
            'location',
            'organizer',
            'contact_email',
            'contact_phone',
            'website',
            'social_media',
            'photo',
            'category',
            'tags',
            'status',
            'visibility',
            'accessibility',
            'latitude',
            'longitude',
        ]);

        // Add a sample row (optional)
        $csv->insertOne([
            'Annual Tech Conference',
            'A conference about emerging technologies',
            '2023-11-15',
            '2023-11-17',
            'San Francisco, CA',
            'Tech Innovations Inc',
            'contact@techconf.com',
            '123-456-7890',
            'https://techconf.com',
            '@techconf',
            'https://example.com/photo.jpg',
            'Technology',
            'tech,innovation,ai',
            'published',
            'public',
            'full',
            '37.7749',
            '-122.4194',
        ]);

        // Set the appropriate headers for download
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="events-template.csv"',
        ];

        // Return the CSV file as a download
        return response((string) $csv, 200, $headers);
    }

    /**
     * Delete multiple events at once
     */
    public function destroySelected(Request $request)
    {
        // Validate the request to ensure we have events to delete
        $validated = $request->validate([
            'selected_events' => 'required|array',
            'selected_events.*' => 'exists:events,id',
        ]);

        // Get the selected event IDs
        $selectedIds = $validated['selected_events'];

        // Delete the events
        $deletedCount = Event::whereIn('id', $selectedIds)->delete();

        // Redirect back with a success message
        return redirect()->route('events.index')
            ->with('success', "Successfully deleted {$deletedCount} events.");
    }
}
