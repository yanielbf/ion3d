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
        $familiesAttributes3D = $this->attributeFamilyRepository->getFamilyAttributes3D();
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

    /**
     * Get product by attributes
     */
    public function add_item_to_cart(): ?JsonResource
    {
        $this->validate(request(), [
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $data['product_id'] = request()->input('product_id');
        $data['quantity'] = intval(request()->input('quantity'));
        $hash = request()->input('hash');

        if(isset($hash)) {
            $design = request()->input('design')[$hash];
            $image = request()->file('image');
            $image->storeAs('covers', $design['filename']);
            $data['designs'] = [
                $hash => [
                    'quantity' => intval($design['quantity']),
                    'filename' => $design['filename'],
                ]
            ];
        }

        try {
            $product = $this->productRepository->with('parent')->find(request()->input('product_id'));

            if (request()->get('is_buy_now')) {
                Cart::deActivateCart();
            }

            $cart = Cart::addProduct($product->id, $data);

            /**
             * To Do (@devansh-webkul): Need to check this and improve cart facade.
             */
            if (is_array($cart) && isset($cart['warning'])) {
                return new JsonResource([
                    'message' => $cart['warning'],
                ]);
            }

            if ($cart) {
                if ($customer = auth()->guard('customer')->user()) {
                    // $this->wishlistRepository->deleteWhere([
                    //     'product_id'  => $product->id,
                    //     'customer_id' => $customer->id,
                    // ]);
                }

                if (request()->get('is_buy_now')) {
                    Event::dispatch('shop.item.buy-now', request()->input('product_id'));

                    return new JsonResource([
                        'data'     => new CartResource(Cart::getCart()),
                        'redirect' => route('shop.checkout.onepage.index'),
                        'message'  => trans('shop::app.checkout.cart.item-add-to-cart'),
                    ]);
                }

                return new JsonResource([
                    'data'     => new CartResource(Cart::getCart()),
                    'message'  => trans('shop::app.checkout.cart.item-add-to-cart'),
                ]);
            }
        } catch (\Exception $exception) {
            return new JsonResource([
                'redirect_uri' => route('shop.product_or_category.index', $product->url_key),
                'message'      => $exception->getMessage(),
            ]);
        }
    }
}
