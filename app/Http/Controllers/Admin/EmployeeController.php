<?php

// app/Http/Controllers/Admin/EmployeeController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = User::with('superior')
            ->where('role', '!=', 'admin')
            ->paginate(20);

        return view('admin.employees.index', compact('employees'));
    }

    public function create()
    {
        $superiors = User::where('role', 'superior')
            ->where('is_active', true)
            ->get();

        return view('admin.employees.create', compact('superiors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'employee_id' => 'required|unique:users,employee_id',
            'role' => 'required|in:employee,superior',
            'position' => 'required|string',
            'department' => 'required|string',
            'superior_id' => 'nullable|exists:users,id',
            'phone' => 'nullable|string',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'employee_id' => $request->employee_id,
            'role' => $request->role,
            'position' => $request->position,
            'department' => $request->department,
            'superior_id' => $request->superior_id,
            'phone' => $request->phone,
            'is_active' => true,
        ]);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee created successfully');
    }

    public function edit($id)
    {
        $employee = User::findOrFail($id);
        $superiors = User::where('role', 'superior')
            ->where('is_active', true)
            ->where('id', '!=', $id)
            ->get();

        return view('admin.employees.edit', compact('employee', 'superiors'));
    }

    public function update(Request $request, $id)
    {
        $employee = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'employee_id' => 'required|unique:users,employee_id,' . $id,
            'role' => 'required|in:employee,superior',
            'position' => 'required|string',
            'department' => 'required|string',
            'superior_id' => 'nullable|exists:users,id',
            'phone' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $employee->update($request->except('password'));

        if ($request->filled('password')) {
            $employee->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee updated successfully');
    }

    public function destroy($id)
    {
        $employee = User::findOrFail($id);
        $employee->update(['is_active' => false]);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee deactivated successfully');
    }
}
