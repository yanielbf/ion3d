<script setup>
import { inject, onMounted, defineProps, ref, watch, reactive } from "vue";
import Dropdown from "primevue/dropdown";
import ProgressSpinner from "primevue/progressspinner";

const axios = inject("$axios");

const props = defineProps({
    info: {
        type: Object,
    },
});

const state = reactive({
    loading: true,
    loadingProduct: false,
    error: null,
    errorProduct: null,
    familyAttributes: [],
    selectedFamilyAttribute: null,
    attributes: [],
    selectedAttributes: {},
    product: null
});

function handleGetFamiliesAttributes() {
    axios
        .get(props.info.urls.get_families_attributes)
        .then((response) => {
            state.familyAttributes = response.data.data;
            state.selectedFamilyAttribute = state.familyAttributes.length && state.familyAttributes[0];
        })
        .catch((error) => {
            state.error = error;
        });
}

function handleGetAttributes() {
    if(state.selectedFamilyAttribute) {
        axios
            .get(`${props.info.urls.get_attributes_by_family}?familyId=${state.selectedFamilyAttribute.id}`)
            .then((response) => {
                state.attributes = response.data.data;
                state.selectedAttributes = state.attributes.reduce((acc, item) => {
                    acc[item.code] = null;
                    return acc;
                }, {});
            })
            .catch((error) => {
                state.error = error;
            }).finally(() => {
                state.loading = false;
            });
    } else {

    }
}

function handleGetProduct() {
    if(Object.keys(state.selectedAttributes).every(x => state.selectedAttributes[x])) {
        state.loadingProduct = true;
        const query = {};
        Object.keys(state.selectedAttributes).forEach(x => query[x] = state.selectedAttributes[x].id);
        const url = Object.keys(query) != '' ? `${props.info.urls.get_product_by_attributes}?${new URLSearchParams(query).toString()}` : props.info.urls.get_product_by_attributes
        axios
            .get(url)
            .then((response) => {
                console.log(response.data.data)
            })
            .catch((error) => {
                state.errorProduct = error;
            }).finally(() => {
                state.loadingProduct = false;
            });
    } else {
        state.product = null;
    }
}

watch(
    () => state.selectedFamilyAttribute,
    () => {
        handleGetAttributes();
    },
    {
        deep: true,
    }
);

watch(
    () => state.selectedAttributes,
    () => {
        handleGetProduct();
    },
    {
        deep: true,
    }
);

onMounted(() => {
    handleGetFamiliesAttributes();
});
</script>

<template>
    <div class="p-8">
        <div v-if="state.loading" class="flex justify-center">
            <ProgressSpinner
                style="width: 50px; height: 50px"
                strokeWidth="4"
                class="fill-surface-0 dark:fill-surface-800"
                animationDuration=".5s"
                aria-label="Custom ProgressSpinner"
            />
        </div>
        <div class="grid md:grid-cols-[1fr_2fr] grid-cols-1 gap-2">
            <Dropdown
                v-for="attribute in state.attributes"
                :key="attribute.id"
                v-model="state.selectedAttributes[attribute.code]"
                :filter="true"
                :options="attribute.options"
                optionLabel="name"
                :placeholder="`Select a ${attribute.name}`"
                class="w-full"
            />
        </div>
    </div>
</template>
