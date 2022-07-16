<template>
    <jet-table-dropdown-button
        placement="bottom-end"
        :active="hasEnabledFilter"
    >
        <template #button>
            <filter-icon
                class="h-5 w-5"
                :class="{
                    'text-gray-400': !hasEnabledFilter,
                    'text-amber-600': hasEnabledFilter,
                }"
                aria-hidden="true"
            />
        </template>
        <div
            role="menu"
            aria-orientation="vertical"
            aria-labelledby="sort-menu"
        >
            <div v-for="filter in filters" :key="filter.key">
                <h3 class="text-xs uppercase tracking-wide bg-gray-100 p-3">
                    {{ filter.label }}
                </h3>
                <div class="p-2">
                    <select
                        :value="filter.value"
                        @change="onChange(filter.key, $event.target.value)"
                        class="block cursor-pointer w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring focus:ring-amber-200 focus:ring-opacity-50"
                    >
                        <option
                            v-for="(option, key) in filter.options"
                            :value="key"
                            :key="key"
                            >{{ option }}</option
                        >
                    </select>
                </div>
            </div>
        </div>
    </jet-table-dropdown-button>
</template>

<script>
import { defineComponent } from "vue";
import { Components } from "@protonemedia/inertiajs-tables-laravel-query-builder";
import { FilterIcon } from "@heroicons/vue/solid";
import JetTableDropdownButton from "@/Jetstream/TableDropdownButton";

export default defineComponent({
    mixins: [Components.TableFilter],
    components: {
        JetTableDropdownButton,
        FilterIcon,
    },
});
</script>
