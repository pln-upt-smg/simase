<template>
    <TransitionRoot as="template" :show="show">
        <Dialog as="div" static class="fixed z-10 inset-0 overflow-y-auto" :show="show" :closeable="closeable"
                @close="close">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0"
                                 enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100"
                                 leave-to="opacity-0">
                    <DialogOverlay class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"/>
                </TransitionChild>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <TransitionChild as="template" enter="ease-out duration-300"
                                 enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                 enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                                 leave-from="opacity-100 translate-y-0 sm:scale-100"
                                 leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    <div
                        class="inline-block align-bottom bg-white rounded-lg p-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                <upload-icon class="h-6 w-6 text-indigo-600" aria-hidden="true"/>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <DialogTitle as="h3" class="text-lg leading-6 font-bold text-gray-900">
                                    {{ title }}
                                </DialogTitle>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        <slot name="content"></slot>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                            <slot name="buttons"></slot>
                        </div>
                    </div>
                </TransitionChild>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script>
import {defineComponent} from 'vue'
import {Dialog, DialogOverlay, DialogTitle, TransitionChild, TransitionRoot} from '@headlessui/vue'
import {UploadIcon} from '@heroicons/vue/outline'

export default defineComponent({
    emits: ['close'],
    props: {
        show: {
            default: false
        },
        closeable: {
            default: true
        },
        title: String
    },
    components: {
        Dialog,
        DialogOverlay,
        DialogTitle,
        TransitionChild,
        TransitionRoot,
        UploadIcon
    },
    methods: {
        close() {
            this.$emit('close')
        }
    }
})
</script>
