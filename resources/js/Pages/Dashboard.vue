<template>
    <administrator-dashboard v-if="$page.props.user.role_id === 1"/>
    <operator-dashboard v-else/>
</template>

<script>
import {defineComponent} from 'vue'
import AdministratorDashboard from '@/Pages/Administrator/Dashboard'
import OperatorDashboard from '@/Pages/Operator/Dashboard'

export default defineComponent({
    components: {
        AdministratorDashboard,
        OperatorDashboard
    },
})
</script>
