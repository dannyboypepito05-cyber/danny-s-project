<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function index(): View
    {
        return view('programs.index', ['programs' => Program::with('department')->latest()->get()]);
    }

    public function create(): View
    {
        return view('programs.create', ['departments' => Department::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:30', 'unique:programs,code'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:Active,Inactive'],
        ]);

        Program::create($validated);

        return redirect()->route('programs.index')->with('success', 'Program created successfully.');
    }

    public function show(Program $program): View
    {
        return view('programs.show', compact('program'));
    }

    public function edit(Program $program): View
    {
        return view('programs.edit', [
            'program' => $program,
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Program $program): RedirectResponse
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:30', 'unique:programs,code,'.$program->id],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:Active,Inactive'],
        ]);

        $program->update($validated);

        return redirect()->route('programs.index')->with('success', 'Program updated successfully.');
    }

    public function destroy(Program $program): RedirectResponse
    {
        $program->delete();

        return redirect()->route('programs.index')->with('success', 'Program deleted successfully.');
    }
}
