<template>
    <TransitionRoot as="template" :show="show">
        <Dialog
            as="div"
            static
            class="fixed inset-0 overflow-hidden z-20"
            :show="show"
            :closeable="closeable"
            @close="close"
        >
            <div class="absolute inset-0 overflow-hidden">
                <TransitionChild
                    as="template"
                    enter="ease-in-out duration-500"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in-out duration-500"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <DialogOverlay
                        class="absolute inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    />
                </TransitionChild>
                <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
                    <TransitionChild
                        as="template"
                        enter="transform transition ease-in-out duration-500 sm:duration-700"
                        enter-from="translate-x-full"
                        enter-to="translate-x-0"
                        leave="transform transition ease-in-out duration-500 sm:duration-700"
                        leave-from="translate-x-0"
                        leave-to="translate-x-full"
                    >
                        <div class="w-screen max-w-md">
                            <div
                                class="h-full flex flex-col bg-white shadow-xl overflow-y-hidden"
                            >
                                <div class="py-6 px-4 bg-amber-700 sm:px-6">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <DialogTitle
                                            class="text-lg font-medium text-white"
                                        >
                                            {{ title }}
                                        </DialogTitle>
                                        <div class="ml-3 h-7 flex items-center">
                                            <button
                                                class="bg-amber-700 rounded-md text-amber-200 hover:text-white focus:outline-none"
                                                @click="close"
                                            >
                                                <span class="sr-only"
                                                    >Tutup</span
                                                >
                                                <x-icon
                                                    class="h-6 w-6"
                                                    aria-hidden="true"
                                                />
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mt-1">
                                        <p class="text-sm text-amber-300">
                                            Mohon arahkan kamera ke barcode
                                            target dengan pencahayaan ruangan
                                            yang cukup.
                                        </p>
                                    </div>
                                    <div
                                        v-if="error"
                                        class="mt-4 rounded-md bg-red-50 p-4"
                                    >
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <XCircleIcon
                                                    class="h-5 w-5 text-red-400"
                                                    aria-hidden="true"
                                                />
                                            </div>
                                            <div class="ml-3">
                                                <h3
                                                    class="text-sm font-medium text-red-800"
                                                >
                                                    Sistem mendeteksi 1
                                                    kesalahan.
                                                </h3>
                                                <div
                                                    class="mt-2 text-sm text-red-700"
                                                >
                                                    <ul
                                                        class="list-disc pl-5 space-y-1"
                                                    >
                                                        <li>{{ error }}</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="relative flex-1 py-6 px-4 sm:px-6">
                                    <div
                                        class="absolute inset-0 py-6 px-4 sm:px-6"
                                    >
                                        <div
                                            class="border-2 border-dashed border-gray-200 min-h-[18.5rem]"
                                            aria-hidden="true"
                                        >
                                            <stream-barcode-reader
                                                ref="scanner"
                                                @decode="onDecode"
                                                @error="onError"
                                            />
                                        </div>
                                        <div
                                            class="mt-6 rounded-md bg-blue-50 p-4 hidden lg:block"
                                        >
                                            <div class="flex">
                                                <div class="flex-shrink-0">
                                                    <information-circle-icon
                                                        class="h-5 w-5 text-blue-400"
                                                        aria-hidden="true"
                                                    />
                                                </div>
                                                <div class="ml-3">
                                                    <h3
                                                        class="text-sm font-medium text-blue-800"
                                                    >
                                                        Mohon perhatikan hal
                                                        berikut untuk
                                                        mendapatkan performa
                                                        pemindaian yang
                                                        maksimal.
                                                    </h3>
                                                    <div
                                                        class="mt-2 text-sm text-blue-700"
                                                    >
                                                        <ul
                                                            class="list-disc pl-5 space-y-1"
                                                        >
                                                            <li>
                                                                Arahkan barcode
                                                                ke kursor
                                                                pointer merah
                                                                saat melakukan
                                                                proses
                                                                pemindaian
                                                            </li>
                                                            <li>
                                                                Pastikkan
                                                                pencerahan pada
                                                                ruangan memadai
                                                            </li>
                                                            <li>
                                                                Gunakan latar
                                                                belakang putih
                                                                pada barcode
                                                            </li>
                                                            <li>
                                                                Pemindaian
                                                                Inverted-color
                                                                Barcode tidak
                                                                didukung!
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            class="mt-6 rounded-md bg-blue-50 p-4 visible lg:hidden"
                                        >
                                            <div
                                                class="flex text-sm text-blue-700"
                                            >
                                                <ul
                                                    class="list-disc pl-5 space-y-1"
                                                >
                                                    <li>
                                                        Arahkan barcode ke
                                                        kursor pointer merah
                                                        saat melakukan proses
                                                        pemindaian
                                                    </li>
                                                    <li>
                                                        Pastikkan pencerahan
                                                        pada ruangan memadai
                                                    </li>
                                                    <li>
                                                        Gunakan latar belakang
                                                        putih pada barcode
                                                    </li>
                                                    <li>
                                                        Pemindaian
                                                        Inverted-color Barcode
                                                        tidak didukung!
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
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
import { defineComponent } from "vue";
import { StreamBarcodeReader } from "vue-barcode-reader";
import {
    Dialog,
    DialogOverlay,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from "@headlessui/vue";
import {
    InformationCircleIcon,
    XCircleIcon,
    XIcon,
} from "@heroicons/vue/outline";

export default defineComponent({
    emits: ["close", "decode"],
    props: {
        title: {
            type: String,
            required: true,
        },
        show: {
            type: Boolean,
            default: false,
        },
        closeable: {
            type: Boolean,
            default: true,
        },
    },
    components: {
        StreamBarcodeReader,
        Dialog,
        DialogOverlay,
        DialogTitle,
        TransitionChild,
        TransitionRoot,
        XIcon,
        XCircleIcon,
        InformationCircleIcon,
    },
    data() {
        return {
            error: null,
        };
    },
    methods: {
        close() {
            setTimeout(() => {
                this.$refs.scanner.codeReader.reset();
            }, 400);
            this.$emit("close");
        },
        onDecode(code) {
            this.close();
            this.$emit("decode", code);
        },
        onError(error) {
            this.error = error;
        },
    },
});
</script>
