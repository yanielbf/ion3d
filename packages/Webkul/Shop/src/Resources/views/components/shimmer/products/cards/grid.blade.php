@props(['count' => 0])

@for ($i = 0;  $i < $count; $i++)
    <div class="grid gap-2.5 relative w-full max-w-[291px] max-sm:grid-cols-1 {{ $attributes["class"] }}">
        <div class="shimmer relative w-full rounded">
            <div class="relative after:content-[' '] after:block after:pb-[calc(100%+9px)]"></div>
        </div>
    </div>
@endfor
