<template>
    <div v-if="notifications.length > 0">
        <ul class="divide-y divide-gray-200">
            <li v-for="notification in notifications" :key="notification.id"
                class="relative bg-white py-4 hover:bg-gray-50">
                <div class="flex justify-between space-x-3">
                    <div class="min-w-0 flex-1">
                        <span class="absolute inset-0" aria-hidden="true"/>
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ notification.title }}</p>
                    </div>
                    <time :datetime="notification.created_at" class="flex-shrink-0 whitespace-nowrap text-sm text-gray-500">
                        {{ notification.time }}
                    </time>
                </div>
                <div class="mt-1">
                    <p class="line-clamp-2 text-sm text-gray-600">
                        {{ notification.description }}
                    </p>
                </div>
            </li>
        </ul>
    </div>
    <div v-else class="text-center text-gray-800 font-medium text-base mt-8">
        Belum ada notifikasi tersedia.
    </div>
</template>

<script>
import {defineComponent} from 'vue'

export default defineComponent({
    data() {
        return {
            notifications: this.$page.props.notifications
        }
    }
})
</script>
