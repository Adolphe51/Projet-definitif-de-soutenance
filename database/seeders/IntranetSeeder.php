<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Intranet\Student;
use App\Models\Intranet\Course;
use App\Models\Intranet\Enrollment;
use App\Models\Intranet\Attendance;
use App\Models\Intranet\Message;
use App\Models\Intranet\Resource;
use App\Models\User;
use Faker\Factory as Faker;
use Carbon\Carbon;

class IntranetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('fr_FR');

        // Créer des étudiants fictifs
        $students = [];
        for ($i = 0; $i < 100; $i++) {
            $students[] = Student::create([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'student_id' => 'STU' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'email' => $faker->unique()->email,
                'phone' => $faker->phoneNumber,
                'date_of_birth' => $faker->dateTimeBetween('-25 years', '-18 years'),
                'address' => $faker->address,
                'status' => $faker->randomElement(['active', 'inactive', 'graduated']),
            ]);
        }

        // Créer des cours fictifs
        $departments = ['Informatique', 'Mathématiques', 'Physique', 'Chimie', 'Biologie', 'Lettres', 'Histoire', 'Économie'];
        $courses = [];
        for ($i = 0; $i < 20; $i++) {
            $courses[] = Course::create([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'course_code' => 'C' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'title' => $faker->sentence(3),
                'description' => $faker->paragraph,
                'department' => $faker->randomElement($departments),
                'credits' => $faker->numberBetween(2, 6),
                'semester' => $faker->randomElement(['S1', 'S2', 'S3', 'S4', 'S5', 'S6']),
                'max_students' => $faker->numberBetween(20, 50),
                'status' => 'active',
            ]);
        }

        // Créer des inscriptions
        foreach ($students as $student) {
            $numEnrollments = $faker->numberBetween(3, 6);
            $selectedCourses = $faker->randomElements($courses, $numEnrollments);

            foreach ($selectedCourses as $course) {
                Enrollment::create([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                    'semester' => $course->semester,
                    'enrollment_date' => $faker->dateTimeBetween('-1 year', 'now'),
                    'grade' => $faker->optional(0.7)->randomFloat(2, 0, 20),
                    'final_score' => $faker->optional(0.8)->randomFloat(2, 0, 20),
                    'status' => $faker->randomElement(['enrolled', 'completed', 'dropped']),
                ]);
            }
        }

        // Créer des présences
        $enrollments = Enrollment::all();
        foreach ($enrollments as $enrollment) {
            $startDate = Carbon::parse($enrollment->enrollment_date);
            $endDate = $startDate->copy()->addMonths(4);

            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                if ($currentDate->isWeekday()) {
                    Attendance::create([
                        'id' => (string) \Illuminate\Support\Str::uuid(),
                        'enrollment_id' => $enrollment->id,
                        'lecture_date' => $currentDate->toDateString(),
                        'status' => $faker->randomElement(['present', 'absent', 'late']),
                        'notes' => $faker->optional(0.2)->sentence,
                    ]);
                }
                $currentDate->addDay();
            }
        }

        // Créer des messages
        for ($i = 0; $i < 200; $i++) {
            $sender = $faker->randomElement($students);
            $recipient = $faker->randomElement($students);

            Message::create([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'sender_id' => $sender->id,
                'recipient_id' => $recipient->id,
                'subject' => $faker->sentence,
                'body' => $faker->paragraphs(2, true),
                'is_read' => $faker->boolean(70),
            ]);
        }

        // Créer des ressources
        $users = User::all(); // Utiliser les utilisateurs existants pour uploaded_by
        foreach ($courses as $course) {
            $numResources = $faker->numberBetween(2, 5);
            for ($i = 0; $i < $numResources; $i++) {
                Resource::create([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'course_id' => $course->id,
                    'title' => $faker->sentence(4),
                    'file_path' => '/storage/intranet/' . $faker->uuid . '.' . $faker->randomElement(['pdf', 'doc', 'ppt', 'xls']),
                    'file_type' => $faker->randomElement(['pdf', 'doc', 'ppt', 'xls']),
                    'uploaded_by' => $users->random()->id,
                    'uploaded_at' => $faker->dateTimeBetween('-6 months', 'now'),
                    'access_count' => $faker->numberBetween(0, 100),
                ]);
            }
        }
    }
}
