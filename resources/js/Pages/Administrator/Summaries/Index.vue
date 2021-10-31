<template>
    <app-layout title="Final Summary">
        <div class="grid grid-cols-1 lg:grid-cols-2 lg:gap-6 mb-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 mb-4 lg:mb-0">
                <jet-period-dropdown :selected="period" :periods="periods" partial="stocks" class="mb-2 lg:mb-0"/>
            </div>
            <div class="lg:text-right">
                <jet-button type="button" @click="confirmExport">
                    <download-icon class="h-5 w-5 mr-2 text-white" aria-hidden="true"/>
                    Ekspor
                </jet-button>
            </div>
        </div>
        <jet-table
            :search="queryBuilderProps.search"
            :on-update="setQueryBuilder"
            :meta="summaries"
            ref="table">
            <template #head>
                <jet-table-header
                    v-show="showColumn('material_code')"
                    :cell="sortableHeader('material_code')">
                    Kode Material
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('material_description')"
                    :cell="sortableHeader('material_description')">
                    Deskripsi Material
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('uom')"
                    :cell="sortableHeader('uom')">
                    UoM
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('mtyp')"
                    :cell="sortableHeader('mtyp')">
                    MType
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('unrestricted')"
                    :cell="sortableHeader('unrestricted')">
                    Unrestricted
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('qualinsp')"
                    :cell="sortableHeader('qualinsp')">
                    QualInsp
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('total_stock')"
                    :cell="sortableHeader('total_stock')">
                    Total Stock
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('gap_stock')"
                    :cell="sortableHeader('gap_stock')">
                    Gap Stock
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('gap_value')"
                    :cell="sortableHeader('gap_value')">
                    Gap Value
                </jet-table-header>
            </template>
            <template #body>
                <tr v-for="summary in summaries.data" :key="summary.id">
                    <td v-show="showColumn('material_code')">{{ summary.material_code }}</td>
                    <td v-show="showColumn('material_description')">{{ summary.material_description }}</td>
                    <td v-show="showColumn('uom')">{{ summary.uom }}</td>
                    <td v-show="showColumn('mtyp')">{{ summary.mtyp }}</td>
                    <td v-show="showColumn('unrestricted')">{{ summary.unrestricted }}</td>
                    <td v-show="showColumn('qualinsp')">{{ summary.qualinsp }}</td>
                    <td v-show="showColumn('total_stock')">{{ summary.total_stock }}</td>
                    <td v-show="showColumn('gap_stock')">{{ summary.gap_stock }}</td>
                    <td v-show="showColumn('gap_value')">{{ summary.gap_value }}</td>
                </tr>
            </template>
        </jet-table>
        <jet-export-modal :show="confirmingExport" @close="closeExportModal" title="Ekspor data final summary">
            <template #content>
                <p>
                    Apakah Anda yakin ingin mengekspor semua data final summary? Proses ekspor dapat memakan waktu lama,
                    tergantung dari banyaknya data yang tersedia.
                </p>
                <p class="mt-2">
                    Sistem akan mengekspor data berupa file spreadsheet dengan format <b>XLSX</b>.
                </p>
                <p class="mt-2">
                    Anda dapat menyaring data final summary berdasarkan periodenya dengan menyesuaikan kolom
                    pilihan dibawah ini.
                </p>
                <div class="mt-4">
                    <jet-select ref="exportPeriod" placeholder="Semua Periode" v-model="exportForm.period"
                                :data="periods" class="mt-4 block w-full"/>
                </div>
            </template>
            <template #buttons>
                <jet-button type="button" @click="exportFile"
                            class="w-full inline-flex justify-center px-4 py-2 mt-2 sm:ml-3 sm:w-auto">
                    Ekspor
                </jet-button>
                <jet-secondary-button @click="closeExportModal"
                                      class="w-full inline-flex justify-center px-4 py-2 mt-2 sm:ml-3 sm:w-auto">
                    Batalkan
                </jet-secondary-button>
            </template>
        </jet-export-modal>
    </app-layout>
</template>

<script>
import {defineComponent} from 'vue'
import {useForm} from '@inertiajs/inertia-vue3'
import {MenuItem} from '@headlessui/vue'
import {DownloadIcon} from '@heroicons/vue/outline'
import AppLayout from '@/Layouts/AppLayout'
import JetButton from '@/Jetstream/Button'
import JetSecondaryButton from '@/Jetstream/SecondaryButton'
import JetDropdown from '@/Jetstream/Dropdown'
import JetExportModal from '@/Jetstream/ExportModal'
import JetTable from '@/Jetstream/Table'
import JetTableEngine from '@/Jetstream/TableEngine'
import JetTableHeader from '@/Jetstream/TableHeader'
import JetPeriodDropdown from '@/Jetstream/PeriodDropdown'
import JetSelect from '@/Jetstream/Select'

JetTableEngine.respectParams(['period'])

export default defineComponent({
    data() {
        return {
            confirmingExport: false,
            exportForm: useForm({
                period: null
            })
        }
    },
    mixins: [JetTableEngine],
    props: {
        period: Object,
        periods: Object,
        summaries: Object
    },
    components: {
        AppLayout,
        JetTable,
        JetTableHeader,
        JetDropdown,
        JetButton,
        JetSecondaryButton,
        JetExportModal,
        JetPeriodDropdown,
        JetSelect,
        MenuItem,
        DownloadIcon
    },
    methods: {
        exportFile() {
            window.open(route('summaries.export', {
                period: this.exportForm.period
            }))
            this.closeExportModal()
        },
        confirmExport() {
            setTimeout(() => this.confirmingExport = true, 150)
            setTimeout(() => this.$refs.exportPeriod.focus(), 300)
        },
        closeExportModal() {
            this.confirmingExport = false
            setTimeout(() => {
                this.exportForm.reset()
                this.exportForm.period = null
            }, 500)
        }
    }
})
</script>
