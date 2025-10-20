<?php

namespace Database\Seeders;

use App\Models\Sponsor;
use Illuminate\Database\Seeder;

class SponsorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sponsors = [
            [
                'name' => 'Alliance Abroad Group',
                'code' => 'AAG',
                'country' => 'USA',
                'contact_email' => 'info@allianceabroad.com',
                'contact_phone' => '+1-512-457-8062',
                'website' => 'https://www.allianceabroad.com',
                'terms_and_conditions' => 'Standard AAG terms apply. Participants must comply with all program requirements.',
                'is_active' => true,
            ],
            [
                'name' => 'American Work Abroad',
                'code' => 'AWA',
                'country' => 'USA',
                'contact_email' => 'contact@americanworkabroad.com',
                'contact_phone' => '+1-800-123-4567',
                'website' => 'https://www.americanworkabroad.com',
                'terms_and_conditions' => 'AWA program guidelines must be followed at all times.',
                'is_active' => true,
            ],
            [
                'name' => 'Global Horizons',
                'code' => 'GH',
                'country' => 'USA',
                'contact_email' => 'support@globalhorizons.com',
                'contact_phone' => '+1-888-555-0123',
                'website' => 'https://www.globalhorizons.com',
                'terms_and_conditions' => 'Global Horizons standard terms and conditions apply.',
                'is_active' => true,
            ],
            [
                'name' => 'InterExchange',
                'code' => 'IEX',
                'country' => 'USA',
                'contact_email' => 'info@interexchange.org',
                'contact_phone' => '+1-212-924-0446',
                'website' => 'https://www.interexchange.org',
                'terms_and_conditions' => 'InterExchange program policies apply to all participants.',
                'is_active' => true,
            ],
            [
                'name' => 'CIEE - Council on International Educational Exchange',
                'code' => 'CIEE',
                'country' => 'USA',
                'contact_email' => 'contact@ciee.org',
                'contact_phone' => '+1-207-553-4000',
                'website' => 'https://www.ciee.org',
                'terms_and_conditions' => 'CIEE terms and conditions for Work & Travel programs.',
                'is_active' => true,
            ],
        ];

        foreach ($sponsors as $sponsorData) {
            Sponsor::create($sponsorData);
        }

        $this->command->info('✅ 5 sponsors created successfully!');
    }
}
