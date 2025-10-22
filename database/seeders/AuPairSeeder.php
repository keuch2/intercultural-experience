<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\AuPairProfile;
use App\Models\FamilyProfile;
use App\Models\AuPairMatch;
use App\Models\ChildcareExperience;
use App\Models\Reference;
use App\Models\HealthDeclaration;
use App\Models\EmergencyContact;
use Faker\Factory as Faker;

class AuPairSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Crear 10 perfiles de Au Pair
        $this->createAuPairs($faker);
        
        // Crear 15 familias host
        $this->createFamilies($faker);
        
        // Crear matches entre Au Pairs y Familias
        $this->createMatches();
        
        echo "✅ Au Pair Seeder completado:\n";
        echo "   - 10 perfiles Au Pair con datos completos\n";
        echo "   - 15 familias host\n";
        echo "   - 20 matches en diferentes estados\n";
        echo "   - 50+ experiencias con niños\n";
        echo "   - 30+ referencias\n";
    }
    
    /**
     * Crear perfiles de Au Pair con datos completos
     */
    private function createAuPairs($faker)
    {
        $nationalities = ['Argentina', 'Brazil', 'Colombia', 'Mexico', 'Peru', 'Chile', 'Ecuador', 'Uruguay'];
        
        for ($i = 1; $i <= 10; $i++) {
            // Crear usuario Au Pair
            $user = User::create([
                'name' => $faker->name,
                'email' => "aupair{$i}@test.com",
                'password' => bcrypt('password'),
                'role' => 'user',
                'phone' => $faker->phoneNumber,
                'nationality' => $faker->randomElement($nationalities),
                'birth_date' => $faker->dateTimeBetween('-26 years', '-18 years'),
                'address' => $faker->address,
                'city' => $faker->city,
                'country' => $faker->randomElement($nationalities),
                'academic_level' => $faker->randomElement(['bachiller', 'licenciatura', 'maestria']),
                'english_level' => $faker->randomElement(['basico', 'intermedio', 'avanzado']),
                'ci_number' => $faker->numerify('########'),
                'passport_number' => $faker->bothify('??######'),
                'passport_expiry' => $faker->dateTimeBetween('+1 year', '+5 years'),
                'marital_status' => $faker->randomElement(['single', 'married', 'divorced']),
                'skype' => $faker->userName,
                'instagram' => '@' . $faker->userName,
                'university' => $faker->company . ' University',
                'career' => $faker->randomElement(['Psychology', 'Education', 'Medicine', 'Engineering', 'Business']),
                'academic_year' => $faker->numberBetween(1, 5),
                'study_modality' => $faker->randomElement(['presencial', 'online', 'hybrid']),
                'has_been_to_usa' => $faker->boolean(30),
                'usa_times' => $faker->boolean(30) ? $faker->numberBetween(1, 3) : 0,
                'smoker' => $faker->boolean(20),
                'has_drivers_license' => $faker->boolean(70),
                'driving_years' => $faker->boolean(70) ? $faker->numberBetween(1, 8) : 0,
                'can_swim' => $faker->boolean(60),
                'first_aid_certified' => $faker->boolean(40),
                'cpr_certified' => $faker->boolean(30),
                'hobbies' => json_encode($faker->randomElements(['reading', 'sports', 'music', 'cooking', 'dancing', 'traveling'], 3)),
                'program_expectations' => $faker->paragraph,
                'email_verified_at' => now(),
            ]);
            
            // Crear perfil Au Pair
            $profile = AuPairProfile::create([
                'user_id' => $user->id,
                'photos' => json_encode([
                    'photos/aupair_' . $i . '_1.jpg',
                    'photos/aupair_' . $i . '_2.jpg',
                    'photos/aupair_' . $i . '_3.jpg',
                    'photos/aupair_' . $i . '_4.jpg',
                    'photos/aupair_' . $i . '_5.jpg',
                    'photos/aupair_' . $i . '_6.jpg',
                ]),
                'main_photo' => 'photos/aupair_' . $i . '_main.jpg',
                'video_presentation' => 'videos/aupair_' . $i . '_presentation.mp4',
                'video_duration' => $faker->numberBetween(60, 180),
                'dear_family_letter' => $this->generateDearFamilyLetter($faker, $user->name),
                'preferred_ages' => $faker->randomElement(['0-2', '3-5', '6-10', '0-2,3-5', '3-5,6-10', 'any']),
                'max_children' => $faker->numberBetween(2, 4),
                'ideal_family_description' => $faker->paragraph,
                'profile_status' => $faker->randomElement(['active', 'pending', 'matched']),
                'profile_complete' => true,
                'profile_views' => $faker->numberBetween(0, 100),
                'available_from' => $faker->dateTimeBetween('+1 month', '+3 months'),
                'commitment_months' => 12,
            ]);
            
            // Crear experiencias con niños (5-8 por perfil)
            $this->createChildcareExperiences($user, $faker, rand(5, 8));
            
            // Crear referencias (3-4 por perfil)
            $this->createReferences($user, $faker, rand(3, 4));
            
            // Crear declaración de salud
            $this->createHealthDeclaration($user, $faker);
            
            // Crear contactos de emergencia (2 por perfil)
            $this->createEmergencyContacts($user, $faker);
        }
    }
    
    /**
     * Crear familias host
     */
    private function createFamilies($faker)
    {
        $states = ['CA', 'NY', 'TX', 'FL', 'IL', 'PA', 'OH', 'GA', 'NC', 'MI', 'NJ', 'VA', 'WA', 'AZ', 'MA'];
        
        for ($i = 1; $i <= 15; $i++) {
            $numChildren = $faker->numberBetween(1, 4);
            $childrenAges = [];
            for ($j = 0; $j < $numChildren; $j++) {
                $childrenAges[] = $faker->numberBetween(0, 14);
            }
            
            FamilyProfile::create([
                'family_name' => $faker->lastName . ' Family',
                'parent1_name' => $faker->name,
                'parent2_name' => $faker->boolean(80) ? $faker->name : null,
                'email' => "family{$i}@test.com",
                'phone' => $faker->phoneNumber,
                'city' => $faker->city,
                'state' => $faker->randomElement($states),
                'country' => 'USA',
                'number_of_children' => $numChildren,
                'children_ages' => $childrenAges,
                'has_infants' => in_array(true, array_map(fn($age) => $age < 2, $childrenAges)),
                'has_special_needs' => $faker->boolean(15),
                'special_needs_detail' => $faker->boolean(15) ? $faker->sentence : null,
                'has_pets' => $faker->boolean(60),
                'pet_types' => $faker->boolean(60) ? $faker->randomElement(['1 dog', '2 cats', '1 dog and 1 cat']) : null,
                'smoking_household' => $faker->boolean(10),
                'required_gender' => $faker->randomElement(['female', 'male', 'any']),
                'drivers_license_required' => $faker->boolean(70),
                'swimming_required' => $faker->boolean(40),
                'weekly_stipend' => $faker->randomFloat(2, 195.75, 250),
                'education_fund' => $faker->randomElement([500, 750, 1000]),
                'additional_benefits' => $faker->randomElement([
                    'Use of car, gym membership',
                    'Travel opportunities, phone plan',
                    'Extra vacation days',
                    null
                ]),
            ]);
        }
    }
    
    /**
     * Crear matches entre Au Pairs y Familias
     */
    private function createMatches()
    {
        $auPairs = AuPairProfile::where('profile_status', 'active')->get();
        $families = FamilyProfile::all();
        
        // Crear 20 matches en diferentes estados
        for ($i = 0; $i < 20; $i++) {
            $auPair = $auPairs->random();
            $family = $families->random();
            
            // Verificar que no exista ya el match
            $exists = AuPairMatch::where('au_pair_profile_id', $auPair->id)
                ->where('family_profile_id', $family->id)
                ->exists();
                
            if (!$exists) {
                $statuses = [
                    ['au_pair_status' => 'pending', 'family_status' => 'pending'],
                    ['au_pair_status' => 'interested', 'family_status' => 'pending'],
                    ['au_pair_status' => 'pending', 'family_status' => 'interested'],
                    ['au_pair_status' => 'interested', 'family_status' => 'interested'],
                    ['au_pair_status' => 'not_interested', 'family_status' => 'interested'],
                ];
                
                $status = $statuses[array_rand($statuses)];
                $isMatched = ($status['au_pair_status'] === 'interested' && 
                             $status['family_status'] === 'interested' && 
                             rand(0, 1));
                
                AuPairMatch::create([
                    'au_pair_profile_id' => $auPair->id,
                    'family_profile_id' => $family->id,
                    'au_pair_status' => $status['au_pair_status'],
                    'family_status' => $status['family_status'],
                    'is_matched' => $isMatched,
                    'matched_at' => $isMatched ? now()->subDays(rand(1, 30)) : null,
                    'messages_count' => rand(0, 50),
                    'last_interaction' => now()->subDays(rand(0, 7)),
                    'video_calls_count' => rand(0, 5),
                ]);
                
                if ($isMatched) {
                    $auPair->update(['profile_status' => 'matched']);
                }
            }
        }
    }
    
    /**
     * Crear experiencias con niños
     */
    private function createChildcareExperiences($user, $faker, $count)
    {
        $types = ['babysitter', 'teacher', 'family', 'daycare', 'camp', 'other'];
        
        for ($i = 0; $i < $count; $i++) {
            $startDate = $faker->dateTimeBetween('-5 years', '-6 months');
            $endDate = $faker->dateTimeBetween($startDate, 'now');
            
            ChildcareExperience::create([
                'user_id' => $user->id,
                'experience_type' => $faker->randomElement($types),
                'ages_cared' => $faker->randomElement(['0-2', '3-5', '6-10', '11-14', '0-2,3-5', '6-10,11-14']),
                'duration_months' => $startDate->diff($endDate)->m + ($startDate->diff($endDate)->y * 12),
                'responsibilities' => $faker->paragraph,
                'cared_for_infants' => $faker->boolean(40),
                'special_needs_experience' => $faker->boolean(20),
                'special_needs_detail' => $faker->boolean(20) ? $faker->sentence : null,
                'reference_name' => $faker->name,
                'reference_phone' => $faker->phoneNumber,
                'reference_email' => $faker->email,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
        }
    }
    
    /**
     * Crear referencias
     */
    private function createReferences($user, $faker, $count)
    {
        $types = ['childcare', 'character', 'professional', 'academic'];
        
        for ($i = 0; $i < $count; $i++) {
            Reference::create([
                'user_id' => $user->id,
                'reference_type' => $faker->randomElement($types),
                'name' => $faker->name,
                'relationship' => $faker->randomElement(['Teacher', 'Employer', 'Family friend', 'Neighbor', 'Coach']),
                'phone' => $faker->phoneNumber,
                'email' => $faker->email,
                'organization' => $faker->company,
                'position' => $faker->jobTitle,
                'letter_content' => $faker->paragraphs(3, true),
                'verified' => $faker->boolean(70),
                'verification_date' => $faker->boolean(70) ? $faker->dateTimeBetween('-30 days', 'now') : null,
            ]);
        }
    }
    
    /**
     * Crear declaración de salud
     */
    private function createHealthDeclaration($user, $faker)
    {
        HealthDeclaration::create([
            'user_id' => $user->id,
            'has_diseases' => $faker->boolean(10),
            'diseases_detail' => $faker->boolean(10) ? $faker->sentence : null,
            'has_allergies' => $faker->boolean(30),
            'allergies_detail' => $faker->boolean(30) ? $faker->randomElement(['Pollen', 'Dust', 'Peanuts', 'Lactose']) : null,
            'has_dietary_restrictions' => $faker->boolean(20),
            'dietary_restrictions_detail' => $faker->boolean(20) ? $faker->randomElement(['Vegetarian', 'Vegan', 'Gluten-free']) : null,
            'has_learning_disabilities' => $faker->boolean(5),
            'has_physical_limitations' => $faker->boolean(5),
            'under_medical_treatment' => $faker->boolean(10),
            'takes_medication' => $faker->boolean(15),
            'medication_detail' => $faker->boolean(15) ? $faker->sentence : null,
            'can_lift_25_pounds' => $faker->boolean(95),
            'allergic_to_pets' => $faker->boolean(10),
            'declaration_date' => now(),
        ]);
    }
    
    /**
     * Crear contactos de emergencia
     */
    private function createEmergencyContacts($user, $faker)
    {
        $relationships = ['parent', 'sibling', 'spouse', 'friend'];
        
        for ($i = 0; $i < 2; $i++) {
            EmergencyContact::create([
                'user_id' => $user->id,
                'name' => $faker->name,
                'relationship' => $faker->randomElement($relationships),
                'phone' => $faker->phoneNumber,
                'email' => $faker->email,
                'address' => $faker->address,
                'is_primary' => $i === 0,
            ]);
        }
    }
    
    /**
     * Generar carta Dear Family
     */
    private function generateDearFamilyLetter($faker, $name)
    {
        $templates = [
            "Dear Host Family,\n\nMy name is {$name} and I am excited about the opportunity to become part of your family as an Au Pair. I have always loved working with children and believe this experience will be enriching for both of us.\n\n" . $faker->paragraph(3) . "\n\nI look forward to creating wonderful memories with your family!\n\nWarm regards,\n{$name}",
            
            "Dear Future Host Family,\n\nI am {$name}, and I am writing to express my enthusiasm about joining your family. Children have always been a big part of my life, and I am passionate about their development and well-being.\n\n" . $faker->paragraph(3) . "\n\nThank you for considering me as your Au Pair.\n\nBest wishes,\n{$name}",
            
            "Hello Dear Family,\n\nMy name is {$name}. I am thrilled at the possibility of becoming your Au Pair and experiencing American culture while caring for your children.\n\n" . $faker->paragraph(3) . "\n\nI can't wait to meet you all!\n\nSincerely,\n{$name}"
        ];
        
        return $templates[array_rand($templates)];
    }
}
