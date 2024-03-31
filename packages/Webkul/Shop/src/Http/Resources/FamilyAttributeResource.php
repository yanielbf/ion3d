<?php

namespace Webkul\Shop\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FamilyAttributeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'      => $this->id,
            'code'    => $this->code,
            'status'    => $this->status,
            'name'    => $this->name ?? $this->admin_name,
            //'options' => AttributeOptionResource::collection($this->options),
        ];
    }
}
