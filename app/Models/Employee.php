<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'employee_code';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_code',
        'department_id',
        'designation_id',
        'dp_rank',
        'rank',
        'name',
        'gender',
        'join_date_formatted',
        'contact_number',
        'email',
        'citizenship_number',
        'citizenship_issue_date',
        'citizenship_issue_place',
        'ssid',
        'dob_ad',
        'dob_bs',
        'marital_status',
        'employee_status',
        'tips_amount',
        'tips_status',
        'point_value',
        'tips_blank',
        'publish_tips',
        'tips_fixed',
        'hrms_password',
        'first_name',
        'middle_name',
        'last_name',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dob_ad' => 'date',
            'tips_amount' => 'decimal:2',
            'point_value' => 'decimal:4',
            'tips_blank' => 'boolean',
            'publish_tips' => 'boolean',
            'tips_fixed' => 'boolean',
        ];
    }

    /**
     * Get the department that the employee belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the designation that the employee belongs to.
     */
    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    /**
     * Get all of the adjustments for the Employee
     */
    public function adjustments(): HasMany
    {
        return $this->hasMany(Adjustment::class);
    }

    /**
     * Get the leaver record associated with the Employee.
     */
    public function leaver(): HasOne
    {
        return $this->hasOne(Leaver::class, 'employee_id', 'employee_code');
    }

    /**
     * Get the tips adjustment record associated with the Employee.
     */
    public function tipsAdjustment(): HasMany
    {
        return $this->hasMany(TipsAdjustment::class, 'employee_id', 'employee_code');
    }

    /**
     * Check if the employee is incomplete.
     */
    public function isIncomplete()
    {
        $checkFields = [
            'designation_id',
            'name',
            'gender',
            'join_date_formatted',
            'contact_number',
            'email',
            'citizenship_number',
            'citizenship_issue_date',
            'citizenship_issue_place',
            'dob_ad',
            'marital_status',
            'tips_amount',
            'point_value',
        ];

        $incompleteFields = collect($checkFields)->filter(function ($field) {
            return $this->{$field} == null;
        })->values();

        return $incompleteFields->count() > 0 ? $incompleteFields : false;
    }

    /**
     * Scope a query to only include incomplete employees.
     */
    public function scopeIsIncomplete(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNull('designation_id')
                ->orWhereNull('name')
                ->orWhereNull('gender')
                ->orWhereNull('join_date_formatted')
                ->orWhereNull('contact_number')
                ->orWhereNull('email')
                ->orWhereNull('citizenship_number')
                ->orWhereNull('citizenship_issue_date')
                ->orWhereNull('citizenship_issue_place')
                ->orWhereNull('dob_ad')
                ->orWhereNull('marital_status')
                ->orWhereNull('tips_amount')
                ->orWhereNull('point_value');
        });
    }
}
