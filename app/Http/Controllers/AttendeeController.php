<?php

namespace App\Http\Controllers;

use App\Mail\PasswordSend;
use App\Mail\VolunteerUpdateLink;
use App\Models\Attendee;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AttendeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendee::query();

        // Apply search filter if provided
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Apply phone filter if provided
        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%' . $request->input('phone') . '%');
        }

        // Apply registration date filter if provided
        if ($request->filled('registration_date')) {
            $query->whereDate('created_at', $request->input('registration_date'));
        }

        $attendees = $query->get();

        return view('attendees.index', compact('attendees'));
    }

    /**
     * Register a new attendee and create a user account.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */

    public function registerLogin(Request $request)
    {

        // Validate and store the new attendee
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:attendees,email',
            'phone' => 'nullable|string|max:15',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
        ]);

        Attendee::create($request->only([
            'first_name',
            'last_name',
            'email',
            'phone',
            'country',
            'city',
            'address',
            'postal_code'
        ]));

        $user = User::create([
            'name' => $request->first_name . ' ' . $request->last_name,
            'email' => $request->email,
            'password' => $request->password, // Generate a random password
            'phone' => $request->phone,
            'role' => 'attendee',
        ]);

        

        Auth::login($user);

        
        // $user = User::findOrFail('id');
            // $password = $user->password;
            // Generate a signed URL valid for 7 days
            // $signedUrl = URL::temporarySignedRoute(
            //     'auth.confirm-password',
            //     now()->addDays(7),
            //     ['id' => $user->id]
            // );
            // $url = view('auth.confirm-password', [
            //     'id' => $user->id,
            //     'email' => $user->email,
            //     'password' => $user->password,
            // ])->render();
            
            // $password = $user->password;

            // Mail::to($user->email)->send(new PasswordSend($user, $url, $password));

            // event(new Registered($user));
        return redirect(route(('public.index'), absolute: false))->with('success', 'Attendee created successfully.');
    }

    public function create()
    {
        // Show the form to create a new attendee
        return view('attendees.create');
    }

    public function store(Request $request)
    {
        // Validate and store the new attendee
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:attendees,email',
            'phone' => 'nullable|string|max:15',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
        ]);

        Attendee::create($request->all());

        return redirect()->route('attendees.index')->with('success', 'Attendee created successfully.');
    }

    public function show(Attendee $attendee)
    {
        // Show the details of a specific attendee
        return view('attendees.show', compact('attendee'));
    }

    public function edit(Attendee $attendee)
    {
        // Show the form to edit an existing attendee
        return view('attendees.edit', compact('attendee'));
    }

    public function update(Request $request, Attendee $attendee)
    {
        // Validate and update the attendee
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:attendees,email,' . $attendee->id,
            'phone' => 'nullable|string|max:15',
        ]);

        $attendee->update($request->all());

        return redirect()->route('attendees.index')->with('success', 'Attendee updated successfully.');
    }

    public function destroy(Attendee $attendee)
    {
        // Delete the attendee
        $attendee->delete();

        return redirect()->route('attendees.index')->with('success', 'Attendee deleted successfully.');
    }

    /**
     * Search attendees by name, email, or phone
     */
    public function search(Request $request)
    {
        // Validate the search query
        $request->validate([
            'query' => 'required|string|max:255',
        ]);

        // Fetch attendees matching the search query
        $attendees = Attendee::where('first_name', 'like', '%' . $request->query . '%')
            ->orWhere('last_name', 'like', '%' . $request->query . '%')
            ->orWhere('email', 'like', '%' . $request->query . '%')
            ->orWhere('phone', 'like', '%' . $request->query . '%')
            ->get();

        // Return the view with the search results
        return view('attendees.index', compact('attendees'));
    }

    /**
     * Filter attendees by registration date and phone
     */
    public function filter(Request $request)
    {
        // Validate the filter criteria
        $request->validate([
            'registration_date' => 'nullable|date',
            'phone' => 'nullable|string|max:15',
        ]);

        // Fetch attendees matching the filter criteria
        $attendees = Attendee::when($request->phone, function ($query) use ($request) {
            return $query->where('phone', 'like', '%' . $request->phone . '%');
        })->when($request->registration_date, function ($query) use ($request) {
            return $query->whereDate('created_at', $request->registration_date);
        })->get();

        // Return the view with the filtered attendees
        return view('attendees.index', compact('attendees'));
    }

    /**
     * Get attendee by ID
     */
    public function getAttendeeById($id)
    {
        // Fetch the attendee by ID
        $attendee = Attendee::findOrFail($id);

        // Return the attendee data as JSON
        return response()->json($attendee);
    }

    /**
     * Get attendee by email
     */
    public function getAttendeeByEmail($email)
    {
        // Fetch attendees by email
        $attendees = Attendee::where('email', 'like', '%' . $email . '%')->get();

        // Return the attendees data as JSON
        return response()->json($attendees);
    }

    /**
     * Get attendee by phone
     */
    public function getAttendeeByPhone($phone)
    {
        // Fetch attendees by phone
        $attendees = Attendee::where('phone', 'like', '%' . $phone . '%')->get();

        // Return the attendees data as JSON
        return response()->json($attendees);
    }

    /**
     * Get attendees by registration date
     */
    public function getAttendeeByRegistrationDate($date)
    {
        // Fetch attendees by registration date
        $attendees = Attendee::whereDate('created_at', $date)->get();

        // Return the attendees data as JSON
        return response()->json($attendees);
    }

    /**
     * Export attendees to CSV with filtering
     */
    public function export(Request $request)
    {
        // Build query with optional filters
        $query = Attendee::query();

        // Apply filters if provided (reusing the filtering logic from index method)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%' . $request->input('phone') . '%');
        }

        if ($request->filled('registration_date')) {
            $query->whereDate('created_at', $request->input('registration_date'));
        }

        // Get all attendees based on filters
        $attendees = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attendees.csv"',
        ];

        $callback = function () use ($attendees) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Created At']);

            // Add attendee data
            foreach ($attendees as $attendee) {
                fputcsv($file, [
                    $attendee->id,
                    $attendee->first_name,
                    $attendee->last_name,
                    $attendee->email,
                    $attendee->phone,
                    $attendee->created_at
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Download CSV template
     */
    public function template()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attendees_template.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // Add headers that match expected input format
            fputcsv($file, ['First Name', 'Last Name', 'Email', 'Phone']);

            // Add sample row as an example
            fputcsv($file, ['John', 'Doe', 'john.doe@example.com', '555-123-4567']);

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Delete multiple selected attendees
     */
    public function destroySelected(Request $request)
    {
        $request->validate([
            'selected_attendees' => 'required|array',
            'selected_attendees.*' => 'exists:attendees,id',
        ]);

        Attendee::whereIn('id', $request->selected_attendees)->delete();

        return redirect()->route('attendees.index')->with('success', 'Selected attendees deleted successfully.');
    }

    public function resetPassword(): View
    {
        return view('auth.new_user.reset-password');
    }

    public function sendLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
