<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\SuperAdminInstructor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\DataTables\Superadmin\SuperAdminInstructorDataTable;

class SuperAdminInstructorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(SuperAdminInstructorDataTable $dataTable)
    {
        return $dataTable->render('superadmin.our-admin-instructors.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('superadmin.our-admin-instructors.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'instructor_image'   => 'nullable|image|mimes:jpg,jpeg,png',
            'bio'     => 'nullable|string',
            'domain'  => 'required|string|max:255',
        ]);

        // Handle image upload
        if ($request->hasFile('instructor_image')) {
            $validated['instructor_image'] = $request->file('instructor_image')->store('instructors', 'public');
        }

        SuperAdminInstructor::create($validated);

        return redirect()->route('super-admin-instructors.index')
            ->with('success', 'Instructor created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $instructor = SuperAdminInstructor::findOrFail($id);
        return view('superadmin.our-admin-instructors.edit', compact('instructor'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // dd("Coming here");
        $instructor = SuperAdminInstructor::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'instructor_image'   => 'nullable|image|mimes:jpg,jpeg,png',
            'bio'     => 'nullable|string',
            'domain'  => 'required|string|max:255',
        ]);

        // Handle new image upload
        if ($request->hasFile('instructor_image')) {
            // Delete old instructor_image if exists
            if ($instructor->instructor_image && Storage::disk('local')->exists($instructor->instructor_image)) {
                Storage::disk('local')->delete($instructor->instructor_image);
            }
            $validated['instructor_image'] = $request->file('instructor_image')->store('instructors', 'public');
        }

        $instructor->update($validated);

        return redirect()->route('super-admin-instructors.index')
            ->with('success', 'Instructor updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $instructor = SuperAdminInstructor::findOrFail($id);

        // Delete instructor_image if exists
        if ($instructor->instructor_image && Storage::disk('local')->exists($instructor->instructor_image)) {
            Storage::disk('local')->delete($instructor->instructor_image);
        }

        $instructor->delete();

        return redirect()->route('super-admin-instructors.index')
            ->with('success', 'Instructor deleted successfully.');
    }

    public function frontendIndex()
    {
        $instructors = \App\Models\SuperAdminInstructor::all();

        return view('frontend.instructors.index', compact('instructors'));
    }
}
