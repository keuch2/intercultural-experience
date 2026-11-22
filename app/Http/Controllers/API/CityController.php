<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\JsonResponse;

/**
 * Catálogo público de ciudades (registro y perfil de la app). Sin autenticación: se usa antes de crear la cuenta.
 */
class CityController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => City::catalog()])
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
