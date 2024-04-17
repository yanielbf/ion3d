{!! view_render_event('bagisto.shop.layout.footer.before') !!}

<!--
    The category repository is injected directly here because there is no way
    to retrieve it from the view composer, as this is an anonymous component.
-->
@inject('themeCustomizationRepository', 'Webkul\Theme\Repositories\ThemeCustomizationRepository')

<!--
    This code needs to be refactored to reduce the amount of PHP in the Blade
    template as much as possible.
-->
@php
    $customization = $themeCustomizationRepository->findOneWhere([
        'type'       => 'footer_links',
        'status'     => 1,
        'channel_id' => core()->getCurrentChannel()->id,
    ]);
@endphp

<footer class="w-full mt-9 md:mt-16 border-t">
    <div class="mx-auto max-w-7xl">
      <!--Grid-->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-8 px-6 md:px-0 pt-10 pb-4 md:pb-10">
        <div class="lg:mb-0 flex flex-col">
          <a href="{{route('shop.home.index')}}" class="flex justify-center lg:justify-start">
            <img
                src="{{ core()->getCurrentChannel()->logo_url ?? bagisto_asset('images/logo.svg') }}"
                alt="{{ config('app.name') }}"
                class="h-[40px]"
            >
          </a>
          <div class="mt-6 flex items-center justify-between w-full max-w-xl mx-auto flex-col  2xl:flex-col 2xl:items-start">
            <ul class="mb-6 md:mb-0">
                <li class="flex">
                    <div class="flex h-10 w-10 items-center justify-center rounded bg-gray-700 text-gray-50">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="h-6 w-6">
                            <path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"></path>
                            <path
                                d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-4 mb-8">
                        <p class="text-gray-600 dark:text-slate-400 text-sm">{{$customization?->options['settings']['address']}}</p>
                    </div>
                </li>
                <li class="flex">
                    <div class="flex h-10 w-10 items-center justify-center rounded bg-gray-700 text-gray-50">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="h-6 w-6">
                            <path
                                d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2">
                            </path>
                            <path d="M15 7a2 2 0 0 1 2 2"></path>
                            <path d="M15 3a6 6 0 0 1 6 6"></path>
                        </svg>
                    </div>
                    <div class="ml-4 mb-4">
                        <p class="text-gray-600 dark:text-slate-400 text-sm">{{$customization?->options['settings']['phone']}}</p>
                        <p class="text-gray-600 dark:text-slate-400 text-sm">{{$customization?->options['settings']['email']}}</p>
                    </div>
                </li>
                <li class="flex">
                    <div class="flex h-10 w-10 items-center justify-center rounded bg-gray-700 text-gray-50">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="h-6 w-6">
                            <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path>
                            <path d="M12 7v5l3 3"></path>
                        </svg>
                    </div>
                    <div class="ml-4 mb-4">
                        @php
                            $hours = explode(';', $customization?->options['settings']['hours']);
                        @endphp
                        @foreach ($hours as $hour)
                            <p class="text-gray-600 dark:text-slate-400 text-sm">{{ $hour }}</p>
                        @endforeach
                    </div>
                </li>
            </ul>
          </div>
        </div>
        <div class="lg:mx-auto text-center md:text-left mt-5 md:mt-0">
            <h4 class="text-lg font-medium text-gray-900 mb-7">@lang('shop::app.components.layouts.footer.pages') </h4>
            @if ($customization?->options['column_1'])
                @php
                    $links = $customization->options['column_1'];
                    usort($links, function ($a, $b) {
                        return $a['sort_order'] - $b['sort_order'];
                    });
                @endphp
                <ul class="text-sm transition-all duration-500">
                    @foreach ($links as $link)
                        <li class="mb-6">
                            <a class="text-gray-600 hover:text-gray-900" href="{{ $link['url'] }}">
                                {{ $link['title'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
        <div class="flex flex-col md:justify-center md:items-center">
            @if (isset($customization?->options['settings']['whatsapp']))
                <div class="mb-5 w-full md:w-1/2">
                    <a href="{{$customization?->options['settings']['whatsapp']}}" target="_blank" class="flex items-center justify-center col-span-2 w-full h-12 bg-gray-700 hover:bg-indigo-800 transition-all duration-700 rounded-full shadow-xs text-white text-sm leading-6">
                        @lang('shop::app.components.layouts.footer.send-message')
                    </a>
                </div>
            @endif
            <div class="flex mt-4 space-x-4 justify-center lg:mt-0 ">
                @if (isset($customization?->options['settings']['instagram']))
                    <a href="{{$customization?->options['settings']['instagram']}}" target="_blank" class="w-9 h-9 rounded-full transition-all duration-700 bg-gray-700 flex justify-center items-center hover:bg-indigo-600">
                        <v-icon name="co-instagram" class="text-white" />
                    </a>
                @endif
                @if (isset($customization?->options['settings']['facebook']))
                    <a href="{{$customization?->options['settings']['facebook']}}" target="_blank" class="w-9 h-9 rounded-full transition-all duration-700 bg-gray-700 flex justify-center items-center hover:bg-indigo-600">
                        <v-icon name="co-facebook-f" class="text-white" />
                    </a>
                @endif
                @if (isset($customization?->options['settings']['tiktok']))
                    <a href="{{$customization?->options['settings']['tiktok']}}" target="_blank" class="w-9 h-9 rounded-full transition-all duration-700 bg-gray-700 flex justify-center items-center hover:bg-indigo-600">
                        <v-icon name="co-tiktok" class="text-white" />
                    </a>
                @endif
                @if (isset($customization?->options['settings']['telegram']))
                    <a href="{{$customization?->options['settings']['telegram']}}" target="_blank" class="w-9 h-9 rounded-full transition-all duration-700 bg-gray-700 flex justify-center items-center hover:bg-indigo-600">
                        <v-icon name="co-telegram" class="text-white" />
                    </a>
                @endif
                @if (isset($customization?->options['settings']['x']))
                    <a href="{{$customization?->options['settings']['x']}}" target="_blank" class="w-9 h-9 rounded-full transition-all duration-700 bg-gray-700 flex justify-center items-center hover:bg-indigo-600">
                        <svg class="transition-all duration-500 group-hover:text-indigo-600 text-white" width="30" height="30" viewBox="0 0 32 32" fill="#FFFFFF" xmlns="http://www.w3.org/2000/svg">
                            <path id="Vector" d="M17.5667 14.7386L24.072 7.33936H22.5305L16.8819 13.764L12.3704 7.33936H7.16699L13.9892 17.0546L7.16699 24.8139H8.70862L14.6736 18.0292L19.4381 24.8139H24.6415L17.5663 14.7386H17.5667ZM15.4552 17.1402L14.764 16.1728L9.2641 8.47491H11.632L16.0704 14.6873L16.7617 15.6548L22.5312 23.73H20.1633L15.4552 17.1406V17.1402Z" fill="currentColor" />
                        </svg>
                    </a>
                @endif
                @if (isset($customization?->options['settings']['youtube']))
                    <a href="{{$customization?->options['settings']['youtube']}}" target="_blank" class="w-9 h-9 rounded-full transition-all duration-700 bg-gray-700 flex justify-center items-center hover:bg-indigo-600">
                        <v-icon name="co-youtube" class="text-white" />
                    </a>
                @endif
            </div>
        </div>
        <!--End Col-->
        {{-- {!! view_render_event('bagisto.shop.layout.footer.contact.before') !!} 
        <div class="">
          <h4 class="text-lg text-gray-900 font-medium mb-7">
            @lang('shop::app.components.layouts.footer.ready-for-start') 
          </h4>
          <x-shop::form
            class="grid grid-cols-1 md:grid-cols-2 gap-2"
            method="POST"
            action="{{ route('shop.contact.store') }}"
          >
            @honeypot
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="!mt-0 required flex items-center mb-2 text-gray-600 text-sm">
                    @lang('shop::app.components.layouts.footer.contact-name')
                </x-shop::form.control-group.label>
                <x-shop::form.control-group.control
                    type="email"
                    class="block w-full h-11 px-5 py-2.5 leading-7 text-sm font-normal shadow-xs text-gray-900 bg-transparent border border-gray-300 rounded-full placeholder-gray-400 focus:outline-none"
                    name="name"
                    rules="required"
                    :label="strtolower(trans('shop::app.components.layouts.footer.contact-name'))"
                    :aria-label="trans('shop::app.components.layouts.footer.contact-name')"
                />
                <x-shop::form.control-group.error control-name="name" />
            </x-shop::form.control-group>
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="!mt-0 required flex items-center mb-2 text-gray-600 text-sm">
                    @lang('shop::app.components.layouts.footer.contact-email')
                </x-shop::form.control-group.label>
                <x-shop::form.control-group.control
                    type="email"
                    class="block w-full h-11 px-5 py-2.5 leading-7 text-sm font-normal shadow-xs text-gray-900 bg-transparent border border-gray-300 rounded-full placeholder-gray-400 focus:outline-none"
                    name="email"
                    rules="required|email"
                    :label="strtolower(trans('shop::app.components.layouts.footer.contact-email'))"
                    :aria-label="trans('shop::app.components.layouts.footer.contact-email')"
                />
                <x-shop::form.control-group.error control-name="email" />
            </x-shop::form.control-group>
            <x-shop::form.control-group class="col-span-2">
                <x-shop::form.control-group.label class="!mt-0 required flex items-center mb-2 text-gray-600 text-sm">
                    @lang('shop::app.components.layouts.footer.contact-message')
                </x-shop::form.control-group.label>
                <x-shop::form.control-group.control
                    type="textarea"
                    class="h-[180px] block resize-none w-full px-5 py-2.5 leading-7 text-sm font-normal shadow-xs text-gray-900 bg-transparent border border-gray-300 rounded-full placeholder-gray-400 focus:outline-none"
                    name="message"
                    rules="required"
                    :label="strtolower(trans('shop::app.components.layouts.footer.contact-message'))"
                    :aria-label="trans('shop::app.components.layouts.footer.contact-message')"
                />
                <x-shop::form.control-group.error control-name="message" />
            </x-shop::form.control-group>
            <button class="col-span-2 w-full h-12 bg-gray-700 hover:bg-indigo-800 transition-all duration-700 rounded-full shadow-xs text-white text-sm leading-6">
                @lang('shop::app.components.layouts.footer.send-message')
            </button>
          </x-shop::form>
        </div>
        {!! view_render_event('bagisto.shop.layout.footer.contact.after') !!} --}}
        <!--End Col-->
        
      </div>
      <!--Grid-->
      <div class="px-6 md:px-0 py-3 border-t border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-2 items-center gap-1 md:gap-4">
            <span class="text-sm text-gray-500 ">@lang('shop::app.components.layouts.footer.footer-text', ['current_year'=> date('Y') ])</span>
            {!! view_render_event('bagisto.shop.layout.footer.newsletter_subscription.before') !!}
            <!-- News Letter subscription -->
            @if (core()->getConfigData('customer.settings.newsletter.subscription'))
                <div class="grid gap-2.5">
                    <x-shop::form
                        :action="route('shop.subscription.store')"
                        class="mt-2.5 rounded"
                    >
                        <div class="relative w-full">
                            <x-shop::form.control-group.control
                                type="email"
                                class="blockw-[420px] max-w-full px-5 py-5 p-28 rounded-xl text-xs font-medium max-1060:w-full"
                                name="email"
                                rules="required|email"
                                label="Email"
                                :aria-label="trans('shop::app.components.layouts.footer.email')"
                                placeholder="email@example.com"
                            />
                            <x-shop::form.control-group.error control-name="email" />
                            <button
                                type="submit"
                                class=" absolute flex items-center top-2 w-max px-7 py-3.5 bg-gray-700 hover:bg-indigo-800 transition-all duration-700 text-white rounded-xl text-xs font-medium rtl:left-2 ltr:right-2"
                            >
                                @lang('shop::app.components.layouts.footer.subscribe')
                            </button>
                        </div>
                    </x-shop::form>
                </div>
            @endif
            {!! view_render_event('bagisto.shop.layout.footer.newsletter_subscription.after') !!}
        </div>
      </div>
    </div>
</footer>

{!! view_render_event('bagisto.shop.layout.footer.after') !!}
