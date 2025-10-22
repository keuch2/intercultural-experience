<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\University;
use App\Models\HigherEducationApplication;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;

class HigherEducationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Universities
        $universities = [];
        
        $universities[] = University::create([
            'university_name' => 'University of California, Los Angeles (UCLA)',
            'code' => 'UCLA2024',
            'type' => 'public',
            'description' => 'Premier public research university in Los Angeles',
            'website' => 'https://www.ucla.edu',
            'address' => '405 Hilgard Avenue',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'zip_code' => '90095',
            'country' => 'USA',
            'main_phone' => '+1-310-825-4321',
            'admissions_email' => 'ugadm@saonet.ucla.edu',
            'admissions_phone' => '+1-310-825-3101',
            'international_office_email' => 'issp@saonet.ucla.edu',
            'founded_year' => 1919,
            'total_students' => 45000,
            'international_students' => 6500,
            'undergraduate_programs' => 125,
            'graduate_programs' => 200,
            'degree_types_offered' => ['bachelor', 'master', 'phd'],
            'accreditation' => 'WASC Senior College and University Commission',
            'us_news_ranking' => 20,
            'qs_world_ranking' => 29,
            'acceptance_rate' => 14.3,
            'graduation_rate' => 91.0,
            'min_gpa_undergraduate' => 3.4,
            'min_gpa_graduate' => 3.0,
            'min_toefl_score' => 100,
            'min_ielts_score' => 7.0,
            'min_sat_score' => 1300,
            'tuition_undergraduate_annual' => 43022.00,
            'tuition_graduate_annual' => 31000.00,
            'room_board_annual' => 17000.00,
            'books_supplies_annual' => 1500.00,
            'estimated_total_annual' => 61522.00,
            'offers_scholarships' => true,
            'offers_financial_aid' => true,
            'offers_work_study' => true,
            'avg_scholarship_amount' => 15000.00,
            'scholarships_available' => 50,
            'campus_size_acres' => 419,
            'has_on_campus_housing' => true,
            'has_library' => true,
            'has_sports_facilities' => true,
            'has_health_center' => true,
            'has_career_services' => true,
            'popular_majors' => ['Computer Science', 'Business Economics', 'Psychology', 'Biology', 'Engineering'],
            'offers_esl' => true,
            'offers_pathway_programs' => true,
            'application_deadlines' => [
                'fall' => '2025-11-30',
                'spring' => '2025-09-01'
            ],
            'application_fee' => 70.00,
            'accepts_common_app' => true,
            'has_international_office' => true,
            'offers_orientation' => true,
            'offers_pickup_service' => true,
            'provides_visa_support' => true,
            'is_partner_university' => true,
            'years_partnership' => 5,
            'students_placed_total' => 150,
            'students_current' => 35,
            'is_verified' => true,
            'verification_date' => now(),
            'is_active' => true,
            'rating' => 4.8,
            'total_reviews' => 45,
        ]);

        $universities[] = University::create([
            'university_name' => 'New York University (NYU)',
            'code' => 'NYU2024',
            'type' => 'private',
            'description' => 'Private research university in the heart of New York City',
            'website' => 'https://www.nyu.edu',
            'address' => '70 Washington Square South',
            'city' => 'New York',
            'state' => 'NY',
            'zip_code' => '10012',
            'country' => 'USA',
            'main_phone' => '+1-212-998-1212',
            'admissions_email' => 'admissions@nyu.edu',
            'founded_year' => 1831,
            'total_students' => 51000,
            'international_students' => 19000,
            'undergraduate_programs' => 230,
            'graduate_programs' => 350,
            'degree_types_offered' => ['bachelor', 'master', 'phd'],
            'us_news_ranking' => 25,
            'qs_world_ranking' => 38,
            'acceptance_rate' => 16.0,
            'graduation_rate' => 86.0,
            'min_gpa_undergraduate' => 3.6,
            'min_gpa_graduate' => 3.2,
            'min_toefl_score' => 100,
            'min_ielts_score' => 7.5,
            'tuition_undergraduate_annual' => 58168.00,
            'tuition_graduate_annual' => 45000.00,
            'room_board_annual' => 20000.00,
            'estimated_total_annual' => 78168.00,
            'offers_scholarships' => true,
            'offers_financial_aid' => true,
            'avg_scholarship_amount' => 20000.00,
            'scholarships_available' => 75,
            'popular_majors' => ['Business', 'Film', 'Computer Science', 'Economics', 'Law'],
            'offers_esl' => true,
            'application_fee' => 80.00,
            'is_partner_university' => true,
            'years_partnership' => 8,
            'students_placed_total' => 200,
            'students_current' => 45,
            'is_verified' => true,
            'verification_date' => now(),
            'is_active' => true,
            'rating' => 4.7,
            'total_reviews' => 60,
        ]);

        $universities[] = University::create([
            'university_name' => 'Santa Monica Community College',
            'code' => 'SMC2024',
            'type' => 'community_college',
            'description' => 'Leading community college with strong transfer programs',
            'website' => 'https://www.smc.edu',
            'address' => '1900 Pico Boulevard',
            'city' => 'Santa Monica',
            'state' => 'CA',
            'zip_code' => '90405',
            'country' => 'USA',
            'main_phone' => '+1-310-434-4000',
            'admissions_email' => 'admissions@smc.edu',
            'total_students' => 30000,
            'international_students' => 3500,
            'undergraduate_programs' => 80,
            'degree_types_offered' => ['associate'],
            'acceptance_rate' => 100.0,
            'graduation_rate' => 40.0,
            'min_gpa_undergraduate' => 2.0,
            'min_toefl_score' => 45,
            'min_ielts_score' => 4.5,
            'tuition_undergraduate_annual' => 9000.00,
            'room_board_annual' => 12000.00,
            'estimated_total_annual' => 21000.00,
            'offers_scholarships' => true,
            'avg_scholarship_amount' => 5000.00,
            'scholarships_available' => 20,
            'popular_majors' => ['Liberal Arts', 'Business', 'Engineering Transfer', 'Computer Science'],
            'offers_esl' => true,
            'offers_pathway_programs' => true,
            'application_fee' => 50.00,
            'is_partner_university' => true,
            'years_partnership' => 3,
            'students_placed_total' => 80,
            'students_current' => 25,
            'is_verified' => true,
            'is_active' => true,
            'rating' => 4.5,
            'total_reviews' => 30,
        ]);

        // Create Scholarships
        $scholarships = [];
        
        $scholarships[] = Scholarship::create([
            'scholarship_name' => 'UCLA International Excellence Scholarship',
            'code' => 'UCLA-IES-2025',
            'university_id' => $universities[0]->id,
            'scholarship_type' => 'merit',
            'description' => 'Merit-based scholarship for outstanding international students',
            'eligible_degree_levels' => ['bachelor', 'master'],
            'eligible_majors' => ['Computer Science', 'Engineering', 'Business'],
            'min_gpa_required' => 3.7,
            'award_type' => 'fixed_amount',
            'award_amount' => 15000.00,
            'award_frequency' => 'annual',
            'renewable_years' => 4,
            'is_renewable' => true,
            'covers_tuition' => true,
            'application_deadline' => now()->addMonths(6),
            'award_notification_date' => now()->addMonths(7),
            'requires_separate_application' => true,
            'required_documents' => ['Essay', 'Transcript', 'Recommendation Letters'],
            'total_awards_available' => 10,
            'awards_remaining' => 8,
            'is_active' => true,
            'application_year' => 2025,
            'requires_essay' => true,
            'requires_interview' => false,
        ]);

        $scholarships[] = Scholarship::create([
            'scholarship_name' => 'NYU Global Scholars Program',
            'code' => 'NYU-GSP-2025',
            'university_id' => $universities[1]->id,
            'scholarship_type' => 'academic',
            'description' => 'Full tuition scholarship for exceptional global leaders',
            'eligible_degree_levels' => ['bachelor'],
            'min_gpa_required' => 3.8,
            'award_type' => 'full_tuition',
            'award_frequency' => 'annual',
            'renewable_years' => 4,
            'is_renewable' => true,
            'covers_tuition' => true,
            'covers_housing' => true,
            'application_deadline' => now()->addMonths(5),
            'requires_separate_application' => true,
            'total_awards_available' => 5,
            'awards_remaining' => 4,
            'is_active' => true,
            'application_year' => 2025,
            'requires_essay' => true,
            'requires_interview' => true,
        ]);

        // Create Student Applications
        for ($i = 1; $i <= 6; $i++) {
            $user = User::create([
                'name' => "Higher Ed Student {$i}",
                'email' => "highered{$i}@test.com",
                'password' => bcrypt('password'),
                'role' => 'participant',
                'email_verified_at' => now(),
            ]);

            $degreeLevel = ['bachelor', 'master', 'associate', 'bachelor', 'master', 'phd'][$i-1];
            $university = $universities[$i <= 2 ? 0 : ($i <= 4 ? 1 : 2)];

            $application = HigherEducationApplication::create([
                'application_number' => 'HE-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'user_id' => $user->id,
                'university_id' => $university->id,
                'degree_level' => $degreeLevel,
                'major_field' => ['Computer Science', 'Business Administration', 'Liberal Arts', 'Engineering', 'Data Science', 'Psychology'][$i-1],
                'specific_program' => 'Full-time',
                'admission_term' => $i % 2 == 0 ? 'fall' : 'spring',
                'admission_year' => 2025,
                'desired_start_date' => now()->addMonths($i % 2 == 0 ? 8 : 4),
                'highest_degree_completed' => $degreeLevel == 'bachelor' ? 'High School' : 'Bachelor',
                'institution_name' => 'Previous University ' . $i,
                'country_of_study' => 'Colombia',
                'graduation_year' => 2023,
                'gpa' => rand(32, 40) / 10,
                'gpa_scale' => '4.0',
                'toefl_score' => rand(90, 110),
                'toefl_test_date' => now()->subMonths(rand(1, 6)),
                'english_level' => 'advanced',
                'needs_esl' => false,
                'funding_source' => ['self', 'family', 'scholarship', 'mixed', 'sponsor', 'loan'][$i-1],
                'available_funds_annual' => rand(20000, 50000),
                'needs_financial_aid' => $i <= 3,
                'applying_for_scholarship' => $i <= 3,
                'personal_statement' => 'I am passionate about ' . ['technology', 'business', 'education', 'innovation', 'research', 'healthcare'][$i-1],
                'career_goals' => 'My goal is to become a leader in my field',
                'needs_housing' => true,
                'housing_preference' => $i % 2 == 0 ? 'on-campus' : 'off-campus',
                'needs_health_insurance' => true,
                'application_status' => $i <= 2 ? 'accepted' : ($i <= 4 ? 'under_review' : 'submitted'),
                'submission_date' => now()->subDays(rand(10, 60)),
                'decision_date' => $i <= 2 ? now()->subDays(5) : null,
                'processed_by' => $i <= 2 ? 1 : null,
            ]);

            // Create scholarship application for first 3
            if ($i <= 3) {
                ScholarshipApplication::create([
                    'user_id' => $user->id,
                    'scholarship_id' => $scholarships[$i % 2]->id,
                    'university_application_id' => $application->id,
                    'application_number' => 'SA-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'application_date' => now()->subDays(rand(5, 30)),
                    'essay_text' => 'I deserve this scholarship because...',
                    'why_deserve_scholarship' => 'Academic excellence and leadership potential',
                    'status' => $i == 1 ? 'awarded' : 'under_review',
                    'submission_date' => now()->subDays(rand(5, 30)),
                    'is_awarded' => $i == 1,
                    'awarded_amount' => $i == 1 ? 15000.00 : null,
                    'award_start_date' => $i == 1 ? now()->addMonths(4) : null,
                    'award_end_date' => $i == 1 ? now()->addYears(1)->addMonths(4) : null,
                ]);
            }
        }

        $this->command->info('✅ Higher Education Seeder completado');
        $this->command->info('   - 3 universidades creadas (Public, Private, Community College)');
        $this->command->info('   - 2 becas activas');
        $this->command->info('   - 6 aplicaciones (2 aceptadas, 2 en review, 2 submitted)');
        $this->command->info('   - 3 aplicaciones de becas (1 otorgada)');
    }
}
