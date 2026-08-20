<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'employee_code' => $this->employee_code,
            'name' => $this->name,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'gender' => $this->gender,
            'contact_number' => $this->contact_number,
            'email' => $this->email,
            'employee_status' => $this->employee_status,
            'join_date_formatted' => $this->join_date_formatted,
            'dob_ad' => $this->dob_ad?->format('Y-m-d'),
            'dob_bs' => $this->dob_bs,
            'marital_status' => $this->marital_status,

            // Legal & Identity
            'citizenship_number' => $this->citizenship_number,
            'citizenship_issue_date' => $this->citizenship_issue_date,
            'citizenship_issue_place' => $this->citizenship_issue_place,
            'ssid' => $this->ssid,

            // Department & Designation
            'department_id' => $this->department_id,
            'department' => $this->whenLoaded('department', function () {
                return $this->department ? [
                    'id' => $this->department->id,
                    'name' => $this->department->name,
                ] : null;
            }),
            'designation_id' => $this->designation_id,
            'designation' => $this->whenLoaded('designation', function () {
                return $this->designation ? [
                    'id' => $this->designation->id,
                    'name' => $this->designation->name,
                    'rank' => $this->designation->rank,
                ] : null;
            }),
            'dp_rank' => $this->dp_rank,
            'rank' => $this->rank,

            // Tips & Financials
            'tips_status' => $this->tips_status,
            'tips_amount' => $this->tips_amount,
            'point_value' => $this->point_value,
            'tips_blank' => (bool) $this->tips_blank,
            'publish_tips' => (bool) $this->publish_tips,
            'tips_fixed' => (bool) $this->tips_fixed,

            // Related modules (loaded conditionally)
            'latest_suspension' => $this->whenLoaded('latestSuspension'),
            'suspensions' => $this->whenLoaded('suspensions'),
            'leaver_details' => $this->whenLoaded('leaver'),
            'termination_details' => $this->whenLoaded('terminatedEmployee'),
            'adjustments' => $this->whenLoaded('adjustments'),
            'tips_adjustments' => $this->whenLoaded('tipsAdjustment'),

            // Metadata / timestamps
            'is_incomplete' => (bool) $this->isIncomplete(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
