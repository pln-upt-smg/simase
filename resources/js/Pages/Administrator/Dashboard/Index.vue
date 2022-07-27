<template>
    <app-layout title="Dashboard">
        <div
            class="px-4 py-5 bg-white sm:p-6 shadow rounded-lg"
            v-if="
                this.$page.props.user.division_id === 1 ||
                this.$page.props.user.division_id === 2
            "
        >
            <TotalAssetChart
                :ids="assetTypeIds"
                :labels="assetTypes"
                :data="TotalAssetChartData"
            />
        </div>
        <div
            class="px-4 py-5 bg-white sm:p-6 shadow r ounded-lg mt-8"
            v-if="
                this.$page.props.user.division_id === 1 ||
                this.$page.props.user.division_id === 2
            "
        >
            <RecentAssetSubmissionTable
                :data="RecentAssetSubmissionTableData"
            />
        </div>
        <div
            class="px-4 py-5 bg-white sm:p-6 shadow r ounded-lg mt-8"
            v-if="
                this.$page.props.user.division_id === 1 ||
                this.$page.props.user.division_id === 2
            "
        >
            <RecentAssetLossDamageTable
                :data="RecentAssetLossDamageTableData"
            />
        </div>
        <div
            class="px-4 py-5 bg-white sm:p-6 shadow r ounded-lg mt-8"
            v-if="
                this.$page.props.user.division_id === 1 ||
                this.$page.props.user.division_id === 2
            "
        >
            <RecentAssetTransferTable :data="RecentAssetTransferTableData" />
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
import TotalAssetChart from "@/Pages/Administrator/Dashboard/TotalAssetChart";
import RecentAssetSubmissionTable from "@/Pages/Administrator/Dashboard/RecentAssetSubmissionTable";
import RecentAssetLossDamageTable from "@/Pages/Administrator/Dashboard/RecentAssetLossDamageTable";
import RecentAssetTransferTable from "@/Pages/Administrator/Dashboard/RecentAssetTransferTable";
import KKUCertificateChart from "@/Pages/Administrator/Dashboard/KKUCertificateChart";
import KKURecentCertificateTable from "@/Pages/Administrator/Dashboard/KKURecentCertificateTable";

export default defineComponent({
    props: {
        provinces: Object,
        provinceIds: Object,
        assetTypes: Object,
        assetTypeIds: Object,
        TotalAssetChartData: Object,
        RecentAssetSubmissionTableData: Object,
        RecentAssetLossDamageTableData: Object,
        RecentAssetTransferTableData: Object,
        KKUCertificateChartData: Object,
        KKURecentCertificateTableData: Object,
    },
    components: {
        AppLayout,
        TotalAssetChart,
        RecentAssetSubmissionTable,
        RecentAssetLossDamageTable,
        RecentAssetTransferTable,
        KKUCertificateChart,
        KKURecentCertificateTable,
    },
});
</script>
