<template>
    <div class="flex text-sm lg:text-lg">
        <label for="period" class="text-sm font-semibold text-gray-700 w-14 mr-4 py-3">Periode</label>
        <select id="period" @change="loadPeriod($event.target.selectedIndex)"
                class="w-48 pl-3 pr-10 py-2 cursor-pointer border-gray-300 rounded-md hover:bg-gray-50 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <option :selected="selected === null">Semua Periode</option>
            <option v-for="period in periods" :selected="selected && selected.id === period.id">
                {{ period.name }}
            </option>
        </select>
    </div>
</template>

<script>
import {defineComponent} from 'vue'

export default defineComponent({
    props: {
        selected: Object,
        periods: Object,
        partial: String,
        multiplePartials: Array,
        enablePartial: {
            type: Boolean,
            default: true
        }
    },
    methods: {
        loadPeriod(index) {
            index--
            let options = {replace: true, preserveState: true, preserveScroll: true}
            let partials = ['period', 'periods']
            if (this.partial) partials.push(this.partial)
            if (this.multiplePartials) this.multiplePartials.forEach((partial) => {
                partials.push(partial)
            })
            if (this.enablePartial) options.only = partials
            this.$inertia.get(route(route().current(), route().params), {
                period: index >= 0 && this.periods[index] ? this.periods[index].id : 0
            }, options)
        }
    }
})
</script>
