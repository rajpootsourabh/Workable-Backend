<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LegalDocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
{
    return [
        // 'id' => $this->id,
        'social_security_number' => $this->social_security_number,
        'issue_date_s_s_n' => $this->issue_date_s_s_n,
        'ssn_file' => $this->ssn_file,
        'national_id' => $this->national_id,
        'issue_date_national_id' => $this->issue_date_national_id,
        'national_id_file' => $this->national_id_file,
        'social_insurance_number' => $this->social_insurance_number,
        'tax_id' => $this->tax_id,
        'issue_date_tax_id' => $this->issue_date_tax_id,
        'tax_id_file' => $this->tax_id_file,
        'citizenship' => $this->citizenship,
        'nationality' => $this->nationality,
        'passport' => $this->passport,
        'work_visa' => $this->work_visa,
        'visa_details' => $this->visa_details,
    ];
}

}
