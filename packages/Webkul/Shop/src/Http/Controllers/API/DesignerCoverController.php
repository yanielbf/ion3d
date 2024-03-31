<?php

namespace Webkul\Shop\Http\Controllers\API;

use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Admin\Http\Resources\AttributeResource;

class DesignerCoverController extends APIController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected AttributeRepository $attributeRepository)
    {
    }

    /**
     * Customer addresses.
     */
    public function get_attributes_dc(): JsonResource
    {
        $attributes = $this->attributeRepository->getDesignCoverAttributes();
        return AttributeResource::collection($attributes);
    }
}
