<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\WorkTravelValidation;
use App\Models\Employer;
use App\Models\WorkContract;
use Illuminate\Support\Facades\Hash;

class WorkTravelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create employers
        $employers = [
            [
                'company_name' => 'Ocean Beach Resort & Spa',
                'business_type' => 'Hospitality - Resort',
                'ein_number' => '12-3456789',
                'established_year' => 2005,
                'number_of_employees' => 250,
                'address' => '1234 Ocean Drive',
                'city' => 'Miami Beach',
                'state' => 'FL',
                'zip_code' => '33139',
                'phone' => '+1 305-555-0100',
                'email' => 'hr@oceanbeachresort.com',
                'website' => 'https://oceanbeachresort.com',
                'contact_name' => 'Sarah Johnson',
                'contact_title' => 'HR Director',
                'contact_phone' => '+1 305-555-0101',
                'contact_email' => 'sarah.johnson@oceanbeachresort.com',
                'years_in_program' => 8,
                'participants_hired_total' => 145,
                'participants_hired_this_year' => 18,
                'positions_available' => 12,
                'job_categories' => ['Front Desk', 'Housekeeping', 'Food Service', 'Recreation'],
                'seasons_hiring' => ['summer', 'winter'],
                'is_verified' => true,
                'verification_date' => now()->subDays(30),
                'is_active' => true,
                'rating' => 4.7,
                'total_reviews' => 89,
                'e_verify_enrolled' => true,
                'workers_comp_insurance' => true,
                'liability_insurance' => true,
                'last_audit_date' => now()->subDays(60),
            ],
            [
                'company_name' => 'Mountain View Lodge',
                'business_type' => 'Hospitality - Lodge',
                'ein_number' => '98-7654321',
                'established_year' => 1998,
                'number_of_employees' => 180,
                'address' => '5678 Mountain Road',
                'city' => 'Aspen',
                'state' => 'CO',
                'zip_code' => '81611',
                'phone' => '+1 970-555-0200',
                'email' => 'jobs@mountainviewlodge.com',
                'website' => 'https://mountainviewlodge.com',
                'contact_name' => 'Michael Chen',
                'contact_title' => 'Operations Manager',
                'contact_phone' => '+1 970-555-0201',
                'contact_email' => 'michael.chen@mountainviewlodge.com',
                'years_in_program' => 12,
                'participants_hired_total' => 230,
                'participants_hired_this_year' => 25,
                'positions_available' => 15,
                'job_categories' => ['Ski Rental', 'Restaurant', 'Maintenance', 'Guest Services'],
                'seasons_hiring' => ['winter'],
                'is_verified' => true,
                'verification_date' => now()->subDays(45),
                'is_active' => true,
                'rating' => 4.9,
                'total_reviews' => 142,
                'e_verify_enrolled' => true,
                'workers_comp_insurance' => true,
                'liability_insurance' => true,
                'last_audit_date' => now()->subDays(90),
            ],
            [
                'company_name' => 'Coastal Amusement Park',
                'business_type' => 'Entertainment - Amusement Park',
                'ein_number' => '45-6789012',
                'established_year' => 2010,
                'number_of_employees' => 450,
                'address' => '9012 Boardwalk Avenue',
                'city' => 'Santa Monica',
                'state' => 'CA',
                'zip_code' => '90401',
                'phone' => '+1 310-555-0300',
                'email' => 'hr@coastalamusementpark.com',
                'website' => 'https://coastalamusementpark.com',
                'contact_name' => 'Jennifer Martinez',
                'contact_title' => 'Recruitment Coordinator',
                'contact_phone' => '+1 310-555-0301',
                'contact_email' => 'jennifer.martinez@coastalpark.com',
                'years_in_program' => 6,
                'participants_hired_total' => 95,
                'participants_hired_this_year' => 22,
                'positions_available' => 20,
                'job_categories' => ['Ride Operator', 'Food Stand', 'Retail', 'Customer Service'],
                'seasons_hiring' => ['summer'],
                'is_verified' => true,
                'verification_date' => now()->subDays(20),
                'is_active' => true,
                'rating' => 4.5,
                'total_reviews' => 67,
                'e_verify_enrolled' => true,
                'workers_comp_insurance' => true,
                'liability_insurance' => true,
                'last_audit_date' => now()->subDays(45),
            ],
            [
                'company_name' => 'Grand National Park Services',
                'business_type' => 'Tourism - Park Services',
                'ein_number' => '78-9012345',
                'established_year' => 1995,
                'number_of_employees' => 320,
                'address' => '3456 Park Entrance Road',
                'city' => 'Yellowstone',
                'state' => 'WY',
                'zip_code' => '82190',
                'phone' => '+1 307-555-0400',
                'email' => 'employment@grandnationalpark.com',
                'website' => 'https://grandnationalpark.com',
                'contact_name' => 'Robert Wilson',
                'contact_title' => 'Seasonal HR Manager',
                'contact_phone' => '+1 307-555-0401',
                'contact_email' => 'robert.wilson@gnps.com',
                'years_in_program' => 15,
                'participants_hired_total' => 380,
                'participants_hired_this_year' => 45,
                'positions_available' => 25,
                'job_categories' => ['Tour Guide', 'Gift Shop', 'Maintenance', 'Food Service'],
                'seasons_hiring' => ['summer'],
                'is_verified' => true,
                'verification_date' => now()->subDays(60),
                'is_active' => true,
                'rating' => 4.8,
                'total_reviews' => 210,
                'e_verify_enrolled' => true,
                'workers_comp_insurance' => true,
                'liability_insurance' => true,
                'last_audit_date' => now()->subDays(120),
            ],
            [
                'company_name' => 'Lakeside Restaurant Group',
                'business_type' => 'Food Service - Restaurant Chain',
                'ein_number' => '23-4567890',
                'established_year' => 2008,
                'number_of_employees' => 150,
                'address' => '7890 Lake Shore Drive',
                'city' => 'Chicago',
                'state' => 'IL',
                'zip_code' => '60611',
                'phone' => '+1 312-555-0500',
                'email' => 'careers@lakesidegroup.com',
                'website' => 'https://lakesidegroup.com',
                'contact_name' => 'Amanda Taylor',
                'contact_title' => 'Regional HR Manager',
                'contact_phone' => '+1 312-555-0501',
                'contact_email' => 'amanda.taylor@lakesidegroup.com',
                'years_in_program' => 5,
                'participants_hired_total' => 78,
                'participants_hired_this_year' => 15,
                'positions_available' => 10,
                'job_categories' => ['Server', 'Host/Hostess', 'Kitchen Staff', 'Bartender'],
                'seasons_hiring' => ['summer'],
                'is_verified' => true,
                'verification_date' => now()->subDays(25),
                'is_active' => true,
                'rating' => 4.3,
                'total_reviews' => 45,
                'e_verify_enrolled' => true,
                'workers_comp_insurance' => true,
                'liability_insurance' => true,
                'last_audit_date' => now()->subDays(50),
            ],
        ];

        $createdEmployers = [];
        foreach ($employers as $employerData) {
            $createdEmployers[] = Employer::create($employerData);
        }

        // Create Work & Travel students
        $students = [
            ['name' => 'Carlos Mendez', 'email' => 'carlos.mendez@universidad.edu.py', 'university' => 'Universidad Nacional de Asunción', 'semester' => 4, 'season' => 'summer'],
            ['name' => 'Maria Rodriguez', 'email' => 'maria.rodriguez@uca.edu.py', 'university' => 'Universidad Católica', 'semester' => 6, 'season' => 'summer'],
            ['name' => 'Juan Perez', 'email' => 'juan.perez@una.edu.py', 'university' => 'Universidad Nacional', 'semester' => 5, 'season' => 'winter'],
            ['name' => 'Ana Silva', 'email' => 'ana.silva@ucsa.edu.py', 'university' => 'Universidad Columbia', 'semester' => 3, 'season' => 'summer'],
            ['name' => 'Diego Fernandez', 'email' => 'diego.fernandez@uaa.edu.py', 'university' => 'Universidad Americana', 'semester' => 7, 'season' => 'summer'],
            ['name' => 'Sofia Gonzalez', 'email' => 'sofia.gonzalez@uninorte.edu.py', 'university' => 'Universidad del Norte', 'semester' => 4, 'season' => 'winter'],
            ['name' => 'Luis Ramirez', 'email' => 'luis.ramirez@unae.edu.py', 'university' => 'Universidad Autónoma', 'semester' => 6, 'season' => 'summer'],
            ['name' => 'Carolina Lopez', 'email' => 'carolina.lopez@uca.edu.py', 'university' => 'Universidad Católica', 'semester' => 5, 'season' => 'summer'],
            ['name' => 'Miguel Torres', 'email' => 'miguel.torres@una.edu.py', 'university' => 'Universidad Nacional', 'semester' => 8, 'season' => 'winter'],
            ['name' => 'Valentina Castro', 'email' => 'valentina.castro@ucsa.edu.py', 'university' => 'Universidad Columbia', 'semester' => 3, 'season' => 'summer'],
        ];

        foreach ($students as $index => $studentData) {
            // Create user
            $user = User::create([
                'name' => $studentData['name'],
                'email' => $studentData['email'],
                'password' => Hash::make('password123'),
                'role' => 'participant',
                'phone' => '+595 21 ' . rand(100000, 999999),
                'date_of_birth' => now()->subYears(rand(19, 25))->format('Y-m-d'),
                'nationality' => 'Paraguayan',
            ]);

            // Create validation
            $validation = WorkTravelValidation::create([
                'user_id' => $user->id,
                'university_name' => $studentData['university'],
                'study_type' => 'presencial',
                'is_presencial_validated' => $index < 7, // First 7 are validated
                'validation_date' => $index < 7 ? now()->subDays(rand(10, 30)) : null,
                'student_id_number' => 'STU' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                'expected_graduation' => now()->addYears(rand(1, 3))->format('Y-m-d'),
                'current_semester' => $studentData['semester'],
                'total_semesters' => 10,
                'gpa' => rand(30, 40) / 10, // 3.0 to 4.0
                'is_full_time_student' => true,
                'weekly_class_hours' => rand(15, 25),
                'current_courses' => ['Mathematics', 'Business Administration', 'English', 'Economics'],
                'program_start_date' => $studentData['season'] == 'summer' ? '2025-06-01' : '2025-12-01',
                'program_end_date' => $studentData['season'] == 'summer' ? '2025-09-15' : '2026-03-15',
                'season' => $studentData['season'],
                'meets_age_requirement' => true,
                'meets_academic_requirement' => true,
                'meets_english_requirement' => $index < 8, // Most meet English requirement
                'has_valid_passport' => true,
                'has_clean_record' => true,
                'validation_status' => $index < 7 ? 'approved' : ($index < 9 ? 'pending' : 'incomplete'),
                'validated_by' => $index < 7 ? 1 : null,
            ]);

            // Create contracts for validated students
            if ($index < 5 && $index < count($createdEmployers)) {
                $employer = $createdEmployers[$index % count($createdEmployers)];
                $startDate = $studentData['season'] == 'summer' ? '2025-06-15' : '2025-12-15';
                $endDate = $studentData['season'] == 'summer' ? '2025-09-10' : '2026-03-10';
                
                WorkContract::create([
                    'user_id' => $user->id,
                    'employer_id' => $employer->id,
                    'contract_number' => 'WTC-2025-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                    'contract_type' => 'seasonal',
                    'position_title' => ['Front Desk Agent', 'Server', 'Housekeeping', 'Kitchen Staff', 'Tour Guide'][$index % 5],
                    'job_description' => 'Seasonal position providing excellent customer service in a dynamic environment.',
                    'work_location_city' => $employer->city,
                    'work_location_state' => $employer->state,
                    'work_location_zip' => $employer->zip_code,
                    'work_location_address' => $employer->address,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'duration_weeks' => 12,
                    'is_flexible_dates' => false,
                    'hourly_rate' => rand(12, 18),
                    'hours_per_week' => 40,
                    'overtime_rate' => rand(18, 27),
                    'estimated_total_earnings' => rand(5000, 8000),
                    'payment_frequency' => 'biweekly',
                    'provides_housing' => $index < 3,
                    'housing_cost_per_week' => $index < 3 ? rand(80, 120) : null,
                    'provides_meals' => $index < 2,
                    'provides_transportation' => false,
                    'deductions' => ['taxes' => 15, 'social_security' => 7.65],
                    'total_deductions_per_week' => rand(50, 100),
                    'contract_signed' => $index < 3,
                    'signed_at' => $index < 3 ? now()->subDays(rand(5, 15)) : null,
                    'status' => $index < 2 ? 'active' : ($index < 4 ? 'pending_signature' : 'draft'),
                    'employer_verified' => true,
                    'position_verified' => true,
                    'verified_by' => 1,
                    'verification_date' => now()->subDays(rand(5, 20)),
                ]);
            }
        }

        $this->command->info('Work & Travel seeder completed successfully!');
        $this->command->info('Created: ' . count($createdEmployers) . ' employers, ' . count($students) . ' students');
    }
}
