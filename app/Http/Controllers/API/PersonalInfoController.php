<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\User;
use App\Services\PersonalInfoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Formulario de información personal de Admisión (app).
 *  GET  /api/me/personal-info?application_id=  → datos precargados + estado de confirmación.
 *  PUT  /api/me/personal-info                   → guarda todo; con `application_id` lo confirma para esa postulación.
 */
class PersonalInfoController extends Controller
{
    public function __construct(private readonly PersonalInfoService $service) {}

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $application = $this->application($user, $request->input('application_id'));

        return $this->respond($user, $application);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        $validator = Validator::make($request->all(), $this->service->rules() + ['application_id' => ['nullable', 'integer']], [
            'phone.regex' => 'Ingresá un celular válido.',
            'emergency_contacts.*.phone.regex' => 'Ingresá un teléfono válido.',
            'emergency_contacts.required' => 'Agregá al menos un contacto de emergencia.',
            'city.required_without' => 'Elegí tu ciudad.',
        ], $this->service->attributes());

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Revisá los campos marcados.', 'errors' => $validator->errors()], 422);
        }

        $application = $this->application($user, $request->input('application_id'));
        if ($request->filled('application_id') && ! $application) {
            return response()->json(['status' => 'error', 'message' => 'Postulación no encontrada.'], 404);
        }

        $this->service->save($user, $validator->validated());
        if ($application) {
            $this->service->confirm($application, $user);
        }

        return $this->respond($user->refresh(), $application?->refresh(), 'Información personal guardada.');
    }

    private function application(User $user, $id): ?Application
    {
        if (! $id) {
            return null;
        }

        return Application::where('user_id', $user->id)->visibleToParticipant()->find((int) $id);
    }

    private function respond(User $user, ?Application $application, ?string $message = null): JsonResponse
    {
        $options = fn (array $labels) => collect($labels)->map(fn ($label, $value) => ['value' => $value, 'label' => $label])->values();

        return response()->json(array_filter([
            'status' => 'success',
            'message' => $message,
            'data' => $this->service->data($user),
            'personal_info' => $this->service->status($user, $application),
            'sections' => collect(PersonalInfoService::SECTIONS)->map(fn ($s, $key) => ['key' => $key, 'label' => $s['label']])->values(),
            'options' => [
                'gender' => $options(User::GENDER_LABELS),
                'marital_status' => $options(PersonalInfoService::MARITAL_STATUS_LABELS),
                'academic_level' => $options(User::ACADEMIC_LEVEL_LABELS),
                'english_level' => $options(User::ENGLISH_LEVEL_LABELS),
            ],
        ], fn ($v) => $v !== null));
    }
}
