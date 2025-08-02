<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use League\Csv\Writer;
use SplTempFileObject;

class PartnerController extends Controller
{
    public function index(Request $request)
    {
        $query = Partner::query();

        // Apply search filter if provided
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('organization_name', 'like', "%{$search}%")
                    ->orWhere('contact_name', 'like', "%{$search}%")
                    ->orWhere('contact_email', 'like', "%{$search}%");
            });
        }

        // Apply organization type filter if provided
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Apply category filter if provided
        if ($request->filled('category')) {
            $query->where('category', 'like', '%' . $request->input('category') . '%');
        }

        // Apply partnership level filter if provided
        if ($request->filled('partnership_level')) {
            $query->where('partnership_level', $request->input('partnership_level'));
        }

        $partners = $query->get();

        return view('partners.index', compact('partners'));
    }

    public function show($id)
    {
        // Fetch the partner by ID
        $partner = Partner::findOrFail($id);

        // Return the view with the partner data
        return view('partners.show', compact('partner'));
    }

    public function create()
    {
        // Return the view to create a new partner
        return view('partners.create');
    }

    public function store(Request $request)
    {
        // Validate the input
        $validated = $request->validate([
            'organization_name' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255|unique:partners',
            'contact_phone' => 'nullable|string|max:20',
            'website' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'nullable|string|in:active,inactive',
            'type' => 'nullable|string|',
            'category' => 'nullable|string|max:100',
            'sub_category' => 'nullable|string|max:100',
            'partnership_level' => 'nullable|string|',
            'partnership_start_date' => 'nullable|date',
            'partnership_end_date' => 'nullable|date',
        ]);

        // Add the user_id to the validated data
        $validated['user_id'] = Auth::check() ? Auth::id() : 1; // Default to user 1 if not authenticated

        // Handle logo upload if provided
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('partners', 'public');
            $validated['logo'] = $path;
        }

        // Create the partner
        $partner = Partner::create($validated);

        // Check if the action is save_and_new
        if ($request->input('action') === 'save_and_new') {
            return redirect()->route('partners.create')
                ->with('success', 'Partner created successfully. You can now add another.');
        }

        // Default redirect to index page
        return redirect()->route('partners.index')
            ->with('success', 'Partner created successfully.');
    }

    public function edit($id)
    {
        // Fetch the partner by ID
        $partner = Partner::findOrFail($id);

        // Return the view to edit the partner
        return view('partners.edit', compact('partner'));
    }

    public function update(Request $request, $id)
    {
        // Validate the request data
        $data = $request->validate([
            'organization_name' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255|unique:partners,contact_email,' . $id,
            'contact_phone' => 'nullable|string|max:20',
            'website' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'nullable|string|in:active,inactive,pending,expired',
            'type' => 'nullable|string|',
            'category' => 'nullable|string|max:100',
            'sub_category' => 'nullable|string|max:100',
            'partnership_level' => 'nullable|string|',
            'partnership_start_date' => 'nullable|date',
            'partnership_end_date' => 'nullable|date',
        ]);

        // 2) Handle logo upload
        if ($request->hasFile('logo')) {
            $partner = Partner::findOrFail($id);
            if ($partner->logo) {
                Storage::disk('public')->delete($partner->logo);
            }
            $data['logo'] = $request->file('logo')->store('partners', 'public');
        }

        // 3) Update model
        Partner::findOrFail($id)->update($data);

        return redirect()
            ->route('partners.index')
            ->with('success', 'Partner updated successfully.');
    }

    public function destroy($id)
    {
        // Fetch the partner by ID
        $partner = Partner::findOrFail($id);

        // Delete the partner
        $partner->delete();

        // Redirect to the partner index with a success message
        return redirect()->route('partners.index')->with('success', 'Partner deleted successfully.');
    }

    public function search(Request $request)
    {
        // Validate the search query
        $request->validate([
            'query' => 'required|string|max:255',
        ]);

        // Fetch partners matching the search query
        $partners = Partner::where('organization_name', 'like', '%' . $request->query . '%')
            ->orWhere('contact_name', 'like', '%' . $request->query . '%')
            ->orWhere('contact_email', 'like', '%' . $request->query . '%')
            ->get();

        // Return the view with the search results
        return view('partners.index', compact('partners'));
    }

    public function filter(Request $request)
    {
        // Validate the filter criteria
        $request->validate([
            'type' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'partnership_level' => 'nullable|string|max:255',
        ]);

        // Fetch partners matching the filter criteria
        $partners = Partner::when($request->type, function ($query) use ($request) {
            return $query->where('type', $request->type);
        })->when($request->category, function ($query) use ($request) {
            return $query->where('category', 'like', '%' . $request->category . '%');
        })->when($request->partnership_level, function ($query) use ($request) {
            return $query->where('partnership_level', $request->partnership_level);
        })->get();

        // Return the view with the filtered partners
        return view('partners.index', compact('partners'));
    }

    public function uploadLogo(Request $request)
    {
        // Validate the uploaded logo
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Store the logo and get the path
        $path = $request->file('logo')->store('partners', 'public');

        // Return the logo URL
        return response()->json(['url' => asset('storage/' . $path)]);
    }

    public function downloadLogo($id)
    {
        // Fetch the partner by ID
        $partner = Partner::findOrFail($id);

        // Check if the logo exists
        if (!$partner->logo) {
            return redirect()->back()->with('error', 'Logo not found.');
        }

        // Return the logo as a download
        return response()->download(storage_path('app/public/' . $partner->logo));
    }

    public function deleteLogo($id)
    {
        // Fetch the partner by ID
        $partner = Partner::findOrFail($id);

        // Delete the logo from storage
        if ($partner->logo) {
            Storage::disk('public')->delete($partner->logo);
            $partner->logo = null;
            $partner->save();
        }

        // Redirect to the partner index with a success message
        return redirect()->route('partners.index')->with('success', 'Logo deleted successfully.');
    }

    public function getPartnerById($id)
    {
        // Fetch the partner by ID
        $partner = Partner::findOrFail($id);

        // Return the partner data as JSON
        return response()->json($partner);
    }

    public function getPartnerByUserId($userId)
    {
        // Fetch partners by user ID
        $partners = Partner::where('user_id', $userId)->get();

        // Return the partners data as JSON
        return response()->json($partners);
    }

    public function getPartnerByOrganization($organization)
    {
        // Fetch partners by organization name
        $partners = Partner::where('organization_name', 'like', '%' . $organization . '%')->get();

        // Return the partners data as JSON
        return response()->json($partners);
    }

    public function getPartnerByType($type)
    {
        // Fetch partners by type
        $partners = Partner::where('type', $type)->get();

        // Return the partners data as JSON
        return response()->json($partners);
    }

    public function getPartnerByEmail($email)
    {
        // Fetch partners by email
        $partners = Partner::where('contact_email', 'like', '%' . $email . '%')->get();

        // Return the partners data as JSON
        return response()->json($partners);
    }

    public function getPartnerByPhone($phone)
    {
        // Fetch partners by phone
        $partners = Partner::where('contact_phone', 'like', '%' . $phone . '%')->get();

        // Return the partners data as JSON
        return response()->json($partners);
    }

    public function getPartnerByCategory($category)
    {
        // Fetch partners by category
        $partners = Partner::where('category', 'like', '%' . $category . '%')->get();

        // Return the partners data as JSON
        return response()->json($partners);
    }

    /**
     * Import partners from CSV file
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
            if (empty($data['contact_email'])) continue;

            // Check if partner already exists
            $existingPartner = Partner::where('contact_email', $data['contact_email'])->first();

            if (!$existingPartner) {
                try {
                    // Associate with a user
                    $user = User::where('email', $data['contact_email'])->first();

                    // If no matching user found, use a default admin/system user
                    if (!$user) {
                        $user = User::find(1); // Using ID 1 as fallback, adjust as needed
                    }

                    // Create the partner with user_id
                    Partner::create([
                        'organization_name' => $data['organization_name'] ?? '',
                        'contact_name' => $data['contact_name'] ?? '',
                        'contact_email' => $data['contact_email'],
                        'contact_phone' => $data['contact_phone'] ?? '',
                        'website' => $data['website'] ?? '',
                        'address' => $data['address'] ?? '',
                        'city' => $data['city'] ?? '',
                        'state' => $data['state'] ?? '',
                        'zip' => $data['zip'] ?? '',
                        'country' => $data['country'] ?? '',
                        'description' => $data['description'] ?? '',
                        'logo' => $data['logo'] ?? null,
                        'status' => $data['status'] ?? 'active',
                        'type' => $data['type'] ?? null,
                        'category' => $data['category'] ?? null,
                        'sub_category' => $data['sub_category'] ?? null,
                        'partnership_level' => $data['partnership_level'] ?? null,
                        'partnership_start_date' => $data['partnership_start_date'] ?? null,
                        'partnership_end_date' => $data['partnership_end_date'] ?? null,
                        'user_id' => $user->id,
                    ]);

                    $importCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                }
            }
        }

        return redirect()->route('partners.index')
            ->with('success', "Imported {$importCount} partners successfully. {$errorCount} errors occurred.");
    }

    /**
     * Export partners to CSV file
     */
    public function export(Request $request)
    {
        // Build query with optional filters
        $query = Partner::query();

        // Apply filters if provided
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('organization_name', 'like', "%{$search}%")
                    ->orWhere('contact_name', 'like', "%{$search}%")
                    ->orWhere('contact_email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('category')) {
            $query->where('category', 'like', '%' . $request->input('category') . '%');
        }

        if ($request->filled('partnership_level')) {
            $query->where('partnership_level', $request->input('partnership_level'));
        }

        // Get all partners based on filters
        $partners = $query->get();

        // Create CSV writer
        $csv = Writer::createFromFileObject(new SplTempFileObject());

        // Add header row
        $csv->insertOne([
            'organization_name',
            'contact_name',
            'contact_email',
            'contact_phone',
            'website',
            'address',
            'city',
            'state',
            'zip',
            'country',
            'description',
            'logo',
            'status',
            'type',
            'category',
            'sub_category',
            'partnership_level',
            'partnership_start_date',
            'partnership_end_date'
        ]);

        // Add data rows
        foreach ($partners as $partner) {
            $csv->insertOne([
                $partner->organization_name,
                $partner->contact_name,
                $partner->contact_email,
                $partner->contact_phone,
                $partner->website,
                $partner->address,
                $partner->city,
                $partner->state,
                $partner->zip,
                $partner->country,
                $partner->description,
                $partner->logo ? asset('storage/' . $partner->logo) : null,
                $partner->status,
                $partner->type,
                $partner->category,
                $partner->sub_category,
                $partner->partnership_level,
                $partner->partnership_start_date,
                $partner->partnership_end_date
            ]);
        }

        // Set the appropriate headers for download
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="partners.csv"',
        ];

        // Return the CSV file as a download
        return response((string) $csv, 200, $headers);
    }

    /**
     * Download a template CSV file for partner import
     */
    public function downloadTemplate()
    {
        // Create CSV writer
        $csv = Writer::createFromFileObject(new SplTempFileObject());

        // Add header row
        $csv->insertOne([
            'organization_name',
            'contact_name',
            'contact_email',
            'contact_phone',
            'website',
            'address',
            'city',
            'state',
            'zip',
            'country',
            'description',
            'logo',
            'status',
            'type',
            'category',
            'sub_category',
            'partnership_level',
            'partnership_start_date',
            'partnership_end_date'
        ]);

        // Add a sample row (optional)
        $csv->insertOne([
            'Acme Corporation',
            'Jane Smith',
            'jane.smith@acme.com',
            '123-456-7890',
            'https://www.acme.com',
            '123 Main Street',
            'New York',
            'NY',
            '10001',
            'USA',
            'A leading technology company specializing in cloud solutions.',
            'https://example.com/logo.jpg',
            'active',
            'for-profit',
            'Technology',
            'Cloud Computing',
            'premium',
            '2023-01-01',
            '2023-12-31'
        ]);

        // Set the appropriate headers for download
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="partners-template.csv"',
        ];

        // Return the CSV file as a download
        return response((string) $csv, 200, $headers);
    }

    /**
     * Delete multiple partners at once
     */
    public function destroySelected(Request $request)
    {
        // Validate the request to ensure we have partners to delete
        $validated = $request->validate([
            'selected_partners' => 'required|array',
            'selected_partners.*' => 'exists:partners,id',
        ]);

        // Get the selected partner IDs
        $selectedIds = $validated['selected_partners'];

        // Delete the partners
        $deletedCount = Partner::whereIn('id', $selectedIds)->delete();

        // Redirect back with a success message
        return redirect()->back()
            ->with('success', "Successfully deleted {$deletedCount} partners.");
    }
}
