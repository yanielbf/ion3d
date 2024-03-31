@php
    $props = json_encode([
        'urls' => [
            'get_attributes_dc' => route('shop.designer_cover.get_attributes_dc')
        ]
    ])
@endphp

<v-designer-cover
    :info="{{ $props }}"
/>