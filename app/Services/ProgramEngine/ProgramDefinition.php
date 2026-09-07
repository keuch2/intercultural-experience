<?php

namespace App\Services\ProgramEngine;

use App\Models\Program;
use App\Models\ProgramChecklistItem;
use App\Models\ProgramDocumentRequirement;
use App\Models\ProgramPaymentGate;
use App\Models\ProgramStage;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Configuración cargada de un programa del motor. Único punto de entrada para
 * admin, API y evaluadores. Memoizada por request; invalidar con forget() al
 * guardar configuración.
 */
final class ProgramDefinition
{
    /** @var array<int, self> */
    private static array $memo = [];

    private function __construct(
        public readonly Program $program,
        private readonly Collection $stages,
        private readonly Collection $requirements,
        private readonly Collection $checklist,
        private readonly Collection $gates,
    ) {}

    public static function for(Program $program): self
    {
        if (isset(self::$memo[$program->id])) {
            return self::$memo[$program->id];
        }

        return self::$memo[$program->id] = new self(
            $program,
            $program->stages()->get(),
            $program->documentRequirements()->get(),
            $program->checklistItems()->get(),
            $program->paymentGates()->get(),
        );
    }

    public static function forget(Program|int|null $program = null): void
    {
        if ($program === null) {
            self::$memo = [];

            return;
        }
        unset(self::$memo[$program instanceof Program ? $program->id : $program]);
    }

    // ── Etapas ─────────────────────────────────────────────────────────
    /** @return Collection<int, ProgramStage> */
    public function stages(): Collection
    {
        return $this->stages;
    }

    /** Etapas no terminales, en orden. */
    public function workflowStages(): Collection
    {
        return $this->stages->where('is_terminal', false)->values();
    }

    public function terminalStage(): ?ProgramStage
    {
        return $this->stages->firstWhere('is_terminal', true);
    }

    public function stage(?string $key): ?ProgramStage
    {
        return $key === null ? null : $this->stages->firstWhere('key', $key);
    }

    public function firstStage(): ?ProgramStage
    {
        return $this->stages->first();
    }

    public function stageIndex(string $key): int
    {
        $idx = $this->stages->search(fn (ProgramStage $s) => $s->key === $key);

        return $idx === false ? -1 : $idx;
    }

    public function nextStage(string $key): ?ProgramStage
    {
        $idx = $this->stageIndex($key);

        return $idx < 0 ? null : $this->stages->get($idx + 1);
    }

    public function hasStages(): bool
    {
        return $this->stages->isNotEmpty();
    }

    // ── Documentos ─────────────────────────────────────────────────────
    /** @return Collection<int, ProgramDocumentRequirement> */
    public function requirements(?string $stageKey = null, ?string $groupKey = null, bool $activeOnly = true): Collection
    {
        return $this->requirements
            ->when($activeOnly, fn ($c) => $c->where('is_active', true))
            ->when($stageKey !== null, fn ($c) => $c->where('stage_key', $stageKey))
            ->when($groupKey !== null, fn ($c) => $c->filter(fn ($r) => $r->effective_group === $groupKey))
            ->sortBy('sort_order')
            ->values();
    }

    public function requirement(string $key): ?ProgramDocumentRequirement
    {
        return $this->requirements->firstWhere('key', $key);
    }

    /**
     * Grupos visuales (tabs de documentos) en orden de etapa y aparición.
     *
     * @return Collection<int, array{key:string,label:string,stage_key:string,unlock_gate_key:?string}>
     */
    public function groups(?string $stageKey = null): Collection
    {
        $order = $this->stages->pluck('key')->flip();

        return $this->requirements(null, null)
            ->when($stageKey !== null, fn ($c) => $c->where('stage_key', $stageKey))
            ->groupBy(fn ($r) => $r->effective_group)
            ->map(function (Collection $reqs, string $group) {
                $first = $reqs->sortBy('sort_order')->first();
                $stage = $this->stage($first->stage_key);
                $label = $group === $first->stage_key && $stage
                    ? $stage->label
                    : Str::headline(str_replace(['_', '-'], ' ', $group));

                return [
                    'key' => $group,
                    'label' => $label,
                    'stage_key' => $first->stage_key,
                    'unlock_gate_key' => $reqs->pluck('unlock_gate_key')->filter()->first(),
                    'sort_order' => $first->sort_order,
                ];
            })
            ->sortBy(fn ($g) => sprintf('%05d-%05d', $order[$g['stage_key']] ?? 999, $g['sort_order']))
            ->values();
    }

    // ── Checklist ──────────────────────────────────────────────────────
    /** @return Collection<int, ProgramChecklistItem> */
    public function checklist(?string $stageKey = null, bool $activeOnly = true): Collection
    {
        return $this->checklist
            ->when($activeOnly, fn ($c) => $c->where('is_active', true))
            ->when($stageKey !== null, fn ($c) => $c->where('stage_key', $stageKey))
            ->sortBy('sort_order')
            ->values();
    }

    public function checklistItem(string $key): ?ProgramChecklistItem
    {
        return $this->checklist->firstWhere('key', $key);
    }

    // ── Gates de pago ──────────────────────────────────────────────────
    /** @return Collection<int, ProgramPaymentGate> */
    public function gates(bool $activeOnly = true): Collection
    {
        return $this->gates
            ->when($activeOnly, fn ($c) => $c->where('is_active', true))
            ->sortBy('sort_order')
            ->values();
    }

    public function gate(?string $key): ?ProgramPaymentGate
    {
        return $key === null ? null : $this->gates->firstWhere('key', $key);
    }

    // ── Módulos y reglas ───────────────────────────────────────────────
    /** @return string[] */
    public function modules(): array
    {
        return ModuleCatalog::sanitize($this->program->modules ?? []);
    }

    public function hasModule(string $module): bool
    {
        return in_array($module, $this->modules(), true);
    }

    public function rule(string $key, mixed $default = null): mixed
    {
        return $this->program->rule($key, $default);
    }

    public function minEnglishLevel(): string
    {
        return (string) $this->rule('min_english_level', 'B1');
    }

    public function maxEnglishAttempts(): int
    {
        return (int) $this->rule('max_english_attempts', 3);
    }

    /** Payload del programa para el envelope / mobile. */
    public function toArray(): array
    {
        return [
            'id' => $this->program->id,
            'slug' => $this->program->slug,
            'name' => $this->program->name,
            'modules' => $this->modules(),
            'rules' => $this->program->rules ?? [],
            'onboarding' => $this->program->onboarding ?? null,
        ];
    }
}
