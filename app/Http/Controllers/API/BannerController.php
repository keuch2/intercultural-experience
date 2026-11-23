<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AppBanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Banners del Inicio de la app (gestionados en el admin).
 *  GET /api/public/banners → Inicio sin sesión.
 *  GET /api/banners        → Inicio del participante (según sus postulaciones).
 */
class BannerController extends Controller
{
    public function public(): JsonResponse
    {
        return $this->respond(AppBanner::query()->live()->forPublic()->get());
    }

    public function index(Request $request): JsonResponse
    {
        return $this->respond(AppBanner::query()->live()->forParticipant($request->user())->get());
    }

    private function respond($banners): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $banners->map->toApiArray()->values(),
        ]);
    }
}
