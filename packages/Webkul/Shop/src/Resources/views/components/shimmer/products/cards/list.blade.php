@props(['count' => 0])

@for ($i = 0; $i < $count; $i++)
<div class="grid grid-cols-1 gap-6">
    <div class="grid gap-4 grid-cols-2 relative max-w-max max-sm:grid-cols-1">
        <div class="shimmer relative min-w-[250px] min-h-[258px] overflow-hidden rounded"> 
            <img class="rounded-sm bg-[#F5F5F5]">
        </div>
    </div>
</div>
@endfor
