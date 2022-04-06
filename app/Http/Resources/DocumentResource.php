<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
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
			'document_name'        =>  $this->document_name,
			'document_description'       => $this->document_description,
			'created_at' => $this->created_at,
			'document_id' => $this->id,
			'document_path' => env('APP_URL').'/storage/app/' . $this->document_file
			];
    }
}
 