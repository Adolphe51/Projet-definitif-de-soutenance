<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use App\Models\Intranet\Enrollment;

class EnrollmentController extends Controller
{
    public function index()
    {
        $enrollments = Enrollment::with(['student', 'course'])
            ->orderBy('semester')
            ->paginate(20);

        return view('intranet.enrollments.index', compact('enrollments'));
    }
}
