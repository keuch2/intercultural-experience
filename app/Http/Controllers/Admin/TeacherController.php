<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherValidation;
use App\Models\JobFairEvent;
use App\Models\JobFairRegistration;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TeacherController extends Controller
{
    /**
     * Display Teachers dashboard.
     */
    public function dashboard()
    {
        $stats = [
            'total_teachers' => TeacherValidation::count(),
            'mec_approved' => TeacherValidation::mecApproved()->count(),
            'validated_teachers' => TeacherValidation::validated()->count(),
            'job_fair_registered' => TeacherValidation::jobFairRegistered()->count(),
            'active_schools' => School::active()->count(),
            'available_positions' => School::active()->sum('positions_available'),
            'successful_placements' => JobFairRegistration::successful()->count(),
            'upcoming_job_fairs' => JobFairEvent::upcoming()->count(),
        ];

        // Recent validations
        $recentValidations = TeacherValidation::with('user')
            ->latest()
            ->take(10)
            ->get();

        // Upcoming job fairs
        $upcomingJobFairs = JobFairEvent::upcoming()
            ->orderBy('event_date')
            ->take(5)
            ->get();

        // Top schools
        $topSchools = School::topRated()
            ->withPositions()
            ->take(5)
            ->get();

        // Teachers by status
        $teachersByStatus = TeacherValidation::select('validation_status', DB::raw('count(*) as count'))
            ->groupBy('validation_status')
            ->pluck('count', 'validation_status');

        // Monthly placements
        $monthlyPlacements = JobFairRegistration::successful()
            ->where('placement_date', '>=', now()->subMonths(6))
            ->select(
                DB::raw('DATE_FORMAT(placement_date, "%Y-%m") as month'),
                DB::raw('count(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('admin.teachers.dashboard', compact(
            'stats',
            'recentValidations',
            'upcomingJobFairs',
            'topSchools',
            'teachersByStatus',
            'monthlyPlacements'
        ));
    }

    /**
     * Display list of teacher validations.
     */
    public function validations(Request $request)
    {
        $query = TeacherValidation::with(['user', 'application']);

        // Filters
        if ($request->filled('status')) {
            $query->where('validation_status', $request->status);
        }

        if ($request->filled('mec_status')) {
            $query->where('mec_status', $request->mec_status);
        }

        if ($request->filled('job_fair')) {
            $query->where('registered_for_job_fair', $request->boolean('job_fair'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('university_name', 'like', "%{$search}%")
              ->orWhere('mec_registration_number', 'like', "%{$search}%");
        }

        $validations = $query->latest()->paginate(20);

        return view('admin.teachers.validations', compact('validations'));
    }

    /**
     * Show teacher validation details.
     */
    public function showValidation($id)
    {
        $validation = TeacherValidation::with([
            'user',
            'application',
            'validator',
            'certifications',
            'jobFairRegistrations.jobFairEvent'
        ])->findOrFail($id);

        return view('admin.teachers.validation-show', compact('validation'));
    }

    /**
     * Validate MEC status.
     */
    public function validateMEC(Request $request, $id)
    {
        $validation = TeacherValidation::findOrFail($id);

        $request->validate([
            'mec_status' => 'required|in:approved,rejected,pending',
            'mec_registration_number' => 'required_if:mec_status,approved',
            'mec_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $data = [
            'mec_status' => $request->mec_status,
            'mec_registration_number' => $request->mec_registration_number,
        ];

        if ($request->mec_status === 'approved') {
            $data['has_mec_validation'] = true;
            $data['mec_validation_date'] = now();
        }

        if ($request->hasFile('mec_certificate')) {
            $path = $request->file('mec_certificate')->store('mec-certificates', 'public');
            $data['mec_certificate_path'] = $path;
        }

        $validation->update($data);

        return back()->with('success', 'MEC validation updated successfully.');
    }

    /**
     * Validate teacher credentials.
     */
    public function validateTeacher(Request $request, $id)
    {
        $validation = TeacherValidation::findOrFail($id);

        $request->validate([
            'action' => 'required|in:approve,reject',
            'rejection_reasons' => 'required_if:action,reject',
            'diploma_apostilled' => 'boolean',
            'transcript_apostilled' => 'boolean',
            'has_criminal_record' => 'boolean',
            'has_child_abuse_clearance' => 'boolean',
        ]);

        DB::transaction(function () use ($request, $validation) {
            if ($request->action === 'approve') {
                $validation->update([
                    'validation_status' => 'approved',
                    'diploma_apostilled' => $request->boolean('diploma_apostilled'),
                    'transcript_apostilled' => $request->boolean('transcript_apostilled'),
                    'has_criminal_record' => $request->boolean('has_criminal_record'),
                    'has_child_abuse_clearance' => $request->boolean('has_child_abuse_clearance'),
                    'validated_by' => auth()->id(),
                    'validation_completed_at' => now(),
                ]);

                // Update application status if exists
                if ($validation->application) {
                    $validation->application->update(['status' => 'approved']);
                }
            } else {
                $validation->update([
                    'validation_status' => 'rejected',
                    'rejection_reasons' => $request->rejection_reasons,
                    'validated_by' => auth()->id(),
                    'validation_completed_at' => now(),
                ]);

                // Update application status if exists
                if ($validation->application) {
                    $validation->application->update(['status' => 'rejected']);
                }
            }
        });

        return redirect()->route('admin.teachers.validations')
            ->with('success', 'Teacher validation ' . $request->action . 'd successfully.');
    }

    /**
     * Display list of job fairs.
     */
    public function jobFairs(Request $request)
    {
        $query = JobFairEvent::query();

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('event_type', $request->type);
        }

        if ($request->filled('upcoming')) {
            $query->upcoming();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('event_name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('venue_name', 'like', "%{$search}%");
            });
        }

        $jobFairs = $query->orderBy('event_date', 'desc')->paginate(20);

        return view('admin.teachers.job-fairs', compact('jobFairs'));
    }

    /**
     * Show job fair details.
     */
    public function showJobFair($id)
    {
        $jobFair = JobFairEvent::with([
            'teacherRegistrations.user',
            'schoolRegistrations.school'
        ])->findOrFail($id);

        $stats = [
            'teachers_registered' => $jobFair->teacherRegistrations()->count(),
            'teachers_confirmed' => $jobFair->teacherRegistrations()->confirmed()->count(),
            'schools_registered' => $jobFair->schoolRegistrations()->count(),
            'schools_confirmed' => $jobFair->schoolRegistrations()->confirmed()->count(),
            'successful_placements' => $jobFair->registrations()->successful()->count(),
        ];

        return view('admin.teachers.job-fair-show', compact('jobFair', 'stats'));
    }

    /**
     * Create new job fair.
     */
    public function createJobFair()
    {
        return view('admin.teachers.job-fair-create');
    }

    /**
     * Store new job fair.
     */
    public function storeJobFair(Request $request)
    {
        $validated = $request->validate([
            'event_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date|after:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'event_type' => 'required|in:virtual,presencial,hybrid',
            'venue_name' => 'required_if:event_type,presencial,hybrid|nullable|string|max:255',
            'venue_address' => 'required_if:event_type,presencial,hybrid|nullable|string|max:255',
            'city' => 'required_if:event_type,presencial,hybrid|nullable|string|max:100',
            'state' => 'required_if:event_type,presencial,hybrid|nullable|string|size:2',
            'virtual_platform' => 'required_if:event_type,virtual,hybrid|nullable|string|max:100',
            'meeting_link' => 'required_if:event_type,virtual,hybrid|nullable|url',
            'max_participants' => 'nullable|integer|min:1',
            'max_schools' => 'nullable|integer|min:1',
            'registration_opens' => 'required|date|before:event_date',
            'registration_closes' => 'required|date|after:registration_opens|before:event_date',
            'requires_mec_validation' => 'boolean',
            'requires_payment' => 'boolean',
            'registration_fee' => 'required_if:requires_payment,true|nullable|numeric|min:0',
            'required_documents' => 'nullable|array',
        ]);

        $jobFair = JobFairEvent::create($validated);

        return redirect()->route('admin.teachers.job-fair.show', $jobFair)
            ->with('success', 'Job Fair created successfully.');
    }

    /**
     * Open job fair registration.
     */
    public function openRegistration($id)
    {
        $jobFair = JobFairEvent::findOrFail($id);
        $jobFair->update(['status' => 'registration_open']);

        return back()->with('success', 'Registration opened successfully.');
    }

    /**
     * Display list of schools.
     */
    public function schools(Request $request)
    {
        $query = School::query();

        // Filters
        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        if ($request->filled('type')) {
            $query->where('school_type', $request->type);
        }

        if ($request->filled('verified')) {
            $query->where('is_verified', $request->boolean('verified'));
        }

        if ($request->filled('positions')) {
            $query->withPositions();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('school_name', 'like', "%{$search}%")
                  ->orWhere('district_name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $schools = $query->latest()->paginate(20);

        $states = School::distinct()->pluck('state')->sort();

        return view('admin.teachers.schools', compact('schools', 'states'));
    }

    /**
     * Show school details.
     */
    public function showSchool($id)
    {
        $school = School::with(['jobFairRegistrations', 'placedTeachers.user'])
            ->findOrFail($id);

        $stats = [
            'teachers_placed' => $school->placedTeachers()->count(),
            'job_fairs_participated' => $school->jobFairRegistrations()->count(),
            'current_openings' => $school->positions_available,
        ];

        return view('admin.teachers.school-show', compact('school', 'stats'));
    }

    /**
     * Create new school.
     */
    public function createSchool()
    {
        return view('admin.teachers.school-create');
    }

    /**
     * Store new school.
     */
    public function storeSchool(Request $request)
    {
        $validated = $request->validate([
            'school_name' => 'required|string|max:255',
            'school_type' => 'required|in:public,private,charter,religious,international',
            'district_name' => 'nullable|string|max:255',
            'school_code' => 'nullable|string|max:50',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|size:2',
            'zip_code' => 'required|string|max:10',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'website' => 'nullable|url|max:255',
            'principal_name' => 'required|string|max:255',
            'principal_email' => 'required|email|max:255',
            'hr_contact_name' => 'nullable|string|max:255',
            'hr_contact_email' => 'nullable|email|max:255',
            'hr_contact_phone' => 'nullable|string|max:20',
            'grade_levels' => 'nullable|array',
            'total_students' => 'nullable|integer|min:1',
            'total_teachers' => 'nullable|integer|min:1',
            'subjects_needed' => 'nullable|array',
            'positions_available' => 'nullable|integer|min:0',
            'minimum_experience_years' => 'nullable|integer|min:0',
            'sponsors_visa' => 'boolean',
            'provides_housing_assistance' => 'boolean',
            'housing_details' => 'nullable|string',
            'salary_range_min' => 'nullable|numeric|min:0',
            'salary_range_max' => 'nullable|numeric|min:0|gte:salary_range_min',
            'benefits_offered' => 'nullable|array',
        ]);

        // Calculate student-teacher ratio
        if ($validated['total_students'] && $validated['total_teachers']) {
            $validated['student_teacher_ratio'] = round($validated['total_students'] / $validated['total_teachers'], 2);
        }

        $school = School::create($validated);

        return redirect()->route('admin.teachers.school.show', $school)
            ->with('success', 'School created successfully.');
    }

    /**
     * Verify a school.
     */
    public function verifySchool($id)
    {
        $school = School::findOrFail($id);
        $school->verify();

        return back()->with('success', 'School verified successfully.');
    }

    /**
     * Generate teacher-school matching.
     */
    public function matching(Request $request)
    {
        // Get eligible teachers
        $teachers = TeacherValidation::validated()
            ->mecApproved()
            ->with('user')
            ->get();

        // Get schools with positions
        $schools = School::active()
            ->verified()
            ->withPositions()
            ->get();

        // Calculate matches
        $matches = [];
        foreach ($teachers as $teacher) {
            foreach ($schools as $school) {
                $teacherScore = $teacher->calculateSchoolMatchScore($school);
                $schoolScore = $school->calculateTeacherMatchScore($teacher);
                $avgScore = ($teacherScore + $schoolScore) / 2;
                
                if ($avgScore > 50) { // Minimum threshold
                    $matches[] = [
                        'teacher' => $teacher,
                        'school' => $school,
                        'score' => $avgScore,
                        'teacher_score' => $teacherScore,
                        'school_score' => $schoolScore,
                    ];
                }
            }
        }

        // Sort by score
        usort($matches, function ($a, $b) {
            return $b['score'] - $a['score'];
        });

        return view('admin.teachers.matching', compact('matches', 'teachers', 'schools'));
    }

    /**
     * Register teacher for job fair.
     */
    public function registerForJobFair(Request $request, $teacherId)
    {
        $validation = TeacherValidation::findOrFail($teacherId);
        
        $request->validate([
            'job_fair_id' => 'required|exists:job_fair_events,id',
            'preferred_schools' => 'nullable|array',
            'interview_slots' => 'nullable|array',
        ]);

        $jobFair = JobFairEvent::findOrFail($request->job_fair_id);

        if (!$jobFair->isRegistrationOpen()) {
            return back()->with('error', 'Registration is not open for this job fair.');
        }

        if (!$validation->isEligibleForJobFair()) {
            return back()->with('error', 'Teacher is not eligible for job fair registration.');
        }

        $registration = $jobFair->registerParticipant(
            $validation->user_id,
            null,
            'teacher'
        );

        if ($registration) {
            $registration->update([
                'preferred_schools' => $request->preferred_schools,
                'interview_slots' => $request->interview_slots,
            ]);

            $validation->update([
                'registered_for_job_fair' => true,
                'job_fair_date' => $jobFair->event_date,
                'job_fair_status' => 'registered',
            ]);

            return back()->with('success', 'Teacher registered for job fair successfully.');
        }

        return back()->with('error', 'Failed to register teacher for job fair.');
    }
}
