@props([
    'name'  => '',
    'entity' => null,
])

<div class="flex justify-center mt-8 max-lg:hidden">
    <div class="flex gap-x-3.5 items-center">        
        {{ Breadcrumbs::view('shop::partials.breadcrumbs', $name, $entity) }}
    </div>
</div>
