<template>
    <input
        class="border-gray-300 focus:border-amber-300 focus:ring focus:ring-amber-200 focus:ring-opacity-50 rounded-md shadow-sm placeholder-gray-400 text-gray-800 disabled:text-gray-400 disabled:bg-gray-100 disabled:border-gray-300 disabled:cursor-not-allowed"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        ref="input"
    />
</template>

<script>
import { defineComponent } from "vue";

export default defineComponent({
    props: ["modelValue"],
    emits: ["update:modelValue"],
    methods: {
        focus() {
            this.$refs.input.focus();
        },
    },
});
</script>
