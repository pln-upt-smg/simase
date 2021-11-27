<template>
    <span @click="startConfirmingPassword">
        <slot/>
    </span>
    <TransitionRoot as="template" :show="confirmingPassword">
        <Dialog as="div" static class="fixed z-10 inset-0 overflow-y-auto" :show="confirmingPassword"
                @close="closeModal">
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
                            <div class="text-center sm:mt-0 sm:text-left">
                                <DialogTitle as="h3" class="text-lg leading-6 font-bold text-gray-900">
                                    {{ title }}
                                </DialogTitle>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        {{ content }}
                                    </p>
                                </div>
                                <div class="mt-4">
                                    <jet-input type="password"
                                               class="block w-full"
                                               placeholder="Kata Sandi"
                                               ref="password"
                                               v-model="form.password"
                                               @keyup.enter="confirmPassword"/>
                                    <jet-input-error :message="form.error" class="mt-2"/>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                            <jet-button @click="confirmPassword"
                                        :class="{ 'opacity-25': form.processing }"
                                        :disabled="form.processing"
                                        class="w-full inline-flex justify-center px-4 py-2 mt-2 sm:ml-3 sm:w-auto">
                                {{ button }}
                            </jet-button>
                            <jet-secondary-button @click="closeModal"
                                                  class="w-full inline-flex justify-center px-4 py-2 mt-2 sm:ml-3 sm:w-auto">
                                Batalkan
                            </jet-secondary-button>
                        </div>
                    </div>
                </TransitionChild>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script>
import {defineComponent, ref} from 'vue'
import {Dialog, DialogOverlay, DialogTitle, TransitionChild, TransitionRoot} from '@headlessui/vue'
import JetInput from '@/Jetstream/Input'
import JetInputError from '@/Jetstream/InputError'
import JetButton from '@/Jetstream/Button'
import JetSecondaryButton from '@/Jetstream/SecondaryButton'

export default defineComponent({
    data() {
        return {
            confirmingPassword: false,
            form: {
                password: null,
                error: null
            }
        }
    },
    setup() {
        const open = ref(true)
        return {
            open
        }
    },
    emits: ['confirmed'],
    props: {
        title: {
            default: 'Konfirmasi Kata Sandi'
        },
        content: {
            default: 'Harap konfirmasikan kata sandi Anda untuk melanjutkan aksi ini.'
        },
        button: {
            default: 'Konfirmasi'
        }
    },
    components: {
        Dialog,
        DialogOverlay,
        DialogTitle,
        TransitionChild,
        TransitionRoot,
        JetButton,
        JetInput,
        JetInputError,
        JetSecondaryButton
    },
    methods: {
        startConfirmingPassword() {
            axios.get(route('password.confirmation')).then(response => {
                if (response.data.confirmed) {
                    this.$emit('confirmed')
                } else {
                    this.confirmingPassword = true
                    setTimeout(() => this.$refs.password.focus(), 250)
                }
            })
        },
        confirmPassword() {
            this.form.processing = true
            axios.post(route('password.confirm'), {
                password: this.form.password
            }).then(() => {
                this.form.processing = false
                this.closeModal()
                this.$nextTick(() => this.$emit('confirmed'))
            }).catch(error => {
                this.form.processing = false
                this.form.error = error.response.data.errors.password[0]
                this.$refs.password.focus()
            })
        },
        closeModal() {
            this.confirmingPassword = false
            this.form.password = null
            this.form.error = null
        }
    }
})
</script>
