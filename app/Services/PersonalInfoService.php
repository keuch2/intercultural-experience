<?php

namespace App\Services;

use App\Models\Application;
use App\Models\City;
use App\Models\EmergencyContact;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\WorkExperience;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Formulario de información personal de Admisión (pedido de IE, nov 2026).
 *
 * - Precargado con lo que ya hay en `users`, contactos de emergencia y experiencia laboral.
 * - Todo obligatorio salvo la sección Laboral (habrá programas para menores sin experiencia).
 * - El participante lo confirma una vez por postulación (`applications.personal_info_confirmed_at`).
 * - Con `personal_info_required` activo (Configuración), sin confirmar no puede subir documentos, la etapa
 *   de Admisión del motor no avanza (guard `require_personal_info`) y la app lo muestra como próxima acción.
 */
class PersonalInfoService
{
    public const SETTING_KEY = 'personal_info_required';

    public const PENDING_MESSAGE = 'Antes de subir documentos completá y confirmá tu información personal.';

    public const MARITAL_STATUS_LABELS = ['single' => 'Soltero/a', 'married' => 'Casado/a', 'divorced' => 'Divorciado/a', 'widowed' => 'Viudo/a'];

    /** Secciones y campos obligatorios de `users` (en el orden del formulario). */
    public const SECTIONS = [
        'personal' => [
            'label' => 'Datos personales',
            'fields' => [
                'name' => 'Nombre y apellido', 'phone' => 'Celular', 'birth_date' => 'Fecha de nacimiento', 'gender' => 'Género',
                'nationality' => 'Nacionalidad', 'ci_number' => 'Cédula de identidad', 'marital_status' => 'Estado civil',
            ],
        ],
        'address' => [
            'label' => 'Domicilio',
            'fields' => ['country' => 'País', 'city' => 'Ciudad', 'address' => 'Dirección'],
        ],
        'academic' => [
            'label' => 'Información académica',
            'fields' => [
                'academic_level' => 'Nivel académico', 'university' => 'Institución educativa', 'career' => 'Carrera / orientación',
                'academic_year' => 'Año o curso', 'english_level' => 'Nivel de inglés',
            ],
        ],
        'health' => [
            'label' => 'Salud',
            'fields' => ['medical_conditions' => 'Condiciones médicas', 'allergies' => 'Alergias', 'medications' => 'Medicamentos actuales'],
        ],
        'emergency' => ['label' => 'Contacto de emergencia', 'fields' => []],
        'work' => ['label' => 'Experiencia laboral (opcional)', 'fields' => []],
    ];

    public const EMERGENCY_FIELDS = ['name', 'relationship', 'phone', 'alternative_phone', 'email'];

    public const WORK_FIELDS = ['company', 'position', 'start_date', 'end_date', 'is_current', 'description', 'reference_name', 'reference_phone', 'reference_email'];

    public static function isRequired(): bool
    {
        return (bool) SystemSetting::get(self::SETTING_KEY, false);
    }

    /** Datos actuales del participante para precargar el formulario. */
    public function data(User $user): array
    {
        $user->loadMissing(['emergencyContacts', 'workExperiences']);
        $data = [];
        foreach (self::SECTIONS as $section) {
            foreach (array_keys($section['fields']) as $field) {
                $data[$field] = $field === 'birth_date' ? $user->birth_date?->toDateString() : $user->{$field};
            }
        }
        $data['email'] = $user->email;
        $data['city_id'] = $user->city_id;
        $data['emergency_contacts'] = $user->emergencyContacts->sortByDesc('is_primary')->values()
            ->map(fn (EmergencyContact $c) => ['id' => $c->id] + $c->only(self::EMERGENCY_FIELDS))->all();
        $data['work_experiences'] = $user->workExperiences->values()->map(fn (WorkExperience $w) => [
            'id' => $w->id,
            'company' => $w->company, 'position' => $w->position,
            'start_date' => $w->start_date?->toDateString(), 'end_date' => $w->end_date?->toDateString(),
            'is_current' => (bool) $w->is_current, 'description' => $w->description,
            'reference_name' => $w->reference_name, 'reference_phone' => $w->reference_phone, 'reference_email' => $w->reference_email,
        ])->all();

        return $data;
    }

    /** @return array<string, string[]> campos obligatorios vacíos, por sección */
    public function missing(User $user): array
    {
        $user->loadMissing('emergencyContacts');
        $missing = [];
        foreach (self::SECTIONS as $key => $section) {
            foreach (array_keys($section['fields']) as $field) {
                if (blank($user->{$field})) {
                    $missing[$key][] = $field;
                }
            }
        }
        $complete = $user->emergencyContacts->first(fn ($c) => filled($c->name) && filled($c->relationship) && filled($c->phone));
        if (! $complete) {
            $missing['emergency'][] = 'emergency_contacts';
        }

        return $missing;
    }

    /** Estado para la app y el admin, relativo a una postulación (null = sin programa). */
    public function status(User $user, ?Application $application): array
    {
        $missing = $this->missing($user);
        $confirmedAt = $application?->personal_info_confirmed_at;

        return [
            'required' => self::isRequired(),
            'confirmed' => (bool) $confirmedAt,
            'confirmed_at' => $confirmedAt?->toIso8601String(),
            'complete' => $missing === [],
            'missing_sections' => array_keys($missing),
            // Pendiente = la app debe pedirlo antes de seguir.
            'pending' => self::isRequired() && $application !== null && ! $confirmedAt,
        ];
    }

    /** Bloquea la carga de documentos del participante hasta que confirme. */
    public function blocksParticipant(?Application $application): bool
    {
        return self::isRequired() && $application !== null && ! $application->personal_info_confirmed_at;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50', 'regex:/^[\+]?[\d\s\-\(\)]{7,}$/'],
            'birth_date' => ['required', 'date', 'before:today', 'after:1900-01-01'],
            'gender' => ['required', Rule::in(array_keys(User::GENDER_LABELS))],
            'nationality' => ['required', 'string', 'max:100'],
            'ci_number' => ['required', 'string', 'max:30'],
            'marital_status' => ['required', Rule::in(array_keys(self::MARITAL_STATUS_LABELS))],
            'country' => ['required', 'string', 'max:100'],
            'city_id' => ['nullable', 'integer', Rule::exists('cities', 'id')->where('is_active', true)],
            'city' => ['required_without:city_id', 'nullable', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'academic_level' => ['required', Rule::in(array_keys(User::ACADEMIC_LEVEL_LABELS))],
            'university' => ['required', 'string', 'max:255'],
            'career' => ['required', 'string', 'max:255'],
            'academic_year' => ['required', 'string', 'max:50'],
            'english_level' => ['required', Rule::in(array_keys(User::ENGLISH_LEVEL_LABELS))],
            'medical_conditions' => ['required', 'string', 'max:1000'],
            'allergies' => ['required', 'string', 'max:1000'],
            'medications' => ['required', 'string', 'max:1000'],
            'emergency_contacts' => ['required', 'array', 'min:1', 'max:3'],
            'emergency_contacts.*.id' => ['nullable', 'integer'],
            'emergency_contacts.*.name' => ['required', 'string', 'max:255'],
            'emergency_contacts.*.relationship' => ['required', 'string', 'max:100'],
            'emergency_contacts.*.phone' => ['required', 'string', 'max:50', 'regex:/^[\+]?[\d\s\-\(\)]{7,}$/'],
            'emergency_contacts.*.alternative_phone' => ['nullable', 'string', 'max:50'],
            'emergency_contacts.*.email' => ['nullable', 'email', 'max:255'],
            'work_experiences' => ['nullable', 'array', 'max:10'],
            'work_experiences.*.id' => ['nullable', 'integer'],
            'work_experiences.*.company' => ['required', 'string', 'max:255'],
            'work_experiences.*.position' => ['required', 'string', 'max:255'],
            'work_experiences.*.start_date' => ['required', 'date', 'before_or_equal:today'],
            'work_experiences.*.is_current' => ['nullable', 'boolean'],
            'work_experiences.*.end_date' => ['nullable', 'date', 'after_or_equal:work_experiences.*.start_date'],
            'work_experiences.*.description' => ['nullable', 'string', 'max:1000'],
            'work_experiences.*.reference_name' => ['nullable', 'string', 'max:255'],
            'work_experiences.*.reference_phone' => ['nullable', 'string', 'max:50'],
            'work_experiences.*.reference_email' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        $attributes = [];
        foreach (self::SECTIONS as $section) {
            $attributes += $section['fields'];
        }

        return $attributes + [
            'emergency_contacts' => 'contacto de emergencia',
            'emergency_contacts.*.name' => 'nombre del contacto', 'emergency_contacts.*.relationship' => 'relación',
            'emergency_contacts.*.phone' => 'teléfono del contacto', 'emergency_contacts.*.email' => 'email del contacto',
            'work_experiences.*.company' => 'empresa', 'work_experiences.*.position' => 'cargo',
            'work_experiences.*.start_date' => 'fecha de inicio', 'work_experiences.*.end_date' => 'fecha de fin',
            'work_experiences.*.reference_email' => 'email de la referencia',
        ];
    }

    /** Guarda el formulario completo (ya validado con rules()). */
    public function save(User $user, array $validated): User
    {
        DB::transaction(function () use ($user, $validated) {
            $fields = [];
            foreach (self::SECTIONS as $section) {
                foreach (array_keys($section['fields']) as $field) {
                    if (array_key_exists($field, $validated)) {
                        $fields[$field] = is_string($validated[$field]) ? trim($validated[$field]) : $validated[$field];
                    }
                }
            }
            $cityId = $validated['city_id'] ?? null;
            $fields['city_id'] = $cityId;
            if ($cityId) {
                $fields['city'] = City::find($cityId)?->name ?? ($fields['city'] ?? null);
            }
            $user->forceFill($fields)->save();

            $this->syncEmergencyContacts($user, $validated['emergency_contacts'] ?? []);
            $this->syncWorkExperiences($user, $validated['work_experiences'] ?? []);
        });

        return $user->refresh();
    }

    public function confirm(Application $application, ?User $actor = null): Application
    {
        $application->forceFill(['personal_info_confirmed_at' => now(), 'personal_info_confirmed_by' => $actor?->id])->save();

        return $application;
    }

    private function syncEmergencyContacts(User $user, array $rows): void
    {
        $keep = [];
        foreach (array_values($rows) as $i => $row) {
            $attrs = collect($row)->only(self::EMERGENCY_FIELDS)->all() + ['is_primary' => $i === 0];
            $contact = isset($row['id']) ? $user->emergencyContacts()->find($row['id']) : null;
            if ($contact) {
                $contact->update($attrs);
            } else {
                $contact = $user->emergencyContacts()->create($attrs);
            }
            $keep[] = $contact->id;
        }
        $user->emergencyContacts()->whereNotIn('id', $keep)->delete();
    }

    private function syncWorkExperiences(User $user, array $rows): void
    {
        $keep = [];
        foreach ($rows as $row) {
            $attrs = collect($row)->only(self::WORK_FIELDS)->all();
            $attrs['is_current'] = (bool) ($attrs['is_current'] ?? false);
            if ($attrs['is_current']) {
                $attrs['end_date'] = null;
            }
            $work = isset($row['id']) ? WorkExperience::where('user_id', $user->id)->find($row['id']) : null;
            if ($work) {
                $work->update($attrs);
            } else {
                $work = WorkExperience::create($attrs + ['user_id' => $user->id]);
            }
            $keep[] = $work->id;
        }
        WorkExperience::where('user_id', $user->id)->whereNotIn('id', $keep)->delete();
    }
}
