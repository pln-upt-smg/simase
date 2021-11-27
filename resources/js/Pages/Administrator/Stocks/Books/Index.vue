<template>
    <app-layout title="Book Stock">
        <div class="grid grid-cols-1 lg:grid-cols-2 lg:gap-6 mb-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 mb-4 lg:mb-0">
                <jet-area-dropdown :selected="area" :areas="areas" partial="stocks" class="mb-4 lg:mb-0"/>
                <jet-period-dropdown :selected="period" :periods="periods" partial="stocks" class="mb-2 lg:mb-0"/>
            </div>
            <div class="lg:text-right">
                <jet-button type="button" @click="confirmStore" class="mr-2 mb-2 lg:mb-0">
                    <plus-icon class="h-5 w-5 mr-2 text-white" aria-hidden="true"/>
                    Tambah
                </jet-button>
                <jet-button type="button" @click="confirmImport" class="mr-2 mb-2 lg:mb-0">
                    <upload-icon class="h-5 w-5 mr-2 text-white" aria-hidden="true"/>
                    Impor
                </jet-button>
                <jet-button type="button" @click="confirmExport" class="mr-2 mb-2 lg:mb-0">
                    <upload-icon class="h-5 w-5 mr-2 text-white" aria-hidden="true"/>
                    Ekspor
                </jet-button>
                <jet-danger-button type="button" @click="confirmTruncate">
                    <trash-icon class="h-5 w-5 mr-2 text-white" aria-hidden="true"/>
                    Hapus Semua
                </jet-danger-button>
            </div>
        </div>
        <jet-table
            :search="queryBuilderProps.search"
            :on-update="setQueryBuilder"
            :meta="stocks"
            :bottom-spacing="true"
            ref="table">
            <template #head>
                <jet-table-header
                    v-show="showColumn('material_code')"
                    :cell="sortableHeader('material_code')">
                    Kode Material
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('batch_code')"
                    :cell="sortableHeader('batch_code')">
                    Kode Batch
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('material_description')"
                    :cell="sortableHeader('material_description')">
                    Deskripsi Material
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
                    v-show="showColumn('plnt')"
                    :cell="sortableHeader('plnt')">
                    Plnt
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('sloc')"
                    :cell="sortableHeader('sloc')">
                    SLoc
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
                    v-show="showColumn('qualinsp')"
                    :cell="staticHeader('action')"/>
            </template>
            <template #body>
                <tr v-for="stock in stocks.data" :key="stock.id">
                    <td v-show="showColumn('material_code')">{{ stock.material_code }}</td>
                    <td v-show="showColumn('batch_code')">{{ stock.batch_code }}</td>
                    <td v-show="showColumn('material_description')">{{ stock.material_description }}</td>
                    <td v-show="showColumn('quantity')">{{ stock.quantity }}</td>
                    <td v-show="showColumn('uom')">{{ stock.uom }}</td>
                    <td v-show="showColumn('mtyp')">{{ stock.mtyp }}</td>
                    <td v-show="showColumn('plnt')">{{ stock.plnt }}</td>
                    <td v-show="showColumn('sloc')">{{ stock.sloc }}</td>
                    <td v-show="showColumn('unrestricted')">{{ stock.unrestricted }}</td>
                    <td v-show="showColumn('qualinsp')">{{ stock.qualinsp }}</td>
                    <td v-show="showColumn('action')" class="text-center">
                        <jet-dropdown name="Opsi">
                            <menu-item>
                                <button @click="confirmUpdate(stock)"
                                        class="text-gray-700 hover:bg-gray-100 group flex items-center px-4 py-2 text-sm w-full">
                                    <pencil-alt-icon class="mr-3 h-5 w-5 text-gray-700" aria-hidden="true"/>
                                    Edit
                                </button>
                            </menu-item>
                            <menu-item>
                                <button @click="confirmDestroy(stock)"
                                        class="text-gray-700 hover:bg-gray-100 group flex items-center px-4 py-2 text-sm w-full">
                                    <trash-icon class="mr-3 h-5 w-5 text-gray-700" aria-hidden="true"/>
                                    Hapus
                                </button>
                            </menu-item>
                        </jet-dropdown>
                    </td>
                </tr>
            </template>
        </jet-table>
        <jet-modal :show="confirmingStore" @close="closeStoreModal" title="Tambah book stock">
            <template #content>
                Silakan masukkan data book stock yang ingin ditambahkan.
                <jet-validation-errors class="mt-4"/>
                <div class="mt-4">
                    <jet-select ref="storeArea" placeholder="Pilih Area" v-model="storeForm.area"
                                :data="areas" class="block w-full"/>
                    <jet-select ref="storePeriod" placeholder="Pilih Periode" v-model="storeForm.period"
                                :data="periods" class="mt-4 block w-full"/>
                    <jet-input type="text" class="mt-4 block w-full uppercase" placeholder="Kode Material"
                               ref="storeMaterialCode" v-model="storeForm.material_code"/>
                    <jet-input type="text" class="mt-4 block w-full uppercase" placeholder="Kode Batch"
                               ref="storeBatch" v-model="storeForm.batch_code"/>
                    <jet-input type="number" class="mt-4 block w-full" placeholder="Kuantitas"
                               ref="storeQuantity" v-model="storeForm.quantity"/>
                    <jet-input type="number" class="mt-4 block w-full" placeholder="Plnt"
                               ref="storePlnt" v-model="storeForm.plnt"/>
                    <jet-input type="number" class="mt-4 block w-full" placeholder="Unrestricted"
                               ref="storeUnrestricted" v-model="storeForm.unrestricted"/>
                    <jet-input type="number" class="mt-4 block w-full" placeholder="QualInsp"
                               ref="storeQualinsp" v-model="storeForm.qualinsp" @keyup.enter="store"/>
                </div>
            </template>
            <template #buttons>
                <jet-button type="button" @click="store"
                            :class="{ 'opacity-25': storeForm.processing }"
                            :disabled="storeForm.processing"
                            class="w-full inline-flex justify-center px-4 py-2 mt-2 sm:ml-3 sm:w-auto">
                    Simpan
                </jet-button>
                <jet-secondary-button @click="closeStoreModal"
                                      class="w-full inline-flex justify-center px-4 py-2 mt-2 sm:ml-3 sm:w-auto">
                    Batalkan
                </jet-secondary-button>
            </template>
        </jet-modal>
        <jet-modal :show="confirmingUpdate" @close="closeUpdateModal" title="Edit book stock">
            <template #content>
                Silakan masukkan data book stock yang ingin diubah.
                <jet-validation-errors class="mt-4"/>
                <div class="mt-4">
                    <jet-select ref="updateArea" placeholder="Pilih Area" v-model="updateForm.area"
                                :data="areas" class="block w-full"/>
                    <jet-select ref="updatePeriod" placeholder="Pilih Periode" v-model="updateForm.period"
                                :data="periods" class="mt-4 block w-full"/>
                    <jet-input type="text" class="mt-4 block w-full uppercase" placeholder="Kode Material"
                               ref="updateMaterialCode" v-model="updateForm.material_code"/>
                    <jet-input type="text" class="mt-4 block w-full uppercase" placeholder="Kode Batch"
                               ref="updateBatch" v-model="updateForm.batch_code"/>
                    <jet-input type="number" class="mt-4 block w-full" placeholder="Kuantitas"
                               ref="updateQuantity" v-model="updateForm.quantity"/>
                    <jet-input type="number" class="mt-4 block w-full" placeholder="Plnt"
                               ref="updatePlnt" v-model="updateForm.plnt"/>
                    <jet-input type="number" class="mt-4 block w-full" placeholder="Unrestricted"
                               ref="updateUnrestricted" v-model="updateForm.unrestricted"/>
                    <jet-input type="number" class="mt-4 block w-full" placeholder="QualInsp"
                               ref="updateQualinsp" v-model="updateForm.qualinsp" @keyup.enter="update"/>
                </div>
            </template>
            <template #buttons>
                <jet-button type="button" @click="update"
                            :class="{ 'opacity-25': updateForm.processing }"
                            :disabled="updateForm.processing"
                            class="w-full inline-flex justify-center px-4 py-2 mt-2 sm:ml-3 sm:w-auto">
                    Simpan
                </jet-button>
                <jet-secondary-button @click="closeUpdateModal"
                                      class="w-full inline-flex justify-center px-4 py-2 mt-2 sm:ml-3 sm:w-auto">
                    Batalkan
                </jet-secondary-button>
            </template>
        </jet-modal>
        <jet-alert-modal :show="confirmingDestroy" @close="closeDestroyModal" title="Hapus book stock">
            <template #content>
                Apakah Anda yakin ingin menghapus book stock ini? Setelah book stock dihapus, semua sumber daya
                dan datanya akan dihapus secara permanen. Aksi ini tidak dapat dibatalkan.
            </template>
            <template #buttons>
                <jet-danger-button @click="destroy"
                                   :class="{ 'opacity-25': destroyForm.processing }"
                                   :disabled="destroyForm.processing"
                                   class="w-full inline-flex justify-center px-4 py-2 mt-2 sm:ml-3 sm:w-auto">
                    Hapus
                </jet-danger-button>
                <jet-secondary-button @click="closeDestroyModal"
                                      class="w-full inline-flex justify-center px-4 py-2 mt-2 sm:ml-3 sm:w-auto">
                    Batalkan
                </jet-secondary-button>
            </template>
        </jet-alert-modal>
        <jet-import-modal :show="confirmingImport" @close="closeImportModal" title="Impor book stock">
            <template #content>
                <p>
                    Silakan unggah file data book stock yang ingin di-impor. Pastikan Anda sudah menggunakan template
                    spreadsheet yang ditentukan. Sistem hanya memproses data yang ada pada sheet <b>Worksheet</b>.
                </p>
                <p class="mt-2">
                    Mengimpor data baru dapat memperbarui data lama yang sudah tersedia. Aksi ini tidak dapat
                    dibatalkan.
                </p>
                <jet-validation-errors class="mt-4"/>
                <div class="mt-4">
                    <jet-select ref="importPeriod" placeholder="Pilih Periode" v-model="importForm.period"
                                :data="periods" class="block w-full"/>
                    <div
                        @click="this.$refs.importInput.click()"
                        class="mt-4 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md cursor-pointer">
                        <div class="space-y-1 text-center">
                            <document-add-icon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true"/>
                            <div class="flex justify-center text-sm text-gray-600">
                                <label for="import-file"
                                       class="relative bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 cursor-pointer">
                                <span>
                                    {{ importForm.file === null ? 'Unggah file dokumen' : importForm.file.name }}
                                </span>
                                    <input for="import-file" ref="importInput" type="file" class="sr-only"
                                           accept=".xlsx, .csv"
                                           @input="importForm.file = $event.target.files[0]"/>
                                </label>
                            </div>
                            <p class="text-xs text-gray-500">
                                XLSX, CSV hingga 50MB
                            </p>
                        </div>
                    </div>
                </div>
            </template>
            <template #buttons>
                <jet-button type="button"
                            @click="importFile"
                            :class="{ 'opacity-25': importForm.processing }"
                            :disabled="importForm.processing"
                            class="w-full inline-flex justify-center px-4 py-2 mt-2 sm:ml-3 sm:w-auto">
                    Impor
                </jet-button>
                <jet-secondary-button
                    @click="openImportTemplate"
                    class="w-full inline-flex justify-center px-4 py-2 mt-2 sm:ml-3 sm:w-auto">
                    Unduh Template
                </jet-secondary-button>
                <jet-secondary-button
                    @click="closeImportModal"
                    class="w-full inline-flex justify-center px-4 py-2 mt-2 sm:ml-3 sm:w-auto">
                    Batalkan
                </jet-secondary-button>
            </template>
        </jet-import-modal>
        <jet-export-modal :show="confirmingExport" @close="closeExportModal" title="Ekspor book stock">
            <template #content>
                <p>
                    Apakah Anda yakin ingin mengekspor semua data book stock? Proses ekspor dapat memakan waktu lama,
                    tergantung dari banyaknya data yang tersedia.
                </p>
                <p class="mt-2">
                    Sistem akan mengekspor data berupa file spreadsheet dengan format <b>XLSX</b>.
                </p>
                <p class="mt-2">
                    Anda dapat menyaring data book stock berdasarkan area dan periodenya dengan menyesuaikan kolom
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
        <jet-alert-modal :show="confirmingTruncate" @close="closeTruncateModal" title="Hapus semua book stock">
            <template #content>
                <p>
                    Apakah Anda yakin ingin menghapus semua book stock? Semua sumber daya dan data book stock akan
                    dihapus secara permanen.
                </p>
                <p class="mt-2">
                    Sangat disarankan untuk mengekspor semua data book stock saat ini sebagai data cadangan. Aksi ini
                    tidak dapat dibatalkan.
                </p>
                <p class="mt-2">
                    Anda dapat menyaring semua data book stock yang ingin dihapus berdasarkan area dan periodenya dengan
                    menyesuaikan kolom pilihan dibawah ini.
                </p>
                <jet-validation-errors class="mt-4"/>
                <div class="mt-4">
                    <jet-select ref="truncateArea" placeholder="Semua Area" v-model="truncateForm.area"
                                :data="areas" class="block w-full"/>
                    <jet-select ref="truncatePeriod" placeholder="Semua Periode" v-model="truncateForm.period"
                                :data="periods" class="mt-4 block w-full"/>
                </div>
            </template>
            <template #buttons>
                <jet-confirm-password-modal @confirmed="truncate">
                    <jet-danger-button :class="{ 'opacity-25': truncateForm.processing }"
                                       :disabled="truncateForm.processing"
                                       class="w-full inline-flex justify-center px-4 py-2 mt-2 sm:ml-3 sm:w-auto">
                        Hapus Semua
                    </jet-danger-button>
                </jet-confirm-password-modal>
                <jet-secondary-button @click="closeTruncateModal"
                                      class="w-full inline-flex justify-center px-4 py-2 mt-2 sm:ml-3 sm:w-auto">
                    Batalkan
                </jet-secondary-button>
            </template>
        </jet-alert-modal>
        <jet-success-notification
            :show="showingSuccessNotification"
            :title="successNotification.title"
            :description="successNotification.description"
            @close="closeSuccessNotification"/>
        <jet-danger-notification
            :show="showingDangerNotification"
            :title="dangerNotification.title"
            :description="dangerNotification.description"
            @close="closeDangerNotification"/>
    </app-layout>
</template>

<script>
import {defineComponent} from 'vue'
import {useForm} from '@inertiajs/inertia-vue3'
import {MenuItem} from '@headlessui/vue'
import {DocumentAddIcon, DownloadIcon, PencilAltIcon, PlusIcon, TrashIcon, UploadIcon} from '@heroicons/vue/outline'
import AppLayout from '@/Layouts/AppLayout'
import JetButton from '@/Jetstream/Button'
import JetDangerButton from '@/Jetstream/DangerButton'
import JetSecondaryButton from '@/Jetstream/SecondaryButton'
import JetDropdown from '@/Jetstream/Dropdown'
import JetModal from '@/Jetstream/Modal'
import JetAlertModal from '@/Jetstream/AlertModal'
import JetImportModal from '@/Jetstream/ImportModal'
import JetExportModal from '@/Jetstream/ExportModal'
import JetInput from '@/Jetstream/Input'
import JetSuccessNotification from '@/Jetstream/SuccessNotification'
import JetDangerNotification from '@/Jetstream/DangerNotification'
import JetValidationErrors from '@/Jetstream/ValidationErrors'
import JetTable from '@/Jetstream/Table'
import JetTableEngine from '@/Jetstream/TableEngine'
import JetTableHeader from '@/Jetstream/TableHeader'
import JetAreaDropdown from '@/Jetstream/AreaDropdown'
import JetPeriodDropdown from '@/Jetstream/PeriodDropdown'
import JetSelect from '@/Jetstream/Select'
import JetConfirmPasswordModal from '@/Jetstream/ConfirmPasswordModal'

JetTableEngine.respectParams(['area', 'period'])

export default defineComponent({
    data() {
        return {
            successNotification: {
                title: null,
                description: null
            },
            dangerNotification: {
                title: null,
                description: null
            },
            confirmingStore: false,
            confirmingUpdate: false,
            confirmingDestroy: false,
            confirmingImport: false,
            confirmingExport: false,
            confirmingTruncate: false,
            showingSuccessNotification: false,
            showingDangerNotification: false,
            storeForm: useForm({
                area: null,
                period: null,
                material_code: null,
                batch_code: null,
                quantity: null,
                plnt: null,
                qualinsp: null,
                unrestricted: null
            }),
            updateForm: useForm({
                id: null,
                area: null,
                period: null,
                material_code: null,
                batch_code: null,
                quantity: null,
                plnt: null,
                qualinsp: null,
                unrestricted: null
            }),
            destroyForm: useForm({
                id: null
            }),
            importForm: useForm({
                period: null,
                file: null
            }),
            exportForm: useForm({
                area: null,
                period: null
            }),
            truncateForm: useForm({
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
        JetDangerButton,
        JetSecondaryButton,
        JetModal,
        JetAlertModal,
        JetImportModal,
        JetExportModal,
        JetInput,
        JetSuccessNotification,
        JetDangerNotification,
        JetValidationErrors,
        JetAreaDropdown,
        JetPeriodDropdown,
        JetSelect,
        JetConfirmPasswordModal,
        MenuItem,
        PlusIcon,
        UploadIcon,
        DownloadIcon,
        PencilAltIcon,
        TrashIcon,
        DocumentAddIcon
    },
    methods: {
        store() {
            this.storeForm.post(route('stocks.books.store'), {
                preserveScroll: true,
                onSuccess: () => {
                    this.reloadData()
                    this.closeStoreModal()
                    this.showSuccessNotification('Book stock berhasil ditambahkan', 'Sistem telah berhasil menyimpan data book stock baru')
                },
                onError: () => this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat menyimpan data book stock, mohon periksa ulang form')
            })
        },
        update() {
            this.updateForm.put(route('stocks.books.update', this.updateForm.id), {
                preserveScroll: true,
                onSuccess: () => {
                    this.reloadData()
                    this.closeUpdateModal()
                    this.showSuccessNotification('Book stock berhasil diedit', 'Sistem telah berhasil mengedit data book stock')
                },
                onError: () => this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat mengubah data book stock, mohon periksa ulang form')
            })
        },
        destroy() {
            this.destroyForm.delete(route('stocks.books.destroy', this.destroyForm.id), {
                preserveScroll: true,
                onSuccess: () => {
                    this.reloadData()
                    this.closeDestroyModal()
                    this.showSuccessNotification('Book stock berhasil dihapus', 'Sistem telah berhasil menghapus data book stock')
                },
                onError: () => this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat menghapus data book stock')
            })
        },
        importFile() {
            this.importForm.post(route('stocks.books.import'), {
                preserveScroll: true,
                onSuccess: () => {
                    this.reloadData()
                    this.closeImportModal()
                    this.showSuccessNotification('Permintaan impor data telah dijadwalkan', 'Sistem berhasil menjadwalkan permintaan impor data book stock di latar belakang')
                },
                onError: () => this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat mengimpor data book stock, mohon periksa kesalahan yang telah dideteksi')
            })
        },
        exportFile() {
            window.open(route('stocks.books.export', {
                area: this.exportForm.area,
                period: this.exportForm.period
            }))
            this.closeExportModal()
        },
        truncate() {
            this.truncateForm.delete(route('stocks.books.truncate', {
                area: this.truncateForm.area,
                period: this.truncateForm.period
            }), {
                preserveScroll: true,
                onSuccess: () => {
                    this.reloadData()
                    this.closeTruncateModal()
                    this.showSuccessNotification('Semua book stock berhasil dihapus', 'Sistem telah berhasil menghapus semua data book stock')
                },
                onError: () => this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat menghapus semua data book stock')
            })
        },
        confirmStore() {
            setTimeout(() => this.confirmingStore = true, 150)
            setTimeout(() => this.$refs.storeArea.focus(), 300)
        },
        confirmUpdate(stock) {
            this.updateForm.id = stock.id
            this.updateForm.area = stock.area_id
            this.updateForm.period = stock.period_id
            this.updateForm.material_code = stock.material_code
            this.updateForm.batch_code = stock.batch_code
            this.updateForm.quantity = stock.quantity
            this.updateForm.plnt = stock.plnt
            this.updateForm.qualinsp = stock.qualinsp
            this.updateForm.unrestricted = stock.unrestricted
            setTimeout(() => this.confirmingUpdate = true, 150)
            setTimeout(() => this.$refs.updateArea.focus(), 300)
        },
        confirmDestroy(stock) {
            this.destroyForm.id = stock.id
            setTimeout(() => this.confirmingDestroy = true, 150)
        },
        confirmImport() {
            setTimeout(() => this.confirmingImport = true, 150)
            setTimeout(() => this.$refs.importPeriod.focus(), 300)
        },
        confirmExport() {
            setTimeout(() => this.confirmingExport = true, 150)
            setTimeout(() => this.$refs.exportArea.focus(), 300)
        },
        confirmTruncate() {
            setTimeout(() => this.confirmingTruncate = true, 150)
            setTimeout(() => this.$refs.truncateArea.focus(), 300)
        },
        closeStoreModal() {
            this.confirmingStore = false
            setTimeout(() => {
                this.clearErrors()
                this.storeForm.clearErrors()
                this.storeForm.reset()
                this.storeForm.area = null
                this.storeForm.period = null
                this.storeForm.material_code = null
                this.storeForm.batch_code = null
                this.storeForm.quantity = null
                this.storeForm.plnt = null
                this.storeForm.qualinsp = null
                this.storeForm.unrestricted = null
            }, 500)
        },
        closeUpdateModal() {
            this.confirmingUpdate = false
            setTimeout(() => {
                this.clearErrors()
                this.updateForm.clearErrors()
                this.updateForm.reset()
                this.updateForm.id = null
                this.updateForm.area = null
                this.updateForm.period = null
                this.updateForm.material_code = null
                this.updateForm.batch_code = null
                this.updateForm.quantity = null
                this.updateForm.plnt = null
                this.updateForm.qualinsp = null
                this.updateForm.unrestricted = null
            }, 500)
        },
        closeDestroyModal() {
            this.confirmingDestroy = false
            setTimeout(() => {
                this.clearErrors()
                this.destroyForm.clearErrors()
                this.destroyForm.reset()
                this.destroyForm.id = null
            }, 500)
        },
        closeImportModal() {
            this.confirmingImport = false
            setTimeout(() => {
                this.clearErrors()
                this.importForm.clearErrors()
                this.importForm.reset()
                this.importForm.period = null
                this.importForm.file = null
            }, 500)
        },
        closeExportModal() {
            this.confirmingExport = false
            setTimeout(() => {
                this.exportForm.reset()
                this.exportForm.area = null
                this.exportForm.period = null
            }, 500)
        },
        closeTruncateModal() {
            this.confirmingTruncate = false
            setTimeout(() => {
                this.truncateForm.reset()
                this.truncateForm.area = null
                this.truncateForm.period = null
            }, 500)
        },
        showSuccessNotification(title, description) {
            this.successNotification.title = title
            this.successNotification.description = description
            this.showingSuccessNotification = true
            setTimeout(() => this.showingSuccessNotification ? this.closeSuccessNotification() : null, 5000)
        },
        showDangerNotification(title, description) {
            this.dangerNotification.title = title
            this.dangerNotification.description = description
            this.showingDangerNotification = true
            setTimeout(() => this.showingDangerNotification ? this.closeDangerNotification() : null, 5000)
        },
        closeSuccessNotification() {
            this.showingSuccessNotification = false
        },
        closeDangerNotification() {
            this.showingDangerNotification = false
        },
        openImportTemplate() {
            if (this.$page.props.template) window.open(this.$page.props.template)
            else this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat membaca template, mohon coba lagi nanti')
        },
        clearErrors() {
            this.$page.props.errors = []
        },
        reloadData() {
            this.$refs.table.reload('stocks')
        }
    }
})
</script>
