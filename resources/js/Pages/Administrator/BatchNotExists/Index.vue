<template>
    <app-layout title="Batch Not Exist">
        <div class="grid grid-cols-1 lg:grid-cols-2 lg:gap-6 mb-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 mb-4 lg:mb-0">
                <jet-area-dropdown :selected="area" :areas="areas" partial="stocks" class="mb-4 lg:mb-0"/>
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
            :meta="stocks"
            ref="table">
            <template #head>
                <jet-table-header
                    v-show="showColumn('area_name')"
                    :cell="sortableHeader('area_name')">
                    Area
                </jet-table-header>
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
                    v-show="showColumn('batch_code')"
                    :cell="sortableHeader('batch_code')">
                    Kode Batch
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('quantity')"
                    :cell="sortableHeader('quantity')">
                    Kuantitas
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
                    v-show="showColumn('batch_status')"
                    :cell="sortableHeader('batch_status')">
                    Status
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('creator_name')"
                    :cell="sortableHeader('creator_name')">
                    Pembuat
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('creation_date')"
                    :cell="sortableHeader('creation_date')">
                    Tanggal Dibuat
                </jet-table-header>
            </template>
            <template #body>
                <tr v-for="stock in stocks.data" :key="stock.id">
                    <td v-show="showColumn('area_name')">{{ stock.area_name }}</td>
                    <td v-show="showColumn('material_code')">{{ stock.material_code }}</td>
                    <td v-show="showColumn('material_description')">{{ stock.material_description }}</td>
                    <td v-show="showColumn('batch_code')">{{ stock.batch_code }}</td>
                    <td v-show="showColumn('quantity')">{{ stock.quantity }}</td>
                    <td v-show="showColumn('uom')">{{ stock.uom }}</td>
                    <td v-show="showColumn('mtyp')">{{ stock.mtyp }}</td>
                    <td v-show="showColumn('batch_status')">{{ stock.batch_status }}</td>
                    <td v-show="showColumn('creator_name')">{{ stock.creator_name }}</td>
                    <td v-show="showColumn('creation_date')">{{ stock.creation_date }}</td>
                </tr>
            </template>
        </jet-table>
        <jet-export-modal :show="confirmingExport" @close="closeExportModal" title="Ekspor data batch not exist">
            <template #content>
                <p>
                    Apakah Anda yakin ingin mengekspor semua data batch not exist? Proses ekspor dapat memakan waktu
                    lama, tergantung dari banyaknya data yang tersedia.
                </p>
                <p class="mt-2">
                    Sistem akan mengekspor data berupa file spreadsheet dengan format <b>XLSX</b>.
                </p>
                <p class="mt-2">
                    Anda dapat menyaring data batch not exist berdasarkan area dan periodenya dengan menyesuaikan kolom
                    pilihan dibawah ini.
                </p>
                <div class="mt-4">
                    <jet-select ref="exportArea" placeholder="Semua Area" v-model="exportForm.area"
                                :data="areas" class="block w-full"/>
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
import JetAreaDropdown from '@/Jetstream/AreaDropdown'
import JetPeriodDropdown from '@/Jetstream/PeriodDropdown'
import JetSelect from '@/Jetstream/Select'

JetTableEngine.respectParams(['area', 'period'])

export default defineComponent({
    data() {
        return {
            confirmingExport: false,
            exportForm: useForm({
                area: null,
                period: null
            })
        }
    },
    mixins: [JetTableEngine],
    props: {
        area: Object,
        areas: Object,
        period: Object,
        periods: Object,
        stocks: Object
    },
    components: {
        AppLayout,
        JetTable,
        JetTableHeader,
        JetDropdown,
        JetButton,
        JetSecondaryButton,
        JetExportModal,
        JetAreaDropdown,
        JetPeriodDropdown,
        JetSelect,
        MenuItem,
        DownloadIcon
    },
    methods: {
        exportFile() {
            window.open(route('batch-not-exists.export', {
                area: this.exportForm.area,
                period: this.exportForm.period
            }))
            this.closeExportModal()
        },
        confirmExport() {
            setTimeout(() => this.confirmingExport = true, 150)
            setTimeout(() => this.$refs.exportArea.focus(), 300)
        },
        closeExportModal() {
            this.confirmingExport = false
            setTimeout(() => {
                this.exportForm.reset()
                this.exportForm.area = null
                this.exportForm.period = null
            }, 500)
        }
    }
})
</script>
