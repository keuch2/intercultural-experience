<?php

namespace Database\Seeders;

use App\Models\HostCompany;
use Illuminate\Database\Seeder;

class HostCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            [
                'name' => 'Marriott International',
                'industry' => 'Hospitality',
                'city' => 'Orlando',
                'state' => 'Florida',
                'country' => 'USA',
                'address' => '6200 International Drive',
                'contact_person' => 'Sarah Johnson',
                'contact_email' => 'hr@marriott.com',
                'contact_phone' => '+1-407-555-0100',
                'rating' => 4.5,
                'notes' => 'Major hotel chain with multiple positions available',
                'is_active' => true,
            ],
            [
                'name' => 'Universal Studios',
                'industry' => 'Entertainment',
                'city' => 'Orlando',
                'state' => 'Florida',
                'country' => 'USA',
                'address' => '6000 Universal Boulevard',
                'contact_person' => 'Michael Chen',
                'contact_email' => 'recruiting@universal.com',
                'contact_phone' => '+1-407-555-0200',
                'rating' => 4.8,
                'notes' => 'Theme park with seasonal positions',
                'is_active' => true,
            ],
            [
                'name' => 'Hilton Hotels & Resorts',
                'industry' => 'Hospitality',
                'city' => 'Miami',
                'state' => 'Florida',
                'country' => 'USA',
                'address' => '1601 Collins Avenue',
                'contact_person' => 'Jennifer Martinez',
                'contact_email' => 'careers@hilton.com',
                'contact_phone' => '+1-305-555-0300',
                'rating' => 4.3,
                'notes' => 'Beachfront resort with housekeeping and F&B positions',
                'is_active' => true,
            ],
            [
                'name' => 'Disney World Resort',
                'industry' => 'Entertainment',
                'city' => 'Lake Buena Vista',
                'state' => 'Florida',
                'country' => 'USA',
                'address' => '1180 Seven Seas Drive',
                'contact_person' => 'Robert Williams',
                'contact_email' => 'jobs@disney.com',
                'contact_phone' => '+1-407-555-0400',
                'rating' => 4.9,
                'notes' => 'Premier theme park employer',
                'is_active' => true,
            ],
            [
                'name' => "Macy's Department Store",
                'industry' => 'Retail',
                'city' => 'New York',
                'state' => 'New York',
                'country' => 'USA',
                'address' => '151 West 34th Street',
                'contact_person' => 'Amanda Davis',
                'contact_email' => 'hr@macys.com',
                'contact_phone' => '+1-212-555-0500',
                'rating' => 4.0,
                'notes' => 'Retail positions in flagship store',
                'is_active' => true,
            ],
            [
                'name' => 'Yellowstone National Park Lodges',
                'industry' => 'Tourism',
                'city' => 'Yellowstone',
                'state' => 'Wyoming',
                'country' => 'USA',
                'address' => 'PO Box 165',
                'contact_person' => 'David Thompson',
                'contact_email' => 'employment@yellowstone.com',
                'contact_phone' => '+1-307-555-0600',
                'rating' => 4.6,
                'notes' => 'National park hospitality positions',
                'is_active' => true,
            ],
            [
                'name' => 'Olive Garden',
                'industry' => 'Food Service',
                'city' => 'San Diego',
                'state' => 'California',
                'country' => 'USA',
                'address' => '1025 Camino de la Reina',
                'contact_person' => 'Maria Rodriguez',
                'contact_email' => 'jobs@olivegarden.com',
                'contact_phone' => '+1-619-555-0700',
                'rating' => 3.8,
                'notes' => 'Restaurant chain with server positions',
                'is_active' => true,
            ],
            [
                'name' => 'Six Flags Magic Mountain',
                'industry' => 'Entertainment',
                'city' => 'Valencia',
                'state' => 'California',
                'country' => 'USA',
                'address' => '26101 Magic Mountain Parkway',
                'contact_person' => 'Chris Anderson',
                'contact_email' => 'hr@sixflags.com',
                'contact_phone' => '+1-661-555-0800',
                'rating' => 4.2,
                'notes' => 'Amusement park seasonal positions',
                'is_active' => true,
            ],
        ];

        foreach ($companies as $companyData) {
            HostCompany::create($companyData);
        }

        $this->command->info('✅ 8 host companies created successfully!');
    }
}
