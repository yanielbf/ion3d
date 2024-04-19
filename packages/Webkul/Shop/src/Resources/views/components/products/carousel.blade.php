<v-products-carousel
    src="{{ $src }}"
    title="{{ $title }}"
    navigation-link="{{ $navigationLink ?? '' }}"
>
    <x-shop::shimmer.products.carousel :navigation-link="$navigationLink ?? false" />
</v-products-carousel>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-products-carousel-template"
    >
        <div
            class="container my-20 max-lg:px-8 max-sm:mt-8"
            v-if="!isLoading && products.length"
        >
            <div class="flex justify-between">
                <h2
                    class="text-3xl max-sm:text-2xl"
                    v-text="title"
                >
                </h2>

                <div class="flex gap-8 justify-between items-center">
                    <span
                        class="icon-arrow-left-stylish rtl:icon-arrow-right-stylish inline-block text-2xl cursor-pointer"
                        role="button"
                        aria-label="@lang('shop::app.components.products.carousel.previous')"
                        tabindex="0"
                        @click="swipeLeft"
                    >
                    </span>

                    <span
                        class="icon-arrow-right-stylish rtl:icon-arrow-left-stylish inline-block text-2xl cursor-pointer"
                        role="button"
                        aria-label="@lang('shop::app.components.products.carousel.next')"
                        tabindex="0"
                        @click="swipeRight"
                    >
                    </span>
                </div>
            </div>

            <div
                ref="swiperContainer"
                class="flex items-stretch gap-8 [&>*]:flex-[0] mt-10 overflow-auto scroll-smooth scrollbar-hide max-sm:mt-5"
            >
                <x-shop::products.card
                    class="min-w-[310px] md:min-w-[400px] flex-1"
                    v-for="product in products"
                />
            </div>

            <a
                :href="navigationLink"
                class="bg-gray-700 hover:bg-indigo-800 transition-all duration-700 rounded-full shadow-xs text-white w-max mt-8 md:mt-14 mx-auto py-3 px-11 block text-base text-center"
                v-if="navigationLink"
            >
                @lang('shop::app.components.products.carousel.view-all')
            </a>
        </div>

        <!-- Product Card Listing -->
        <template v-if="isLoading">
            <x-shop::shimmer.products.carousel :navigation-link="$navigationLink ?? false" />
        </template>
    </script>

    <script type="module">
        app.component('v-products-carousel', {
            template: '#v-products-carousel-template',

            props: [
                'src',
                'title',
                'navigationLink',
            ],

            data() {
                return {
                    isLoading: true,

                    products: [],

                    offset: 342,
                };
            },

            mounted() {
                this.getProducts();
            },

            methods: {
                getProducts() {
                    this.$axios.get(this.src)
                        .then(response => {
                            this.isLoading = false;
                            this.products = response.data.data;
                            console.log(this.products);
                        }).catch(error => {
                            console.log(error);
                        });
                },

                swipeLeft() {
                    const container = this.$refs.swiperContainer;

                    container.scrollLeft -= this.offset;
                },

                swipeRight() {
                    const container = this.$refs.swiperContainer;

                    // Check if scroll reaches the end
                    if (container.scrollLeft + container.clientWidth >= container.scrollWidth) {
                        // Reset scroll to the beginning
                        container.scrollLeft = 0;
                    } else {
                        // Scroll to the right
                        container.scrollLeft += this.offset;
                    }
                },
            },
        });
    </script>
@endPushOnce
