<v-designer-cover />

@pushOnce('scripts')
    <script type="text/x-template" id="v-designer-cover-template">
        <div class="w-full flex justify-between mt-9 px-[60px] max-1180:px-8">
            1111
        </div>
    </script>

    <script type="module">
        app.component('v-designer-cover', {
            template: '#v-designer-cover-template',

            data() {
                return  {
                    // isLoading: false,
                    // categories: [],
                }
            },

            mounted() {
                // this.get();
            },

            methods: {
                // get() {
                //     this.$axios.get("{{ route('shop.api.categories.tree') }}")
                //         .then(response => {
                //             this.isLoading = false;

                //             this.categories = response.data.data;
                //         }).catch(error => {
                //             console.log(error);
                //         });
                // },

                // pairCategoryChildren(category) {
                //     return category.children.reduce((result, value, index, array) => {
                //         if (index % 2 === 0) {
                //             result.push(array.slice(index, index + 2));
                //         }

                //         return result;
                //     }, []);
                // }
            },
        });
    </script>
@endPushOnce
