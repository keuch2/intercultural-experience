<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Country;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            // América del Norte
            ['name' => 'United States', 'code' => 'USA', 'iso2' => 'US', 'region' => 'North America', 'flag_emoji' => '🇺🇸', 'display_order' => 1],
            ['name' => 'Canada', 'code' => 'CAN', 'iso2' => 'CA', 'region' => 'North America', 'flag_emoji' => '🇨🇦', 'display_order' => 2],
            ['name' => 'Mexico', 'code' => 'MEX', 'iso2' => 'MX', 'region' => 'North America', 'flag_emoji' => '🇲🇽', 'display_order' => 3],
            
            // Europa Occidental
            ['name' => 'United Kingdom', 'code' => 'GBR', 'iso2' => 'GB', 'region' => 'Europe', 'flag_emoji' => '🇬🇧', 'display_order' => 4],
            ['name' => 'Ireland', 'code' => 'IRL', 'iso2' => 'IE', 'region' => 'Europe', 'flag_emoji' => '🇮🇪', 'display_order' => 5],
            ['name' => 'France', 'code' => 'FRA', 'iso2' => 'FR', 'region' => 'Europe', 'flag_emoji' => '🇫🇷', 'display_order' => 6],
            ['name' => 'Germany', 'code' => 'DEU', 'iso2' => 'DE', 'region' => 'Europe', 'flag_emoji' => '🇩🇪', 'display_order' => 7],
            ['name' => 'Spain', 'code' => 'ESP', 'iso2' => 'ES', 'region' => 'Europe', 'flag_emoji' => '🇪🇸', 'display_order' => 8],
            ['name' => 'Italy', 'code' => 'ITA', 'iso2' => 'IT', 'region' => 'Europe', 'flag_emoji' => '🇮🇹', 'display_order' => 9],
            ['name' => 'Portugal', 'code' => 'PRT', 'iso2' => 'PT', 'region' => 'Europe', 'flag_emoji' => '🇵🇹', 'display_order' => 10],
            ['name' => 'Netherlands', 'code' => 'NLD', 'iso2' => 'NL', 'region' => 'Europe', 'flag_emoji' => '🇳🇱', 'display_order' => 11],
            ['name' => 'Belgium', 'code' => 'BEL', 'iso2' => 'BE', 'region' => 'Europe', 'flag_emoji' => '🇧🇪', 'display_order' => 12],
            ['name' => 'Switzerland', 'code' => 'CHE', 'iso2' => 'CH', 'region' => 'Europe', 'flag_emoji' => '🇨🇭', 'display_order' => 13],
            ['name' => 'Austria', 'code' => 'AUT', 'iso2' => 'AT', 'region' => 'Europe', 'flag_emoji' => '🇦🇹', 'display_order' => 14],
            
            // Europa Nórdica
            ['name' => 'Sweden', 'code' => 'SWE', 'iso2' => 'SE', 'region' => 'Europe', 'flag_emoji' => '🇸🇪', 'display_order' => 15],
            ['name' => 'Norway', 'code' => 'NOR', 'iso2' => 'NO', 'region' => 'Europe', 'flag_emoji' => '🇳🇴', 'display_order' => 16],
            ['name' => 'Denmark', 'code' => 'DNK', 'iso2' => 'DK', 'region' => 'Europe', 'flag_emoji' => '🇩🇰', 'display_order' => 17],
            ['name' => 'Finland', 'code' => 'FIN', 'iso2' => 'FI', 'region' => 'Europe', 'flag_emoji' => '🇫🇮', 'display_order' => 18],
            
            // Europa Central y del Este
            ['name' => 'Poland', 'code' => 'POL', 'iso2' => 'PL', 'region' => 'Europe', 'flag_emoji' => '🇵🇱', 'display_order' => 19],
            ['name' => 'Czech Republic', 'code' => 'CZE', 'iso2' => 'CZ', 'region' => 'Europe', 'flag_emoji' => '🇨🇿', 'display_order' => 20],
            ['name' => 'Hungary', 'code' => 'HUN', 'iso2' => 'HU', 'region' => 'Europe', 'flag_emoji' => '🇭🇺', 'display_order' => 21],
            ['name' => 'Greece', 'code' => 'GRC', 'iso2' => 'GR', 'region' => 'Europe', 'flag_emoji' => '🇬🇷', 'display_order' => 22],
            
            // Oceanía
            ['name' => 'Australia', 'code' => 'AUS', 'iso2' => 'AU', 'region' => 'Oceania', 'flag_emoji' => '🇦🇺', 'display_order' => 23],
            ['name' => 'New Zealand', 'code' => 'NZL', 'iso2' => 'NZ', 'region' => 'Oceania', 'flag_emoji' => '🇳🇿', 'display_order' => 24],
            
            // Asia
            ['name' => 'Japan', 'code' => 'JPN', 'iso2' => 'JP', 'region' => 'Asia', 'flag_emoji' => '🇯🇵', 'display_order' => 25],
            ['name' => 'South Korea', 'code' => 'KOR', 'iso2' => 'KR', 'region' => 'Asia', 'flag_emoji' => '🇰🇷', 'display_order' => 26],
            ['name' => 'China', 'code' => 'CHN', 'iso2' => 'CN', 'region' => 'Asia', 'flag_emoji' => '🇨🇳', 'display_order' => 27],
            ['name' => 'Singapore', 'code' => 'SGP', 'iso2' => 'SG', 'region' => 'Asia', 'flag_emoji' => '🇸🇬', 'display_order' => 28],
            ['name' => 'Thailand', 'code' => 'THA', 'iso2' => 'TH', 'region' => 'Asia', 'flag_emoji' => '🇹🇭', 'display_order' => 29],
            ['name' => 'India', 'code' => 'IND', 'iso2' => 'IN', 'region' => 'Asia', 'flag_emoji' => '🇮🇳', 'display_order' => 30],
            
            // América del Sur
            ['name' => 'Brazil', 'code' => 'BRA', 'iso2' => 'BR', 'region' => 'South America', 'flag_emoji' => '🇧🇷', 'display_order' => 31],
            ['name' => 'Argentina', 'code' => 'ARG', 'iso2' => 'AR', 'region' => 'South America', 'flag_emoji' => '🇦🇷', 'display_order' => 32],
            ['name' => 'Chile', 'code' => 'CHL', 'iso2' => 'CL', 'region' => 'South America', 'flag_emoji' => '🇨🇱', 'display_order' => 33],
            ['name' => 'Colombia', 'code' => 'COL', 'iso2' => 'CO', 'region' => 'South America', 'flag_emoji' => '🇨🇴', 'display_order' => 34],
            ['name' => 'Peru', 'code' => 'PER', 'iso2' => 'PE', 'region' => 'South America', 'flag_emoji' => '🇵🇪', 'display_order' => 35],
            ['name' => 'Ecuador', 'code' => 'ECU', 'iso2' => 'EC', 'region' => 'South America', 'flag_emoji' => '🇪🇨', 'display_order' => 36],
            
            // América Central
            ['name' => 'Costa Rica', 'code' => 'CRI', 'iso2' => 'CR', 'region' => 'Central America', 'flag_emoji' => '🇨🇷', 'display_order' => 37],
            ['name' => 'Panama', 'code' => 'PAN', 'iso2' => 'PA', 'region' => 'Central America', 'flag_emoji' => '🇵🇦', 'display_order' => 38],
            
            // África
            ['name' => 'South Africa', 'code' => 'ZAF', 'iso2' => 'ZA', 'region' => 'Africa', 'flag_emoji' => '🇿🇦', 'display_order' => 39],
            ['name' => 'Morocco', 'code' => 'MAR', 'iso2' => 'MA', 'region' => 'Africa', 'flag_emoji' => '🇲🇦', 'display_order' => 40],
            ['name' => 'Egypt', 'code' => 'EGY', 'iso2' => 'EG', 'region' => 'Africa', 'flag_emoji' => '🇪🇬', 'display_order' => 41],
            
            // Medio Oriente
            ['name' => 'United Arab Emirates', 'code' => 'ARE', 'iso2' => 'AE', 'region' => 'Middle East', 'flag_emoji' => '🇦🇪', 'display_order' => 42],
            ['name' => 'Israel', 'code' => 'ISR', 'iso2' => 'IL', 'region' => 'Middle East', 'flag_emoji' => '🇮🇱', 'display_order' => 43],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['code' => $country['code']], // Buscar por código
                $country // Actualizar o crear con estos datos
            );
        }

        $this->command->info('✅ ' . count($countries) . ' países creados/actualizados exitosamente');
    }
}
