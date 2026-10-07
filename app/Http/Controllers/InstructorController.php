<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstructorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('instructors.index', ['instructors' => Instructor::withCount('classrooms')->latest()->get()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('instructors.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Instructor::create($this->validated($request));

        return redirect()->route('instructors.index')->with('success', 'Instructor created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Instructor $instructor): View
    {
        return view('instructors.show', compact('instructor'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Instructor $instructor): View
    {
        return view('instructors.edit', compact('instructor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Instructor $instructor): RedirectResponse
    {
        $instructor->update($this->validated($request, $instructor));

        return redirect()->route('instructors.index')->with('success', 'Instructor updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Instructor $instructor): RedirectResponse
    {
        if ($instructor->classrooms()->exists()) {
            return back()->with('error', 'Reassign this instructor\'s classrooms before deleting the profile.');
        }

        $instructor->delete();

        return redirect()->route('instructors.index')->with('success', 'Instructor deleted successfully.');
    }

    private function validated(Request $request, ?Instructor $instructor = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:instructors,email,'.($instructor?->id ?? 'NULL')],
            'phone' => ['nullable', 'string', 'max:50'],
            'specialization' => ['required', 'string', 'max:255'],
            'qualification' => ['required', 'string', 'max:255'],
            'years_experience' => ['required', 'integer', 'min:0', 'max:60'],
            'quality_score' => ['required', 'numeric', 'min:0', 'max:5'],
            'status' => ['required', 'in:Active,On Leave,Inactive'],
            'bio' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
