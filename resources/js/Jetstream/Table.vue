<style scoped>
table :deep(th) {
    font-weight: 500;
    font-size: 0.75rem;
    line-height: 1rem;
    padding: 0.75rem 1.5rem;
    text-align: left;
    --tw-text-opacity: 1;
    color: rgba(31, 41, 55, var(--tw-text-opacity));
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

table :deep(td) {
    font-size: 0.875rem;
    line-height: 1.25rem;
    padding: 1rem 1.5rem;
    --tw-text-opacity: 1;
    color: rgba(107, 114, 128, var(--tw-text-opacity));
    white-space: nowrap;
}

table :deep(tr:hover td) {
    --tw-bg-opacity: 1;
    background-color: rgba(249, 250, 251, var(--tw-bg-opacity));
}
</style>

<template>
    <div>
        <div class="flex space-x-4">
            <slot
                name="tableFilter"
                :hasFilters="hasFilters"
                :filters="filters"
                :changeFilterValue="changeFilterValue">
                <table-filter v-if="hasFilters" :filters="filters" :on-change="changeFilterValue"/>
            </slot>
            <slot
                name="tableGlobalSearch"
                :search="search"
                :changeGlobalSearchValue="changeGlobalSearchValue">
                <div class="flex-grow">
                    <table-global-search
                        v-if="search && search.global"
                        :value="search.global.value"
                        :on-change="changeGlobalSearchValue"/>
                </div>
            </slot>
            <slot
                name="tableAddSearchRow"
                :hasSearchRows="hasSearchRows"
                :search="search"
                :newSearch="newSearch"
                :enableSearch="enableSearch">
                <table-add-search-row
                    v-if="hasSearchRows"
                    :rows="search"
                    :new="newSearch"
                    :on-add="enableSearch"/>
            </slot>
            <slot
                name="tableColumns"
                :hasColumns="hasColumns"
                :columns="columns"
                :changeColumnStatus="changeColumnStatus">
                <table-columns v-if="hasColumns" :columns="columns" :on-change="changeColumnStatus"/>
            </slot>
        </div>
        <slot
            name="tableSearchRows"
            :hasSearchRows="hasSearchRows"
            :search="search"
            :newSearch="newSearch"
            :disableSearch="disableSearch"
            :changeSearchValue="changeSearchValue">
            <table-search-rows
                ref="rows"
                v-if="hasSearchRows"
                :rows="search"
                :new="newSearch"
                :on-remove="disableSearch"
                :on-change="changeSearchValue"/>
        </slot>
        <slot name="tableWrapper" :meta="meta">
            <div class="flex flex-col">
                <div
                    class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8 scrollbar-thin scrollbar-thumb-indigo-800 scrollbar-track-indigo-200">
                    <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                        <div class="shadow border-b border-gray-200">
                            <slot name="table">
                                <table class="min-w-full divide-y divide-gray-200 bg-white mt-4 lg:mt-6 z-0">
                                    <thead class="bg-indigo-100 text-white">
                                    <tr class="clickable-table-header">
                                        <slot name="head"/>
                                    </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                    <slot name="body"/>
                                    </tbody>
                                </table>
                            </slot>
                            <slot name="pagination">
                                <table-pagination :meta="paginationMeta"/>
                            </slot>
                        </div>
                    </div>
                </div>
            </div>
        </slot>
    </div>
</template>

<script>
import {defineComponent} from 'vue'
import {Components, Tailwind2} from '@protonemedia/inertiajs-tables-laravel-query-builder'
import TableFilter from '@/Jetstream/TableFilter'
import TableGlobalSearch from '@/Jetstream/TableSearch'
import TableAddSearchRow from '@/Jetstream/TableAddSearchRow'
import TableSearchRows from '@/Jetstream/TableSearchRows'
import TablePagination from '@/Jetstream/TablePagination'

export default defineComponent({
    mixins: [Components.Table],
    components: {
        TableColumns: Tailwind2.TableColumns,
        TableFilter,
        TableGlobalSearch,
        TableAddSearchRow,
        TableSearchRows,
        TablePagination
    },
    methods: {
        reload(resource) {
            let options = {replace: true, preserveState: true, preserveScroll: true}
            if (resource) options.only = [resource]
            this.$inertia.get(route(route().current(), route().params), {}, options)
        }
    }
})
</script>
