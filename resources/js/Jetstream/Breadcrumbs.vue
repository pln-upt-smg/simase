<template>
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-4">
            <li>
                <div>
                    <jet-link :href="route('dashboard')">
                        <HomeIcon class="flex-shrink-0 h-6 w-6 text-gray-600" aria-hidden="true"/>
                        <span class="sr-only">Menu</span>
                    </jet-link>
                </div>
            </li>
            <li v-for="page in pages" :key="page.name">
                <div class="flex items-center">
                    <svg class="flex-shrink-0 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                         fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z"/>
                    </svg>
                    <jet-link :href="route(page.href)"
                              class="ml-4 text-2xl font-semibold text-gray-600 hover:text-gray-800"
                              :aria-current="page.current ? 'page' : undefined">{{ page.name }}
                    </jet-link>
                </div>
            </li>
        </ol>
    </nav>
</template>

<script>
import {defineComponent} from 'vue'
import {Link as JetLink} from '@inertiajs/inertia-vue3'
import {HomeIcon} from '@heroicons/vue/solid'

export default defineComponent({
    props: {
        pages: Array
    },
    components: {
        JetLink,
        HomeIcon
    }
})
</script>
