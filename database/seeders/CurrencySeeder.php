<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Currency;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            [
                'code' => 'PYG',
                'name' => 'Guaraní Paraguayo',
                'symbol' => '₲',
                'exchange_rate_to_pyg' => 1.0000, // Base currency
                'is_active' => true,
            ],
            [
                'code' => 'USD',
                'name' => 'Dólar Estadounidense',
                'symbol' => '$',
                'exchange_rate_to_pyg' => 7300.0000, // Ejemplo: 1 USD = 7,300 PYG
                'is_active' => true,
            ],
            [
                'code' => 'EUR',
                'name' => 'Euro',
                'symbol' => '€',
                'exchange_rate_to_pyg' => 8000.0000, // Ejemplo: 1 EUR = 8,000 PYG
                'is_active' => true,
            ],
            [
                'code' => 'BRL',
                'name' => 'Real Brasileño',
                'symbol' => 'R$',
                'exchange_rate_to_pyg' => 1400.0000, // Ejemplo: 1 BRL = 1,400 PYG
                'is_active' => true,
            ],
            [
                'code' => 'ARS',
                'name' => 'Peso Argentino',
                'symbol' => '$',
                'exchange_rate_to_pyg' => 7.5000, // Ejemplo: 1 ARS = 7.5 PYG
                'is_active' => true,
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }
    }
}
