<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class FacilityServiceResource extends JsonResource
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
            'number' => $this->doc_number,
            'type' => $this->flow_type,
            'user_created' => $this->usersCreated,
            'user_updated' => $this->usersUpdated,
            'created' => $this->created_at,
            'status' => $this->status,
            'component_id' => json_decode($this->component_id),
            'component' => json_decode($this->component_name),
            'company'=> $this->company,
            'location' => $this->location,
            'details' => $this->request_description,
            'phone' => $this->phone,
            'room_type' => $this->room_type,
            'WO_type' => $this->WO_type,
            'contact_reason' => $this->contact_reason,
            'report' => $this->report,
            'service_item' => $this->serviceItem,
            'attachment_file' => $this->attachmentFiles,
        ];
    }
}
