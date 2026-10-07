<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Instructor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClassroomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('classrooms.index', ['classrooms' => Classroom::with('instructor')->latest()->get()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('classrooms.create', ['instructors' => Instructor::where('status', 'Active')->orderBy('name')->get()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Classroom::create($this->validated($request));

        return redirect()->route('classrooms.index')->with('success', 'Classroom created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Classroom $classroom): View
    {
        $classroom->load('instructor');

        return view('classrooms.show', compact('classroom'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Classroom $classroom): View
    {
        return view('classrooms.edit', ['classroom' => $classroom, 'instructors' => Instructor::where('status', 'Active')->orWhereKey($classroom->instructor_id)->orderBy('name')->get()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Classroom $classroom): RedirectResponse
    {
        $classroom->update($this->validated($request, $classroom));

        return redirect()->route('classrooms.index')->with('success', 'Classroom updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Classroom $classroom): RedirectResponse
    {
        $classroom->delete();

        return redirect()->route('classrooms.index')->with('success', 'Classroom deleted successfully.');
    }

    private function validated(Request $request, ?Classroom $classroom = null): array
    {
        return $request->validate([
            'instructor_id' => [
                'required',
                'integer',
                Rule::exists('instructors', 'id')->where(function ($query) use ($classroom) {
                    $query->where('status', 'Active');

                    if ($classroom) {
                        $query->orWhere('id', $classroom->instructor_id);
                    }
                }),
            ],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:classrooms,code,'.($classroom?->id ?? 'NULL')],
            'subject' => ['required', 'string', 'max:255'],
            'grade_level' => ['required', 'string', 'max:100'],
            'capacity' => ['required', 'integer', 'min:1', 'max:1000'],
            'schedule' => ['required', 'string', 'max:255'],
            'delivery_mode' => ['required', 'in:Physical,Online,Hybrid'],
            'status' => ['required', 'in:Active,Draft,Archived'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
