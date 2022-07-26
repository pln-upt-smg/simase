<template>
    <app-layout title="Dashboard">
        <div
            class="px-4 py-5 bg-white sm:p-6 shadow rounded-lg"
            v-if="
                this.$page.props.user.division_id === 1 ||
                this.$page.props.user.division_id === 2
            "
        >
            <KKUCertificateChart
                :ids="provinceIds"
                :labels="provinces"
                :data="KKUCertificateChartData"
            />
        </div>
        <div
            class="px-4 py-5 bg-white sm:p-6 shadow rounded-lg mt-8"
            v-if="
                this.$page.props.user.division_id === 1 ||
                this.$page.props.user.division_id === 2
            "
        >
            <KKURecentCertificateTable :data="KKURecentCertificateTableData" />
        </div>
        <div
            class="px-4 py-5 bg-white sm:p-6 shadow rounded-lg"
            v-if="this.$page.props.user.division_id === 3"
        >
            <KKUCertificateChart
                :ids="provinceIds"
                :labels="provinces"
                :data="KKUCertificateChartData"
            />
        </div>
        <div
            class="px-4 py-5 bg-white sm:p-6 shadow rounded-lg mt-8"
            v-if="this.$page.props.user.division_id === 3"
        >
            <KKURecentCertificateTable :data="KKURecentCertificateTableData" />
        </div>
    </app-layout>
</template>

<script>
import { defineComponent } from "vue";
import AppLayout from "@/Layouts/AppLayout";
import KKUCertificateChart from "@/Pages/Administrator/Dashboard/KKUCertificateChart";
import KKURecentCertificateTable from "@/Pages/Administrator/Dashboard/KKURecentCertificateTable";

export default defineComponent({
    props: {
        provinces: Object,
        provinceIds: Object,
        KKUCertificateChartData: Object,
        KKURecentCertificateTableData: Object,
    },
    components: {
        AppLayout,
        KKUCertificateChart,
        KKURecentCertificateTable,
    },
});
</script>
