<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ConfigResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'config_name' => $this->config_name,
            'config_value' => json_decode($this->config_value),
            'status' => $this->is_active,
            'facility' => $this->facility_name,
        ];
    }
}
