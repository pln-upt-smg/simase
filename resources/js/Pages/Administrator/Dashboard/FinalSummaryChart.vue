<style scoped>
.chart {
    height: 340px;
    display: flex;
    flex-direction: column;
}
</style>

<template>
    <div class="font-semibold text-lg">
        Final Summary
    </div>
    <div class="mt-6">
        <vue3-chart-js
            ref="chart"
            class="chart"
            :id="chartData.id"
            :type="chartData.type"
            :data="chartData.data"
            :options="chartData.options"/>
    </div>
</template>

<script>
import {defineComponent, ref} from 'vue'
import Vue3ChartJs from '@j-t-mcc/vue3-chartjs'

export default defineComponent({
    props: {
        ids: Object,
        labels: Object,
        data: Object,
        partials: {
            type: Array,
            default: [
                'area',
                'areas',
                'areaIds',
                'gapValueRank'
            ]
        }
    },
    components: {
        Vue3ChartJs
    },
    setup() {
        const chart = ref(null)
        const chartData = {
            id: 'final-summary-chart',
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    data: []
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        }
                    },
                    y: {
                        grid: {
                            drawBorder: false,
                            borderDash: [8, 4]
                        }
                    }
                }
            }
        }
        const updateChart = (labels, data) => {
            chartData.data.labels = labels
            chartData.data.datasets = [{
                data: data,
                backgroundColor: '#4338ca',
                maxBarThickness: 60,
                borderRadius: 10,
                borderSkipped: false
            }]
            chart.value.update()
        }
        const registerOnBarClickListener = (callback) => {
            chartData.options.onClick = callback
            chart.value.update()
        }
        return {
            chart,
            chartData,
            updateChart,
            registerOnBarClickListener
        }
    },
    mounted() {
        this.reloadData()
    },
    updated() {
        this.reloadData()
    },
    methods: {
        reloadData() {
            this.updateChart(Object.values(this.labels), Object.values(this.data))
            this.registerOnBarClickListener((point, event) => {
                this.$inertia.get(route(route().current(), route().params), {
                    area: this.ids && event[0] ? this.ids[event[0].index] : 0
                }, {
                    replace: true,
                    preserveState: true,
                    preserveScroll: true,
                    only: this.partials
                })
            })
        }
    }
})
</script>
