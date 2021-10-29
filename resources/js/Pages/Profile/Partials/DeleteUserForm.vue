<template>
    <jet-action-section>
        <template #title>
            Hapus akun
        </template>
        <template #description>
            Hapus akun Anda secara permanen.
        </template>
        <template #content>
            <div class="max-w-xl text-sm text-gray-600">
                Setelah akun Anda dihapus, semua sumber daya dan datanya akan dihapus secara permanen. Sebelum menghapus
                akun Anda, silakan unduh data atau informasi apa pun yang ingin Anda simpan.
            </div>
            <div class="mt-5">
                <jet-danger-button @click="confirmUserDeletion">
                    Hapus akun
                </jet-danger-button>
            </div>
            <jet-alert-modal :show="confirmingUserDeletion" @close="closeModal" title="Hapus akun">
                <template #content>
                    Apakah Anda yakin ingin menghapus akun Anda? Setelah akun Anda dihapus, semua sumber dayanya dan
                    data akan dihapus secara permanen. Silakan masukkan kata sandi Anda untuk mengonfirmasi bahwa Anda
                    ingin hapus akun Anda secara permanen.
                    <div class="mt-4">
                        <jet-input type="password" class="mt-1 block w-full normal-case" placeholder="Kata Sandi"
                                   ref="password"
                                   v-model="form.password"
                                   @keyup.enter="deleteUser"/>
                        <jet-input-error :message="form.errors.password" class="mt-2"/>
                    </div>
                </template>
                <template #buttons>
                    <jet-danger-button @click="deleteUser"
                                       :class="{ 'opacity-25': form.processing }"
                                       :disabled="form.processing"
                                       class="w-full inline-flex justify-center px-4 py-2 mt-2 sm:ml-3 sm:w-auto">
                        Hapus Akun
                    </jet-danger-button>
                    <jet-secondary-button @click="closeModal"
                                          class="w-full inline-flex justify-center px-4 py-2 mt-2 sm:ml-3 sm:w-auto">
                        Batalkan
                    </jet-secondary-button>
                </template>
            </jet-alert-modal>
        </template>
    </jet-action-section>
</template>

<script>
import {defineComponent} from 'vue'
import JetActionSection from '@/Jetstream/ActionSection'
import JetAlertModal from '@/Jetstream/AlertModal'
import JetDangerButton from '@/Jetstream/DangerButton'
import JetInput from '@/Jetstream/Input'
import JetInputError from '@/Jetstream/InputError'
import JetSecondaryButton from '@/Jetstream/SecondaryButton'

export default defineComponent({
    data() {
        return {
            confirmingUserDeletion: false,
            form: this.$inertia.form({
                password: null
            })
        }
    },
    components: {
        JetActionSection,
        JetAlertModal,
        JetInput,
        JetInputError,
        JetDangerButton,
        JetSecondaryButton
    },
    methods: {
        confirmUserDeletion() {
            this.confirmingUserDeletion = true
            setTimeout(() => this.$refs.password.focus(), 250)
        },
        deleteUser() {
            this.form.delete(route('current-user.destroy'), {
                preserveScroll: true,
                onSuccess: () => this.closeModal(),
                onError: () => this.$refs.password.focus(),
                onFinish: () => this.form.reset()
            })
        },
        closeModal() {
            this.confirmingUserDeletion = false
            this.form.reset()
        }
    }
})
</script>
