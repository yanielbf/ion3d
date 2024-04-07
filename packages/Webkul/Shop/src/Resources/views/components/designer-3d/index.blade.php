@php
    $props = json_encode([
        'currency' => core()->getCurrentCurrency()->symbol,
        'urls' => [
            'get_families_attributes' => route('shop.designer_3d.get_families_attributes'),
            'get_attributes_by_family' => route('shop.designer_3d.get_attributes_by_family'),
            'get_product_by_attributes' => route('shop.designer_3d.get_product_by_attributes'),
            'add_item_to_cart' => route('shop.api.checkout.cart.store_design')
        ],
        'texts' => [
            'custom_your_cover' => trans('shop::app.design3d.custom_your_cover'),
            'select_attribute' => trans('shop::app.design3d.select_attribute'),
            'restart_values' => trans('shop::app.design3d.restart_values'),
            'add_to_cart_finish' => trans('shop::app.design3d.add_to_cart_finish'),
            'add_to_cart' => trans('shop::app.design3d.add_to_cart'),
            'back_piece' => trans('shop::app.design3d.back_piece'),
            'side_piece' => trans('shop::app.design3d.side_piece'),
            'text_print' => trans('shop::app.design3d.text_print'),
            'text_size' => trans('shop::app.design3d.text_size'),
            'text_size_normal' => trans('shop::app.design3d.text_size_normal'),
            'text_size_medium' => trans('shop::app.design3d.text_size_medium'),
            'text_size_big' => trans('shop::app.design3d.text_size_big'),
            'validation_quantity' => trans('shop::app.design3d.validation_quantity'),
            'loading_modal_3d' => trans('shop::app.design3d.loading_modal_3d'),
        ] 
    ])
@endphp

<v-designer-3d
    :info="{{ $props }}"
/>