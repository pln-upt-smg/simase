<template>
    <select @change="$emit('update:modelValue', this.resolve($event.target.selectedIndex))" ref="select"
            class="pl-3 pr-10 py-2 cursor-pointer text-gray-800 text-sm lg:text-base border-gray-300 rounded-md shadow-sm focus:ring focus:ring-indigo-200 focus:ring-opacity-50 disabled:text-gray-800 disabled:bg-gray-100 disabled:border-gray-300 disabled:cursor-not-allowed"
            :disabled="disabled">
        <option selected>{{ placeholder }}</option>
        <option v-for="entity in data" :key="'option-' + entity.id" :selected="modelValue === entity.id">
            {{ entity.name }}
        </option>
    </select>
</template>

<script>
import {defineComponent} from 'vue'

export default defineComponent({
    props: {
        placeholder: String,
        data: Object,
        modelValue: Number,
        disabled: {
            type: Boolean,
            default: false
        }
    },
    emits: ['update:modelValue'],
    methods: {
        focus() {
            this.$refs.select.focus()
        },
        resolve(index) {
            index--
            return index >= 0 && this.data ? this.data[index].id : 0;
        }
    }
})
</script>
