<template>
    <TransitionRoot as="template" :show="show">
        <Dialog as="div" static class="fixed inset-0 overflow-hidden z-20" :show="show" :closeable="closeable"
                @close="close">
            <div class="absolute inset-0 overflow-hidden">
                <TransitionChild as="template" enter="ease-in-out duration-500" enter-from="opacity-0"
                                 enter-to="opacity-100" leave="ease-in-out duration-500" leave-from="opacity-100"
                                 leave-to="opacity-0">
                    <DialogOverlay class="absolute inset-0 bg-gray-500 bg-opacity-75 transition-opacity"/>
                </TransitionChild>
                <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
                    <TransitionChild as="template" enter="transform transition ease-in-out duration-500 sm:duration-700"
                                     enter-from="translate-x-full" enter-to="translate-x-0"
                                     leave="transform transition ease-in-out duration-500 sm:duration-700"
                                     leave-from="translate-x-0" leave-to="translate-x-full">
                        <div class="w-screen max-w-md">
                            <div class="h-full flex flex-col bg-white shadow-xl overflow-y-hidden">
                                <div class="py-6 px-4 bg-indigo-700 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <DialogTitle class="text-lg font-medium text-white">
                                            Notifikasi
                                        </DialogTitle>
                                        <div class="ml-3 h-7 flex items-center">
                                            <button
                                                class="bg-indigo-700 rounded-md text-indigo-200 hover:text-white focus:outline-none"
                                                @click="close">
                                                <span class="sr-only">Tutup</span>
                                                <x-icon class="h-6 w-6" aria-hidden="true"/>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="relative flex-1 py-2 px-4 sm:px-6">
                                    <div class="absolute inset-0 py-2 px-4 sm:px-6">
                                        <notification-list/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script>
import {defineComponent} from 'vue'
import {Dialog, DialogOverlay, DialogTitle, TransitionChild, TransitionRoot} from '@headlessui/vue'
import {XIcon} from '@heroicons/vue/outline'
import NotificationList from '@/Layouts/NotificationList'

export default defineComponent({
    emits: ['close'],
    props: {
        show: {
            type: Boolean,
            default: false
        },
        closeable: {
            type: Boolean,
            default: true
        }
    },
    components: {
        NotificationList,
        Dialog,
        DialogOverlay,
        DialogTitle,
        TransitionChild,
        TransitionRoot,
        XIcon
    },
    methods: {
        close() {
            this.$page.props.unreadNotificationCount = 0
            this.$inertia.reload({
                replace: true,
                preserveScroll: true,
                preserveState: true,
                headers: {'Readed-Notification': 1}
            })
            this.$emit('close')
        }
    }
})
</script>
