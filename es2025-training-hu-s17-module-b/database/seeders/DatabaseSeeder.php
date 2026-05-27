<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Mentor;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $jsonPath = base_path('public/assets/data/data.json');

        $json = file_get_contents($jsonPath);

        $data = json_decode($json);

        DB::transaction(function () use ($data) {
            $usersByEmail = [];
            $coursesByTitle = [];

            $this->seedCoreUsers($data['users'] ?? [], $usersByEmail);
            $this->seedAdminUsers();
            $this->seedMentors($data['mentors'] ?? [], $usersByEmail);
            $coursesByTitle = $this->seedCourses($data['courses'] ?? []);
            $this->seedChapterCompletions($data['chapterCompleted'] ?? [], $coursesByTitle, $usersByEmail);
        });
    }

    private function seedCoreUsers(array $users, array $usersByEmail)
    {
        foreach ($users as $userData) {
            $firstName = (string) ($userData['firstName'] ?? '');
            $lastName = (string) ($userData['lastName'] ?? '');
            $fullName = trim($firstName . ' ' . $lastName);
            $role = $this->mapUserRole((string) ($userData['role'] ?? 'learner'));
            $email = (string) ($userData['email'] ?? '');

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $fullName,
                    'password' => Hash::make(Str::random(16)),
                    'role' => $role,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'registration_date' => $this->normalizeDateTime($userData['registrationDate'] ?? null),
                    'status' => 'active',
                    'credit_balance' => 0,
                ]
            );

            $usersByEmail[$email] = $user;
        }
    }

    private function seedAdminUsers()
    {
        User::updateOrCreate(
            ['email' => 'admin@ssa.org'],
            [
                'name' => 'Zeus Helmet',
                'password' => Hash::make('skills2025admin1'),
                'role' => 'admin',
                'first_name' => 'Zeus',
                'last_name' => 'Helmet',
                'registration_date' => '2025-08-10 14:23:00',
                'status' => 'active',
                'credit_balance' => 0,
            ]
        );

        User::updateOrCreate(
            ['email' => 'alice@example.com'],
            [
                'name' => 'Alice Johnson',
                'password' => Hash::make('WtfiA?'),
                'role' => 'user',
                'first_name' => 'Alice',
                'last_name' => 'Johnson',
                'registration_date' => '2025-08-10 14:23:00',
                'status' => 'active',
                'credit_balance' => 0,
            ]
        );
    }

    private function seedMentors(array $mentors, array $usersByEmail)
    {
        foreach ($mentors as $index => $mentorData) {
            $mentorName = (string) ($mentorData['mentorName'] ?? '');
            $email = $this->mentorEmail($mentorName, $index);
            $nameParts = array_values(array_filter(explode(' ', trim($mentorName)), static fn ($part) => $part !== ''));
            $firstName = $nameParts[0] ?? $mentorName;
            $lastName = isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : '';
            $approvalStatus = $this->mapMentorApproval((string) ($mentorData['approvalStatus'] ?? 'pending'));
            $registrationDate = $this->normalizeDateTime($mentorData['registrationDate'] ?? null);

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $mentorName,
                    'password' => Hash::make(Str::random(16)),
                    'role' => 'mentor',
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'registration_date' => $registrationDate,
                    'status' => $approvalStatus === 'approved' ? 'active' : 'suspended',
                    'credit_balance' => 0,
                ]
            );

            $usersByEmail[$email] = $user;

            Mentor::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'expertise_areas' => (string) ($mentorData['expertise'] ?? ''),
                    'hourly_credit_rate' => (int) ($mentorData['hourlyCreditRate'] ?? 0),
                    'bio' => (string) ($mentorData['bio'] ?? ''),
                    'years_experience' => (int) ($mentorData['yearsExperience'] ?? 0),
                    'availability_status' => $approvalStatus === 'approved' ? 'available' : 'limited',
                    'approval_status' => $approvalStatus,
                    'approval_date' => $approvalStatus === 'approved' ? $registrationDate : null,
                    'total_sessions_completed' => 0,
                    'average_rating' => (float) ($mentorData['averageRating'] ?? 0),
                ]
            );
        }
    }

    private function seedCourses(array $courses)
    {
        $coursesByTitle = [];

        foreach ($courses as $courseData) {
            $title = (string) ($courseData['title'] ?? '');
            $chapterRows = $courseData['chapters'] ?? [];
            $totalCredits = 0;
            $chapterCount = 0;

            $course = Course::updateOrCreate(
                ['title' => $title],
                [
                    'description' => (string) ($courseData['description'] ?? ''),
                    'total_credits' => 0,
                    'difficulty_level' => (string) ($courseData['difficulty'] ?? 'beginner'),
                    'estimated_hours' => 0,
                    'status' => 'active',
                    'category' => null,
                    'created_date' => null,
                    'instructor_name' => null,
                ]
            );

            foreach ($chapterRows as $index => $chapterData) {
                $credits = (int) ($chapterData['credits'] ?? 0);
                $chapterCount++;
                $totalCredits += $credits;

                Chapter::updateOrCreate(
                    [
                        'course_id' => $course->id,
                        'title' => (string) ($chapterData['title'] ?? ''),
                    ],
                    [
                        'description' => (string) ($chapterData['description'] ?? ''),
                        'credit_reward' => $credits,
                        'chapter_order' => $index + 1,
                        'estimated_minutes' => max(15, $credits * 15),
                        'content_type' => 'video_text',
                    ]
                );
            }

            $course->update([
                'total_credits' => $totalCredits,
                'estimated_hours' => max(1, $chapterCount * 2),
            ]);

            $coursesByTitle[$title] = $course->fresh();
        }

        return $coursesByTitle;
    }

    private function seedChapterCompletions(array $completions, array $coursesByTitle, array $usersByEmail)
    {
        $enrollmentState = [];
        $creditBalances = [];

        foreach ($completions as $completionData) {
            $email = (string) ($completionData['userEmail'] ?? '');
            $courseTitle = (string) ($completionData['courseName'] ?? '');
            $chapterTitle = (string) ($completionData['chapterName'] ?? '');

            $user = $usersByEmail[$email] ?? User::where('email', $email)->first();
            $course = $coursesByTitle[$courseTitle] ?? Course::where('title', $courseTitle)->first();

            if (!$user || !$course) {
                continue;
            }

            $chapter = Chapter::query()
                ->where('course_id', $course->id)
                ->where('title', $chapterTitle)
                ->first();

            if (!$chapter) {
                continue;
            }

            $completionDate = $this->normalizeDateTime($completionData['completionDate'] ?? null);
            $creditsEarned = (int) ($completionData['creditsEarned'] ?? 0);
            $enrollmentKey = $email . '|' . $courseTitle;

            Transaction::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'amount' => $creditsEarned,
                    'transaction_type' => 'credit_earned',
                    'description' => 'Completed ' . $chapterTitle,
                    'related_entity_type' => 'chapter',
                    'related_entity_id' => $chapter->id,
                    'created_at' => $completionDate,
                ],
                [
                    'processed_by' => 'system',
                ]
            );

            $creditBalances[$user->id] = ($creditBalances[$user->id] ?? 0) + $creditsEarned;

            if (! isset($enrollmentState[$enrollmentKey])) {
                $enrollmentState[$enrollmentKey] = [
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'enrollment_date' => $this->normalizeDateTime($user->registration_date) ?? $completionDate,
                    'progress_percentage' => 0,
                    'completed_chapters' => 0,
                    'total_chapters' => $course->chapters()->count(),
                    'completion_date' => null,
                    'status' => 'enrolled',
                    'last_activity' => null,
                ];
            }

            $enrollmentState[$enrollmentKey]['completed_chapters']++;
            $enrollmentState[$enrollmentKey]['progress_percentage'] = 100;
            $enrollmentState[$enrollmentKey]['status'] = 'completed';
            $enrollmentState[$enrollmentKey]['completion_date'] = $completionDate;
            $enrollmentState[$enrollmentKey]['last_activity'] = $completionDate;
        }

        foreach ($enrollmentState as $state) {
            Enrollment::updateOrCreate(
                [
                    'user_id' => $state['user_id'],
                    'course_id' => $state['course_id'],
                ],
                $state
            );
        }

        foreach ($creditBalances as $userId => $creditBalance) {
            User::whereKey($userId)->update([
                'credit_balance' => $creditBalance,
            ]);
        }
    }

    private function mapUserRole(string $role)
    {
        return match ($role) {
            'learner' => 'user',
            'mentor' => 'mentor',
            default => 'user',
        };
    }

    private function mapMentorApproval(string $approvalStatus)
    {
        return match ($approvalStatus) {
            'approved' => 'approved',
            'unapproved' => 'pending',
            default => 'pending',
        };
    }

    private function mentorEmail(string $mentorName, int $index)
    {
        return 'mentor-' . ($index + 1) . '@ssa.org';
    }

    private function normalizeDateTime(mixed $value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        return substr((string) $value, 0, 19);
    }
}
