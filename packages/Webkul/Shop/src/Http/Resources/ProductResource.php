<?php

namespace Webkul\Shop\Http\Resources;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Product\Helpers\Review;

class ProductResource extends JsonResource
{
    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource)
    {
        $this->reviewHelper = app(Review::class);

        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $productTypeInstance = $this->getTypeInstance();

        $attributesValues = [];

        if($this->customizable) {
            $attributes = $this->attribute_family->custom_attributes->filter(function ($item) {
                return false !== stristr($item->code, '3d_');
            })->reduce(function($carry, $item) {
                $carry[strval($item->id)] = $item->code;
                return $carry;
            }, []);
			
			foreach ($attributes as $key => $value) {
                $aux = $this->attribute_values->firstWhere('attribute_id', $key);
				if(isset($aux))
                	$attributesValues[$value] = $aux['integer_value'];
            }
        }

        return [
            'id'          => $this->id,
            'sku'         => $this->sku,
            'name'        => $this->name,
            'description' => $this->description,
            'url_key'     => $this->url_key,
            'base_image'  => product_image()->getProductBaseImage($this),
            'images'      => product_image()->getGalleryImages($this),
            'is_new'      => (bool) $this->new,
            'is_featured' => (bool) $this->featured,
            'on_sale'     => (bool) $productTypeInstance->haveDiscount(),
            'is_saleable' => (bool) $productTypeInstance->isSaleable(),
            'is_wishlist' => (bool) auth()->guard()->user()?->wishlist_items
                ->where('channel_id', core()->getCurrentChannel()->id)
                ->where('product_id', $this->id)->count(),
            'min_price'   => core()->formatPrice($productTypeInstance->getMinimalPrice()),
            'prices'      => $productTypeInstance->getProductPrices(),
            'price_html'  => $productTypeInstance->getPriceHtml(),
            'avg_ratings' => round($this->reviewHelper->getAverageRating($this)),
            'design3d' => $this->design3d ? Storage::url($this->design3d) : null,
            'customizable' => $this->customizable,
            'attribute_family' => $this->attribute_family->code,
            'attributes_values_3d' => $attributesValues
        ];
    }
}
