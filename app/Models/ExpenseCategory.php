<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExpenseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'color',
        'budget_limit',
        'requires_approval',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'budget_limit' => 'decimal:2',
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope para categorías activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para ordenar por sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Relación con transacciones financieras
     */
    public function transactions()
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    /**
     * Obtener solo transacciones de tipo expense
     */
    public function expenses()
    {
        return $this->hasMany(FinancialTransaction::class)->where('type', 'expense');
    }

    /**
     * Calcular total gastado en esta categoría
     */
    public function getTotalSpentAttribute()
    {
        return $this->expenses()->sum('amount_pyg') ?? 0;
    }

    /**
     * Calcular porcentaje del presupuesto utilizado
     */
    public function getBudgetUsagePercentageAttribute()
    {
        if (!$this->budget_limit || $this->budget_limit <= 0) {
            return null;
        }

        return ($this->total_spent / $this->budget_limit) * 100;
    }

    /**
     * Verificar si está sobre presupuesto
     */
    public function isOverBudget(): bool
    {
        if (!$this->budget_limit) {
            return false;
        }

        return $this->total_spent > $this->budget_limit;
    }

    /**
     * Obtener el balance restante del presupuesto
     */
    public function getRemainingBudgetAttribute()
    {
        if (!$this->budget_limit) {
            return null;
        }

        return $this->budget_limit - $this->total_spent;
    }

    /**
     * Método estático para obtener categorías para selects
     */
    public static function getForSelect()
    {
        return static::active()->ordered()->pluck('name', 'id');
    }

    /**
     * Obtener estadísticas de gastos por período
     */
    public function getExpenseStats(string $period = 'month', int $periods = 6): array
    {
        $stats = [];
        
        for ($i = $periods - 1; $i >= 0; $i--) {
            switch ($period) {
                case 'week':
                    $startDate = now()->subWeeks($i)->startOfWeek();
                    $endDate = now()->subWeeks($i)->endOfWeek();
                    $label = $startDate->format('M j') . ' - ' . $endDate->format('M j, Y');
                    break;
                case 'year':
                    $startDate = now()->subYears($i)->startOfYear();
                    $endDate = now()->subYears($i)->endOfYear();
                    $label = $startDate->format('Y');
                    break;
                default: // month
                    $startDate = now()->subMonths($i)->startOfMonth();
                    $endDate = now()->subMonths($i)->endOfMonth();
                    $label = $startDate->format('M Y');
                    break;
            }

            $total = $this->expenses()
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->sum('amount_pyg') ?? 0;

            $stats[] = [
                'period' => $label,
                'total' => $total,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ];
        }

        return $stats;
    }
}
