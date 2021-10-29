<template>
    <jet-action-section>
        <template #title>
            Sesi Peramban
        </template>
        <template #description>
            Kelola dan keluar dari sesi aktif Anda di browser dan perangkat lain.
        </template>
        <template #content>
            <div class="max-w-xl text-sm text-gray-600">
                Jika perlu, Anda dapat keluar dari semua sesi browser lainnya di semua perangkat Anda. Beberapa
                sesi terakhir Anda tercantum di bawah ini. Jika Anda merasa Anda akun telah disusupi,
                Anda juga harus memperbarui kata sandi Anda.
            </div>
            <div class="mt-5 space-y-6" v-if="sessions.length > 0">
                <div class="flex items-center" v-for="(session, i) in sessions" :key="i">
                    <div>
                        <svg fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                             viewBox="0 0 24 24" stroke="currentColor" class="w-8 h-8 text-gray-500"
                             v-if="session.agent.is_desktop">
                            <path
                                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2"
                             stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"
                             class="w-8 h-8 text-gray-500" v-else>
                            <path d="M0 0h24v24H0z" stroke="none"></path>
                            <rect x="7" y="4" width="10" height="16" rx="1"></rect>
                            <path d="M11 5h2M12 17v.01"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <div class="text-sm text-gray-600">
                            {{ session.agent.platform }} - {{ session.agent.browser }}
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">
                                {{ session.ip_address }},
                                <span class="text-green-500 font-semibold"
                                      v-if="session.is_current_device">Gawai ini</span>
                                <span v-else>Last active {{ session.last_active }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center mt-5">
                <jet-button @click="confirmLogout">
                    Keluar dari Sesi Peramban Lain
                </jet-button>
                <jet-action-message :on="form.recentlySuccessful" class="ml-3">
                    Selesai.
                </jet-action-message>
            </div>
            <jet-modal :show="confirmingLogout" @close="closeModal" title="Keluar dari Sesi Peramban Lain">
                <template #content>
                    Silakan masukkan kata sandi Anda untuk mengonfirmasi bahwa Anda ingin keluar dari sesi browser Anda
                    yang lain di semua perangkat Anda.
                    <div class="mt-4">
                        <jet-input type="password" class="mt-1 block w-full normal-case" placeholder="Kata Sandi"
                                   ref="password"
                                   v-model="form.password"
                                   @keyup.enter="logoutOtherBrowserSessions"/>
                        <jet-input-error :message="form.errors.password" class="mt-2"/>
                    </div>
                </template>
                <template #buttons>
                    <jet-button @click="logoutOtherBrowserSessions"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                                class="w-full inline-flex justify-center px-4 py-2 mt-2 sm:ml-3 sm:w-auto">
                        Konfirmasi
                    </jet-button>
                    <jet-secondary-button @click="closeModal"
                                          class="w-full inline-flex justify-center px-4 py-2 mt-2 sm:ml-3 sm:w-auto">
                        Batalkan
                    </jet-secondary-button>
                </template>
            </jet-modal>
        </template>
    </jet-action-section>
</template>

<script>
import {defineComponent} from 'vue'
import JetActionMessage from '@/Jetstream/ActionMessage'
import JetActionSection from '@/Jetstream/ActionSection'
import JetButton from '@/Jetstream/Button'
import JetModal from '@/Jetstream/Modal'
import JetInput from '@/Jetstream/Input'
import JetInputError from '@/Jetstream/InputError'
import JetSecondaryButton from '@/Jetstream/SecondaryButton'

export default defineComponent({
    props: ['sessions'],
    components: {
        JetActionMessage,
        JetActionSection,
        JetButton,
        JetModal,
        JetInput,
        JetInputError,
        JetSecondaryButton
    },
    data() {
        return {
            confirmingLogout: false,
            form: this.$inertia.form({
                password: null
            })
        }
    },
    methods: {
        confirmLogout() {
            this.confirmingLogout = true
            setTimeout(() => this.$refs.password.focus(), 250)
        },
        logoutOtherBrowserSessions() {
            this.form.delete(route('other-browser-sessions.destroy'), {
                preserveScroll: true,
                onSuccess: () => this.closeModal(),
                onError: () => this.$refs.password.focus(),
                onFinish: () => this.form.reset()
            })
        },
        closeModal() {
            this.confirmingLogout = false
            this.form.reset()
        }
    }
})
</script>
