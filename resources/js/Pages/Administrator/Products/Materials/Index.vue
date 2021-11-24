<template>
    <app-layout title="FG to Material">
        <div class="grid grid-cols-1 lg:grid-cols-2 lg:gap-6 mb-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 mb-4 lg:mb-0">
                <jet-period-dropdown :selected="period" :periods="periods" partial="productMaterials" class="mb-2 lg:mb-0"/>
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
                <jet-button type="button" @click="confirmExport">
                    <download-icon class="h-5 w-5 mr-2 text-white" aria-hidden="true"/>
                    Ekspor
                </jet-button>
            </div>
        </div>
        <jet-table
            :search="queryBuilderProps.search"
            :on-update="setQueryBuilder"
            :meta="productMaterials"
            :bottom-spacing="true"
            ref="table">
            <template #head>
                <jet-table-header
                    v-show="showColumn('product_code')"
                    :cell="sortableHeader('product_code')">
                    Kode SKU
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('product_description')"
                    :cell="sortableHeader('product_description')">
                    Deskripsi Produk
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('product_quantity')"
                    :cell="sortableHeader('product_quantity')">
                    Kuantitas Produk
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
                    v-show="showColumn('material_uom')"
                    :cell="sortableHeader('material_uom')">
                    UoM Material
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('material_quantity')"
                    :cell="sortableHeader('material_quantity')">
                    Kuantitas Material
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('action')"
                    :cell="staticHeader('action')"/>
            </template>
            <template #body>
                <tr v-for="productMaterial in productMaterials.data" :key="productMaterial.id">
                    <td v-show="showColumn('product_code')">{{ productMaterial.product_code }}</td>
                    <td v-show="showColumn('product_description')">{{ productMaterial.product_description }}</td>
                    <td v-show="showColumn('product_quantity')">{{ productMaterial.product_quantity }}</td>
                    <td v-show="showColumn('material_code')">{{ productMaterial.material_code }}</td>
                    <td v-show="showColumn('material_description')">{{ productMaterial.material_description }}</td>
                    <td v-show="showColumn('material_uom')">{{ productMaterial.material_uom }}</td>
                    <td v-show="showColumn('material_quantity')">{{ productMaterial.material_quantity }}</td>
                    <td v-show="showColumn('action')" class="text-center">
                        <jet-dropdown name="Opsi">
                            <menu-item>
                                <button @click="confirmUpdate(productMaterial)"
                                        class="text-gray-700 hover:bg-gray-100 group flex items-center px-4 py-2 text-sm w-full">
                                    <pencil-alt-icon class="mr-3 h-5 w-5 text-gray-700" aria-hidden="true"/>
                                    Edit
                                </button>
                            </menu-item>
                            <menu-item>
                                <button @click="confirmDestroy(productMaterial)"
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
        <jet-modal :show="confirmingStore" @close="closeStoreModal" title="Tambah product material">
            <template #content>
                Silakan masukkan data product material yang ingin ditambahkan.
                <jet-validation-errors class="mt-4"/>
                <div class="mt-4">
                    <jet-select ref="storePeriod" placeholder="Pilih Periode" v-model="storeForm.period"
                                :data="periods" class="block w-full"/>
                    <jet-input type="text" class="mt-4 block w-full uppercase" placeholder="Kode SKU"
                               ref="storeProductCode" v-model="storeForm.product_code"/>
                    <jet-input type="text" class="mt-4 block w-full uppercase" placeholder="Kode Material"
                               ref="storeMaterialCode" v-model="storeForm.material_code"/>
                    <jet-input type="number" class="mt-4 block w-full" placeholder="Kuantitas Produk"
                               ref="storeProductQuantity" v-model="storeForm.product_quantity"/>
                    <jet-input type="number" class="mt-4 block w-full" placeholder="Kuantitas Material"
                               ref="storeMaterialQuantity" v-model="storeForm.material_quantity"/>
                    <jet-input type="text" class="mt-4 block w-full uppercasse" placeholder="UoM Material"
                               ref="storeMaterialUom" v-model="storeForm.material_uom" @keyup.enter="store"/>
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
        <jet-modal :show="confirmingUpdate" @close="closeUpdateModal" title="Edit product material">
            <template #content>
                Silakan masukkan data product material yang ingin diubah.
                <jet-validation-errors class="mt-4"/>
                <div class="mt-4">
                    <jet-select ref="updatePeriod" placeholder="Pilih Periode" v-model="updateForm.period"
                                :data="periods" class="block w-full"/>
                    <jet-input type="text" class="mt-4 block w-full uppercase" placeholder="Kode SKU"
                               ref="updateProductCode" v-model="updateForm.product_code"/>
                    <jet-input type="text" class="mt-4 block w-full uppercase" placeholder="Kode Material"
                               ref="updateMaterialCode" v-model="updateForm.material_code"/>
                    <jet-input type="number" class="mt-4 block w-full" placeholder="Kuantitas Produk"
                               ref="updateProductQuantity" v-model="updateForm.product_quantity"/>
                    <jet-input type="number" class="mt-4 block w-full" placeholder="Kuantitas Material"
                               ref="updateMaterialQuantity" v-model="updateForm.material_quantity"/>
                    <jet-input type="text" class="mt-4 block w-full uppercasse" placeholder="UoM Material"
                               ref="updateMaterialUom" v-model="updateForm.material_uom" @keyup.enter="update"/>
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
        <jet-alert-modal :show="confirmingDestroy" @close="closeDestroyModal" title="Hapus product material">
            <template #content>
                Apakah Anda yakin ingin menghapus product material ini? Setelah product material dihapus, semua sumber daya
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
        <jet-import-modal :show="confirmingImport" @close="closeImportModal" title="Impor data product material">
            <template #content>
                <p>
                    Silakan unggah file data product material yang ingin di-impor. Pastikan Anda sudah menggunakan template
                    spreadsheet yang ditentukan. Sistem hanya memproses data yang ada pada sheet <b>Worksheet</b>.
                </p>
                <p class="mt-2">
                    Mengimpor data baru dapat memperbarui data lama yang sudah tersedia. Aksi ini tidak dapat dibatalkan.
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
        <jet-export-modal :show="confirmingExport" @close="closeExportModal" title="Ekspor data product material">
            <template #content>
                <p>
                    Apakah Anda yakin ingin mengekspor semua data product material? Proses ekspor dapat memakan waktu lama,
                    tergantung dari banyaknya data yang tersedia.
                </p>
                <p class="mt-2">
                    Sistem akan mengekspor data berupa file spreadsheet dengan format <b>XLSX</b>.
                </p>
                <p class="mt-2">
                    Anda dapat menyaring data product material berdasarkan periodenya dengan menyesuaikan kolom
                    pilihan dibawah ini.
                </p>
                <div class="mt-4">
                    <jet-select ref="exportPeriod" placeholder="Semua Periode" v-model="exportForm.period"
                                :data="periods" class="block w-full"/>
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
import JetPeriodDropdown from '@/Jetstream/PeriodDropdown'
import JetSelect from '@/Jetstream/Select'

JetTableEngine.respectParams(['period'])

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
            showingSuccessNotification: false,
            showingDangerNotification: false,
            storeForm: useForm({
                period: null,
                product_code: null,
                product_quantity: null,
                material_code: null,
                material_quantity: null,
                material_uom: null
            }),
            updateForm: useForm({
                period: null,
                id: null,
                product_code: null,
                product_quantity: null,
                material_code: null,
                material_quantity: null,
                material_uom: null
            }),
            destroyForm: useForm({
                id: null
            }),
            importForm: useForm({
                period: null,
                file: null
            }),
            exportForm: useForm({
                period: null
            })
        }
    },
    mixins: [JetTableEngine],
    props: {
        period: Object,
        periods: Object,
        productMaterials: Object
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
        JetPeriodDropdown,
        JetSelect,
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
            this.storeForm.post(route('products.materials.store'), {
                preserveScroll: true,
                onSuccess: () => {
                    this.reloadData()
                    this.closeStoreModal()
                    this.showSuccessNotification('FG to Material berhasil ditambahkan', 'Sistem telah berhasil menyimpan data product material baru')
                },
                onError: () => this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat menyimpan data product material, mohon periksa ulang form')
            })
        },
        update() {
            this.updateForm.put(route('products.materials.update', this.updateForm.id), {
                preserveScroll: true,
                onSuccess: () => {
                    this.reloadData()
                    this.closeUpdateModal()
                    this.showSuccessNotification('FG to Material berhasil diedit', 'Sistem telah berhasil mengedit data product material')
                },
                onError: () => this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat mengubah data product material, mohon periksa ulang form')
            })
        },
        destroy() {
            this.destroyForm.delete(route('products.materials.destroy', this.destroyForm.id), {
                preserveScroll: true,
                onSuccess: () => {
                    this.reloadData()
                    this.closeDestroyModal()
                    this.showSuccessNotification('FG to Material berhasil dihapus', 'Sistem telah berhasil menghapus data product material')
                },
                onError: () => this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat menghapus data product material')
            })
        },
        importFile() {
            this.importForm.post(route('products.materials.import'), {
                preserveScroll: true,
                onSuccess: () => {
                    this.reloadData()
                    this.closeImportModal()
                    this.showSuccessNotification('Permintaan impor data telah dijadwalkan', 'Sistem berhasil menjadwalkan permintaan impor data product material di latar belakang')
                },
                onError: () => this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat mengimpor data product material, mohon periksa kesalahan yang telah dideteksi')
            })
        },
        exportFile() {
            window.open(route('products.materials.export', {
                period: this.exportForm.period
            }))
            this.closeExportModal()
        },
        confirmStore() {
            setTimeout(() => this.confirmingStore = true, 150)
            setTimeout(() => this.$refs.storePeriod.focus(), 300)
        },
        confirmUpdate(productMaterial) {
            this.updateForm.id = productMaterial.id
            this.updateForm.period = productMaterial.period_id
            this.updateForm.product_code = productMaterial.product_code
            this.updateForm.product_quantity = productMaterial.product_quantity
            this.updateForm.material_code = productMaterial.material_code
            this.updateForm.material_quantity = productMaterial.material_quantity
            this.updateForm.material_uom = productMaterial.material_uom
            setTimeout(() => this.confirmingUpdate = true, 150)
            setTimeout(() => this.$refs.updatePeriod.focus(), 300)
        },
        confirmDestroy(productMaterial) {
            this.destroyForm.id = productMaterial.id
            setTimeout(() => this.confirmingDestroy = true, 150)
        },
        confirmImport() {
            setTimeout(() => this.confirmingImport = true, 150)
            setTimeout(() => this.$refs.importPeriod.focus(), 300)
        },
        confirmExport() {
            setTimeout(() => this.confirmingExport = true, 150)
            setTimeout(() => this.$refs.exportPeriod.focus(), 300)
        },
        closeStoreModal() {
            this.confirmingStore = false
            setTimeout(() => {
                this.clearErrors()
                this.storeForm.clearErrors()
                this.storeForm.reset()
                this.storeForm.period = null
                this.storeForm.code = null
                this.storeForm.description = null
                this.storeForm.uom = null
                this.storeForm.mtyp = null
                this.storeForm.price = null
                this.storeForm.per = null
            }, 500)
        },
        closeUpdateModal() {
            this.confirmingUpdate = false
            setTimeout(() => {
                this.clearErrors()
                this.updateForm.clearErrors()
                this.updateForm.reset()
                this.updateForm.id = null
                this.updateForm.period = null
                this.updateForm.code = null
                this.updateForm.description = null
                this.updateForm.uom = null
                this.updateForm.mtyp = null
                this.updateForm.price = null
                this.updateForm.per = null
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
                this.exportForm.period = null
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
            this.$refs.table.reload('productMaterials')
        }
    }
})
</script>
