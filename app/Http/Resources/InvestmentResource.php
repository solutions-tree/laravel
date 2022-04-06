<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvestmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
			'investment_amt'        =>  $this->investment_amt,
			'investment_date'       => $this->investment_date,
			'scheme_code' => $this->scheme_code,
			'folio_number' => $this->folio_number,
			'scheme_name' => $this->scheme_nav_name,
			'investment_unit' => $this->investment_unit,
			'nav' => number_format((float)$this->nav, 2, '.', ''), 
			'current_value' => number_format((float) $this->nav * (float) $this->investment_unit, 2, '.', '')
			];
    }
}
