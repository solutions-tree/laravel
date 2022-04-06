<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MutualFundResource extends JsonResource
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
			'scheme_code'        =>  $this->code,
			'name'       => $this->scheme_name,
			'scheme_category' => $this->scheme_category,
			'title' =>"(" . $this->code . ") " . $this->scheme_name . " - " . $this->scheme_nav_name,
			'id' => $this->code,
			'pid' => $this->id,
			'scheme_nav_name' => $this->scheme_nav_name
			];
    }
}
