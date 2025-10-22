<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\WorkStudyProgram;
use App\Models\WorkStudyEmployer;
use App\Models\WorkStudyPlacement;

class WorkStudySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Employers
        $employers = [];
        
        $employers[] = WorkStudyEmployer::create([
            'employer_name' => 'Santa Monica Beach Hotel',
            'employer_code' => 'SMBH-2024',
            'business_type' => 'hotel',
            'description' => 'Luxury beachfront hotel in Santa Monica',
            'website' => 'https://www.smbeachhotel.com',
            'address' => '1700 Ocean Avenue',
            'city' => 'Santa Monica',
            'state' => 'CA',
            'zip_code' => '90401',
            'contact_person_name' => 'Maria Rodriguez',
            'contact_person_title' => 'HR Manager',
            'contact_email' => 'hr@smbeachhotel.com',
            'contact_phone' => '+1-310-555-0101',
            'available_positions' => ['Front Desk', 'Housekeeping', 'Restaurant Server', 'Concierge'],
            'total_positions_available' => 15,
            'positions_currently_filled' => 8,
            'hourly_wage_min' => 16.00,
            'hourly_wage_max' => 22.00,
            'hours_per_week_min' => 30,
            'hours_per_week_max' => 40,
            'work_schedule' => ['Flexible', 'Rotating shifts'],
            'provides_tips' => true,
            'avg_tips_per_week' => 150.00,
            'provides_meals' => true,
            'provides_uniform' => true,
            'provides_transportation' => false,
            'provides_housing' => true,
            'housing_cost_weekly' => 150.00,
            'other_benefits' => 'Employee discounts, Health insurance after 90 days',
            'min_english_level' => 'intermediate',
            'min_age' => 18,
            'requires_experience' => false,
            'students_hosted_total' => 45,
            'students_current' => 8,
            'rating' => 4.6,
            'total_reviews' => 23,
            'positive_reviews' => 20,
            'negative_reviews' => 3,
            'is_verified' => true,
            'verification_date' => now()->subMonths(12),
            'is_active' => true,
            'years_partnership' => 3,
        ]);

        $employers[] = WorkStudyEmployer::create([
            'employer_name' => 'The Coffee Bean & Tea Leaf - Hollywood',
            'employer_code' => 'CBTL-HWD-2024',
            'business_type' => 'cafe',
            'description' => 'Popular coffee shop chain location in Hollywood',
            'website' => 'https://www.coffeebean.com',
            'address' => '6750 Hollywood Blvd',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'zip_code' => '90028',
            'contact_person_name' => 'James Park',
            'contact_person_title' => 'Store Manager',
            'contact_email' => 'james.park@coffeebean.com',
            'contact_phone' => '+1-323-555-0202',
            'available_positions' => ['Barista', 'Cashier', 'Shift Supervisor'],
            'total_positions_available' => 8,
            'positions_currently_filled' => 5,
            'hourly_wage_min' => 16.50,
            'hourly_wage_max' => 18.00,
            'hours_per_week_min' => 25,
            'hours_per_week_max' => 35,
            'work_schedule' => ['Morning', 'Afternoon', 'Evening'],
            'provides_tips' => true,
            'avg_tips_per_week' => 80.00,
            'provides_meals' => true,
            'provides_uniform' => true,
            'min_english_level' => 'intermediate',
            'min_age' => 18,
            'students_hosted_total' => 30,
            'students_current' => 5,
            'rating' => 4.4,
            'total_reviews' => 18,
            'positive_reviews' => 15,
            'negative_reviews' => 3,
            'is_verified' => true,
            'verification_date' => now()->subMonths(6),
            'is_active' => true,
            'years_partnership' => 2,
        ]);

        $employers[] = WorkStudyEmployer::create([
            'employer_name' => 'Target Store - Westwood',
            'employer_code' => 'TGT-WWD-2024',
            'business_type' => 'retail_store',
            'description' => 'Large retail store near UCLA campus',
            'website' => 'https://www.target.com',
            'address' => '10861 Weyburn Ave',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'zip_code' => '90024',
            'contact_person_name' => 'Sarah Johnson',
            'contact_person_title' => 'HR Coordinator',
            'contact_email' => 'westwood.hr@target.com',
            'contact_phone' => '+1-310-555-0303',
            'available_positions' => ['Sales Associate', 'Cashier', 'Stock Associate'],
            'total_positions_available' => 12,
            'positions_currently_filled' => 7,
            'hourly_wage_min' => 17.00,
            'hourly_wage_max' => 19.50,
            'hours_per_week_min' => 20,
            'hours_per_week_max' => 32,
            'work_schedule' => ['Flexible', 'Part-time'],
            'provides_tips' => false,
            'provides_meals' => false,
            'provides_uniform' => true,
            'provides_transportation' => false,
            'other_benefits' => 'Employee discount, Team member wellness programs',
            'min_english_level' => 'intermediate',
            'min_age' => 18,
            'students_hosted_total' => 25,
            'students_current' => 7,
            'rating' => 4.2,
            'total_reviews' => 15,
            'positive_reviews' => 12,
            'negative_reviews' => 3,
            'is_verified' => true,
            'verification_date' => now()->subMonths(8),
            'is_active' => true,
            'years_partnership' => 2,
        ]);

        // Create Work & Study Programs
        for ($i = 1; $i <= 6; $i++) {
            $user = User::create([
                'name' => "Work Study Student {$i}",
                'email' => "workstudy{$i}@test.com",
                'password' => bcrypt('password'),
                'role' => 'participant',
                'email_verified_at' => now(),
            ]);

            $startDate = now()->addMonths($i <= 3 ? -2 : 2);
            $weeks = [12, 16, 24, 12, 16, 20][$i-1];

            $program = WorkStudyProgram::create([
                'user_id' => $user->id,
                'program_number' => 'WS-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'language_school_name' => ['EC Los Angeles', 'Kaplan Santa Monica', 'Kings Hollywood', 'Mentor Language Institute', 'Columbia West College', 'LSI San Diego'][$i-1],
                'school_city' => ['Los Angeles', 'Santa Monica', 'Los Angeles', 'Los Angeles', 'Los Angeles', 'San Diego'][$i-1],
                'school_state' => 'CA',
                'school_address' => '123 Main St',
                'school_accreditation' => ['CEA', 'ACCET', 'CEA', 'ACCET', 'none', 'CEA'][$i-1],
                'program_type' => ['intensive_english', 'semi_intensive', 'intensive_english', 'business_english', 'intensive_english', 'semi_intensive'][$i-1],
                'weeks_of_study' => $weeks,
                'hours_per_week' => [20, 18, 20, 15, 20, 18][$i-1],
                'program_start_date' => $startDate,
                'program_end_date' => $startDate->copy()->addWeeks($weeks),
                'tuition_cost' => rand(3000, 6000),
                'program_level' => ['Intermediate', 'Advanced', 'Beginner', 'Intermediate', 'Upper Intermediate', 'Intermediate'][$i-1],
                'current_english_level' => ['intermediate', 'advanced', 'beginner', 'intermediate', 'upper_intermediate', 'intermediate'][$i-1],
                'english_test_score' => rand(50, 90),
                'english_test_type' => 'TOEFL',
                'test_date' => now()->subMonths(rand(2, 6)),
                'includes_work_component' => true,
                'work_hours_per_week' => 20,
                'work_category' => ['hospitality', 'food_service', 'retail', 'hospitality', 'food_service', 'retail'][$i-1],
                'expected_hourly_wage' => rand(16, 20),
                'accommodation_type' => ['homestay', 'student_residence', 'homestay', 'shared_apartment', 'homestay', 'student_residence'][$i-1],
                'accommodation_included' => true,
                'accommodation_cost_weekly' => rand(200, 350),
                'insurance_included' => true,
                'insurance_cost' => rand(100, 200),
                'total_program_cost' => rand(5000, 10000),
                'registration_fee' => 100,
                'materials_fee' => 50,
                'status' => $i <= 2 ? 'active' : ($i <= 4 ? 'accepted' : 'submitted'),
                'submission_date' => now()->subDays(rand(20, 60)),
                'acceptance_date' => $i <= 4 ? now()->subDays(rand(5, 15)) : null,
                'employer_id' => $i <= 2 ? $employers[$i-1]->id : null,
                'work_start_date' => $i <= 2 ? $startDate->copy()->addWeeks(2) : null,
                'work_end_date' => $i <= 2 ? $startDate->copy()->addWeeks($weeks - 2) : null,
                'processed_by' => $i <= 4 ? 1 : null,
            ]);

            // Create placements for active programs
            if ($i <= 2) {
                WorkStudyPlacement::create([
                    'placement_number' => 'PL-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'program_id' => $program->id,
                    'user_id' => $user->id,
                    'employer_id' => $employers[$i-1]->id,
                    'job_title' => ['Front Desk Associate', 'Barista'][$i-1],
                    'job_description' => 'Provide excellent customer service',
                    'job_responsibilities' => ['Greet guests', 'Handle reservations', 'Answer phones'],
                    'start_date' => $program->work_start_date,
                    'end_date' => $program->work_end_date,
                    'hours_per_week' => 20,
                    'work_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                    'shift_type' => ['Morning', 'Afternoon'][$i-1],
                    'hourly_wage' => [18.00, 16.50][$i-1],
                    'receives_tips' => true,
                    'avg_tips_weekly' => [120.00, 80.00][$i-1],
                    'estimated_weekly_earnings' => [480.00, 410.00][$i-1],
                    'total_earnings_to_date' => rand(2000, 5000),
                    'meals_provided' => true,
                    'uniform_provided' => true,
                    'total_hours_worked' => rand(100, 300),
                    'weeks_completed' => rand(4, 10),
                    'attendance_rating' => 'excellent',
                    'performance_rating' => ['excellent', 'good'][$i-1],
                    'status' => 'active',
                    'activation_date' => $program->work_start_date,
                    'supervisor_name' => ['Maria Rodriguez', 'James Park'][$i-1],
                    'supervisor_email' => ['hr@smbeachhotel.com', 'james.park@coffeebean.com'][$i-1],
                    'processed_by' => 1,
                ]);
            }
        }

        $this->command->info('✅ Work & Study Seeder completado');
        $this->command->info('   - 3 empleadores creados (Hotel, Café, Retail)');
        $this->command->info('   - 6 programas (2 activos, 2 aceptados, 2 submitted)');
        $this->command->info('   - 2 colocaciones activas');
    }
}
