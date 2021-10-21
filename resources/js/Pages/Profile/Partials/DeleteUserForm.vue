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

            <!-- Delete Account Confirmation Modal -->
            <jet-dialog-modal :show="confirmingUserDeletion" @close="closeModal">
                <template #title>
                    Hapus akun
                </template>

                <template #content>
                    Apakah Anda yakin ingin menghapus akun Anda? Setelah akun Anda dihapus, semua sumber dayanya dan
                    data akan dihapus secara permanen. Silakan masukkan kata sandi Anda untuk mengonfirmasi bahwa Anda
                    ingin hapus akun Anda secara permanen.

                    <div class="mt-4">
                        <jet-input type="password" class="mt-1 block w-3/4" placeholder="Kata Sandi"
                                   ref="password"
                                   v-model="form.password"
                                   @keyup.enter="deleteUser"/>

                        <jet-input-error :message="form.errors.password" class="mt-2"/>
                    </div>
                </template>

                <template #footer>
                    <jet-secondary-button @click="closeModal">
                        Batalkan
                    </jet-secondary-button>

                    <jet-danger-button class="ml-2"
                                       @click="deleteUser"
                                       :class="{ 'opacity-25': form.processing }"
                                       :disabled="form.processing">
                        Hapus Akun
                    </jet-danger-button>
                </template>
            </jet-dialog-modal>
        </template>
    </jet-action-section>
</template>

<script>
import {defineComponent} from 'vue'
import JetActionSection from '@/Jetstream/ActionSection.vue'
import JetDialogModal from '@/Jetstream/DialogModal.vue'
import JetDangerButton from '@/Jetstream/DangerButton.vue'
import JetInput from '@/Jetstream/Input.vue'
import JetInputError from '@/Jetstream/InputError.vue'
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue'

export default defineComponent({
    components: {
        JetActionSection,
        JetDangerButton,
        JetDialogModal,
        JetInput,
        JetInputError,
        JetSecondaryButton,
    },

    data() {
        return {
            confirmingUserDeletion: false,

            form: this.$inertia.form({
                password: '',
            })
        }
    },

    methods: {
        confirmUserDeletion() {
            this.confirmingUserDeletion = true;

            setTimeout(() => this.$refs.password.focus(), 250)
        },

        deleteUser() {
            this.form.delete(route('current-user.destroy'), {
                preserveScroll: true,
                onSuccess: () => this.closeModal(),
                onError: () => this.$refs.password.focus(),
                onFinish: () => this.form.reset(),
            })
        },

        closeModal() {
            this.confirmingUserDeletion = false

            this.form.reset()
        },
    },
})
</script>
