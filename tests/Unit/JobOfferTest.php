<?php

namespace Tests\Unit;

use App\Models\JobOffer;
use App\Models\Sponsor;
use App\Models\HostCompany;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobOfferTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que los cupos se gestionan correctamente
     */
    public function test_slot_management(): void
    {
        $sponsor = Sponsor::factory()->create();
        $company = HostCompany::factory()->create();

        $offer = JobOffer::create([
            'job_offer_id' => 'TEST-001',
            'sponsor_id' => $sponsor->id,
            'host_company_id' => $company->id,
            'position' => 'Test Position',
            'city' => 'Test City',
            'state' => 'Test State',
            'start_date' => now()->addMonth(),
            'end_date' => now()->addMonths(4),
            'total_slots' => 10,
            'available_slots' => 10,
            'salary_min' => 12.00,
            'salary_max' => 15.00,
            'status' => 'available',
        ]);

        // Test reservar cupo
        $offer->reserveSlot();
        $this->assertEquals(9, $offer->available_slots);
        $this->assertEquals('available', $offer->status);

        // Test liberar cupo
        $offer->releaseSlot();
        $this->assertEquals(10, $offer->available_slots);

        // Test marcar como lleno
        $offer->update(['available_slots' => 0]);
        $offer->reserveSlot();
        $this->assertEquals('full', $offer->fresh()->status);
    }

    /**
     * Test que el matching score se calcula correctamente
     */
    public function test_matching_score_calculation(): void
    {
        $sponsor = Sponsor::factory()->create();
        $company = HostCompany::factory()->create();

        $offer = JobOffer::create([
            'job_offer_id' => 'TEST-002',
            'sponsor_id' => $sponsor->id,
            'host_company_id' => $company->id,
            'position' => 'Server',
            'city' => 'Orlando',
            'state' => 'Florida',
            'start_date' => '2025-06-01',
            'end_date' => '2025-09-30',
            'total_slots' => 10,
            'available_slots' => 10,
            'salary_min' => 12.00,
            'salary_max' => 15.00,
            'required_english_level' => 'B1',
            'required_gender' => 'any',
            'status' => 'available',
        ]);

        $user = User::factory()->create([
            'gender' => 'female',
            'english_level' => 'B1',
        ]);

        $score = $offer->calculateMatchScore($user);

        // Score debería ser alto (inglés correcto + género match + disponibilidad)
        $this->assertGreaterThan(60, $score);
        $this->assertLessThanOrEqual(100, $score);
    }

    /**
     * Test que las ofertas disponibles se filtran correctamente
     */
    public function test_available_offers_scope(): void
    {
        $sponsor = Sponsor::factory()->create();
        $company = HostCompany::factory()->create();

        // Crear oferta disponible
        JobOffer::create([
            'job_offer_id' => 'TEST-003',
            'sponsor_id' => $sponsor->id,
            'host_company_id' => $company->id,
            'position' => 'Available Position',
            'city' => 'Miami',
            'state' => 'Florida',
            'start_date' => now()->addMonth(),
            'end_date' => now()->addMonths(4),
            'total_slots' => 10,
            'available_slots' => 5,
            'salary_min' => 12.00,
            'salary_max' => 15.00,
            'status' => 'available',
        ]);

        // Crear oferta llena
        JobOffer::create([
            'job_offer_id' => 'TEST-004',
            'sponsor_id' => $sponsor->id,
            'host_company_id' => $company->id,
            'position' => 'Full Position',
            'city' => 'Miami',
            'state' => 'Florida',
            'start_date' => now()->addMonth(),
            'end_date' => now()->addMonths(4),
            'total_slots' => 10,
            'available_slots' => 0,
            'salary_min' => 12.00,
            'salary_max' => 15.00,
            'status' => 'full',
        ]);

        $availableOffers = JobOffer::available()->get();
        $this->assertEquals(1, $availableOffers->count());
        $this->assertEquals('Available Position', $availableOffers->first()->position);
    }

    /**
     * Test que los filtros por ubicación funcionan
     */
    public function test_location_filters(): void
    {
        $sponsor = Sponsor::factory()->create();
        $company = HostCompany::factory()->create();

        JobOffer::create([
            'job_offer_id' => 'TEST-005',
            'sponsor_id' => $sponsor->id,
            'host_company_id' => $company->id,
            'position' => 'Florida Position',
            'city' => 'Orlando',
            'state' => 'Florida',
            'start_date' => now()->addMonth(),
            'end_date' => now()->addMonths(4),
            'total_slots' => 10,
            'available_slots' => 10,
            'salary_min' => 12.00,
            'salary_max' => 15.00,
            'status' => 'available',
        ]);

        JobOffer::create([
            'job_offer_id' => 'TEST-006',
            'sponsor_id' => $sponsor->id,
            'host_company_id' => $company->id,
            'position' => 'California Position',
            'city' => 'Los Angeles',
            'state' => 'California',
            'start_date' => now()->addMonth(),
            'end_date' => now()->addMonths(4),
            'total_slots' => 10,
            'available_slots' => 10,
            'salary_min' => 12.00,
            'salary_max' => 15.00,
            'status' => 'available',
        ]);

        $floridaOffers = JobOffer::byState('Florida')->get();
        $this->assertEquals(1, $floridaOffers->count());
        $this->assertEquals('Orlando', $floridaOffers->first()->city);

        $orlandoOffers = JobOffer::byCity('Orlando')->get();
        $this->assertEquals(1, $orlandoOffers->count());
    }
}
