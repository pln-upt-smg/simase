<template>
    <Head title="Masuk"/>
    <jet-authentication-card>
        <template #logo>
            <jet-authentication-card-logo/>
        </template>
        <jet-validation-errors class="mb-4"/>
        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>
        <form @submit.prevent="submit">
            <div>
                <jet-label for="nip" value="NIP"/>
                <jet-input id="nip" type="text" class="mt-1 block w-full" v-model="form.nip" required autofocus/>
            </div>
            <div class="mt-4">
                <jet-label for="password" value="Kata Sandi"/>
                <jet-input id="password" type="password" class="mt-1 block w-full" v-model="form.password" required
                           autocomplete="current-password"/>
            </div>
            <div class="block mt-4">
                <label class="flex float-left items-center pt-2">
                    <jet-checkbox name="remember" v-model:checked="form.remember"/>
                    <span class="ml-2 text-sm text-gray-600">Ingat saya</span>
                </label>
            </div>
            <div class="flex items-center justify-end">
                <Link v-if="canResetPassword" :href="route('password.request')"
                      class="underline text-sm text-gray-600 hover:text-gray-900">
                    Lupa kata sandi?
                </Link>
                <jet-button class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Masuk
                </jet-button>
            </div>
        </form>
    </jet-authentication-card>
</template>

<script>
import {defineComponent} from 'vue'
import JetAuthenticationCard from '@/Jetstream/AuthenticationCard'
import JetAuthenticationCardLogo from '@/Jetstream/AuthenticationCardLogo'
import JetButton from '@/Jetstream/Button'
import JetInput from '@/Jetstream/Input'
import JetCheckbox from '@/Jetstream/Checkbox'
import JetLabel from '@/Jetstream/Label'
import JetValidationErrors from '@/Jetstream/ValidationErrors'
import {Head, Link} from '@inertiajs/inertia-vue3'

export default defineComponent({
    components: {
        Head,
        JetAuthenticationCard,
        JetAuthenticationCardLogo,
        JetButton,
        JetInput,
        JetCheckbox,
        JetLabel,
        JetValidationErrors,
        Link
    },
    props: {
        canResetPassword: Boolean,
        status: String
    },
    data() {
        return {
            form: this.$inertia.form({
                nip: null,
                password: null,
                remember: false
            })
        }
    },
    methods: {
        submit() {
            this.form
                .transform(data => ({
                    ...data,
                    remember: this.form.remember ? 'on' : ''
                }))
                .post(this.route('login'), {
                    onFinish: () => this.form.reset('password')
                })
        }
    }
})
</script>
