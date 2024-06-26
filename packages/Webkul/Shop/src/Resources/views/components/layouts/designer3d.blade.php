@props([
    'hasHeader'  => true,
    'hasFeature' => true,
    'hasFooter'  => true,
])

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ core()->getCurrentLocale()->direction }}">
    <head>
        <title>{{ $title ?? '' }}</title>

        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="base-url" content="{{ url()->to('/') }}">
        <meta name="currency-code" content="{{ core()->getCurrentCurrencyCode() }}">
        <meta http-equiv="content-language" content="{{ app()->getLocale() }}">

        @stack('meta')

        <link
            rel="icon"
            sizes="16x16"
            href="{{ core()->getCurrentChannel()->favicon_url ?? bagisto_asset('images/favicon.ico') }}"
        />

        @bagistoVite(['src/Resources/assets/css/app.css', 'src/Resources/assets/js/app.js'])

        <link rel="preload" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" as="style">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap">
        <link rel="preload" href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" as="style">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap">
        <link rel="stylesheet" type="text/css" href="{{asset("vendor/cookie-consent/css/cookie-consent.css")}}">

        @stack('styles')

        <style>
            {!! core()->getConfigData('general.content.custom_scripts.custom_css') !!}
        </style>

        {!! view_render_event('bagisto.shop.layout.head') !!}
    </head>

    <body>
        <x-shop::screen-loading />
        <div id="app">
            {!! view_render_event('bagisto.shop.layout.body.before') !!}

            <!-- Flash Message Blade Component -->
            <x-shop::flash-group />

            <!-- Confirm Modal Blade Component -->
            <x-shop::modal.confirm />

            <div class="grid grid-rows-[90px,auto,80px,50px] md:grid-rows-[80px,auto,80px,50px] grid-cols-[120px,auto] min-h-screen">
                @if ($hasHeader)
                    <div class="col-span-2 row-span-1">
                        <x-shop::layouts.header />
                    </div>
                @endif
                
                <div class="col-span-2 min-h-[400px]">
                    <!-- Page Content Blade Component -->
                    <div id="main" class="bg-white">
                        {{ $slot }}
                    </div>
                </div>
                
                @if ($hasFooter)
                    <div class="col-span-2 row-span-1">
                        <x-shop::layouts.footer />
                        <a href="#" class="block pdcc-open-modal text-center w-full col-span-3 p-2 border-t bg-gray-100 cursor-pointer">Cookies</a>
                    </div>
                @endif
            </div>

            {!! view_render_event('bagisto.shop.layout.body.after') !!}
        </div>

        @stack('scripts')

        <script type="text/javascript">
            {!! core()->getConfigData('general.content.custom_scripts.custom_javascript') !!}
        </script>
    </body>
</html>
