<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SectionController extends Controller
{
    public function index(): View
    {
        return view('sections.index', ['sections' => Section::with('program')->latest()->get()]);
    }

    public function create(): View
    {
        return view('sections.create', ['programs' => Program::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
            'name' => ['required', 'string', 'max:255'],
            'year_level' => ['required', 'string', 'max:50'],
            'academic_year' => ['required', 'string', 'max:20'],
            'semester' => ['required', 'string', 'max:20'],
            'status' => ['required', 'in:Active,Inactive'],
        ]);

        Section::create($validated);

        return redirect()->route('sections.index')->with('success', 'Section created successfully.');
    }

    public function show(Section $section): View
    {
        return view('sections.show', compact('section'));
    }

    public function edit(Section $section): View
    {
        return view('sections.edit', [
            'section' => $section,
            'programs' => Program::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Section $section): RedirectResponse
    {
        $validated = $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
            'name' => ['required', 'string', 'max:255'],
            'year_level' => ['required', 'string', 'max:50'],
            'academic_year' => ['required', 'string', 'max:20'],
            'semester' => ['required', 'string', 'max:20'],
            'status' => ['required', 'in:Active,Inactive'],
        ]);

        $section->update($validated);

        return redirect()->route('sections.index')->with('success', 'Section updated successfully.');
    }

    public function destroy(Section $section): RedirectResponse
    {
        $section->delete();

        return redirect()->route('sections.index')->with('success', 'Section deleted successfully.');
    }
}
