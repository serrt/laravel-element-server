<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
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
            'id' => $this->id,
            'log_name' => $this->log_name,
            'description' => $this->description,

            'subject_id' => $this->subject_id,
            'subject_type' => $this->subject_type,
            'subject' => $this->whenLoaded('subject'),

            'causer_id' => $this->causer_id,
            'causer_type' => $this->causer_type,
            'causer' => AdminUserResource::make($this->whenLoaded('causer')),

            'properties' => $this->properties,
            'created_at' => $this->created_at->timestamp
        ];
    }
}
