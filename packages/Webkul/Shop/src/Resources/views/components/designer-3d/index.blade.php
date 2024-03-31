@php
    $props = json_encode([
        'urls' => [
            'get_families_attributes' => route('shop.designer_3d.get_families_attributes'),
            'get_attributes_by_family' => route('shop.designer_3d.get_attributes_by_family'),
            'get_product_by_attributes' => route('shop.designer_3d.get_product_by_attributes')
        ]
    ])
@endphp

<v-designer-3d
    :info="{{ $props }}"
/>