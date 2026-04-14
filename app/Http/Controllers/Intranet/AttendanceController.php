<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use App\Models\Intranet\Attendance;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendances = Attendance::with(['enrollment.student', 'enrollment.course'])
            ->orderBy('lecture_date', 'desc')
            ->paginate(20);

        return view('intranet.attendances.index', compact('attendances'));
    }
}
