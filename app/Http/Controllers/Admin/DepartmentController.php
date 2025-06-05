<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display a listing of departments.
     */
    public function index()
    {
        $departments = Department::withCount(['users', 'bookings'])
            ->latest()
            ->paginate(5);

        return view('admin.departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new department.
     */
    public function create()
    {
        return view('admin.departments.create');
    }

    /**
     * Store a newly created department.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments',
            'description' => 'nullable|string|max:1000',
        ]);

        Department::create($validated);

        return redirect()->route('admin.departments.index')
            ->with('status', 'Department created successfully.');
    }

    /**
     * Display the specified department.
     */
    public function show(Department $department)
    {
        $department->load([
            'users' => function($query) {
                $query->latest()->take(5);
            },
            'bookings' => function($query) {
                $query->latest()->take(5);
            }
        ]);

        return view('admin.departments.show', compact('department'));
    }

    /**
     * Show the form for editing the specified department.
     */
    public function edit(Department $department)
    {
        return view('admin.departments.edit', compact('department'));
    }

    /**
     * Update the specified department.
     */
    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $department->update($validated);

        return redirect()->route('admin.departments.index')
            ->with('status', 'Department updated successfully.');
    }

    /**
     * Remove the specified department.
     */
    public function destroy(Department $department)
    {
        // Check if department has any users or bookings
        if ($department->users()->exists()) {
            return back()->with('error', 'Cannot delete department with assigned users.');
        }

        if ($department->bookings()->exists()) {
            return back()->with('error', 'Cannot delete department with existing bookings.');
        }

        $department->delete();

        return redirect()->route('admin.departments.index')
            ->with('status', 'Department deleted successfully.');
    }
} 