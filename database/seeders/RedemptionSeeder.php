<?php

namespace Database\Seeders;

use App\Models\Redemption;
use App\Models\Reward;
use App\Models\User;
use Illuminate\Database\Seeder;

class RedemptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', 'user')->get();
        $rewards = Reward::where('is_active', true)->get();
        $statuses = ['pending', 'approved', 'rejected'];
        
        // Algunos usuarios canjearán recompensas
        foreach ($users as $user) {
            // No todos los usuarios tendrán canjes
            if (rand(0, 100) < 70) {
                // Cada usuario que canjea tendrá entre 1 y 3 canjes
                $numRedemptions = rand(1, 3);
                
                for ($i = 0; $i < $numRedemptions; $i++) {
                    $reward = $rewards->random();
                    
                    // Asumimos que el usuario tiene suficientes puntos para el canje
                    // ya que no existe la columna 'points' en la tabla users
                    {
                        $status = $statuses[array_rand($statuses)];
                        
                        $requestedAt = now()->subDays(rand(1, 30));
                        $resolvedAt = null;
                        
                        if ($status !== 'pending') {
                            $resolvedAt = (clone $requestedAt)->addDays(rand(1, 7));
                        }
                        
                        // Deshabilitar timestamps automáticos ya que la tabla no los usa
                        Redemption::create([
                            'user_id' => $user->id,
                            'reward_id' => $reward->id,
                            'status' => $status,
                            'requested_at' => $requestedAt,
                            'resolved_at' => $resolvedAt,
                        ]);
                        
                        // En un caso real, aquí restaríamos puntos al usuario si el canje fue aprobado
                        // pero como no tenemos la columna 'points' en la tabla users, lo omitimos
                    }
                }
            }
        }
    }
}
