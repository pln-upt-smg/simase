<template>
    <div class="flex text-sm lg:text-lg">
        <label for="area" class="text-sm font-semibold text-gray-700 w-14 mr-4 py-3">Area</label>
        <select id="area" @change="loadArea($event.target.selectedIndex)"
                class="w-48 pl-3 pr-10 py-2 cursor-pointer border-gray-300 rounded-md hover:bg-gray-50 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <option :selected="selected === null">Semua Area</option>
            <option v-for="area in areas" :selected="selected && selected.id === area.id">
                {{ area.name }}
            </option>
        </select>
    </div>
</template>

<script>
import {defineComponent} from 'vue'

export default defineComponent({
    props: {
        selected: Object,
        areas: Object,
        partial: String
    },
    methods: {
        loadArea(index) {
            index--
            this.$inertia.get(route(route().current(), route().params), {
                area: index >= 0 && this.areas[index] ? this.areas[index].id : 0
            }, {
                replace: true,
                preserveState: true,
                preserveScroll: true,
                only: [
                    this.partial ? this.partial : '',
                    'area',
                    'areas'
                ]
            })
        }
    }
})
</script>
