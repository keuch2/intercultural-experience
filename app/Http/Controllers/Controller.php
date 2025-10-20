<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="Intercultural Experience Platform API",
 *     version="1.0.0",
 *     description="API completa para la plataforma de intercambios culturales. Incluye autenticación, gestión de programas, aplicaciones, sistema de puntos y más.",
 *     @OA\Contact(
 *         email="developers@ie.org.py",
 *         name="IE Development Team"
 *     ),
 *     @OA\License(
 *         name="Proprietary",
 *         url="https://ie.org.py/terms"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="https://ie.org.py/api",
 *     description="Servidor de Producción"
 * )
 * 
 * @OA\Server(
 *     url="https://staging.ie.org.py/api",
 *     description="Servidor de Staging"
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Servidor de Desarrollo Local"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Laravel Sanctum token authentication"
 * )
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="Operaciones de autenticación y gestión de sesiones"
 * )
 * 
 * @OA\Tag(
 *     name="Programs",
 *     description="Gestión de programas de intercambio"
 * )
 * 
 * @OA\Tag(
 *     name="Applications",
 *     description="Gestión de aplicaciones a programas"
 * )
 * 
 * @OA\Tag(
 *     name="Users",
 *     description="Gestión de usuarios y perfiles"
 * )
 * 
 * @OA\Tag(
 *     name="Points & Rewards",
 *     description="Sistema de gamificación y recompensas"
 * )
 * 
 * @OA\Tag(
 *     name="Forms",
 *     description="Formularios dinámicos y submissions"
 * )
 * 
 * @OA\Tag(
 *     name="Support",
 *     description="Sistema de tickets de soporte"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
