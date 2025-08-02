<?php

namespace App\Http\Controllers;

use App\Mail\VolunteerUpdateLink;
use App\Models\User;
use App\Models\Volunteer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use League\Csv\Reader;
use League\Csv\Writer;
use SplTempFileObject;

class VolunteerController extends Controller
{
    //
    public function index(Request $request)
    {
        $query = Volunteer::query();

        // Apply search filter if provided
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply skills filter if provided
        if ($request->filled('skills')) {
            $query->where('skills', 'like', '%' . $request->input('skills') . '%');
        }

        // Apply status filter if provided
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Apply active filter if provided
        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        $volunteers = $query->get();

        return view('volunteers.index', compact('volunteers'));
    }

    public function create()
    {
        return view('volunteers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:volunteers,email',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'skills' => 'nullable|string|max:255',
            'availability' => 'nullable|string|max:255',
            'interests' => 'nullable|string|max:255',
            'cv_resume' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'notes' => 'nullable|string|max:1000',
            'active' => 'boolean',
            'status' => 'nullable|string|max:255',
            'referral_source' => 'nullable|string|max:255',
            'background_check_status' => 'nullable|string|max:255',
            'background_check_date' => 'nullable|date',
            'background_check_notes' => 'nullable|string|max:1000',
            'training_status' => 'nullable|string|max:255',
            'training_date' => 'nullable|date',
            'training_notes' => 'nullable|string|max:1000',
            'orientation_status' => 'nullable|string|max:255',
            'orientation_date' => 'nullable|date',
            'orientation_notes' => 'nullable|string|max:1000',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:255',
            'emergency_contact_relationship' => 'nullable|string|max:255',
            'medical_conditions' => 'nullable|string|max:1000',
            'allergies' => 'nullable|string|max:1000',
            'languages' => 'nullable|string|max:255',
        ]);

        // Handle photo upload if provided
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('volunteers', 'public');
            $request->merge(['photo' => $path]);
        }

        // Handle CV/Resume upload if provided
        if ($request->hasFile('cv_resume')) {
            $path = $request->file('cv_resume')->store('volunteers/cv', 'public');
            $request->merge(['cv_resume' => $path]);
        }

        // Create the volunteer
        $volunteer = Volunteer::create($request->only([
            'first_name',
            'last_name',
            'email',
            'phone',
            'skills',
            'availability',
            'interests',
            'cv_resume',
            'photo',
            'notes',
            'active',
            'status',
        ]));

        // Check if the action is save_and_new
        if ($request->input('action') === 'save_and_new') {
            return redirect()->route('volunteers.edit', $volunteer->id+1)
                ->with('success', 'Volunteer created successfully. You can now add another.');
        }

        return redirect()->route('volunteers.index')->with('success', 'Volunteer created successfully.');
    }

    public function show($id)
    {
        // Fetch the volunteer by ID
        $volunteer = Volunteer::findOrFail($id);

        // Return the view with the volunteer data
        return view('volunteers.show', compact('volunteer'));
    }

    public function edit($id)
    {
        // Fetch the volunteer by ID
        $volunteer = Volunteer::findOrFail($id);

        // Return the view to edit the volunteer
        return view('volunteers.edit', compact('volunteer'));
    }

    public function update(Request $request, $id)
    {
        // Capture validated data
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:volunteers,email,' . $id,
            'phone'      => 'nullable|string|max:255',
            'address'    => 'nullable|string|max:255',
            'city'       => 'nullable|string|max:255',
            'skills'     => 'nullable|string|max:255',
            'availability' => 'nullable|string|max:255',
            'interests'  => 'nullable|string|max:255',
            'cv_resume'  => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'photo'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'notes'      => 'nullable|string|max:1000',
            'active'     => 'boolean',
            'status'     => 'nullable|string|max:255',
            'referral_source' => 'nullable|string|max:255',
            'background_check_status' => 'nullable|string|max:255',
            'background_check_date' => 'nullable|date',
            'background_check_notes' => 'nullable|string|max:1000',
            'training_status' => 'nullable|string|max:255',
            'training_date' => 'nullable|date',
            'training_notes' => 'nullable|string|max:1000',
            'orientation_status' => 'nullable|string|max:255',
            'orientation_date' => 'nullable|date',
            'orientation_notes' => 'nullable|string|max:1000',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:255',
            'emergency_contact_relationship' => 'nullable|string|max:255',
            'medical_conditions' => 'nullable|string|max:1000',
            'allergies' => 'nullable|string|max:1000',
            'languages' => 'nullable|string|max:255',
        ]);

        $volunteer = Volunteer::findOrFail($id);

        // Photo upload
        if ($request->hasFile('photo')) {
            Storage::disk('public')->delete($volunteer->photo);
            $validated['photo'] = $request->file('photo')->store('volunteers', 'public');
        } else {
            $validated['photo'] = $volunteer->photo;
        }

        // CV/Resume upload
        if ($request->hasFile('cv_resume')) {
            Storage::disk('public')->delete($volunteer->cv_resume);
            $validated['cv_resume'] = $request->file('cv_resume')->store('volunteers/cv', 'public');
        } else {
            $validated['cv_resume'] = $volunteer->cv_resume;
        }

        // Update with processed data
        $volunteer->update($validated);

        // Handle "Save and Update Next"
        if ($request->input('action') === 'save_and_new') {
            $next = Volunteer::where('id', '>', $id)->orderBy('id')->first();
            if ($next) {
                return redirect()
                    ->route('volunteers.edit', $next->id)
                    ->with('success', 'Volunteer updated. You can now edit the next one.');
            }
            return redirect()
                ->route('volunteers.index')
                ->with('success', 'Volunteer updated. No more volunteers to edit.');
        }

        // Default redirect
        return redirect()
            ->route('volunteers.index')
            ->with('success', 'Volunteer updated successfully.');
    }

    public function destroy($id)
    {
        // Fetch the volunteer by ID
        $volunteer = Volunteer::findOrFail($id);

        // Delete the volunteer
        $volunteer->delete();

        // Redirect to the volunteers index with a success message
        return redirect()->route('volunteers.index')->with('success', 'Volunteer deleted successfully.');
    }

    //////////////////////////////////////////////////////////////////
    // Send a notification to the volunteer to fill out their profile
    public function sendUpdateLink($id)
    {
        $volunteer = Volunteer::findOrFail($id);

        if (!$volunteer->email) {
            return redirect()->back()->with('error', 'This volunteer does not have an email address.');
        }

        // Generate a signed URL valid for 7 days
        $signedUrl = URL::temporarySignedRoute(
            'volunteer.update.form',
            now()->addDays(7),
            ['id' => $volunteer->id]
        );

        // Send the email with the link
        Mail::to($volunteer->email)->send(new VolunteerUpdateLink($volunteer, $signedUrl));

        return redirect()->back()->with('success', 'Secure update link sent to volunteer.');
    }

    public function updateForm(Request $request, $id)
    {
        $volunteer = Volunteer::findOrFail($id);
        return view('volunteers.public-update', compact('volunteer'));
    }

    public function processUpdateForm(Request $request, $id)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:volunteers,email,' . $id,
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'skills' => 'nullable|string|max:255',
            'availability' => 'nullable|string|max:255',
            'interests' => 'nullable|string|max:255',
            'cv_resume' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'notes' => 'nullable|string|max:1000',
            'active' => 'boolean',
            'status' => 'nullable|string|max:255',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:255',
            'emergency_contact_relationship' => 'nullable|string|max:255',
            'medical_conditions' => 'nullable|string|max:1000',
            'allergies' => 'nullable|string|max:1000',
            'languages' => 'nullable|string|max:255',
        ]);

        $volunteer = Volunteer::findOrFail($id);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            if ($volunteer->photo) {
                Storage::disk('public')->delete($volunteer->photo);
            }

            $validated['photo'] = $request->file('photo')->store('volunteers', 'public');
        } else {
            $validated['photo'] = $volunteer->photo;
        }

        // Handle CV/Resume upload
        if ($request->hasFile('cv_resume')) {
            if ($volunteer->cv_resume) {
                Storage::disk('public')->delete($volunteer->cv_resume);
            }

            $validated['cv_resume'] = $request->file('cv_resume')->store('volunteers/cv', 'public');
        } else {
            $validated['cv_resume'] = $volunteer->cv_resume;
        }

        // Update the volunteer with validated and processed data
        $volunteer->update($validated);

        return redirect()
            ->route('public.index')
            ->with('success', 'Volunteer updated successfully.');
    }

    //////////////////////////////////////////////////////////////////

    public function search(Request $request)
    {
        // Validate the search query
        $request->validate([
            'query' => 'required|string|max:255',
        ]);

        // Fetch volunteers matching the search query
        $volunteers = Volunteer::where('first_name', 'like', '%' . $request->query . '%')
            ->orWhere('last_name', 'like', '%' . $request->query . '%')
            ->orWhere('email', 'like', '%' . $request->query . '%')
            ->get();

        // Return the view with the search results
        return view('volunteers.index', compact('volunteers'));
    }

    public function filter(Request $request)
    {
        // Validate the filter criteria
        $request->validate([
            'skills' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'active' => 'nullable|boolean',
        ]);

        // Fetch volunteers matching the filter criteria
        $volunteers = Volunteer::when($request->skills, function ($query) use ($request) {
            return $query->where('skills', 'like', '%' . $request->skills . '%');
        })->when($request->status, function ($query) use ($request) {
            return $query->where('status', $request->status);
        })->when($request->has('active'), function ($query) use ($request) {
            return $query->where('active', $request->boolean('active'));
        })->get();

        // Return the view with the filtered volunteers
        return view('volunteers.index', compact('volunteers'));
    }

    public function uploadPhoto(Request $request)
    {
        // Validate the uploaded photo
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Store the photo and get the path
        $path = $request->file('photo')->store('volunteers', 'public');

        // Return the photo URL
        return response()->json(['url' => asset('storage/' . $path)]);
    }

    public function downloadPhoto($id)
    {
        // Fetch the volunteer by ID
        $volunteer = Volunteer::findOrFail($id);

        // Check if the photo exists
        if (!$volunteer->photo) {
            return redirect()->back()->with('error', 'Photo not found.');
        }

        // Return the photo as a download
        return response()->download(storage_path('app/public/' . $volunteer->photo));
    }

    public function deletePhoto($id)
    {
        // Fetch the volunteer by ID
        $volunteer = Volunteer::findOrFail($id);

        // Delete the photo from storage
        if ($volunteer->photo) {
            Storage::disk('public')->delete($volunteer->photo);
            $volunteer->photo = null;
            $volunteer->save();
        }

        // Redirect to the volunteers index with a success message
        return redirect()->route('volunteers.index')->with('success', 'Photo deleted successfully.');
    }

    public function getVolunteerById($id)
    {
        // Fetch the volunteer by ID
        $volunteer = Volunteer::findOrFail($id);

        // Return the volunteer data as JSON
        return response()->json($volunteer);
    }

    public function getVolunteersBySkills($skills)
    {
        // Fetch volunteers by skills
        $volunteers = Volunteer::where('skills', 'like', '%' . $skills . '%')->get();

        // Return the volunteers data as JSON
        return response()->json($volunteers);
    }

    public function getVolunteersByAvailability($availability)
    {
        // Fetch volunteers by availability
        $volunteers = Volunteer::where('availability', 'like', '%' . $availability . '%')->get();

        // Return the volunteers data as JSON
        return response()->json($volunteers);
    }

    public function getActiveVolunteers()
    {
        // Fetch active volunteers
        $volunteers = Volunteer::where('active', true)->get();

        // Return the volunteers data as JSON
        return response()->json($volunteers);
    }

    /**
     * Import volunteers from CSV file
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

            // Check if volunteer already exists
            $existingVolunteer = Volunteer::where('email', $data['email'])->first();

            if (!$existingVolunteer) {
                try {
                    // Create the volunteer record
                    Volunteer::create([
                        'first_name' => $data['first_name'] ?? '',
                        'last_name' => $data['last_name'] ?? '',
                        'email' => $data['email'],
                        'phone' => $data['phone'] ?? '',
                        'address' => $data['address'] ?? '',
                        'city' => $data['city'] ?? '',
                        'skills' => $data['skills'] ?? '',
                        'availability' => $data['availability'] ?? '',
                        'interests' => $data['interests'] ?? '',
                        'cv_resume' => $data['cv_resume'] ?? '',
                        'photo' => $data['photo'] ?? null,
                        'notes' => $data['notes'] ?? '',
                        'active' => $data['active'] ?? true,
                        'status' => $data['status'] ?? '',
                        'referral_source' => $data['referral_source'] ?? '',
                    ]);

                    $importCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                }
            }
        }

        return redirect()->route('volunteers.index')
            ->with('success', "Imported {$importCount} volunteers successfully. {$errorCount} errors occurred.");
    }

    /**
     * Export volunteers to CSV file
     */
    public function export(Request $request)
    {
        // Build query with optional filters
        $query = Volunteer::query();

        // Apply filters if provided
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('skills')) {
            $query->where('skills', 'like', '%' . $request->input('skills') . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        // Get all volunteers based on filters
        $volunteers = $query->get();

        // Create CSV writer
        $csv = Writer::createFromFileObject(new SplTempFileObject());

        // Add header row with main volunteer fields
        $csv->insertOne([
            'first_name',
            'last_name',
            'email',
            'phone',
            'address',
            'city',
            'skills',
            'availability',
            'interests',
            'cv_resume',
            'photo',
            'notes',
            'active',
            'status',
            'referral_source',
        ]);

        // Add data rows
        foreach ($volunteers as $volunteer) {
            $csv->insertOne([
                $volunteer->first_name,
                $volunteer->last_name,
                $volunteer->email,
                $volunteer->phone,
                $volunteer->address,
                $volunteer->city,
                $volunteer->skills,
                $volunteer->availability,
                $volunteer->interests,
                $volunteer->cv_resume ? asset('storage/' . $volunteer->cv_resume) : null,
                $volunteer->photo ? asset('storage/' . $volunteer->photo) : null,
                $volunteer->notes,
                $volunteer->active ? 'Yes' : 'No',
                $volunteer->status,
                $volunteer->referral_source,
            ]);
        }

        // Set the appropriate headers for download
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="volunteers.csv"',
        ];

        // Return the CSV file as a download
        return response((string) $csv, 200, $headers);
    }

    /**
     * Download a template CSV file for volunteer import
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
            'address',
            'city',
            'skills',
            'availability',
            'interests',
            'cv_resume',
            'photo',
            'notes',
            'active',
            'status',
            'referral_source',
        ]);

        // Add a sample row (optional)
        $csv->insertOne([
            'Jane',
            'Doe',
            'jane.doe@example.com',
            '123-456-7890',
            '123 Main St',
            'Anytown',
            'teaching,organizing,social media',
            'weekends,evenings',
            'education,community service',
            'https://example.com/cv.pdf',
            'https://example.com/photo.jpg',
            'Enthusiastic volunteer with prior experience',
            'Yes',
            'active',
            'website',
        ]);

        // Set the appropriate headers for download
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="volunteers-template.csv"',
        ];

        // Return the CSV file as a download
        return response((string) $csv, 200, $headers);
    }

    /**
     * Delete multiple volunteers at once
     */
    public function destroySelected(Request $request)
    {
        // Validate the request to ensure we have volunteers to delete
        $validated = $request->validate([
            'selected_volunteers' => 'required|array',
            'selected_volunteers.*' => 'exists:volunteers,id',
        ]);

        // Get the selected volunteer IDs
        $selectedIds = $validated['selected_volunteers'];

        // Delete the volunteers
        $deletedCount = Volunteer::whereIn('id', $selectedIds)->delete();

        // Redirect back with a success message
        return redirect()->route('volunteers.index')
            ->with('success', "Successfully deleted {$deletedCount} volunteers.");
    }
}
