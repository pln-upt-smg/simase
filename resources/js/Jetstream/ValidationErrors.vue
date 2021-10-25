<template>
    <div v-if="hasErrors" class="rounded-md bg-red-50 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <XCircleIcon class="h-5 w-5 text-red-400" aria-hidden="true"/>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">
                    Sistem mendeteksi {{ Object.keys(errors).length }} kesalahan.
                </h3>
                <div class="mt-2 text-sm text-red-700">
                    <ul class="list-disc pl-5 space-y-1">
                        <li v-for="(error, key) in errors" :key="key">{{ error }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {defineComponent} from 'vue'
import {XCircleIcon} from '@heroicons/vue/solid'

export default defineComponent({
    components: {
        XCircleIcon
    },
    computed: {
        errors() {
            return this.$page.props.errors
        },
        hasErrors() {
            return Object.keys(this.errors).length > 0
        }
    }
})
</script>
