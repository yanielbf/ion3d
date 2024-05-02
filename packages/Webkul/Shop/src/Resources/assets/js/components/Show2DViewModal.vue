<script setup>
    import { ref } from 'vue';
    import Dialog from 'primevue/dialog';

    const props = defineProps({
        index: {
            type: Number,
        },
        text: {
            type: String,
        },
        imageSrc: {
            type: String,
        },
    });

    const visible = ref(false);
</script>

<template>
    <button @click="visible = true" class="bg-gray-700 hover:bg-indigo-800 transition-all duration-700 rounded-full px-4 py-1 text-sm text-white cursor-pointer">
        Modelo {{index}}
    </button>
    <Dialog v-model:visible="visible" modal :header="`Modelo ${index}`" :style="{ width: '25rem', display: 'flex', justifyContent: 'center' }">
        <div class="flex justify-center">
            <img :src="imageSrc">
        </div>
    </Dialog>
</template>
