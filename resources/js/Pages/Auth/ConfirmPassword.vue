<template>
    <Head title="Secure Area"/>

    <jet-authentication-card>
        <template #logo>
            <jet-authentication-card-logo/>
        </template>

        <div class="mb-4 text-sm text-gray-600">
            Ini adalah area aplikasi yang aman. Harap konfirmasi kata sandi Anda sebelum melanjutkan.
        </div>

        <jet-validation-errors class="mb-4"/>

        <form @submit.prevent="submit">
            <div>
                <jet-label for="password" value="Kata Sandi"/>
                <jet-input id="password" type="password" class="mt-1 block w-full" v-model="form.password" required
                           autocomplete="current-password" autofocus/>
            </div>

            <div class="flex justify-end mt-4">
                <jet-button class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Konfirmasi
                </jet-button>
            </div>
        </form>
    </jet-authentication-card>
</template>

<script>
import {defineComponent} from 'vue'
import {Head} from '@inertiajs/inertia-vue3'
import JetAuthenticationCard from '@/Jetstream/AuthenticationCard'
import JetAuthenticationCardLogo from '@/Jetstream/AuthenticationCardLogo'
import JetButton from '@/Jetstream/Button'
import JetInput from '@/Jetstream/Input'
import JetLabel from '@/Jetstream/Label'
import JetValidationErrors from '@/Jetstream/ValidationErrors'

export default defineComponent({
    components: {
        Head,
        JetAuthenticationCard,
        JetAuthenticationCardLogo,
        JetButton,
        JetInput,
        JetLabel,
        JetValidationErrors
    },
    data() {
        return {
            form: this.$inertia.form({
                password: null
            })
        }
    },
    methods: {
        submit() {
            this.form.post(this.route('password.confirm'), {
                onFinish: () => this.form.reset()
            })
        }
    }
})
</script>
