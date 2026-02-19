<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AuPairProcess extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'application_id',
        'user_id',
        'enrollment_date',
        'enrollment_city',
        'enrollment_country',
        'current_stage',
        'admission_status',
        'application_status',
        'match_visa_status',
        'support_status',
        'welcome_email_sent',
        'interview_process_email_sent',
        'all_docs_and_payments_complete',
        'itep_completed',
        'contract_signed',
        'contract_signed_at',
        'contract_confirmed_by',
        'payment_1_verified',
        'payment_2_verified',
        'finalization_result',
        'finalization_reason',
        'finalization_date',
        'finalized_by',
        'notes',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'contract_signed_at' => 'datetime',
        'welcome_email_sent' => 'boolean',
        'interview_process_email_sent' => 'boolean',
        'all_docs_and_payments_complete' => 'boolean',
        'itep_completed' => 'boolean',
        'contract_signed' => 'boolean',
        'payment_1_verified' => 'boolean',
        'payment_2_verified' => 'boolean',
        'finalization_date' => 'date',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contractConfirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contract_confirmed_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AuPairDocument::class);
    }

    public function admissionDocuments(): HasMany
    {
        return $this->documents()->where('stage', 'admission');
    }

    public function payment1Documents(): HasMany
    {
        return $this->documents()->where('stage', 'application_payment1');
    }

    public function payment2Documents(): HasMany
    {
        return $this->documents()->where('stage', 'application_payment2');
    }

    public function visaDocuments(): HasMany
    {
        return $this->documents()->where('stage', 'visa');
    }

    public function englishTests(): HasMany
    {
        return $this->hasMany(AuPairEnglishTest::class);
    }

    public function visaProcess(): HasOne
    {
        return $this->hasOne(AuPairVisaProcess::class);
    }

    public function matchesExtended(): HasMany
    {
        return $this->hasMany(AuPairMatchExtended::class)->orderBy('sort_order');
    }

    public function supportLogs(): HasMany
    {
        return $this->hasMany(AuPairSupportLog::class)->orderByDesc('log_date');
    }

    /**
     * Check if admission docs (required) are all approved
     */
    public function admissionDocsApproved(): bool
    {
        $required = $this->documents()
            ->where('stage', 'admission')
            ->where('is_required', true)
            ->get();

        if ($required->isEmpty()) {
            return false;
        }

        return $required->every(fn($doc) => $doc->status === 'approved');
    }

    /**
     * Check if process can advance from admission to application
     */
    public function canAdvanceToApplication(): bool
    {
        return $this->admissionDocsApproved();
    }

    /**
     * Check if process can advance from application to match/visa
     */
    public function canAdvanceToMatchVisa(): bool
    {
        return $this->application_status === 'approved'
            && $this->payment_1_verified
            && $this->contract_signed;
    }

    /**
     * Advance to next stage
     */
    public function advanceStage(): bool
    {
        switch ($this->current_stage) {
            case 'admission':
                if (!$this->canAdvanceToApplication()) return false;
                $this->update([
                    'current_stage' => 'application',
                    'admission_status' => 'approved',
                    'application_status' => 'pending',
                ]);
                return true;

            case 'application':
                if (!$this->canAdvanceToMatchVisa()) return false;
                $this->update([
                    'current_stage' => 'match_visa',
                    'application_status' => 'approved',
                    'match_visa_status' => 'pending',
                ]);
                return true;

            case 'match_visa':
                $this->update([
                    'current_stage' => 'support',
                    'match_visa_status' => 'approved',
                    'support_status' => 'active',
                ]);
                return true;

            default:
                return false;
        }
    }

    // Scopes
    public function scopeAtStage($query, string $stage)
    {
        return $query->where('current_stage', $stage);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('current_stage', ['completed', 'cancelled']);
    }
}
