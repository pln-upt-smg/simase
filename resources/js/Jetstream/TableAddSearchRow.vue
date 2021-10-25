<template>
    <jet-table-dropdown-button ref="dropdown" v-if="hasRows" :disabled="!rowsLeft" class="w-auto">
        <template #button>
            <plus-circle-icon class="h-5 w-5 mr-2 text-gray-400" aria-hidden="true"/>
            <span>Filter</span>
        </template>
        <div role="menu" aria-orientation="vertical" aria-labelledby="sort-menu">
            <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                <button
                    v-for="searchOption in rows"
                    v-show="showRow(searchOption)"
                    :key="searchOption.key"
                    @click.prevent="enableSearch(searchOption.key)"
                    class="text-left w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                    role="menuitem">
                    {{ searchOption.label }}
                </button>
            </div>
        </div>
    </jet-table-dropdown-button>
</template>

<script>
import {defineComponent} from 'vue'
import {Components} from '@protonemedia/inertiajs-tables-laravel-query-builder'
import {PlusCircleIcon} from '@heroicons/vue/outline'
import JetTableDropdownButton from '@/Jetstream/TableDropdownButton'

export default defineComponent({
    mixins: [Components.TableAddSearchRow],
    components: {
        JetTableDropdownButton,
        PlusCircleIcon
    }
})
</script>
