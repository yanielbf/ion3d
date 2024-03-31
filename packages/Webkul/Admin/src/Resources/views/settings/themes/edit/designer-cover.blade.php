<!-- Design-cover Component -->
<v-designer-cover></v-designer-cover>

@pushOnce('scripts')
    <script type="text/x-template" id="v-designer-cover-template">
        <div class="flex gap-2.5 mt-3.5 max-xl:flex-wrap">
            <div class="flex flex-col gap-2 flex-1 min-w-[931px] max-xl:flex-auto">
                <div class="p-4 bg-white dark:bg-gray-900 rounded box-shadow">
                    Implementar configuraciones
                </div>
            </div>

            <!-- General -->
            <div class="flex flex-col gap-2 w-[360px] max-w-full max-sm:w-full">
                <x-admin::accordion>
                    <x-slot:header>
                        <p class="p-2.5 text-gray-800 dark:text-white text-base font-semibold">
                            @lang('admin::app.settings.themes.edit.general')
                        </p>
                    </x-slot>
                
                    <x-slot:content>
                        <input type="hidden" name="type" value="designer_3d">

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.themes.edit.name')
                            </x-admin::form.control-group.label>

                            <v-field
                                type="text"
                                name="name"
                                value="{{ $theme->name }}"
                                class="flex w-full min-h-[39px] py-2 px-3 border rounded-md text-sm text-gray-600 dark:text-gray-300 transition-all hover:border-gray-400 dark:hover:border-gray-400 focus:border-gray-400 dark:focus:border-gray-400 dark:bg-gray-900 dark:border-gray-800"
                                :class="[errors['name'] ? 'border border-red-600 hover:border-red-600' : '']"
                                rules="required"
                                label="@lang('admin::app.settings.themes.edit.name')"
                                placeholder="@lang('admin::app.settings.themes.edit.name')"
                            >
                            </v-field>

                            <x-admin::form.control-group.error control-name="name" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.themes.edit.sort-order')
                            </x-admin::form.control-group.label>

                            <v-field
                                type="text"
                                name="sort_order"
                                value="{{ $theme->sort_order }}"
                                rules="required|min_value:1"
                                class="flex w-full min-h-[39px] py-2 px-3 border rounded-md text-sm text-gray-600 dark:text-gray-300 transition-all hover:border-gray-400 dark:hover:border-gray-400 focus:border-gray-400 dark:focus:border-gray-400 dark:bg-gray-900 dark:border-gray-800"
                                :class="[errors['sort_order'] ? 'border border-red-600 hover:border-red-600' : '']"
                                label="@lang('admin::app.settings.themes.edit.sort-order')"
                                placeholder="@lang('admin::app.settings.themes.edit.sort-order')"
                            >
                            </v-field>

                            <x-admin::form.control-group.error control-name="sort_order" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.themes.edit.channels')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="select"
                                name="channel_id"
                                rules="required"
                                :value="$theme->channel_id"
                            >
                                @foreach($channels as $channel)
                                    <option value="{{ $channel->id }}">{{ $channel->name }}</option>
                                @endforeach 
                            </x-admin::form.control-group.control>

                            <x-admin::form.control-group.error control-name="channel_id" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group class="!mb-0">
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.themes.edit.status')
                            </x-admin::form.control-group.label>

                            <label class="relative inline-flex items-center cursor-pointer">
                                <v-field
                                    type="checkbox"
                                    name="status"
                                    class="hidden"
                                    v-slot="{ field }"
                                    value="{{ $theme->status }}"
                                >
                                    <input
                                        type="checkbox"
                                        name="status"
                                        id="status"
                                        class="sr-only peer"
                                        v-bind="field"
                                        :checked="{{ $theme->status }}"
                                    />
                                </v-field>
                    
                                <label
                                    class="rounded-full dark:peer-focus:ring-blue-800 peer-checked:bg-blue-600 w-9 h-5 bg-gray-200 cursor-pointer peer-focus:ring-blue-300 after:bg-white after:border-gray-300 peer-checked:bg-navyBlue peer peer-checked:after:border-white peer-checked:after:ltr:translate-x-full peer-checked:after:rtl:-translate-x-full after:content-[''] after:absolute after:top-0.5 after:ltr:left-0.5 after:rtl:right-0.5 peer-focus:outline-none after:border after:rounded-full after:h-4 after:w-4 after:transition-all"
                                    for="status"
                                ></label>
                            </label>
                            
                            <x-admin::form.control-group.error control-name="status" />
                        </x-admin::form.control-group>
                    </x-slot>
                </x-admin::accordion>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-designer-cover', {
            template: '#v-designer-cover-template',

            props: ['errors'],

            data() {
                return {
                    options: @json($theme->translate($currentLocale->code)['options'] ?? null),
                };
            },

            created() {
                if (this.options === null) {
                    this.options = { html: {} };
                }   
            },

            methods: {
            },
        });
    </script>

@endPushOnce

@pushOnce('styles')
@endPushOnce