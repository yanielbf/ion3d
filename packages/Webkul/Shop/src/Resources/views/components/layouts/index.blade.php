@props([
    'hasHeader'  => true,
    'hasFeature' => true,
    'hasFooter'  => true,
])

<!DOCTYPE html>

<html
    lang="{{ app()->getLocale() }}"
    dir="{{ core()->getCurrentLocale()->direction }}"
>
    <head>
        <title>{{ $title ?? '' }}</title>

        <meta charset="UTF-8">

        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta http-equiv="content-language" content="{{ app()->getLocale() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="base-url" content="{{ url()->to('/') }}" >
        <meta name="currency" content="{{ core()->getCurrentCurrency()->toJson() }}">

        @stack('meta')

        <link rel="icon" sizes="16x16" href="{{ core()->getCurrentChannel()->favicon_url ?? bagisto_asset('images/favicon.ico') }}"/>

        @bagistoVite(['src/Resources/assets/css/app.css', 'src/Resources/assets/js/app.js'])

        <link rel="preload" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" as="style">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap">
        <link rel="preload" href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" as="style">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap">
        <link rel="stylesheet" type="text/css" href="{{asset("vendor/cookie-consent/css/cookie-consent.css")}}">
        <script src="https://cdn.tailwindcss.com"></script>

        @stack('styles')

        <style>
            {!! core()->getConfigData('general.content.custom_scripts.custom_css') !!}
        </style>

        {!! view_render_event('bagisto.shop.layout.head') !!}
    </head>

    <body>
        {!! view_render_event('bagisto.shop.layout.body.before') !!}
        <x-shop::screen-loading />
        <div id="app">
            <!-- Flash Message Blade Component -->
            <x-shop::flash-group />

            <!-- Confirm Modal Blade Component -->
            <x-shop::modal.confirm />

            <!-- Page Header Blade Component -->
            @if ($hasHeader)
                <x-shop::layouts.header />
            @endif

            {!! view_render_event('bagisto.shop.layout.content.before') !!}

            <!-- Page Content Blade Component -->
            <main id="main" class="bg-white">
                {{ $slot }}
            </main>

            {!! view_render_event('bagisto.shop.layout.content.after') !!}


            <!-- Page Services Blade Component -->
            @if ($hasFeature)
                <x-shop::services />
            @endif

            <!-- Page Footer Blade Component -->
            @if ($hasFooter)
                <x-shop::layouts.footer />
            @endif

            <a href="#" class="pdcc-open-modal text-center block p-2 border-t bg-gray-100 cursor-pointer rounded-l-lg absolute right-0 top-20">Cookies</a>
        </div>

        {!! view_render_event('bagisto.shop.layout.body.after') !!}

        @stack('scripts')

        {!! view_render_event('bagisto.shop.layout.vue-app-mount.before') !!}

        <script>
            /**
             * Load event, the purpose of using the event is to mount the application
             * after all of our `Vue` components which is present in blade file have
             * been registered in the app. No matter what `app.mount()` should be
             * called in the last.
             */
            window.addEventListener("load", function (event) {
                //app.mount("#app");
            });
        </script>

        {!! view_render_event('bagisto.shop.layout.vue-app-mount.after') !!}

        {!! core()->getConfigData('general.content.custom_scripts.custom_javascript') !!}
    </body>
</html>