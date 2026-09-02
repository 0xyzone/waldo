<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Candidate extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone_number',
        'dob_ad',
        'dob_bs',
        'cv_image',
        'reference',
        'department_id',
        'status',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dob_ad' => 'date',
            'cv_image' => 'array',
            'department_id' => 'integer',
        ];
    }

    /**
     * Get the department that owns the Candidate.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the full public URLs for all uploaded CV images.
     *
     * @return list<string>
     */
    public function getCvImageUrls(): array
    {
        $images = $this->cv_image;

        if (empty($images)) {
            return [];
        }

        if (is_string($images)) {
            $images = json_decode($images, true) ?? [$images];
        }

        if (! is_array($images)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($path): ?string {
            if (empty($path)) {
                return null;
            }

            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }

            return Storage::disk('public')->url($path);
        }, $images)));
    }
}
