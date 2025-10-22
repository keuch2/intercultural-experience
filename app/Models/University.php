<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class University extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'university_name',
        'code',
        'type',
        'description',
        'website',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'main_phone',
        'admissions_email',
        'admissions_phone',
        'international_office_email',
        'international_office_phone',
        'founded_year',
        'total_students',
        'international_students',
        'undergraduate_programs',
        'graduate_programs',
        'degree_types_offered',
        'accreditation',
        'us_news_ranking',
        'qs_world_ranking',
        'acceptance_rate',
        'graduation_rate',
        'min_gpa_undergraduate',
        'min_gpa_graduate',
        'min_toefl_score',
        'min_ielts_score',
        'min_sat_score',
        'min_gre_score',
        'tuition_undergraduate_annual',
        'tuition_graduate_annual',
        'room_board_annual',
        'books_supplies_annual',
        'estimated_total_annual',
        'offers_scholarships',
        'offers_financial_aid',
        'offers_work_study',
        'avg_scholarship_amount',
        'scholarships_available',
        'campus_size_acres',
        'has_on_campus_housing',
        'has_library',
        'has_sports_facilities',
        'has_health_center',
        'has_career_services',
        'popular_majors',
        'language_programs',
        'offers_esl',
        'offers_pathway_programs',
        'offers_online_programs',
        'application_deadlines',
        'application_fee',
        'accepts_common_app',
        'application_portal_url',
        'has_international_office',
        'offers_orientation',
        'offers_pickup_service',
        'provides_visa_support',
        'is_partner_university',
        'years_partnership',
        'students_placed_total',
        'students_current',
        'is_verified',
        'verification_date',
        'verified_by',
        'is_active',
        'rating',
        'total_reviews',
        'brochure_path',
        'catalog_path',
        'campus_photos',
    ];

    protected $casts = [
        'founded_year' => 'integer',
        'total_students' => 'integer',
        'international_students' => 'integer',
        'undergraduate_programs' => 'integer',
        'graduate_programs' => 'integer',
        'degree_types_offered' => 'array',
        'us_news_ranking' => 'integer',
        'qs_world_ranking' => 'integer',
        'acceptance_rate' => 'decimal:2',
        'graduation_rate' => 'decimal:2',
        'min_gpa_undergraduate' => 'decimal:2',
        'min_gpa_graduate' => 'decimal:2',
        'min_toefl_score' => 'integer',
        'min_ielts_score' => 'decimal:1',
        'min_sat_score' => 'integer',
        'min_gre_score' => 'integer',
        'tuition_undergraduate_annual' => 'decimal:2',
        'tuition_graduate_annual' => 'decimal:2',
        'room_board_annual' => 'decimal:2',
        'books_supplies_annual' => 'decimal:2',
        'estimated_total_annual' => 'decimal:2',
        'offers_scholarships' => 'boolean',
        'offers_financial_aid' => 'boolean',
        'offers_work_study' => 'boolean',
        'avg_scholarship_amount' => 'decimal:2',
        'scholarships_available' => 'integer',
        'campus_size_acres' => 'integer',
        'has_on_campus_housing' => 'boolean',
        'has_library' => 'boolean',
        'has_sports_facilities' => 'boolean',
        'has_health_center' => 'boolean',
        'has_career_services' => 'boolean',
        'popular_majors' => 'array',
        'language_programs' => 'array',
        'offers_esl' => 'boolean',
        'offers_pathway_programs' => 'boolean',
        'offers_online_programs' => 'boolean',
        'application_deadlines' => 'array',
        'application_fee' => 'decimal:2',
        'accepts_common_app' => 'boolean',
        'has_international_office' => 'boolean',
        'offers_orientation' => 'boolean',
        'offers_pickup_service' => 'boolean',
        'provides_visa_support' => 'boolean',
        'is_partner_university' => 'boolean',
        'years_partnership' => 'integer',
        'students_placed_total' => 'integer',
        'students_current' => 'integer',
        'is_verified' => 'boolean',
        'verification_date' => 'datetime',
        'is_active' => 'boolean',
        'rating' => 'decimal:1',
        'total_reviews' => 'integer',
        'campus_photos' => 'array',
    ];

    /**
     * Relationships
     */
    public function applications()
    {
        return $this->hasMany(HigherEducationApplication::class);
    }

    public function scholarships()
    {
        return $this->hasMany(Scholarship::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePartner($query)
    {
        return $query->where('is_partner_university', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByState($query, $state)
    {
        return $query->where('state', $state);
    }

    /**
     * Accessors
     */
    public function getInternationalPercentageAttribute()
    {
        if (!$this->total_students || !$this->international_students) {
            return 0;
        }
        return round(($this->international_students / $this->total_students) * 100, 1);
    }

    public function getTypeNameAttribute()
    {
        return match($this->type) {
            'public' => 'Public University',
            'private' => 'Private University',
            'community_college' => 'Community College',
            'technical' => 'Technical Institute',
            default => $this->type,
        };
    }

    /**
     * Helper Methods
     */
    public function meetsGpaRequirement($gpa, $level = 'undergraduate')
    {
        $required = $level === 'undergraduate' 
            ? $this->min_gpa_undergraduate 
            : $this->min_gpa_graduate;
            
        return $gpa >= ($required ?? 0);
    }

    public function meetsEnglishRequirement($toefl = null, $ielts = null)
    {
        if ($toefl && $this->min_toefl_score) {
            return $toefl >= $this->min_toefl_score;
        }
        
        if ($ielts && $this->min_ielts_score) {
            return $ielts >= $this->min_ielts_score;
        }
        
        return false;
    }

    public function hasAvailableScholarships()
    {
        return $this->scholarships()
            ->where('is_active', true)
            ->where('awards_remaining', '>', 0)
            ->exists();
    }
}
