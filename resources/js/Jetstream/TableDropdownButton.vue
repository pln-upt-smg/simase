<template>
    <on-click-outside :do="hide">
        <div class="relative">
            <button
                @click.prevent="toggle"
                type="button"
                :disabled="disabled"
                class="w-full bg-white hover:bg-gray-50 border rounded-md shadow-sm px-4 py-2 inline-flex justify-center text-sm font-medium text-gray-700 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 border-gray-300 focus:border-indigo-300"
                :class="{'border-indigo-600': active, 'border-gray-300': !active, 'cursor-not-allowed': disabled }"
                aria-haspopup="true"
                ref="button">
                <slot name="button"/>
            </button>
            <transition
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95">
                <div ref="tooltip" class="absolute z-20" v-show="opened">
                    <div
                        class="mt-2 w-64 rounded-md shadow-lg bg-white">
                        <slot/>
                    </div>
                </div>
            </transition>
        </div>
    </on-click-outside>
</template>

<script>
import {defineComponent} from 'vue'
import {Components} from '@protonemedia/inertiajs-tables-laravel-query-builder'

export default defineComponent({
    mixins: [Components.ButtonWithDropdown],
    components: {
        OnClickOutside: Components.OnClickOutside
    }
})
</script>
