<script setup>
import { computed, onMounted, defineModel } from "vue";

const props = defineProps({
    colors: {
        type: Array,
    },
    label: {
        type: String,
    },
});

const selectedColor = defineModel();

</script>

<template>
    <div>
        <div class="mb-2">{{ props.label }}</div>
        <div class="flex flex-wrap gap-3">
            <div
                v-for="item in props.colors"
                :key="item.id"
                :class="{
                    [`bg-[${item.color}]`]: true,
                    'border-white': item.color != selectedColor.color,
                    'border-black border-2':
                        item.color == selectedColor.color,
                }"
                class="w-8 h-8 cursor-pointer rounded-full flex justify-center items-center"
                @click="selectedColor = item"
            >
                <svg
                    v-if="item.color == selectedColor.color"
                    class="w-6 h-6 text-white"
                    aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke="currentColor"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 11.917 9.724 16.5 19 7.5"
                    />
                </svg>
            </div>
        </div>
    </div>
</template>
