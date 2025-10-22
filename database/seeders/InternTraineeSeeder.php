<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\HostCompany;
use App\Models\InternTraineeValidation;
use App\Models\TrainingPlan;
use Illuminate\Support\Str;

class InternTraineeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Host Companies
        $companies = [];
        
        $companies[] = HostCompany::create([
            'company_name' => 'Tech Innovations Inc.',
            'legal_name' => 'Tech Innovations Incorporated',
            'company_code' => 'TII2024',
            'company_type' => 'corporation',
            'industry_sector' => 'IT',
            'company_description' => 'Leading software development company specializing in cloud solutions',
            'website' => 'https://techinnovations.com',
            'ein_number' => '12-3456789',
            'established_year' => 2015,
            'number_of_employees' => 250,
            'address' => '123 Tech Boulevard',
            'city' => 'San Francisco',
            'state' => 'CA',
            'zip_code' => '94102',
            'country' => 'USA',
            'phone' => '+1-415-555-0100',
            'email' => 'hr@techinnovations.com',
            'contact_name' => 'Sarah Johnson',
            'contact_title' => 'HR Director',
            'contact_phone' => '+1-415-555-0101',
            'contact_email' => 'sjohnson@techinnovations.com',
            'hr_contact_name' => 'Mike Chen',
            'hr_contact_title' => 'Talent Acquisition Manager',
            'hr_contact_phone' => '+1-415-555-0102',
            'hr_contact_email' => 'mchen@techinnovations.com',
            'years_in_program' => 5,
            'interns_hosted_total' => 45,
            'trainees_hosted_total' => 12,
            'current_participants' => 8,
            'positions_available' => 5,
            'sectors_offered' => ['Software Development', 'Cloud Engineering', 'DevOps', 'QA'],
            'has_training_program' => true,
            'provides_mentorship' => true,
            'offers_certification' => true,
            'offers_stipend' => true,
            'stipend_range_min' => 1500.00,
            'stipend_range_max' => 2500.00,
            'stipend_frequency' => 'monthly',
            'provides_housing' => false,
            'provides_transportation' => true,
            'benefits_offered' => ['Health Insurance', 'Gym Membership', 'Professional Development'],
            'minimum_education_level' => 3,
            'minimum_experience_years' => 1,
            'required_skills' => ['Python', 'JavaScript', 'Git'],
            'preferred_skills' => ['React', 'Node.js', 'Docker'],
            'english_level_required' => 'advanced',
            'min_duration_months' => 6,
            'max_duration_months' => 18,
            'flexible_start_dates' => true,
            'is_verified' => true,
            'verification_date' => now(),
            'e_verify_enrolled' => true,
            'has_liability_insurance' => true,
            'last_audit_date' => now()->subMonths(3),
            'is_active' => true,
            'rating' => 4.7,
            'total_reviews' => 28,
        ]);

        $companies[] = HostCompany::create([
            'company_name' => 'Global Finance Solutions',
            'legal_name' => 'Global Finance Solutions LLC',
            'company_code' => 'GFS2024',
            'company_type' => 'LLC',
            'industry_sector' => 'Finance',
            'company_description' => 'International financial services and consulting firm',
            'website' => 'https://globalfinance.com',
            'ein_number' => '98-7654321',
            'established_year' => 2010,
            'number_of_employees' => 180,
            'address' => '456 Wall Street',
            'city' => 'New York',
            'state' => 'NY',
            'zip_code' => '10005',
            'country' => 'USA',
            'phone' => '+1-212-555-0200',
            'email' => 'careers@globalfinance.com',
            'contact_name' => 'David Martinez',
            'contact_title' => 'VP of Human Resources',
            'contact_phone' => '+1-212-555-0201',
            'contact_email' => 'dmartinez@globalfinance.com',
            'years_in_program' => 3,
            'interns_hosted_total' => 25,
            'trainees_hosted_total' => 18,
            'current_participants' => 6,
            'positions_available' => 4,
            'sectors_offered' => ['Financial Analysis', 'Risk Management', 'Trading', 'Compliance'],
            'has_training_program' => true,
            'provides_mentorship' => true,
            'offers_certification' => false,
            'offers_stipend' => true,
            'stipend_range_min' => 2000.00,
            'stipend_range_max' => 3000.00,
            'stipend_frequency' => 'monthly',
            'provides_housing' => false,
            'minimum_education_level' => 3,
            'minimum_experience_years' => 1,
            'required_skills' => ['Excel', 'Financial Modeling', 'SQL'],
            'english_level_required' => 'advanced',
            'min_duration_months' => 6,
            'max_duration_months' => 12,
            'is_verified' => true,
            'verification_date' => now(),
            'e_verify_enrolled' => true,
            'has_liability_insurance' => true,
            'is_active' => true,
            'rating' => 4.5,
            'total_reviews' => 15,
        ]);

        $companies[] = HostCompany::create([
            'company_name' => 'Green Energy Systems',
            'company_code' => 'GES2024',
            'company_type' => 'corporation',
            'industry_sector' => 'Engineering',
            'company_description' => 'Renewable energy solutions and engineering services',
            'website' => 'https://greenenergy.com',
            'established_year' => 2018,
            'number_of_employees' => 120,
            'address' => '789 Solar Drive',
            'city' => 'Austin',
            'state' => 'TX',
            'zip_code' => '78701',
            'country' => 'USA',
            'phone' => '+1-512-555-0300',
            'email' => 'hr@greenenergy.com',
            'contact_name' => 'Emily Rodriguez',
            'contact_title' => 'Talent Manager',
            'contact_phone' => '+1-512-555-0301',
            'contact_email' => 'erodriguez@greenenergy.com',
            'years_in_program' => 2,
            'interns_hosted_total' => 15,
            'trainees_hosted_total' => 8,
            'current_participants' => 4,
            'positions_available' => 3,
            'sectors_offered' => ['Electrical Engineering', 'Mechanical Engineering', 'Project Management'],
            'has_training_program' => true,
            'provides_mentorship' => true,
            'offers_stipend' => true,
            'stipend_range_min' => 1800.00,
            'stipend_range_max' => 2400.00,
            'stipend_frequency' => 'monthly',
            'provides_housing' => true,
            'housing_details' => 'Shared apartment, 2 bedrooms, utilities included',
            'minimum_education_level' => 3,
            'minimum_experience_years' => 0,
            'required_skills' => ['CAD', 'Engineering Fundamentals'],
            'english_level_required' => 'intermediate',
            'min_duration_months' => 3,
            'max_duration_months' => 12,
            'is_verified' => true,
            'verification_date' => now(),
            'is_active' => true,
            'rating' => 4.8,
            'total_reviews' => 10,
        ]);

        // Create Intern Participants
        for ($i = 1; $i <= 5; $i++) {
            $user = User::create([
                'name' => "Intern Participant {$i}",
                'email' => "intern{$i}@test.com",
                'password' => bcrypt('password'),
                'role' => 'participant',
                'email_verified_at' => now(),
            ]);

            $validation = InternTraineeValidation::create([
                'user_id' => $user->id,
                'program_type' => 'intern',
                'university_name' => ['MIT', 'Stanford University', 'UC Berkeley', 'Carnegie Mellon', 'Georgia Tech'][$i-1],
                'degree_field' => ['Computer Science', 'Software Engineering', 'Data Science', 'Electrical Engineering', 'Information Systems'][$i-1],
                'current_year' => rand(3, 4),
                'total_years' => 4,
                'gpa' => rand(32, 40) / 10,
                'expected_graduation' => now()->addMonths(rand(6, 18)),
                'is_currently_enrolled' => true,
                'industry_sector' => ['IT', 'IT', 'IT', 'Engineering', 'IT'][$i-1],
                'position_title' => ['Software Developer Intern', 'Web Developer Intern', 'Data Analyst Intern', 'Engineering Intern', 'IT Support Intern'][$i-1],
                'program_start_date' => now()->addMonths(2),
                'program_end_date' => now()->addMonths(8),
                'duration_months' => 6,
                'technical_skills' => ['Python', 'JavaScript', 'React', 'SQL', 'Git'],
                'software_proficiency' => ['VS Code', 'Git', 'Docker'],
                'languages_spoken' => ['Spanish', 'English'],
                'preferred_states' => ['CA', 'NY', 'TX'],
                'willing_to_relocate' => true,
                'meets_age_requirement' => true,
                'meets_education_requirement' => true,
                'meets_experience_requirement' => true,
                'meets_english_requirement' => true,
                'has_valid_passport' => true,
                'has_clean_record' => true,
                'is_student_or_recent_grad' => true,
                'validation_status' => $i <= 3 ? 'approved' : 'pending_review',
                'validated_by' => $i <= 3 ? 1 : null,
                'validation_completed_at' => $i <= 3 ? now() : null,
            ]);

            // Create training plan for first 2 approved
            if ($i <= 2) {
                TrainingPlan::create([
                    'plan_number' => 'TP-INTERN-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'user_id' => $user->id,
                    'host_company_id' => $companies[$i-1]->id,
                    'validation_id' => $validation->id,
                    'plan_title' => $validation->position_title . ' Training Plan',
                    'plan_description' => 'Comprehensive training program for ' . $validation->position_title,
                    'program_type' => 'intern',
                    'position_title' => $validation->position_title,
                    'department' => 'Engineering',
                    'primary_objectives' => [
                        'Gain practical experience in software development',
                        'Learn industry best practices',
                        'Contribute to real-world projects'
                    ],
                    'learning_outcomes' => [
                        'Proficiency in modern development frameworks',
                        'Understanding of agile methodologies',
                        'Team collaboration skills'
                    ],
                    'skills_to_acquire' => ['React', 'Node.js', 'MongoDB', 'CI/CD', 'Testing'],
                    'training_phases' => [
                        ['phase' => 'Onboarding', 'duration' => 2, 'activities' => 'Company orientation, tool setup'],
                        ['phase' => 'Learning', 'duration' => 8, 'activities' => 'Coding tasks, code reviews'],
                        ['phase' => 'Contributing', 'duration' => 14, 'activities' => 'Feature development, testing']
                    ],
                    'participant_responsibilities' => 'Complete assigned tasks, attend meetings, provide progress reports',
                    'company_responsibilities' => 'Provide mentorship, resources, and feedback',
                    'supervisor_name' => 'John Smith',
                    'supervisor_title' => 'Senior Software Engineer',
                    'supervisor_email' => 'jsmith@company.com',
                    'supervisor_phone' => '+1-555-0100',
                    'supervision_hours_per_week' => 5,
                    'start_date' => now()->addMonths(2),
                    'end_date' => now()->addMonths(8),
                    'total_duration_months' => 6,
                    'hours_per_week' => 40,
                    'work_schedule' => ['Monday-Friday', '9:00 AM - 5:00 PM'],
                    'training_location_address' => $companies[$i-1]->address,
                    'training_location_city' => $companies[$i-1]->city,
                    'training_location_state' => $companies[$i-1]->state,
                    'training_location_zip' => $companies[$i-1]->zip_code,
                    'allows_remote_work' => true,
                    'remote_days_per_week' => 2,
                    'is_paid' => true,
                    'stipend_amount' => 2000.00,
                    'stipend_frequency' => 'monthly',
                    'requires_progress_reports' => true,
                    'report_frequency' => 'monthly',
                    'company_approved' => true,
                    'company_approved_at' => now(),
                    'participant_approved' => true,
                    'participant_approved_at' => now(),
                    'sponsor_approved' => $i == 1,
                    'sponsor_approved_at' => $i == 1 ? now() : null,
                    'status' => $i == 1 ? 'active' : 'pending_sponsor_approval',
                    'completion_percentage' => $i == 1 ? 35 : 0,
                ]);
            }
        }

        // Create Trainee Participants
        for ($i = 1; $i <= 5; $i++) {
            $user = User::create([
                'name' => "Trainee Participant {$i}",
                'email' => "trainee{$i}@test.com",
                'password' => bcrypt('password'),
                'role' => 'participant',
                'email_verified_at' => now(),
            ]);

            InternTraineeValidation::create([
                'user_id' => $user->id,
                'program_type' => 'trainee',
                'field_of_expertise' => ['Software Engineering', 'Financial Analysis', 'Marketing', 'Data Analytics', 'Project Management'][$i-1],
                'years_of_experience' => rand(2, 5),
                'years_verified' => rand(1, 3),
                'current_position' => ['Software Developer', 'Financial Analyst', 'Marketing Specialist', 'Data Analyst', 'Project Coordinator'][$i-1],
                'current_employer' => ['Tech Company', 'Bank', 'Marketing Agency', 'Consulting Firm', 'Construction Co.'][$i-1],
                'previous_positions' => [
                    ['company' => 'Previous Employer', 'position' => 'Junior Developer', 'years' => 2]
                ],
                'industry_sector' => ['IT', 'Finance', 'Marketing', 'IT', 'Engineering'][$i-1],
                'position_title' => ['Senior Software Developer', 'Risk Analyst', 'Marketing Manager', 'Lead Data Scientist', 'Project Manager'][$i-1],
                'program_start_date' => now()->addMonths(3),
                'program_end_date' => now()->addMonths(15),
                'duration_months' => 12,
                'technical_skills' => ['Python', 'Java', 'AWS', 'Docker', 'Kubernetes'],
                'software_proficiency' => ['IntelliJ', 'Git', 'Jira', 'Jenkins'],
                'languages_spoken' => ['Spanish', 'English', 'Portuguese'],
                'preferred_states' => ['CA', 'NY', 'WA'],
                'willing_to_relocate' => true,
                'meets_age_requirement' => true,
                'meets_education_requirement' => true,
                'meets_experience_requirement' => true,
                'meets_english_requirement' => true,
                'has_valid_passport' => true,
                'has_clean_record' => true,
                'has_minimum_experience' => true,
                'has_professional_references' => true,
                'validation_status' => $i <= 3 ? 'approved' : 'pending_review',
                'validated_by' => $i <= 3 ? 1 : null,
                'validation_completed_at' => $i <= 3 ? now() : null,
            ]);
        }

        $this->command->info('✅ Intern/Trainee Seeder completado');
        $this->command->info('   - 3 empresas host creadas');
        $this->command->info('   - 5 interns creados (3 aprobados)');
        $this->command->info('   - 5 trainees creados (3 aprobados)');
        $this->command->info('   - 2 training plans creados (1 activo)');
    }
}
