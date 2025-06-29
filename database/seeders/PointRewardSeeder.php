<?php

namespace Database\Seeders;

use App\Models\Point;
use App\Models\Reward;
use App\Models\User;
use Illuminate\Database\Seeder;

class PointRewardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear recompensas
        $rewards = [
            [
                'name' => 'Descuento en matrícula',
                'description' => 'Obtén un 10% de descuento en la matrícula de tu próximo programa de intercultural',
                'cost' => 500,
                'category' => 'Educación',
                'stock' => null,
                'status' => 'active',
            ],
            [
                'name' => 'Curso de idioma gratuito',
                'description' => 'Acceso a un curso online de idioma de 3 meses con certificación',
                'cost' => 300,
                'category' => 'Educación',
                'stock' => 20,
                'status' => 'active',
            ],
            [
                'name' => 'Seguro de viaje internacional',
                'description' => 'Seguro de viaje completo para tu programa de intercambio con cobertura mundial',
                'cost' => 400,
                'category' => 'Viajes',
                'stock' => null,
                'status' => 'active',
            ],
            [
                'name' => 'Tour cultural privado',
                'description' => 'Tour guiado privado por los principales puntos culturales de tu destino',
                'cost' => 200,
                'category' => 'Entretenimiento',
                'stock' => 10,
                'status' => 'active',
            ],
            [
                'name' => 'Kit de bienvenida premium',
                'description' => 'Kit exclusivo con productos de Intercultural Experience y souvenirs especiales',
                'cost' => 150,
                'category' => 'Otros',
                'stock' => 50,
                'status' => 'active',
            ],
            [
                'name' => 'Asesoría personalizada',
                'description' => 'Sesión de asesoría personalizada con un experto en intercambios culturales',
                'cost' => 250,
                'category' => 'Educación',
                'stock' => null,
                'status' => 'active',
            ],
            [
                'name' => 'Tarjeta SIM internacional',
                'description' => 'Tarjeta SIM con 5GB de datos para tu destino internacional',
                'cost' => 100,
                'category' => 'Electrónicos',
                'stock' => 30,
                'status' => 'active',
            ],
            [
                'name' => 'Alojamiento premium',
                'description' => 'Mejora a alojamiento premium durante la primera semana de tu programa',
                'cost' => 600,
                'category' => 'Viajes',
                'stock' => 5,
                'status' => 'active',
            ],
            [
                'name' => 'Audífonos Bluetooth',
                'description' => 'Audífonos inalámbricos de alta calidad para tus viajes',
                'cost' => 250,
                'category' => 'Electrónicos',
                'stock' => 15,
                'status' => 'active',
            ],
            [
                'name' => 'Mochila de viaje',
                'description' => 'Mochila resistente al agua perfecta para tus aventuras internacionales',
                'cost' => 180,
                'category' => 'Otros',
                'stock' => 25,
                'status' => 'active',
            ],
            [
                'name' => 'Voucher de restaurante',
                'description' => 'Cena gratis para dos personas en un restaurante local recomendado',
                'cost' => 120,
                'category' => 'Entretenimiento',
                'stock' => 40,
                'status' => 'active',
            ],
            [
                'name' => 'Tablet 10 pulgadas',
                'description' => 'Tablet de última generación para estudios y entretenimiento',
                'cost' => 800,
                'category' => 'Electrónicos',
                'stock' => 3,
                'status' => 'active',
            ],
        ];

        foreach ($rewards as $reward) {
            // Crear directamente sin preocuparnos por timestamps ya que están deshabilitados en el modelo
            Reward::create($reward);
        }

        // Asignar puntos a los usuarios
        $users = User::where('role', 'user')->get();
        $pointReasons = [
            'Registro en la plataforma' => 50,
            'Completar perfil' => 30,
            'Solicitud de programa' => 40,
            'Documento verificado' => 20,
            'Completar todos los requisitos' => 100,
            'Programa aprobado' => 150,
            'Referir a un amigo' => 50,
            'Participación en webinar' => 25,
            'Completar encuesta' => 15,
            'Compartir experiencia' => 35,
        ];

        foreach ($users as $user) {
            // Cada usuario tendrá entre 3 y 8 registros de puntos
            $numPointEntries = rand(3, 8);
            $totalPoints = 0;
            
            for ($i = 0; $i < $numPointEntries; $i++) {
                $reason = array_rand($pointReasons);
                $points = $pointReasons[$reason];
                $createdAt = now()->subDays(rand(1, 90));
                
                Point::create([
                    'user_id' => $user->id,
                    'change' => $points,
                    'reason' => $reason,
                    'created_at' => $createdAt,
                ]);
                
                $totalPoints += $points;
            }
            
            // Calculamos el total de puntos pero no lo guardamos en el usuario
            // ya que no existe la columna 'points' en la tabla users
            // En su lugar, podríamos calcular esto dinámicamente cuando sea necesario
        }
    }
}
