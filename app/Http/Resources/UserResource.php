<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
			'id'        =>  $this->id,
			'user_type' => $this->user_type,
			'name'       => $this->first_name . " " . $this->last_name,
			'email'      => $this->email,
			'phone'    => $this->phone,
			'is_active'    => $this->is_active,
		];
    }
}
