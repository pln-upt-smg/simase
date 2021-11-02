<template>
    <div>
        <div class="sm:hidden">
            <label for="tabs" class="sr-only">Pilih Tab</label>
            <select id="tabs" name="tabs"
                    class="block w-full focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md"
                    @change="reload($event.target.selectedIndex)">
                <option v-for="tab in tabs" :key="tab.name" :selected="route().current(tab.href)">
                    {{ tab.name }}
                </option>
            </select>
        </div>
        <div class="hidden sm:block">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <jet-link v-for="tab in tabs" :key="tab.name" :href="route(tab.href)"
                              :class="[route().current(tab.href) ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm']"
                              :aria-current="route().current(tab.href) ? 'page' : undefined">
                        <component :is="tab.icon"
                                   :class="[route().current(tab.href) ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500', '-ml-0.5 mr-2 h-5 w-5']"
                                   aria-hidden="true"/>
                        <span>{{ tab.name }}</span>
                    </jet-link>
                </nav>
            </div>
        </div>
    </div>
</template>

<script>
import {defineComponent} from 'vue'
import {Link as JetLink} from '@inertiajs/inertia-vue3'
import {ArchiveIcon, CubeIcon} from '@heroicons/vue/solid'

const tabs = [
    {name: 'Material Code', href: 'stocks.create', icon: ArchiveIcon},
    {name: 'SKU Code', href: 'stocks.sku.create', icon: CubeIcon}
]

export default defineComponent({
    components: {
        JetLink,
        ArchiveIcon,
        CubeIcon
    },
    setup() {
        return {
            tabs
        }
    },
    methods: {
        reload(index) {
            if (index > this.tabs.length - 1) return
            const tab = this.tabs[index]
            if (route().current() === tab.href) return
            this.$inertia.get(route(tab.href), {}, {
                replace: false,
                preserveState: true,
                preserveScroll: true
            })
        }
    }
})
</script>
