<template>
    <jet-action-section>
        <template #title>
            Otentikasi Dua Faktor
        </template>
        <template #description>
            Tambahkan keamanan tambahan ke akun Anda menggunakan otentikasi dua faktor.
        </template>
        <template #content>
            <h3 class="text-lg font-medium text-gray-900" v-if="twoFactorEnabled">
                Anda telah mengaktifkan otentikasi dua faktor.
            </h3>
            <h3 class="text-lg font-medium text-gray-900" v-else>
                Anda belum mengaktifkan otentikasi dua faktor.
            </h3>
            <div class="mt-3 max-w-xl text-sm text-gray-600">
                <p>
                    Saat otentikasi dua faktor diaktifkan, Anda akan dimintai token acak yang aman selama
                    autentikasi. Anda dapat mengambil token ini dari aplikasi Google Authenticator ponsel Anda.
                </p>
            </div>
            <div v-if="twoFactorEnabled">
                <div v-if="qrCode">
                    <div class="mt-4 max-w-xl text-sm text-gray-600">
                        <p class="font-semibold">
                            Otentikasi dua faktor sekarang diaktifkan. Pindai kode QR berikut menggunakan ponsel Anda
                            pada aplikasi autentikator.
                        </p>
                    </div>
                    <div class="mt-4" v-html="qrCode"></div>
                </div>
                <div v-if="recoveryCodes.length > 0">
                    <div class="mt-4 max-w-xl text-sm text-gray-600">
                        <p class="font-semibold">
                            Simpan kode pemulihan ini di pengelola kata sandi yang aman. Kode ini dapat digunakan untuk
                            memulihkan akses ke akun Anda jika perangkat otentikasi dua faktor Anda hilang.
                        </p>
                    </div>
                    <div class="grid gap-1 max-w-xl mt-4 px-4 py-4 font-mono text-sm bg-gray-100 rounded-lg">
                        <div v-for="code in recoveryCodes" :key="code">
                            {{ code }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-5">
                <div v-if="! twoFactorEnabled">
                    <jet-confirm-password-modal @confirmed="enableTwoFactorAuthentication">
                        <jet-button type="button" :class="{ 'opacity-25': enabling }" :disabled="enabling">
                            Aktifkan
                        </jet-button>
                    </jet-confirm-password-modal>
                </div>
                <div v-else>
                    <jet-confirm-password-modal @confirmed="regenerateRecoveryCodes">
                        <jet-secondary-button class="mr-3"
                                              v-if="recoveryCodes.length > 0">
                            Buat Ulang Kode Pemulihan
                        </jet-secondary-button>
                    </jet-confirm-password-modal>
                    <jet-confirm-password-modal @confirmed="showRecoveryCodes">
                        <jet-secondary-button class="mr-3" v-if="recoveryCodes.length === 0">
                            Tampilkan Kode Pemulihan
                        </jet-secondary-button>
                    </jet-confirm-password-modal>
                    <jet-confirm-password-modal @confirmed="disableTwoFactorAuthentication">
                        <jet-danger-button
                            :class="{ 'opacity-25': disabling }"
                            :disabled="disabling">
                            Non-aktifkan
                        </jet-danger-button>
                    </jet-confirm-password-modal>
                </div>
            </div>
        </template>
    </jet-action-section>
</template>

<script>
import {defineComponent} from 'vue'
import JetActionSection from '@/Jetstream/ActionSection'
import JetButton from '@/Jetstream/Button'
import JetConfirmPasswordModal from '@/Jetstream/ConfirmPasswordModal'
import JetDangerButton from '@/Jetstream/DangerButton'
import JetSecondaryButton from '@/Jetstream/SecondaryButton'

export default defineComponent({
    components: {
        JetActionSection,
        JetButton,
        JetDangerButton,
        JetSecondaryButton,
        JetConfirmPasswordModal
    },
    data() {
        return {
            enabling: false,
            disabling: false,
            qrCode: null,
            recoveryCodes: []
        }
    },
    methods: {
        enableTwoFactorAuthentication() {
            this.enabling = true
            this.$inertia.post('/user/two-factor-authentication', {}, {
                preserveScroll: true,
                onSuccess: () => Promise.all([
                    this.showQrCode(),
                    this.showRecoveryCodes(),
                ]),
                onFinish: () => (this.enabling = false)
            })
        },
        showQrCode() {
            return axios.get('/user/two-factor-qr-code')
                .then(response => {
                    this.qrCode = response.data.svg
                })
        },
        showRecoveryCodes() {
            return axios.get('/user/two-factor-recovery-codes')
                .then(response => {
                    this.recoveryCodes = response.data
                })
        },
        regenerateRecoveryCodes() {
            axios.post('/user/two-factor-recovery-codes')
                .then(() => {
                    this.showRecoveryCodes()
                })
        },
        disableTwoFactorAuthentication() {
            this.disabling = true
            this.$inertia.delete('/user/two-factor-authentication', {
                preserveScroll: true,
                onSuccess: () => (this.disabling = false)
            })
        }
    },
    computed: {
        twoFactorEnabled() {
            return !this.enabling && this.$page.props.user.two_factor_enabled
        }
    }
})
</script>
