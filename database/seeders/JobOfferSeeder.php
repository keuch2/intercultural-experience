<?php

namespace Database\Seeders;

use App\Models\JobOffer;
use App\Models\Sponsor;
use App\Models\HostCompany;
use Illuminate\Database\Seeder;

class JobOfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get sponsors and companies
        $aag = Sponsor::where('code', 'AAG')->first();
        $awa = Sponsor::where('code', 'AWA')->first();
        $gh = Sponsor::where('code', 'GH')->first();
        
        $marriott = HostCompany::where('name', 'Marriott International')->first();
        $universal = HostCompany::where('name', 'Universal Studios')->first();
        $hilton = HostCompany::where('name', 'Hilton Hotels & Resorts')->first();
        $disney = HostCompany::where('name', 'Disney World Resort')->first();
        $macys = HostCompany::where('name', "Macy's Department Store")->first();
        $yellowstone = HostCompany::where('name', 'Yellowstone National Park Lodges')->first();

        $offers = [
            [
                'job_offer_id' => 'JO-2025-001',
                'sponsor_id' => $aag->id,
                'host_company_id' => $marriott->id,
                'position' => 'Front Desk Agent',
                'description' => 'Greet guests, check-in/check-out, handle reservations and provide excellent customer service.',
                'city' => 'Orlando',
                'state' => 'Florida',
                'start_date' => '2025-06-01',
                'end_date' => '2025-09-30',
                'total_slots' => 10,
                'available_slots' => 10,
                'salary_min' => 12.50,
                'salary_max' => 14.00,
                'hours_per_week' => 40,
                'required_english_level' => 'B1',
                'required_gender' => 'any',
                'status' => 'available',
            ],
            [
                'job_offer_id' => 'JO-2025-002',
                'sponsor_id' => $aag->id,
                'host_company_id' => $universal->id,
                'position' => 'Ride Operator',
                'description' => 'Operate theme park rides, ensure guest safety, and provide entertainment.',
                'city' => 'Orlando',
                'state' => 'Florida',
                'start_date' => '2025-05-15',
                'end_date' => '2025-08-31',
                'total_slots' => 15,
                'available_slots' => 15,
                'salary_min' => 13.00,
                'salary_max' => 15.00,
                'hours_per_week' => 40,
                'required_english_level' => 'B1+',
                'required_gender' => 'any',
                'status' => 'available',
            ],
            [
                'job_offer_id' => 'JO-2025-003',
                'sponsor_id' => $awa->id,
                'host_company_id' => $hilton->id,
                'position' => 'Housekeeping',
                'description' => 'Clean and maintain guest rooms, ensure high standards of cleanliness.',
                'city' => 'Miami',
                'state' => 'Florida',
                'start_date' => '2025-06-01',
                'end_date' => '2025-10-31',
                'total_slots' => 20,
                'available_slots' => 20,
                'salary_min' => 12.00,
                'salary_max' => 13.50,
                'hours_per_week' => 40,
                'required_english_level' => 'A2',
                'required_gender' => 'female',
                'status' => 'available',
            ],
            [
                'job_offer_id' => 'JO-2025-004',
                'sponsor_id' => $awa->id,
                'host_company_id' => $disney->id,
                'position' => 'Food & Beverage Server',
                'description' => 'Serve food and beverages in Disney restaurants, provide magical guest experiences.',
                'city' => 'Lake Buena Vista',
                'state' => 'Florida',
                'start_date' => '2025-05-20',
                'end_date' => '2025-09-15',
                'total_slots' => 25,
                'available_slots' => 25,
                'salary_min' => 13.50,
                'salary_max' => 16.00,
                'hours_per_week' => 40,
                'required_english_level' => 'B2',
                'required_gender' => 'any',
                'status' => 'available',
            ],
            [
                'job_offer_id' => 'JO-2025-005',
                'sponsor_id' => $gh->id,
                'host_company_id' => $macys->id,
                'position' => 'Sales Associate',
                'description' => 'Assist customers, process sales, maintain store appearance.',
                'city' => 'New York',
                'state' => 'New York',
                'start_date' => '2025-06-15',
                'end_date' => '2025-09-30',
                'total_slots' => 12,
                'available_slots' => 12,
                'salary_min' => 15.00,
                'salary_max' => 17.00,
                'hours_per_week' => 35,
                'required_english_level' => 'B1+',
                'required_gender' => 'any',
                'status' => 'available',
            ],
            [
                'job_offer_id' => 'JO-2025-006',
                'sponsor_id' => $gh->id,
                'host_company_id' => $yellowstone->id,
                'position' => 'Lodge Front Desk',
                'description' => 'Check-in guests, provide park information, handle reservations.',
                'city' => 'Yellowstone',
                'state' => 'Wyoming',
                'start_date' => '2025-05-01',
                'end_date' => '2025-09-30',
                'total_slots' => 8,
                'available_slots' => 8,
                'salary_min' => 13.00,
                'salary_max' => 15.00,
                'hours_per_week' => 40,
                'required_english_level' => 'B1',
                'required_gender' => 'any',
                'status' => 'available',
            ],
        ];

        foreach ($offers as $offerData) {
            JobOffer::create($offerData);
        }

        $this->command->info('✅ 6 job offers created successfully!');
    }
}
