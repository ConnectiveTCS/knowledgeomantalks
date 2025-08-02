<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactController extends Controller
{
    //
    public function index()
    {
        return view('contact.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string|max:5000',
        ]);

        // Save the contact message to the database
        \App\Models\Contact::create($request->all());

        return redirect()->back()->with('success', 'Your message has been sent successfully!');
    }
    public function show($id)
    {
        $contact = \App\Models\Contact::findOrFail($id);
        return view('contact.show', compact('contact'));
    }
    public function edit($id)
    {
        $contact = \App\Models\Contact::findOrFail($id);
        return view('contact.edit', compact('contact'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string|max:5000',
        ]);

        $contact = \App\Models\Contact::findOrFail($id);
        $contact->update($request->all());

        return redirect()->route('contacts.index')->with('success', 'Contact updated successfully!');
    }
    public function destroy($id)
    {
        $contact = \App\Models\Contact::findOrFail($id);
        $contact->delete();

        return redirect()->route('contacts.index')->with('success', 'Contact deleted successfully!');
    }
    public function list()
    {
        $contacts = \App\Models\Contact::all();
        return view('contact.list', compact('contacts'));
    }
    public function search(Request $request)
    {
        $query = $request->input('query');
        $contacts = \App\Models\Contact::where('first_name', 'LIKE', "%{$query}%")
            ->orWhere('last_name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->orWhere('phone', 'LIKE', "%{$query}%")
            ->orWhere('message', 'LIKE', "%{$query}%")
            ->get();

        return view('contact.list', compact('contacts'));
    }

}
