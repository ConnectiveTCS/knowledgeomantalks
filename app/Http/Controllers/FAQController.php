<?php

namespace App\Http\Controllers;

use \App\Models\FAQ;
use Illuminate\Http\Request;

class FAQController extends Controller
{
    //
    public function index()
    {
        // Logic to retrieve and display FAQs
        $faqs = FAQ::where('is_active', true)->get();
        return view('faqs.index', compact('faqs'));
    }
    public function show($id)
    {
        // Logic to display a single FAQ
        $faq = \App\Models\FAQ::findOrFail($id);
        return view('faqs.show', compact('faq'));
    }   
    public function create()
    {
        // Logic to show the form for creating a new FAQ
        return view('faqs.create');
    }
    public function store(Request $request)
    {
        // Logic to store a new FAQ
        $request->validate([
            'question' => 'required|string|max:255|unique:f_a_q_s,question',
            'answer' => 'required|string',
            'is_active' => 'boolean',
        ]);
        \App\Models\FAQ::create($request->all());
        return redirect()->route('faqs.index')->with('success', 'FAQ created successfully.');
    }
    public function edit($id)
    {
        // Logic to show the form for editing an existing FAQ
        $faq = \App\Models\FAQ::findOrFail($id);
        return view('faqs.edit', compact('faq'));
    }
    public function update(Request $request, $id)
    {
        // Logic to update an existing FAQ
        $request->validate([
            'question' => 'required|string|max:255|unique:f_a_q_s,question,' . $id,
            'answer' => 'required|string',
            'is_active' => 'boolean',
        ]);
        $faq = \App\Models\FAQ::findOrFail($id);
        $faq->update($request->all());
        return redirect()->route('faqs.index')->with('success', 'FAQ updated successfully.');
    }
    public function destroy($id)
    {
        // Logic to delete an existing FAQ
        $faq = \App\Models\FAQ::findOrFail($id);
        $faq->delete();
        return redirect()->route('faqs.index')->with('success', 'FAQ deleted successfully.');
    }
    public function toggleActive($id)
    {
        // Logic to toggle the active status of an FAQ
        $faq = \App\Models\FAQ::findOrFail($id);
        $faq->is_active = !$faq->is_active;
        $faq->save();
        return redirect()->route('faqs.index')->with('success', 'FAQ status updated successfully.');
    }
}
