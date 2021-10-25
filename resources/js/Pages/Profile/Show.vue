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
import AppLayout from '@/Layouts/AppLayout'
import GridHeader from '@/Layouts/GridHeader'
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm'
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm'
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm'
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm'
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm'
import JetBredcrumbs from '@/Jetstream/Breadcrumbs'
import JetButton from '@/Jetstream/Button'
import JetSectionBorder from '@/Jetstream/SectionBorder'

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
