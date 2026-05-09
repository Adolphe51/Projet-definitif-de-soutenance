<?php

namespace Tests\Feature\CyberGuard;

use App\Models\Intranet\Course;
use App\Models\Intranet\Enrollment;
use App\Models\Intranet\Message;
use App\Models\Intranet\Student;
use Database\Seeders\IntranetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Tests\TestCase;

class IntranetSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_refreshes_the_mini_site_dataset_with_compact_demo_data(): void
    {
        Config::set('cyberguard.mini_site.refresh_on_seed', true);

        $legacyStudent = Student::create([
            'id' => (string) Str::uuid(),
            'student_id' => 'LEGACY001',
            'first_name' => 'Legacy',
            'last_name' => 'User',
            'email' => 'legacy.user@cyberguard.demo',
            'phone' => '+225 00 00 00 00 00',
            'date_of_birth' => '1990-01-01',
            'address' => 'Ancienne fiche',
            'status' => 'active',
        ]);

        $legacyCourse = Course::create([
            'id' => (string) Str::uuid(),
            'course_code' => 'OLD-SVC',
            'title' => 'Ancien service',
            'description' => 'Donnee historique a supprimer.',
            'department' => 'Archive',
            'credits' => 1,
            'semester' => 'OLD',
            'max_students' => 5,
            'status' => 'inactive',
        ]);

        Enrollment::create([
            'id' => (string) Str::uuid(),
            'student_id' => $legacyStudent->id,
            'course_id' => $legacyCourse->id,
            'semester' => 'OLD',
            'enrollment_date' => '2026-01-10 08:00:00',
            'grade' => null,
            'final_score' => null,
            'status' => 'enrolled',
        ]);

        Message::create([
            'id' => (string) Str::uuid(),
            'sender_id' => $legacyStudent->id,
            'recipient_id' => $legacyStudent->id,
            'subject' => 'Ancien message',
            'body' => 'A supprimer pendant le refresh du mini site.',
            'is_read' => false,
        ]);

        Artisan::call('db:seed', ['--class' => IntranetSeeder::class]);

        $this->assertDatabaseMissing('intranet_students', ['student_id' => 'LEGACY001']);
        $this->assertDatabaseMissing('intranet_courses', ['course_code' => 'OLD-SVC']);

        $this->assertSame(6, Student::count());
        $this->assertSame(5, Course::count());
        $this->assertSame(6, Enrollment::count());
        $this->assertSame(6, Message::count());

        $this->assertDatabaseHas('intranet_students', [
            'student_id' => 'USR001',
            'email' => 'awa.konan@cyberguard.demo',
        ]);

        $this->assertDatabaseHas('intranet_courses', [
            'course_code' => 'SVC-IT',
            'title' => 'Catalogue IT - demandes et changements techniques',
        ]);

        $this->assertDatabaseHas('intranet_messages', [
            'subject' => 'Demo CyberGuard | Fil conducteur mini site, alerte et SOC',
        ]);
    }
}
