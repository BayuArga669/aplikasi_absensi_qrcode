<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = User::where('role', 'employee')
            ->orWhere('role', 'superior')  // Include superiors as employees too
            ->paginate(20);
            
        $superiors = User::where('role', 'superior')->get();
        
        return view('admin.employees.index', compact('employees', 'superiors'));
    }

    public function create()
    {
        $superiors = User::where('role', 'superior')->get();
        return view('admin.employees.create', compact('superiors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:admin,superior,employee',
            'position' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'superior_id' => 'nullable|exists:users,id',
            'address' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'position' => $request->position,
            'phone' => $request->phone,
            'superior_id' => $request->superior_id,
            'address' => $request->address,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('admin.employees.index')->with('success', 'Employee created successfully.');
    }

    public function edit($id)
    {
        $employee = User::findOrFail($id);
        $superiors = User::where('role', 'superior')->get();
        
        return view('admin.employees.edit', compact('employee', 'superiors'));
    }

    public function update(Request $request, $id)
    {
        $employee = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$employee->id,
            'password' => 'nullable|min:8|confirmed',
            'role' => 'required|in:admin,superior,employee',
            'position' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'superior_id' => 'nullable|exists:users,id',
            'address' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'position' => $request->position,
            'phone' => $request->phone,
            'superior_id' => $request->superior_id,
            'address' => $request->address,
            'is_active' => $request->is_active ?? true,
        ];

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $employee->update($data);

        return redirect()->route('admin.employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy($id)
    {
        $employee = User::findOrFail($id);
        $employee->delete();

        return redirect()->route('admin.employees.index')->with('success', 'Employee deleted successfully.');
    }
}