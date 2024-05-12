@inject ('reviewHelper', 'Webkul\Product\Helpers\Review')
@inject ('productViewHelper', 'Webkul\Product\Helpers\View')

@php
    $avgRatings = round($reviewHelper->getAverageRating($product));

    $percentageRatings = $reviewHelper->getPercentageRating($product);

    $customAttributeValues = $productViewHelper->getAdditionalData($product);

    $attributeData = collect($customAttributeValues)->filter(fn ($item) => ! empty($item['value']));
@endphp

<!-- SEO Meta Content -->
@push('meta')
    <meta name="description" content="{{ trim($product->meta_description) != "" ? $product->meta_description : \Illuminate\Support\Str::limit(strip_tags($product->description), 120, '') }}"/>

    <meta name="keywords" content="{{ $product->meta_keywords }}"/>

    @if (core()->getConfigData('catalog.rich_snippets.products.enable'))
        <script type="application/ld+json">
            {{ app('Webkul\Product\Helpers\SEO')->getProductJsonLd($product) }}
        </script>
    @endif

    <?php $productBaseImage = product_image()->getProductBaseImage($product); ?>

    <meta name="twitter:card" content="summary_large_image" />

    <meta name="twitter:title" content="{{ $product->name }}" />

    <meta name="twitter:description" content="{!! htmlspecialchars(trim(strip_tags($product->description))) !!}" />

    <meta name="twitter:image:alt" content="" />

    <meta name="twitter:image" content="{{ $productBaseImage['medium_image_url'] }}" />

    <meta property="og:type" content="og:product" />

    <meta property="og:title" content="{{ $product->name }}" />

    <meta property="og:image" content="{{ $productBaseImage['medium_image_url'] }}" />

    <meta property="og:description" content="{!! htmlspecialchars(trim(strip_tags($product->description))) !!}" />

    <meta property="og:url" content="{{ route('shop.product_or_category.index', $product->url_key) }}" />
@endPush

<!-- Page Layout -->
<x-shop::layouts>
    <!-- Page Title -->
    <x-slot:title>
        {{ trim($product->meta_title) != "" ? $product->meta_title : $product->name }}
    </x-slot>

    {!! view_render_event('bagisto.shop.products.view.before', ['product' => $product]) !!}

    <!-- Breadcrumbs -->
    <div class="flex justify-center max-lg:hidden">
        <x-shop::breadcrumbs
            name="product"
            :entity="$product"
        />
    </div>

    <!-- Product Information Vue Component -->
    <v-product :product-id="{{ $product->id }}">
        <x-shop::shimmer.products.view />
    </v-product>

    <!-- Information Section -->
    <div class="1180:mt-20 mb-20">
        <x-shop::tabs position="center">
            <!-- Description Tab -->
            {!! view_render_event('bagisto.shop.products.view.description.before', ['product' => $product]) !!}

            <x-shop::tabs.item
                class="container mt-[60px] !p-0 max-1180:hidden"
                :title="trans('shop::app.products.view.description')"
                :is-selected="true"
            >
                <div class="container mt-[60px] max-1180:px-5">
                    <p class="text-[#6E6E6E] max-1180:text-sm">
                        {!! $product->description !!}
                    </p>
                </div>
            </x-shop::tabs.item>

            {!! view_render_event('bagisto.shop.products.view.description.after', ['product' => $product]) !!}

            <!-- Additional Information Tab -->
            @if(count($attributeData))
                <x-shop::tabs.item
                    class="container mt-[60px] !p-0 max-1180:hidden"
                    :title="trans('shop::app.products.view.additional-information')"
                    :is-selected="false"
                >
                    <div class="container mt-[60px] max-1180:px-5">
                        <div class="grid gap-4 grid-cols-[auto_1fr] max-w-max mt-8">
                            @foreach ($customAttributeValues as $customAttributeValue)
                                @if (! empty($customAttributeValue['value']))
                                    <div class="grid">
                                        <p class="text-base text-black">
                                            {!! $customAttributeValue['label'] !!}
                                        </p>
                                    </div>

                                    @if ($customAttributeValue['type'] == 'file')
                                        <a 
                                            href="{{ Storage::url($product[$customAttributeValue['code']]) }}" 
                                            download="{{ $customAttributeValue['label'] }}"
                                        >
                                            <span class="icon-download text-2xl"></span>
                                        </a>
                                    @elseif ($customAttributeValue['type'] == 'image')
                                        <a 
                                            href="{{ Storage::url($product[$customAttributeValue['code']]) }}" 
                                            download="{{ $customAttributeValue['label'] }}"
                                        >
                                            <img 
                                                class="h-5 w-5 min-h-5 min-w-5" 
                                                src="{{ Storage::url($customAttributeValue['value']) }}" 
                                            />
                                        </a>
                                    @else
                                        <div class="grid">
                                            <p class="text-base text-[#7D7D7D]">
                                                {!! $customAttributeValue['value'] !!}
                                            </p>
                                        </div>
                                    @endif
                                @endif
                            @endforeach
                        </div>
                    </div>
                </x-shop::tabs.item>
            @endif

            <!-- Reviews Tab -->
            {{-- <x-shop::tabs.item
                class="container mt-[60px] !p-0 max-1180:hidden"
                :title="trans('shop::app.products.view.review')"
                :is-selected="false"
            >
                @include('shop::products.view.reviews')
            </x-shop::tabs.item> --}}
        </x-shop::tabs>
    </div>

    <!-- Information Section -->
    <div class="container mt-10 !p-0 max-1180:px-5 1180:hidden">
        <!-- Description Accordion -->
        <x-shop::accordion :is-active="true">
            <x-slot:header class="bg-gray-100">
                <p class="text-base font-medium 1180:hidden">
                    @lang('shop::app.products.view.description')
                </p>
            </x-slot>

            <x-slot:content>
                <div class="text-[#7D7D7D] text-lg max-1180:text-sm mb-5">
                    {!! $product->description !!}
                </div>
            </x-slot>
        </x-shop::accordion>

        <!-- Additional Information Accordion -->
        @if (count($attributeData))
            <x-shop::accordion class="bg-gray-100" :is-active="false">
                <x-slot:header>
                    <p class="text-base font-medium 1180:hidden">
                        @lang('shop::app.products.view.additional-information')
                    </p>
                </x-slot>

                <x-slot:content>
                    <div class="container my-4 max-1180:px-5">
                        <div class="grid gap-4 grid-cols-[auto_1fr] max-w-max text-[#6E6E6E] text-lg max-1180:text-sm">
                            @foreach ($customAttributeValues as $customAttributeValue)
                                @if (! empty($customAttributeValue['value']))
                                    <div class="grid">
                                        <p class="text-base text-black">
                                            {{ $customAttributeValue['label'] }}
                                        </p>
                                    </div>

                                    @if ($customAttributeValue['type'] == 'file')
                                        <a
                                            href="{{ Storage::url($product[$customAttributeValue['code']]) }}"
                                            download="{{ $customAttributeValue['label'] }}"
                                        >
                                            <span class="icon-download text-2xl"></span>
                                        </a>
                                    @elseif ($customAttributeValue['type'] == 'image')
                                        <a 
                                            href="{{ Storage::url($product[$customAttributeValue['code']]) }}" 
                                            download="{{ $customAttributeValue['label'] }}"
                                        >
                                            <img 
                                                class="h-5 w-5 min-h-5 min-w-5" 
                                                src="{{ Storage::url($customAttributeValue['value']) }}" 
                                            />
                                        </a>
                                    @else
                                        <div class="grid">
                                            <p class="text-base text-[#6E6E6E]">
                                                {{ $customAttributeValue['value'] ?? '-' }}
                                            </p>
                                        </div>
                                    @endif 
                                @endif
                            @endforeach
                        </div>
                    </div>
                </x-slot>
            </x-shop::accordion>
        @endif

        <!-- Reviews Accordion -->
        <!--x-shop::accordion class="bg-gray-100" :is-active="false">
            <x-slot:header>
                <p class="text-base font-medium 1180:hidden">
                    @lang('shop::app.products.view.review')
                </p>
            </x-slot>

            <x-slot:content>
                @include('shop::products.view.reviews')
            </x-slot>
        </x-shop::accordion -->
    </div>

    <!-- Featured Products -->
    <x-shop::products.carousel
        :title="trans('shop::app.products.view.related-product-title')"
        :src="route('shop.api.products.related.index', ['id' => $product->id])"
    />

    <!-- Upsell Products -->
    <x-shop::products.carousel
        :title="trans('shop::app.products.view.up-sell-title')"
        :src="route('shop.api.products.up-sell.index', ['id' => $product->id])"
    />

    {!! view_render_event('bagisto.shop.products.view.after', ['product' => $product]) !!}

    @pushOnce('scripts')
        <script type="text/x-template" id="v-product-template">
            <x-shop::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
            >
                <form
                    ref="formData"
                    @submit="handleSubmit($event, addToCart)"
                >
                    <input 
                        type="hidden" 
                        name="product_id" 
                        value="{{ $product->id }}"
                    >

                    <input
                        type="hidden"
                        name="is_buy_now"
                        v-model="is_buy_now"
                    >
                    
                    <input 
                        type="hidden" 
                        name="quantity" 
                        :value="qty"
                        ref="qty"
                    >

                    <div class="container px-[60px] max-1180:px-0">
                        <div class="flex gap-9 mt-12 max-1180:flex-wrap max-lg:mt-0 max-sm:gap-y-6">
                            <!-- Gallery Blade Inclusion -->
                            @include('shop::products.view.gallery')

                            <!-- Details -->
                            <div class="max-w-[590px] relative max-1180:w-full max-1180:max-w-full max-1180:px-5">
                                {!! view_render_event('bagisto.shop.products.name.before', ['product' => $product]) !!}

                                <div class="flex gap-4 justify-between">
                                    <h1 class="text-3xl font-medium max-sm:text-xl">
                                        {{ $product->name }}
                                    </h1>
                                </div>

                                {!! view_render_event('bagisto.shop.products.name.after', ['product' => $product]) !!}

                                <!-- Pricing -->
                                {!! view_render_event('bagisto.shop.products.price.before', ['product' => $product]) !!}

                                <p class="flex gap-2.5 items-center mt-5 text-2xl !font-medium max-sm:mt-4 max-sm:text-lg">
                                    {!! $product->getTypeInstance()->getPriceHtml() !!}

                                    <span class="text-lg text-[#6E6E6E]">
                                        @if (
                                            (bool) core()->getConfigData('taxes.catalogue.pricing.tax_inclusive') 
                                            && $product->getTypeInstance()->getTaxCategory()
                                        )
                                            @lang('shop::app.products.view.tax-inclusive')
                                        @endif
                                    </span>
                                </p>

                                @if (count($product->getTypeInstance()->getCustomerGroupPricingOffers()))
                                    <div class="grid gap-1.5 mt-2.5">
                                        @foreach ($product->getTypeInstance()->getCustomerGroupPricingOffers() as $offer)
                                            <p class="text-[#6E6E6E] [&>*]:text-black">
                                                {!! $offer !!}
                                            </p>
                                        @endforeach
                                    </div>
                                @endif

                                {!! view_render_event('bagisto.shop.products.price.after', ['product' => $product]) !!}

                                {!! view_render_event('bagisto.shop.products.short_description.before', ['product' => $product]) !!}

                                <p class="mt-6 text-[#6E6E6E] max-sm:text-sm max-sm:mt-4">
                                    {!! $product->short_description !!}
                                </p>

                                {!! view_render_event('bagisto.shop.products.short_description.after', ['product' => $product]) !!}

                                @include('shop::products.view.types.configurable')

                                @include('shop::products.view.types.grouped')

                                @include('shop::products.view.types.bundle')

                                @include('shop::products.view.types.downloadable')


                                <!-- Product Actions and Qunatity Box -->
                                <div class="flex gap-4 mt-8">

                                    {!! view_render_event('bagisto.shop.products.view.quantity.before', ['product' => $product]) !!}

                                    @if ($product->getTypeInstance()->showQuantityBox())
                                        <x-shop::quantity-changer
                                            v-if="!isLoading && product"
                                            name="quantity"
                                            value="1"
                                            class="gap-x-4 py-2 px-7 rounded-xl"
                                        />
                                    @endif

                                    {!! view_render_event('bagisto.shop.products.view.quantity.after', ['product' => $product]) !!}

                                    <!-- Add To Cart Button -->
                                    {!! view_render_event('bagisto.shop.products.view.add_to_cart.before', ['product' => $product]) !!}

                                    <div
                                        v-if="product && product.customizable && !isLoading"
                                        class="cursor-pointer flex gap-2 text-center w-full px-5 py-2 shadow-sm tracking-wider text-white rounded-full bg-gray-700 hover:bg-indigo-800 transition-all duration-700"
                                        :loading="isAddingToCart"
                                        @click="addToCartCustomizable"
                                    >
                                        <i
                                            v-if="isAddingToCart"
                                            class="pi pi-spin pi-spinner flex justify-center items-center mr-2"
                                            style="font-size: 1rem"
                                        />
                                        <span v-if="!isAddingToCart">@lang('shop::app.components.products.card.buy')</span>
                                    </div>
                                    <div
                                        v-if="product && product.customizable && !isLoading"
                                        class="cursor-pointer text-center w-full px-5 py-2 shadow-sm tracking-wider bg-white border text-gray-600 rounded-full hover:bg-gray-100 transition-all duration-700"
                                        @click="goToDesigner"
                                    >
                                        @lang('shop::app.components.products.card.customize')
                                    </div>

                                    {!! view_render_event('bagisto.shop.products.view.add_to_cart.after', ['product' => $product]) !!}
                                </div>

                                <!-- Buy Now Button -->
                                {!! view_render_event('bagisto.shop.products.view.buy_now.before', ['product' => $product]) !!}
								
								<div v-if="product && !product.customizable" class="mt-8 flex gap-3 h-[45px] items-center">
                                    <x-shop::button
                                        type="submit"
                                        class="primary-button w-full text-white rounded-full bg-gray-700 hover:bg-indigo-800 transition-all duration-700"
                                        button-type="secondary-button"
                                        :loading="false"
                                        :title="trans('shop::app.products.view.add-to-cart')"
                                        :disabled="! $product->isSaleable(1)"
                                        ::loading="isStoring.addToCart"
                                    />

                                    @if (core()->getConfigData('catalog.products.storefront.buy_now_button_display'))
                                        <x-shop::button
                                            type="submit"
                                            class="primary-button w-full text-white rounded-full bg-gray-700 hover:bg-indigo-800 transition-all duration-700"
                                            button-type="secondary-button"
                                            :title="trans('shop::app.products.view.buy-now')"
                                            :disabled="! $product->isSaleable(1)"
                                            ::loading="isStoring.buyNow"
                                            @click="is_buy_now=1;"
                                        />
                                    @endif
								</div>

                                {!! view_render_event('bagisto.shop.products.view.buy_now.after', ['product' => $product]) !!}

                                {!! view_render_event('bagisto.shop.products.view.additional_actions.before', ['product' => $product]) !!}

                                <!-- Share Buttons -->
                                <div class="flex mt-10 max-sm:flex-wrap max-sm:justify-center">
                                    {!! view_render_event('bagisto.shop.products.view.compare.before', ['product' => $product]) !!}

                                    <div
                                        class="flex gap-2.5 justify-center items-center cursor-pointer"
                                        role="button"
                                        tabindex="0"
                                        @click="is_buy_now=0; addToCompare({{ $product->id }})"
                                    >
                                        @if (core()->getConfigData('general.content.shop.compare_option'))
                                            <span
                                                class="icon-compare text-2xl"
                                                role="presentation"
                                            ></span>

                                            @lang('shop::app.products.view.compare')
                                        @endif
                                    </div>

                                    {!! view_render_event('bagisto.shop.products.view.compare.after', ['product' => $product]) !!}
                                </div>

                                {!! view_render_event('bagisto.shop.products.view.additional_actions.after', ['product' => $product]) !!}
                            </div>
                        </div>
                    </div>
                </form>
            </x-shop::form>
        </script>

        <script type="module">
            app.component('v-product', {
                template: '#v-product-template',

                props: ['productId'],

                data() {
                    return {
                        product: null,
                        isWishlist: Boolean("{{ (boolean) auth()->guard()->user()?->wishlist_items->where('channel_id', core()->getCurrentChannel()->id)->where('product_id', $product->id)->count() }}"),
                        isCustomer: '{{ auth()->guard('customer')->check() }}',
                        is_buy_now: 0,
                        isStoring: {
                            addToCart: false,
                            buyNow: false,
                        },
                        isLoading: true,
                        isAddingToCart: false,
                        quantity: 1
                    }
                },
                mounted() {
                    this.getProduct();
                    this.$emitter.on('quantity-change', (qty) => {
                        this.quantity = qty;
                    })
                },
                methods: {
                    getProduct() {
                        this.$axios.get("{{ route('shop.api.products.index') }}")
                        .then(response => {
                            this.product = response.data.data.find(x => x.id == this.productId);
                        }).catch(error => {
                            console.log(error);
                        }).finally(() => {
                            this.isLoading = false;
                        });
                    },

                    addToCart(params) {
                         const operation = this.is_buy_now ? 'buyNow' : 'addToCart';

                         this.isStoring[operation] = true;

                         let formData = new FormData(this.$refs.formData);

                         this.$axios.post('{{ route("shop.api.checkout.cart.store") }}', formData, {
                                 headers: {
                                     'Content-Type': 'multipart/form-data'
                                 }
                             })
                             .then(response => {
                                 if (response.data.message) {
                                     this.$emitter.emit('update-mini-cart', response.data.data);

                                     this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                                     if (response.data.redirect) {
                                         window.location.href= response.data.redirect;
                                     }
                                 } else {
                                     this.$emitter.emit('add-flash', { type: 'warning', message: response.data.data.message });
                                 }

                                 this.isStoring[operation] = false;
                             })
                             .catch(error => {
                                 this.isStoring[operation] = false;
                             });
                     },

                    addToCartCustomizable() {
                        let url = '{{ route("shop.api.checkout.cart.store") }}';
                        let hash = this.handleCreateHash(`product_${this.product.id}_back_1_83BE01_side_1_057EB5_text__fontSize_20.png`);
                        let data = {
                            quantity: this.quantity,
                            product_id: this.product.id,
                        }
                        if(this.product.customizable) {
                            url = '{{ route("shop.api.checkout.cart.store_design") }}';
                            data = {
                                quantity: this.quantity,
                                product_id: this.product.id,
                                image: null,
                                hash: hash,
                                design: {
                                    [hash]: {
                                        filename: `default.png`,
                                        quantity: this.quantity,
                                    },
                                },
                            }
                        }
                        this.isAddingToCart = true;
                        this.$axios.post(url, data)
                            .then(response => {
                                if (response.data.message) {
                                    this.$emitter.emit('update-mini-cart', response.data.data );
                                    this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                                } else {
                                    this.$emitter.emit('add-flash', { type: 'warning', message: response.data.data.message });
                                }
                            })
                            .catch(error => {
                                this.$emitter.emit('add-flash', { type: 'error', message: response.data.message });
                            }).finally(() => {
                                this.isAddingToCart = false;
                            });
                    },

                    goToDesigner() {
                        const attributes = '';
                        let url = '{{ route("shop.designer3d.index") }}?attribute_family=' + this.product.attribute_family;
                        Object.keys(this.product.attributes_values_3d).forEach(key => {
                            url+=`&attributes[]=${key}-${this.product.attributes_values_3d[key]}`;
                        });
                        window.location.href = url;
                    },

                    addToWishlist() {
                        if (this.isCustomer) {
                            this.$axios.post('{{ route('shop.api.customers.account.wishlist.store') }}', {
                                    product_id: "{{ $product->id }}"
                                })
                                .then(response => {
                                    this.isWishlist = ! this.isWishlist;
                                    this.$emitter.emit('add-flash', { type: 'success', message: response.data.data.message });
                                })
                                .catch(error => {});
                        } else {
                            window.location.href = "{{ route('shop.customer.session.index')}}";
                        }
                    },

                    addToCompare(productId) {
                        /**
                         * This will handle for customers.
                         */
                        if (this.isCustomer) {
                            this.$axios.post('{{ route("shop.api.compare.store") }}', {
                                    'product_id': productId
                                })
                                .then(response => {
                                    this.$emitter.emit('add-flash', { type: 'success', message: response.data.data.message });
                                })
                                .catch(error => {
                                    if ([400, 422].includes(error.response.status)) {
                                        this.$emitter.emit('add-flash', { type: 'warning', message: error.response.data.data.message });

                                        return;
                                    }

                                    this.$emitter.emit('add-flash', { type: 'error', message: error.response.data.message});
                                });

                            return;
                        }

                        /**
                         * This will handle for guests.
                         */
                        let existingItems = this.getStorageValue(this.getCompareItemsStorageKey()) ?? [];

                        if (existingItems.length) {
                            if (! existingItems.includes(productId)) {
                                existingItems.push(productId);

                                this.setStorageValue(this.getCompareItemsStorageKey(), existingItems);

                                this.$emitter.emit('add-flash', { type: 'success', message: "@lang('shop::app.products.view.add-to-compare')" });
                            } else {
                                this.$emitter.emit('add-flash', { type: 'warning', message: "@lang('shop::app.products.view.already-in-compare')" });
                            }
                        } else {
                            this.setStorageValue(this.getCompareItemsStorageKey(), [productId]);

                            this.$emitter.emit('add-flash', { type: 'success', message: "@lang('shop::app.products.view.add-to-compare')" });
                        }
                    },

                    getCompareItemsStorageKey() {
                        return 'compare_items';
                    },

                    setStorageValue(key, value) {
                        localStorage.setItem(key, JSON.stringify(value));
                    },

                    getStorageValue(key) {
                        let value = localStorage.getItem(key);

                        if (value) {
                            value = JSON.parse(value);
                        }

                        return value;
                    },

                    handleCreateHash(codeCover) {
                        let hash = 0;
                        for (let i = 0; i < codeCover.length; i++) {
                            hash += codeCover.charCodeAt(i);
                        }
                        return hash.toString(16);
                    }
                },
            });
        </script>
    @endPushOnce
</x-shop::layouts>
