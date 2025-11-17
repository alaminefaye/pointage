<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Employee::with('department');
        
        // Filtre par nom, email ou code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }
        
        // Filtre par département
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        
        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        $employees = $query->orderBy('first_name')->paginate(20)->appends($request->query());
        $departments = \App\Models\Department::all();
        return view('employees.index', compact('employees', 'departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::all();
        return view('employees.create', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees',
            'phone' => 'nullable|string',
            'password' => 'required|string|min:6',
            'department_id' => 'required|exists:departments,id',
            'position' => 'required|string|max:255',
            'standard_hours_per_day' => 'required|integer|min:1|max:24',
            'overtime_threshold_hours' => 'nullable|numeric|min:0|max:100',
        ]);

        $validated['employee_code'] = 'EMP' . Str::upper(Str::random(6));
        $validated['password'] = Hash::make($validated['password']);
        // Gérer le checkbox is_active (si non coché, il n'est pas dans la requête)
        $validated['is_active'] = $request->has('is_active') ? true : false;

        Employee::create($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Employé créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        $employee->load('department', 'attendanceRecords', 'restDays');
        return view('employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        $departments = Department::all();
        return view('employees.edit', compact('employee', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $employee->id,
            'phone' => 'nullable|string',
            'password' => 'nullable|string|min:6',
            'department_id' => 'required|exists:departments,id',
            'position' => 'required|string|max:255',
            'standard_hours_per_day' => 'required|integer|min:1|max:24',
            'overtime_threshold_hours' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Gérer le checkbox is_active (si non coché, il n'est pas dans la requête)
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $employee->update($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Employé mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employé supprimé avec succès.');
    }
}
