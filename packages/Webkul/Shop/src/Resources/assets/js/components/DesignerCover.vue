<script setup>
import { inject, onMounted, defineProps, ref } from "vue";

const axios = inject("$axios");

const props = defineProps({
    info: {
        type: Object,
    },
});

const attributes = ref([]);

onMounted(() => {
    axios
        .get(props.info.urls.get_attributes_dc)
        .then((response) => {
            attributes.value = response;
        })
        .catch((error) => {
            console.log(error);
        });
});
</script>
<template>
    <div class="p-8">Hellow world</div>
</template>
