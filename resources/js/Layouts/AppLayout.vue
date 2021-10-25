<template>
    <Head :title="title"/>
    <administrator-layout :header="title" v-if="$page.props.user.role_id === 1">
        <slot></slot>
    </administrator-layout>
    <operator-layout :header="title" v-else>
        <slot></slot>
    </operator-layout>
</template>

<script>
import {defineComponent} from 'vue'
import {Head} from '@inertiajs/inertia-vue3'
import AdministratorLayout from '@/Layouts/Administrator/Layout'
import OperatorLayout from '@/Layouts/Operator/Layout'

export default defineComponent({
    props: {
        title: String
    },
    components: {
        Head,
        AdministratorLayout,
        OperatorLayout
    }
})
</script>
