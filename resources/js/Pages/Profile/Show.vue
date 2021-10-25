<template>
    <app-layout title="Profil Saya">
        <grid-header>
            <jet-bredcrumbs :pages="[{name: 'Pengaturan', href: 'profile.show', current: true}]"/>
        </grid-header>
        <div v-if="$page.props.jetstream.canUpdateProfileInformation">
            <update-profile-information-form :user="$page.props.user"/>
            <jet-section-border/>
        </div>
        <div v-if="$page.props.jetstream.canUpdatePassword">
            <update-password-form class="mt-10 sm:mt-0"/>
            <jet-section-border/>
        </div>
        <div v-if="$page.props.jetstream.canManageTwoFactorAuthentication">
            <two-factor-authentication-form class="mt-10 sm:mt-0"/>
            <jet-section-border/>
        </div>
        <logout-other-browser-sessions-form :sessions="sessions" class="mt-10 sm:mt-0"/>
        <template v-if="$page.props.jetstream.hasAccountDeletionFeatures && $page.props.user.role_id !== 1">
            <jet-section-border/>
            <delete-user-form class="mt-10 sm:mt-0"/>
        </template>
    </app-layout>
</template>

<script>
import {defineComponent} from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import GridHeader from '@/Layouts/GridHeader.vue'
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue'
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue'
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue'
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue'
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue'
import JetBredcrumbs from '@/Jetstream/Breadcrumbs.vue'
import JetButton from '@/Jetstream/Button.vue'
import JetSectionBorder from '@/Jetstream/SectionBorder.vue'

export default defineComponent({
    props: {
        sessions: Object
    },
    components: {
        AppLayout,
        GridHeader,
        DeleteUserForm,
        LogoutOtherBrowserSessionsForm,
        TwoFactorAuthenticationForm,
        UpdatePasswordForm,
        UpdateProfileInformationForm,
        JetBredcrumbs,
        JetButton,
        JetSectionBorder
    }
})
</script>
