@php
    $props = json_encode([
        'currency' => core()->getCurrentCurrency()->symbol,
        'urls' => [
            'get_families_attributes' => route('shop.designer_3d.get_families_attributes'),
            'get_attributes_by_family' => route('shop.designer_3d.get_attributes_by_family'),
            'get_product_by_attributes' => route('shop.designer_3d.get_product_by_attributes'),
            'add_item_to_cart' => route('shop.api.checkout.cart.store_design')
        ]
    ])
@endphp

<v-designer-3d
    :info="{{ $props }}"
/>