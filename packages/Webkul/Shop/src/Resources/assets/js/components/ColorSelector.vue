<script setup>
import { defineModel } from "vue";

const props = defineProps({
    colors: {
        type: Array,
    },
    label: {
        type: String,
    },
});

const selectedColor = defineModel();

const colorsPieces = (color) => {
    const pieces = color.split('_');
    return {
        category: pieces[0],
        name: pieces[1],
        color: pieces[2],
    }
}

const colorName = (color) => {
    return `${colorsPieces(color).name} ${colorsPieces(color).category}`.toUpperCase()
}

</script>

<template>
    <div>
        <div class="mb-2 uppercase text-center md:text-left">{{ props.label }}: {{ colorName(selectedColor.color) }}</div>
        <div class="flex flex-wrap gap-3 justify-center items-center">
            <div
                v-for="item in props.colors"
                :key="item.id"
                :class="{
                    'border-white': item.id != selectedColor.id,
                    'border-black border-2': item.id == selectedColor.id,
                }"
                :style="{backgroundColor: colorsPieces(item.color).color}"
                class="w-8 h-8 cursor-pointer rounded-full flex justify-center items-center"
                @click="selectedColor = item"
                v-tooltip="colorName(item.color)"
            >
                <svg
                    v-if="item.id == selectedColor.id"
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