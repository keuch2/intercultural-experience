<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\LanguageProgram;

class LanguageProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Language Programs
        for ($i = 1; $i <= 8; $i++) {
            $user = User::create([
                'name' => "Language Student {$i}",
                'email' => "language{$i}@test.com",
                'password' => bcrypt('password'),
                'role' => 'participant',
                'email_verified_at' => now(),
            ]);

            $startDate = now()->addMonths($i <= 3 ? -1 : ($i <= 6 ? 1 : 3));
            $weeks = [12, 16, 24, 12, 20, 16, 12, 24][$i-1];
            $hoursPerWeek = [20, 25, 30, 20, 25, 20, 15, 30][$i-1];

            LanguageProgram::create([
                'user_id' => $user->id,
                'program_number' => 'LP-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'school_name' => [
                    'EC Los Angeles',
                    'Kaplan International - San Francisco',
                    'ELS Language Centers - Boston',
                    'Kings Education - New York',
                    'Embassy English - San Diego',
                    'LSI - Berkeley',
                    'Mentor Language Institute - Hollywood',
                    'Columbia West College - Los Angeles'
                ][$i-1],
                'school_city' => ['Los Angeles', 'San Francisco', 'Boston', 'New York', 'San Diego', 'Berkeley', 'Los Angeles', 'Los Angeles'][$i-1],
                'school_state' => ['CA', 'CA', 'MA', 'NY', 'CA', 'CA', 'CA', 'CA'][$i-1],
                'school_country' => 'USA',
                'school_address' => '123 Main Street',
                'school_accreditation' => ['CEA', 'ACCET', 'CEA', 'ACCET', 'CEA', 'IALC', 'none', 'ACCET'][$i-1],
                'language' => ['english', 'english', 'english', 'english', 'english', 'english', 'spanish', 'english'][$i-1],
                'current_level' => ['intermediate', 'beginner', 'intermediate', 'upper_intermediate', 'elementary', 'intermediate', 'beginner', 'advanced'][$i-1],
                'target_level' => ['advanced', 'intermediate', 'advanced', 'proficiency', 'intermediate', 'advanced', 'intermediate', 'proficiency'][$i-1],
                'program_type' => [
                    'intensive',
                    'general_language',
                    'exam_preparation',
                    'business_language',
                    'intensive',
                    'general_language',
                    'conversation_only',
                    'super_intensive'
                ][$i-1],
                'exam_type' => $i == 3 ? 'TOEFL' : ($i == 4 ? 'IELTS' : null),
                'activity_type' => null,
                'weeks_duration' => $weeks,
                'hours_per_week' => $hoursPerWeek,
                'class_size_max' => rand(10, 15),
                'start_date' => $startDate,
                'end_date' => $startDate->copy()->addWeeks($weeks),
                'tuition_fee' => rand(2500, 5000),
                'registration_fee' => 100,
                'materials_fee' => rand(50, 150),
                'exam_fee' => in_array($i, [3, 4]) ? 200 : null,
                'accommodation_type' => ['homestay', 'student_residence', 'homestay', 'apartment', 'homestay', 'student_residence', 'homestay', 'self_arranged'][$i-1],
                'accommodation_included' => $i != 8,
                'accommodation_cost_weekly' => $i != 8 ? rand(250, 400) : null,
                'meals_included' => in_array($i, [1, 3, 5, 7]),
                'meal_plan' => in_array($i, [1, 3, 5, 7]) ? 'half_board' : 'none',
                'airport_transfer' => in_array($i, [1, 2, 3, 5]),
                'airport_transfer_cost' => in_array($i, [1, 2, 3, 5]) ? 150 : null,
                'insurance_included' => true,
                'insurance_cost' => rand(100, 200),
                'study_materials_included' => true,
                'total_program_cost' => rand(5000, 12000),
                'placement_test_score' => $i <= 3 ? rand(60, 95) : null,
                'placement_test_date' => $i <= 3 ? now()->subDays(rand(5, 15)) : null,
                'assigned_level' => $i <= 3 ? ['Intermediate', 'Elementary', 'Upper Intermediate'][$i-1] : null,
                'attendance_percentage' => $i <= 3 ? rand(85, 100) : 0,
                'completed_weeks' => $i <= 3 ? rand(4, 10) : 0,
                'teacher_feedback' => $i <= 3 ? 'Good progress, active participation in class' : null,
                'progress_rating' => $i <= 3 ? ['excellent', 'good', 'excellent'][$i-1] : 'not_rated',
                'status' => [
                    'active',
                    'active',
                    'active',
                    'enrolled',
                    'enrolled',
                    'accepted',
                    'submitted',
                    'submitted'
                ][$i-1],
                'submission_date' => now()->subDays(rand(15, 60)),
                'acceptance_date' => $i <= 6 ? now()->subDays(rand(5, 30)) : null,
                'enrollment_date' => $i <= 5 ? now()->subDays(rand(1, 10)) : null,
                'learning_goals' => [
                    'Improve business communication skills',
                    'Reach intermediate level for university',
                    'Pass TOEFL exam with 100+ score',
                    'Improve professional English for career',
                    'General English improvement',
                    'Academic English for college',
                    'Learn conversational Spanish',
                    'Achieve English fluency'
                ][$i-1],
                'processed_by' => $i <= 6 ? 1 : null,
            ]);
        }

        $this->command->info('✅ Language Program Seeder completado');
        $this->command->info('   - 8 programas creados');
        $this->command->info('   - 3 activos, 2 enrolled, 1 accepted, 2 submitted');
        $this->command->info('   - Múltiples idiomas (principalmente English)');
        $this->command->info('   - 6 tipos diferentes de programas');
    }
}
