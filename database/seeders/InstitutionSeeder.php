<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Institution;

class InstitutionSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $institutions = [
            [
                'code' => 'IE',
                'name' => 'Intercultural Experience',
                'short_name' => 'IE Paraguay',
                'description' => 'Intercultural Experience es una organización dedicada a promover experiencias culturales y educativas internacionales, facilitando oportunidades transformadoras para jóvenes de todo el mundo.',
                'primary_color' => '#007bff',
                'secondary_color' => '#6c757d',
                'contact_email' => 'info@ie-paraguay.org',
                'contact_phone' => '+595 21 123 4567',
                'address' => 'Av. Brasilia 123, Asunción, Paraguay',
                'website_url' => 'https://ie-paraguay.org',
                'settings' => [
                    'theme' => 'blue',
                    'show_logo' => true,
                    'enable_notifications' => true,
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'code' => 'YFU',
                'name' => 'Youth For Understanding',
                'short_name' => 'YFU Paraguay',
                'description' => 'Youth For Understanding promueve la comprensión intercultural, el respeto mutuo y la responsabilidad social a través de programas de intercambio educativo para jóvenes, familias y comunidades.',
                'primary_color' => '#28a745',
                'secondary_color' => '#ffc107',
                'contact_email' => 'contacto@yfu.org.py',
                'contact_phone' => '+595 21 987 6543',
                'address' => 'Calle España 456, Asunción, Paraguay',
                'website_url' => 'https://yfu.org.py',
                'settings' => [
                    'theme' => 'green',
                    'show_logo' => true,
                    'enable_notifications' => true,
                    'default_program_duration' => 12,
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($institutions as $institutionData) {
            Institution::updateOrCreate(
                ['code' => $institutionData['code']],
                $institutionData
            );
        }

        $this->command->info('Instituciones creadas/actualizadas: IE y YFU');
    }
}
