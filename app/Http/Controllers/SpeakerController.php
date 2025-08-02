<?php

namespace App\Http\Controllers;

use App\Models\Speaker;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use League\Csv\Writer;
use SplTempFileObject;

class SpeakerController extends Controller
{
    //
    public function index(Request $request)
    {
        $query = Speaker::query();

        // Apply search filter if provided
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply company filter if provided
        if ($request->filled('company')) {
            $query->where('company', 'like', '%' . $request->input('company') . '%');
        }

        // Apply position filter if provided
        if ($request->filled('position')) {
            $query->where('position', 'like', '%' . $request->input('position') . '%');
        }

        // Apply industry filter if provided
        if ($request->filled('industry')) {
            $query->where('industry', $request->input('industry'));
        }

        $Speaker = $query->get();

        // Change from lowercase to match your actual directory
        return view('Speaker.index', compact('Speaker'));
    }
    public function show($id)
    {
        // Fetch the speaker by ID
        $speaker = Speaker::findOrFail($id);

        // Return the view with the speaker data
        return view('Speaker.show', compact('speaker'));
    }
    public function create()
    {
        // Return the view to create a new speaker
        return view('Speaker.create');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:speakers',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:8048',
            'CV_Resume' => 'nullable|file|mimes:pdf,doc,docx|max:8048',
            'website' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'facebook' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'youtube' => 'nullable|url|max:255',
            'tiktok' => 'nullable|url|max:255',
            'is_featured' => 'nullable',
        ]);

        $validated['user_id'] = Auth::check() ? Auth::id() : 1;

        // store and overwrite the 'photo' field in $validated
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')
                ->store('speakers', 'public');
        }

        // store and overwrite the 'CV_Resume' field in $validated
        if ($request->hasFile('CV_Resume')) {
            $validated['CV_Resume'] = $request->file('CV_Resume')
                ->store('speakers', 'public');
        }

        $speaker = Speaker::create($validated);

        // Check if the action is save_and_new
        if ($request->input('action') === 'save_and_new') {
            return redirect()->route('speakers.create')
                ->with('success', 'Speaker created successfully. You can now add another.');
        }

        // Default redirect to index page
        return redirect()->route('speakers.index')
            ->with('success', 'Speaker created successfully.');
    }
    public function edit($id)
    {
        // Fetch the speaker by ID
        $speaker = Speaker::findOrFail($id);

        // Return the view to edit the speaker
        return view('Speaker.edit', compact('speaker'));
    }
    public function update(Request $request, $id)
    {
        // 1) Validate into $validated
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:8048',
            'CV_Resume' => 'nullable|file|mimes:pdf,doc,docx|max:8048',
            'website' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'facebook' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'youtube' => 'nullable|url|max:255',
            'tiktok' => 'nullable|url|max:255',
            'is_featured' => 'nullable',
        ]);

        $speaker = Speaker::findOrFail($id);

        // 2) If new photo, store it; otherwise keep old
        if ($request->hasFile('photo')) {
            Storage::disk('public')->delete($speaker->photo);
            $validated['photo'] = $request->file('photo')->store('speakers', 'public');
        } else {
            $validated['photo'] = $speaker->photo;
        }

        // 3) Same for CV_Resume
        if ($request->hasFile('CV_Resume')) {
            Storage::disk('public')->delete($speaker->CV_Resume);
            $validated['CV_Resume'] = $request->file('CV_Resume')->store('speakers', 'public');
        } else {
            $validated['CV_Resume'] = $speaker->CV_Resume;
        }

        // 4) Update with correct paths
        $speaker->update($validated);
        // Handle "Save and Update Next"
        if ($request->input('action') === 'save_and_new') {
            $next = Speaker::where('id', '>', $id)->orderBy('id')->first();
            if ($next) {
                return redirect()
                    ->route('speakers.edit', $next->id)
                    ->with('success', 'Volunteer updated. You can now edit the next one.');
            }
            return redirect()
                ->route('speakers.index')
                ->with('success', 'Volunteer updated. No more speakers to edit.');
        }

        // Default redirect
        return redirect()
            ->route('speakers.index')
            ->with('success', 'Speaker updated successfully.');
    }
    public function destroy($id)
    {
        // Fetch the speaker by ID
        $speaker = Speaker::findOrFail($id);

        // Delete the speaker
        $speaker->delete();

        // Redirect to the Speaker index with a success message
        return redirect()->route('speakers.index')->with('success', 'Speaker deleted successfully.');
    }
    public function search(Request $request)
    {
        // Validate the search query
        $request->validate([
            'query' => 'required|string|max:255',
        ]);

        // Fetch Speaker matching the search query
        $Speaker = Speaker::where('first_name', 'like', '%' . $request->query . '%')
            ->orWhere('last_name', 'like', '%' . $request->query . '%')
            ->orWhere('email', 'like', '%' . $request->query . '%')
            ->get();

        // Return the view with the search results
        return view('Speaker.index', compact('Speaker'));
    }
    public function filter(Request $request)
    {
        // Validate the filter criteria
        $request->validate([
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
        ]);

        // Fetch Speaker matching the filter criteria
        $Speaker = Speaker::when($request->company, function ($query) use ($request) {
            return $query->where('company', 'like', '%' . $request->company . '%');
        })->when($request->position, function ($query) use ($request) {
            return $query->where('position', 'like', '%' . $request->position . '%');
        })->get();

        // Return the view with the filtered Speaker
        return view('Speaker.index', compact('Speaker'));
    }
    public function uploadPhoto(Request $request)
    {
        // Validate the uploaded photo
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Store the photo and get the path
        $path = $request->file('photo')->store('Speaker', 'public');

        // Return the photo URL
        return response()->json(['url' => asset('storage/' . $path)]);
    }
    public function downloadPhoto($id)
    {
        // Fetch the speaker by ID
        $speaker = Speaker::findOrFail($id);

        // Check if the photo exists
        if (!$speaker->photo) {
            return redirect()->back()->with('error', 'Photo not found.');
        }

        // Return the photo as a download
        return response()->download(storage_path('app/public/' . $speaker->photo));
    }

    /**
     * Download the speaker's CV/Resume
     */
    public function downloadCV($id)
    {
        // Fetch the speaker by ID
        $speaker = Speaker::findOrFail($id);

        // Check if the CV/Resume exists
        if (!$speaker->CV_Resume) {
            return redirect()->back()->with('error', 'CV/Resume not found.');
        }

        // Return the CV/Resume as a download
        return response()->download(storage_path('app/public/' . $speaker->CV_Resume));
    }

    public function deletePhoto($id)
    {
        // Fetch the speaker by ID
        $speaker = Speaker::findOrFail($id);

        // Delete the photo from storage
        if ($speaker->photo) {
            Storage::disk('public')->delete($speaker->photo);
            $speaker->photo = null;
            $speaker->save();
        }

        // Redirect to the Speaker index with a success message
        return redirect()->route('speakers.index')->with('success', 'Photo deleted successfully.');
    }
    public function getSpeakerById($id)
    {
        // Fetch the speaker by ID
        $speaker = Speaker::findOrFail($id);

        // Return the speaker data as JSON
        return response()->json($speaker);
    }
    public function getSpeakerByUserId($userId)
    {
        // Fetch Speaker by user ID
        $Speaker = Speaker::where('user_id', $userId)->get();

        // Return the Speaker data as JSON
        return response()->json($Speaker);
    }
    public function getSpeakerByCompany($company)
    {
        // Fetch Speaker by company
        $Speaker = Speaker::where('company', 'like', '%' . $company . '%')->get();

        // Return the Speaker data as JSON
        return response()->json($Speaker);
    }
    public function getSpeakerByPosition($position)
    {
        // Fetch Speaker by position
        $Speaker = Speaker::where('position', 'like', '%' . $position . '%')->get();

        // Return the Speaker data as JSON
        return response()->json($Speaker);
    }
    public function getSpeakerByEmail($email)
    {
        // Fetch Speaker by email
        $Speaker = Speaker::where('email', 'like', '%' . $email . '%')->get();

        // Return the Speaker data as JSON
        return response()->json($Speaker);
    }
    public function getSpeakerByPhone($phone)
    {
        // Fetch Speaker by phone
        $Speaker = Speaker::where('phone', 'like', '%' . $phone . '%')->get();

        // Return the Speaker data as JSON
        return response()->json($Speaker);
    }
    public function getSpeakerByIndustry($industry)
    {
        // Fetch Speaker by industry
        $Speaker = Speaker::where('industry', 'like', '%' . $industry . '%')->get();

        // Return the Speaker data as JSON
        return response()->json($Speaker);
    }

    /**
     * Import speakers from CSV file
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

            // Skip if email is empty
            if (empty($data['email'])) continue;

            // Check if speaker already exists
            $existingSpeaker = Speaker::where('email', $data['email'])->first();

            if (!$existingSpeaker) {
                try {
                    // IMPORTANT: Associate with a user
                    // If there's a user with matching email, use that user_id
                    $user = User::where('email', $data['email'])->first();

                    // If no matching user found, you may need to decide what to do:
                    // Option 1: Use a default admin/system user
                    if (!$user) {
                        $user = User::find(1); // Using ID 1 as fallback, adjust as needed
                    }

                    // Now create the speaker with user_id
                    Speaker::create([
                        'email' => $data['email'],
                        'first_name' => $data['first_name'] ?? '',
                        'last_name' => $data['last_name'] ?? '',
                        'phone' => $data['phone'] ?? '',
                        'company' => $data['company'] ?? '',
                        'industry' => $data['industry'] ?? '',
                        'position' => $data['position'] ?? '',
                        'bio' => $data['bio'] ?? '',
                        'photo' => $data['photo'] ?? null,
                        'CV_Resume' => $data['CV_Resume'] ?? null,
                        'user_id' => $user->id, // Add the user_id here
                    ]);

                    $importCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                }
            }
        }

        return redirect()->route('speakers.index')
            ->with('success', "Imported {$importCount} speakers successfully. {$errorCount} errors occurred.");
    }

    /**
     * Export speakers to CSV file
     */
    public function export(Request $request)
    {
        // Build query with optional filters
        $query = Speaker::query();

        // Apply filters if provided (reusing the filtering logic from index method)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('company')) {
            $query->where('company', 'like', '%' . $request->input('company') . '%');
        }

        if ($request->filled('position')) {
            $query->where('position', 'like', '%' . $request->input('position') . '%');
        }

        if ($request->filled('industry')) {
            $query->where('industry', $request->input('industry'));
        }

        // Get all speakers based on filters
        $speakers = $query->get();

        // Create CSV writer
        $csv = Writer::createFromFileObject(new SplTempFileObject());

        // Add header row
        $csv->insertOne([
            'first_name',
            'last_name',
            'email',
            'phone',
            'company',
            'industry',
            'position',
            'bio',
            'photo',
            'CV_Resume',
        ]);

        // Add data rows
        foreach ($speakers as $speaker) {
            $csv->insertOne([
                $speaker->first_name,
                $speaker->last_name,
                $speaker->email,
                $speaker->phone,
                $speaker->company,
                $speaker->industry,
                $speaker->position,
                $speaker->bio,
                $speaker->photo ? asset('storage/' . $speaker->photo) : null, // Include photo URL if exists
                $speaker->CV_Resume ? asset('storage/' . $speaker->CV_Resume) : null, // Include CV/Resume URL if exists
            ]);
        }

        // Set the appropriate headers for download
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="speakers.csv"',
        ];

        // Return the CSV file as a download - Fix for deprecated getContent()
        return response((string) $csv, 200, $headers);
    }

    /**
     * Download a template CSV file for speaker import
     */
    public function downloadTemplate()
    {
        // Create CSV writer
        $csv = Writer::createFromFileObject(new SplTempFileObject());

        // Add header row
        $csv->insertOne([
            'first_name',
            'last_name',
            'email',
            'phone',
            'company',
            'industry',
            'position',
            'bio',
            'photo',
            'CV_Resume',
        ]);

        // Add a sample row (optional)
        $csv->insertOne([
            'John',
            'Doe',
            'john.doe@example.com',
            '123-456-7890',
            'Acme Inc',
            'Technology',
            'CEO',
            'John is a technology leader with 15+ years of experience.',
            'https://example.com/photo.jpg', // Sample photo URL
            'https://example.com/cv.pdf', // Sample CV/Resume URL
        ]);

        // Set the appropriate headers for download
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="speakers-template.csv"',
        ];

        // Return the CSV file as a download - Fix for deprecated getContent()
        return response((string) $csv, 200, $headers);
    }

    /**
     * Delete multiple speakers at once
     */
    public function destroySelected(Request $request)
    {
        // Validate the request to ensure we have speakers to delete
        $validated = $request->validate([
            'selected_speakers' => 'required|array',
            'selected_speakers.*' => 'exists:speakers,id', // Changed from "Speaker" to "speakers"
        ]);

        // Get the selected speaker IDs
        $selectedIds = $validated['selected_speakers'];

        // Delete the speakers
        $deletedCount = Speaker::whereIn('id', $selectedIds)->delete();

        // Redirect back with a success message
        return redirect()->route('speakers.index')
            ->with('success', "Successfully deleted {$deletedCount} speakers.");
    }

    // Get JSON Data for industry filter
    public function getIndustryData()
    {
        $industries = Speaker::select('industry')->distinct()->get();

        return response()->json($industries);
    }
}
