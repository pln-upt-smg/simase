<template>
    <app-layout title="Dashboard">
        <div>
            <jet-period-dropdown
                :selected="period"
                :periods="periods"
                :multiple-partials="['areaFinalSummaries', 'gapValueRank']"/>
        </div>
        <div class="px-4 py-5 bg-white sm:p-6 shadow rounded-lg mt-7">
            <final-summary-chart :labels="areas" :data="areaFinalSummaries"/>
        </div>
        <div class="px-4 py-5 bg-white sm:p-6 shadow rounded-lg mt-8">
            <gap-value-rank-table :area="area" :data="gapValueRank"/>
        </div>
    </app-layout>
</template>

<script>
import {defineComponent} from 'vue'
import AppLayout from '@/Layouts/AppLayout'
import FinalSummaryChart from '@/Pages/Administrator/Dashboard/FinalSummaryChart'
import GapValueRankTable from '@/Pages/Administrator/Dashboard/GapValueRankTable'
import JetPeriodDropdown from '@/Jetstream/PeriodDropdown'

export default defineComponent({
    props: {
        area: Object,
        areas: Object,
        period: Object,
        periods: Object,
        areaFinalSummaries: Object,
        gapValueRank: Object
    },
    components: {
        AppLayout,
        FinalSummaryChart,
        GapValueRankTable,
        JetPeriodDropdown
    }
})
</script>
