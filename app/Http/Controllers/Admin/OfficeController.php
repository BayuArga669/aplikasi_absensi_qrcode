<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OfficeLocation;

class OfficeController extends Controller
{
    public function index()
    {
        $offices = OfficeLocation::all();
        return view('admin.offices.index', compact('offices'));
    }

    public function create()
    {
        return view('admin.offices.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:1',
            'check_in_deadline' => 'required|date_format:H:i',
            'check_out_deadline' => 'required|date_format:H:i',
            'is_active' => 'boolean'
        ]);

        OfficeLocation::create([
            'name' => $request->name,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius' => $request->radius,
            'check_in_deadline' => $request->check_in_deadline,
            'check_out_deadline' => $request->check_out_deadline,
            'is_active' => $request->boolean('is_active', true) // Default to true if not provided
        ]);

        return redirect()->route('admin.offices.index')->with('success', 'Office location created successfully.');
    }

    public function edit($id)
    {
        $office = OfficeLocation::findOrFail($id);
        return view('admin.offices.edit', compact('office'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:1',
            'check_in_deadline' => 'required|date_format:H:i',
            'check_out_deadline' => 'required|date_format:H:i',
            'is_active' => 'boolean'
        ]);

        $office = OfficeLocation::findOrFail($id);
        
        $office->update([
            'name' => $request->name,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius' => $request->radius,
            'check_in_deadline' => $request->check_in_deadline,
            'check_out_deadline' => $request->check_out_deadline,
            'is_active' => $request->boolean('is_active', true) // Default to true if not provided
        ]);

        return redirect()->route('admin.offices.index')->with('success', 'Office location updated successfully.');
    }

    public function destroy($id)
    {
        $office = OfficeLocation::findOrFail($id);
        
        try {
            $office->delete();
            return redirect()->route('admin.offices.index')->with('success', 'Office location deleted successfully.');
        } catch (\Exception $e) {
            // Handle foreign key constraint violations or other database errors
            if ($e->getCode() == 23000) {
                return redirect()->route('admin.offices.index')->with('error', 'Cannot delete office location because it is being used by QR codes or other records.');
            }
            return redirect()->route('admin.offices.index')->with('error', 'An error occurred while deleting the office location.');
        }
    }
}