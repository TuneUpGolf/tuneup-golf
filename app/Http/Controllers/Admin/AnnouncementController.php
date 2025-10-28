<?php

namespace App\Http\Controllers\Admin;

use App\Models\Student;
use App\Models\Announcement;
use Illuminate\Http\Request;
use App\Services\TenantOption;
use App\Events\AnnouncementCreated;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\DataTables\AnnouncementDataTable;

class AnnouncementController extends Controller
{
    // public function __construct(protected TenantOption $tenant_option)
    // {
        
    // }
    public function index(AnnouncementDataTable $dataTable)
    {
        if (Auth::user()->can('manage-announcements')) {
        return $dataTable->render('admin.announcements.index'); 
        }
        
        abort(403, 'Unauthorized action.');
    }

    public function create()
    {
        if (!Auth::user()->can('create-announcements')) {
            abort(403, 'Unauthorized action.');
        }

        $students = Student::where('active_status',1)->get();

        return view('admin.announcements.create', compact('students'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->can('create-announcements')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'recipient_type' => 'required|in:all,specific',
            'recipient_students' => 'required_if:recipient_type,specific|array',
            'recipient_students.*' => 'exists:users,id',
            'is_active' => 'boolean'
        ]);

        // Create announcement
        $announcement = Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            // 'is_active' => $request->has('is_active'),
            // 'created_by' => Auth::id(), // Uncomment this line
        ]);

        $studentIds = [];


        // Handle recipients based on recipient_type
        if ($request->recipient_type === 'all') {
            // Send to all students
            $students = Student::get();
            foreach ($students as $student) {
                \App\Models\AnnouncementRecipient::create([
                    'announcement_id' => $announcement->id,
                    'student_id' => $student->id,
                ]);
            }
        } else {
            // Send to specific students
            foreach ($request->recipient_students as $studentId) {
                \App\Models\AnnouncementRecipient::create([
                    'announcement_id' => $announcement->id,
                    'student_id' => $studentId,
                ]);
            }
        }

        broadcast(new AnnouncementCreated($announcement, $studentIds));


        return redirect()->route('announcements.index')
            ->with('success', __('Announcement created successfully.'));
    }


    public function show(Announcement $announcement)
    {
        return view('admin.announcements.show', compact('announcement'));
    }

    public function edit(Announcement $announcement)
    {
        if (!Auth::user()->can('edit-announcements')) {
            abort(403, 'Unauthorized action.');
        }

        $students = Student::get(); // Adjust this based on your User model

        return view('admin.announcements.edit', compact('announcement', 'students'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        if (!Auth::user()->can('edit-announcements')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'recipient_type' => 'required|in:all,specific',
            'recipient_students' => 'required_if:recipient_type,specific|array',
            'recipient_students.*' => 'exists:students,id',
            'is_active' => 'boolean'
        ]);

        // Update announcement
        $announcement->update([
            'title' => $request->title,
            'content' => $request->content,
            'recipient_type' => $request->recipient_type,
            // 'is_active' => $request->has('is_active'),
        ]);

        // Update recipients if specific students are selected
        if ($request->recipient_type === 'specific' && $request->has('recipient_students')) {
            // Remove existing recipients
            $announcement->recipients()->delete();
            
            // Add new recipients
            foreach ($request->recipient_students as $studentId) {
                $announcement->recipients()->create([
                    'student_id' => $studentId
                ]);
            }
        } elseif ($request->recipient_type === 'all') {
            // Remove all specific recipients when sending to all students
            $announcement->recipients()->delete();
        }

        return redirect()->route('announcements.index')
            ->with('success', __('Announcement updated successfully.'));
    }

    /**
     * Remove the specified announcement from storage.
     */
    public function destroy(Announcement $announcement)
    {
        if (!Auth::user()->can('delete-announcements')) {
            abort(403, 'Unauthorized action.');
        }

        $announcement->delete();

        return redirect()->route('announcements.index')
            ->with('success', __('Announcement deleted successfully.'));
    }

    public function action()
    {
        return view('admin.announcements.action');

    }

    public function tenantOption()
    {
        return $this->tenant_option->TenantOption();
    }
}
