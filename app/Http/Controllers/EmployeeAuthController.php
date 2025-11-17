<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class EmployeeAuthController extends Controller
{
    /**
     * Show employee login form.
     */
    public function showLoginForm()
    {
        return view('employee-auth.login');
    }

    /**
     * Handle employee login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $employee = Employee::where('email', $request->email)
            ->where('is_active', true)
            ->first();

        if (!$employee || !Hash::check($request->password, $employee->password)) {
            throw ValidationException::withMessages([
                'email' => __('Les identifiants fournis sont incorrects.'),
            ]);
        }

        // Store employee in session
        session(['employee_id' => $employee->id, 'employee_name' => $employee->full_name]);

        return redirect()->route('employee.dashboard')
            ->with('success', 'Connexion réussie.');
    }

    /**
     * Handle employee logout.
     */
    public function logout(Request $request)
    {
        $request->session()->forget(['employee_id', 'employee_name']);
        
        return redirect()->route('employee.login')
            ->with('success', 'Déconnexion réussie.');
    }

    /**
     * Show employee dashboard.
     */
    public function dashboard()
    {
        $employeeId = session('employee_id');
        
        if (!$employeeId) {
            return redirect()->route('employee.login');
        }

        $employee = Employee::findOrFail($employeeId);
        $today = \Carbon\Carbon::today();
        
        $todayAttendance = \App\Models\AttendanceRecord::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        return view('employee-auth.dashboard', compact('employee', 'todayAttendance'));
    }

    /**
     * Show employee attendance history.
     */
    public function attendanceHistory(Request $request)
    {
        $employeeId = session('employee_id');
        
        if (!$employeeId) {
            return redirect()->route('employee.login');
        }

        $employee = Employee::findOrFail($employeeId);
        
        $query = \App\Models\AttendanceRecord::where('employee_id', $employee->id)
            ->with('site')
            ->orderBy('date', 'desc');

        // Filtres optionnels
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $records = $query->paginate(20);

        return view('employee-auth.attendance-history', compact('employee', 'records'));
    }
}
