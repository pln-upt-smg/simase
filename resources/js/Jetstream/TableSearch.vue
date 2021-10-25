<template>
    <div class="relative w-full">
        <input
            class="block w-full sm:text-sm rounded-md shadow-sm hover:bg-gray-50 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 border-gray-300 focus:border-indigo-300"
            type="text"
            :placeholder="placeholder"
            :value="value"
            @input="onChange($event.target.value)"/>
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
            <search-icon class="h-5 w-5 text-gray-400" aria-hidden="true"/>
        </div>
    </div>
</template>

<script>
import {defineComponent} from 'vue'
import {Components} from '@protonemedia/inertiajs-tables-laravel-query-builder'
import {SearchIcon} from '@heroicons/vue/outline'

export default defineComponent({
    mixins: [Components.TableGlobalSearch],
    props: {
        placeholder: {
            type: String,
            default: 'Cari...',
            required: false
        }
    },
    components: {
        SearchIcon
    }
})
</script>
