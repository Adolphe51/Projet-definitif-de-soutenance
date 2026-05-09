<?php

namespace Tests\Feature\CyberGuard;

use App\Models\Intranet\Course;
use App\Models\Intranet\Message;
use App\Models\Intranet\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntranetVulnerabilitiesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_sql_command_creates_demo_student_and_message(): void
    {
        $this->artisan('intranet:vulnerabilities sql')
            ->assertExitCode(0);

        $student = Student::where('student_id', 'USR999')->first();

        $this->assertNotNull($student);
        $this->assertNotNull($student->id);
        $this->assertDatabaseHas('intranet_messages', [
            'sender_id' => $student->id,
            'recipient_id' => $student->id,
            'subject' => 'Test UNION SELECT',
        ]);
    }

    public function test_xss_command_creates_demo_course_with_uuid(): void
    {
        $this->artisan('intranet:vulnerabilities xss')
            ->assertExitCode(0);

        $course = Course::where('course_code', 'MAL001')->first();

        $this->assertNotNull($course);
        $this->assertNotNull($course->id);
        $this->assertStringContainsString('<script>', $course->title);
    }

    public function test_bruteforce_command_bootstraps_demo_students_when_database_is_empty(): void
    {
        $this->artisan('intranet:vulnerabilities bruteforce')
            ->assertExitCode(0);

        $this->assertGreaterThanOrEqual(2, Student::count());
        $this->assertGreaterThanOrEqual(24, Message::where('subject', 'like', 'BRUTEFORCE-DEMO-%')->count());
    }
}
