<template>
    <app-layout title="Dashboard">
        <div class="px-4 py-5 bg-white sm:p-6 shadow rounded-lg">
            <final-summary-chart
                :ids="areaIds"
                :labels="areas"
                :data="areaFinalSummaries"
            />
        </div>
        <div class="px-4 py-5 bg-white sm:p-6 shadow rounded-lg mt-8">
            <gap-value-rank-table :area="area" :data="gapValueRank" />
        </div>
    </app-layout>
</template>

<script>
import { defineComponent } from "vue";
import AppLayout from "@/Layouts/AppLayout";
import FinalSummaryChart from "@/Pages/Administrator/Dashboard/FinalSummaryChart";
import GapValueRankTable from "@/Pages/Administrator/Dashboard/GapValueRankTable";

export default defineComponent({
    props: {
        area: Object,
        areas: Object,
        areaIds: Object,
        areaFinalSummaries: Object,
        gapValueRank: Object,
    },
    components: {
        AppLayout,
        FinalSummaryChart,
        GapValueRankTable,
    },
});
</script>
