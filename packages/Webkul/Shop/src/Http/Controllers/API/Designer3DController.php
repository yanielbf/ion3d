<?php

namespace Webkul\Shop\Http\Controllers\API;

use Webkul\Attribute\Repositories\AttributeFamilyRepository;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Product\Repositories\ProductRepository;
use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Shop\Http\Resources\AttributeResource;
use Webkul\Shop\Http\Resources\FamilyAttributeResource;
use Webkul\Shop\Http\Resources\ProductResource;
use Webkul\Checkout\Facades\Cart;
use Illuminate\Support\Facades\Event;
use Webkul\Shop\Http\Resources\CartResource;

class Designer3DController extends APIController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected AttributeFamilyRepository $attributeFamilyRepository,
        protected AttributeRepository $attributeRepository,
        protected ProductRepository $productRepository
    )
    {
    }

    /**
     * Get families attributes
     */
    public function get_families_attributes(): JsonResource
    {
        $code = request()->query('code');
        $familiesAttributes3D = $this->attributeFamilyRepository->getFamilyAttributes3D($code);
        return FamilyAttributeResource::collection($familiesAttributes3D);
    }

    /**
     * Get attributes
     */
    public function get_attributes_by_family(): JsonResource
    {
        $attributes = $this->attributeRepository->getDesign3DAttributes(request()->input('familyId'));
        return AttributeResource::collection($attributes);
    }


    /**
     * Get product by attributes
     */
    public function get_product_by_attributes(): ?JsonResource
    {
        $product = $this->productRepository->getOneFromDatabaseByAttributes();
        return !is_null($product) ? new ProductResource($product) : null;
    }
}
