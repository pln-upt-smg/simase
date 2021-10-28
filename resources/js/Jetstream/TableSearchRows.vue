<template>
    <div
        class="w-full bg-indigo-100 p-4 rounded mt-4 lg:mt-6"
        v-show="hasFiltersEnabled">
        <div class="flex flex-row space-x-4">
            <div class="space-y-4">
                <div v-for="(search, key) in enabledFilters" :key="key" class="h-8 flex items-center">
                    <span class="text-sm">{{ search.label }}</span>
                </div>
            </div>
            <div class="flex-grow space-y-4">
                <div v-for="(search, key) in enabledFilters" :key="key" class="h-8 flex items-center">
                    <div class="flex-grow relative">
                        <input
                            class="block w-full sm:text-sm rounded-md shadow-sm hover:bg-gray-50 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 border-gray-300 focus:border-indigo-300 lowercase"
                            type="text"
                            :ref="search.key"
                            :value="search.value"
                            @input="onChange(search.key, $event.target.value)"/>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <button
                                @click.prevent="onRemove(search.key)"
                                class="rounded-md text-gray-400 hover:text-gray-500">
                                <span class="sr-only">Remove search</span>
                                <x-icon class="h-5 w-5" aria-hidden="true"/>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {defineComponent} from 'vue'
import {Components} from '@protonemedia/inertiajs-tables-laravel-query-builder'
import {XIcon} from '@heroicons/vue/solid'

export default defineComponent({
    mixins: [Components.TableSearchRows],
    components: {
        XIcon
    }
})
</script>
