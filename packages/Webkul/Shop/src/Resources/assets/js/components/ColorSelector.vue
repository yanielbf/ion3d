<script setup>
import { reactive } from "vue";

const props = defineProps({
    colors: {
        type: Array,
    },
    place: {
        type: Number,
    },
    label: {
        type: String,
    },
});

const emit = defineEmits(["changeColor"]);

const state = reactive({
    selectedColor: props.colors[0],
});

function handlerChangeColor(color) {
    state.selectedColor = color;
    emit("changeColor", props.place, color);
}
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
                    'border-white': item.color != state.selectedColor.color,
                    'border-black border-2':
                        item.color == state.selectedColor.color,
                }"
                class="w-8 h-8 cursor-pointer rounded-full flex justify-center items-center"
                @click="handlerChangeColor(item)"
            >
                <svg
                    v-if="item.color == state.selectedColor.color"
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
