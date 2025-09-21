<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReclamoLogResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'         => $this->id,
            'reclamo_id' => $this->reclamo_id,
            'old_status' => $this->old_status,
            'new_status' => $this->new_status,
            'changed_by' => $this->changed_by,
            'user'       => $this->whenLoaded('user', function () {
                return [
                    'id'    => $this->user->id,
                    'name'  => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'created_at' => optional($this->created_at)->toISOString(),
        ];
    }
}
