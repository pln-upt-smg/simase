<template>
    <app-layout title="Material">
        <div class="grid grid-cols-1 lg:grid-cols-2 lg:gap-6 mb-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 mb-4 lg:mb-0">
                <jet-area-dropdown :selected="area" :areas="areas" partial="materials" class="mb-4 lg:mb-0"/>
                <jet-period-dropdown :selected="period" :periods="periods" partial="materials" class="mb-2 lg:mb-0"/>
            </div>
            <div class="lg:text-right">
                <jet-button @click="confirmStore" class="mr-2 mb-2 lg:mb-0">
                    <plus-icon class="h-5 w-5 mr-2 text-white" aria-hidden="true"/>
                    Tambah
                </jet-button>
                <jet-button @click="confirmImport" class="mr-2 mb-2 lg:mb-0">
                    <upload-icon class="h-5 w-5 mr-2 text-white" aria-hidden="true"/>
                    Impor
                </jet-button>
                <jet-button @click="confirmExport">
                    <download-icon class="h-5 w-5 mr-2 text-white" aria-hidden="true"/>
                    Ekspor
                </jet-button>
            </div>
        </div>
        <jet-table
            :filters="queryBuilderProps.filters"
            :search="queryBuilderProps.search"
            :on-update="setQueryBuilder"
            :meta="materials">
            <template #head>
                <th v-show="showColumn('code')" @click.prevent="sortBy('code')">Kode Material</th>
                <th v-show="showColumn('description')" @click.prevent="sortBy('description')">Deskripsi Material</th>
                <th v-show="showColumn('uom')" @click.prevent="sortBy('uom')">UoM</th>
                <th v-show="showColumn('mtyp')" @click.prevent="sortBy('mtyp')">MType</th>
                <th v-show="showColumn('crcy')" @click.prevent="sortBy('crcy')">Currency</th>
                <th v-show="showColumn('price')" @click.prevent="sortBy('price')">Harga</th>
                <th v-show="showColumn('per')" @click.prevent="sortBy('per')">Per</th>
                <th v-show="showColumn('update_date')" @click.prevent="sortBy('update_date')">Tanggal Pembaruan</th>
                <th v-show="showColumn('action')"></th>
            </template>
            <template #body>
                <tr v-for="material in materials.data" :key="material.id">
                    <td v-show="showColumn('code')">{{ material.code }}</td>
                    <td v-show="showColumn('description')">{{ material.description }}</td>
                    <td v-show="showColumn('uom')">{{ material.uom }}</td>
                    <td v-show="showColumn('mtyp')">{{ material.mtyp }}</td>
                    <td v-show="showColumn('crcy')">{{ material.crcy }}</td>
                    <td v-show="showColumn('price')">{{ material.price }}</td>
                    <td v-show="showColumn('per')">{{ material.per }}</td>
                    <td v-show="showColumn('update_date')">{{ material.update_date }}</td>
                    <td v-show="showColumn('action')" class="text-center">
                        <jet-dropdown name="Opsi">
                            <menu-item>
                                <button @click="confirmUpdate(material)"
                                        class="text-gray-700 hover:bg-gray-100 group flex items-center px-4 py-2 text-sm w-full">
                                    <pencil-alt-icon class="mr-3 h-5 w-5 text-gray-700" aria-hidden="true"/>
                                    Edit
                                </button>
                            </menu-item>
                            <menu-item>
                                <button @click="confirmDestroy(material)"
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
        <jet-modal :show="confirmingStore" @close="closeStoreModal" title="Tambah material">
            <template #content>
                Silakan masukkan data material yang ingin ditambahkan.
                <jet-validation-errors class="mt-4"/>
                <div class="mt-4">
                    <jet-select ref="storeArea" placeholder="Pilih Area" v-model="storeForm.area"
                                :data="areas" class="block w-full"/>
                    <jet-select ref="storePeriod" placeholder="Pilih Period" v-model="storeForm.period"
                                :data="periods" class="mt-4 block w-full"/>
                    <jet-input type="text" class="mt-4 block w-full" placeholder="Kode Material"
                               ref="storeCode" v-model="storeForm.code"/>
                    <jet-input type="text" class="mt-4 block w-full" placeholder="Deskripsi Material"
                               ref="storeDescription" v-model="storeForm.description"/>
                    <jet-input type="text" class="mt-4 block w-full" placeholder="UoM"
                               ref="storeUom" v-model="storeForm.uom"/>
                    <jet-input type="text" class="mt-4 block w-full" placeholder="MType"
                               ref="storeMtyp" v-model="storeForm.mtyp"/>
                    <jet-input type="text" class="mt-4 block w-full bg-gray-100 cursor-not-allowed"
                               placeholder="Currency"
                               ref="storeCrcy" v-model="storeForm.crcy" disabled/>
                    <jet-input type="number" class="mt-4 block w-full" placeholder="Harga"
                               ref="storePrice" v-model="storeForm.price"/>
                    <jet-input type="number" class="mt-4 block w-full" placeholder="Per"
                               ref="storePer" v-model="storeForm.per" @keyup.enter="store"/>
                </div>
            </template>
            <template #buttons>
                <jet-button @click="store"
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
        <jet-modal :show="confirmingUpdate" @close="closeUpdateModal" title="Edit material">
            <template #content>
                Silakan masukkan data material yang ingin diubah.
                <jet-validation-errors class="mt-4"/>
                <div class="mt-4">
                    <jet-select ref="updateArea" placeholder="Pilih Area" v-model="updateForm.area"
                                :data="areas" class="block w-full"/>
                    <jet-select ref="updatePeriod" placeholder="Pilih Period" v-model="updateForm.period"
                                :data="periods" class="mt-4 block w-full"/>
                    <jet-input type="text" class="block w-full" placeholder="Kode Material"
                               ref="updateCode" v-model="updateForm.code"/>
                    <jet-input type="text" class="mt-4 block w-full" placeholder="Deskripsi Material"
                               ref="updateDescription" v-model="updateForm.description"/>
                    <jet-input type="text" class="mt-4 block w-full" placeholder="UoM"
                               ref="updateUom" v-model="updateForm.uom"/>
                    <jet-input type="text" class="mt-4 block w-full" placeholder="MType"
                               ref="updateMtyp" v-model="updateForm.mtyp"/>
                    <jet-input type="text" class="mt-4 block w-full bg-gray-100 cursor-not-allowed"
                               placeholder="Currency"
                               ref="updateCrcy" v-model="updateForm.crcy" disabled/>
                    <jet-input type="number" class="mt-4 block w-full" placeholder="Harga"
                               ref="updatePrice" v-model="updateForm.price"/>
                    <jet-input type="number" class="mt-4 block w-full" placeholder="Per"
                               ref="updatePer" v-model="updateForm.per" @keyup.enter="update"/>
                </div>
            </template>
            <template #buttons>
                <jet-button @click="update"
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
        <jet-alert-modal :show="confirmingDestroy" @close="closeDestroyModal" title="Hapus material">
            <template #content>
                Apakah Anda yakin ingin menghapus material ini? Setelah material dihapus, semua sumber daya
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
        <jet-import-modal :show="confirmingImport" @close="closeImportModal" title="Impor data material">
            <template #content>
                <p>
                    Silakan unggah file data material yang ingin di-impor. Pastikan Anda sudah menggunakan template
                    spreadsheet yang ditentukan. Sistem hanya memproses data yang ada pada sheet <b>Worksheet</b>.
                </p>
                <p class="mt-2">
                    Mengimpor data baru akan menimpa data lama yang sudah ada. Aksi ini tidak dapat dibatalkan.
                </p>
                <jet-validation-errors class="mt-4"/>
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
                                       accept=".xls, .xlsx, .csv"
                                       @input="importForm.file = $event.target.files[0]"/>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500">
                            XLS, XLSX, CSV hingga 50MB
                        </p>
                    </div>
                </div>
            </template>
            <template #buttons>
                <jet-button
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
        <jet-export-modal :show="confirmingExport" @close="closeExportModal" title="Ekspor data material">
            <template #content>
                <p>
                    Apakah Anda yakin ingin mengekspor semua data material? Proses ekspor dapat memakan waktu lama,
                    tergantung dari banyaknya data yang tersedia.
                </p>
                <p class="mt-2">
                    Sistem akan mengekspor data berupa file spreadsheet dengan format <b>XLSX</b>.
                </p>
            </template>
            <template #buttons>
                <jet-button @click="exportFile"
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
import JetAreaDropdown from '@/Jetstream/AreaDropdown'
import JetPeriodDropdown from '@/Jetstream/PeriodDropdown'
import JetSelect from '@/Jetstream/Select'

JetTableEngine.respectParams(['area', 'period'])
JetTableEngine.enablePartial('materials')

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
                area: null,
                period: null,
                code: null,
                description: null,
                uom: null,
                mtyp: null,
                crcy: 'IDR',
                price: null,
                per: null
            }),
            updateForm: useForm({
                area: null,
                period: null,
                id: null,
                code: null,
                description: null,
                uom: null,
                mtyp: null,
                crcy: 'IDR',
                price: null,
                per: null
            }),
            destroyForm: useForm({
                id: null
            }),
            importForm: useForm({
                file: null
            })
        }
    },
    mixins: [JetTableEngine],
    props: {
        area: Object,
        areas: Object,
        period: Object,
        periods: Object,
        materials: Object
    },
    components: {
        AppLayout,
        JetTable,
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
            this.storeForm.post(route('materials.store'), {
                preserveScroll: true,
                onSuccess: () => {
                    this.reloadData()
                    this.closeStoreModal()
                    this.showSuccessNotification('Material berhasil ditambahkan', 'Sistem telah berhasil menyimpan data material baru')
                },
                onError: () => this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat menyimpan data material, mohon periksa ulang form')
            })
        },
        update() {
            this.updateForm.put(route('materials.update', this.updateForm.id), {
                preserveScroll: true,
                onSuccess: () => {
                    this.reloadData()
                    this.closeUpdateModal()
                    this.showSuccessNotification('Material berhasil diedit', 'Sistem telah berhasil mengedit data material')
                },
                onError: () => this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat mengubah data material, mohon periksa ulang form')
            })
        },
        destroy() {
            this.destroyForm.delete(route('materials.destroy', this.destroyForm.id), {
                preserveScroll: true,
                onSuccess: () => {
                    this.reloadData()
                    this.closeDestroyModal()
                    this.showSuccessNotification('Material berhasil dihapus', 'Sistem telah berhasil menghapus data material')
                },
                onError: () => this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat menghapus data material')
            })
        },
        importFile() {
            this.importForm.post(route('materials.import'), {
                preserveScroll: true,
                onSuccess: () => {
                    this.reloadData()
                    this.closeImportModal()
                    this.showSuccessNotification('Data material berhasil di-impor', 'Sistem telah berhasil mengimpor data material')
                },
                onError: () => this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat mengimpor data material, mohon gunakan template yang sudah ditentukan')
            })
        },
        exportFile() {
            window.open(route('materials.export'))
            this.closeExportModal()
        },
        confirmStore() {
            setTimeout(() => this.confirmingStore = true, 150)
            setTimeout(() => this.$refs.storeArea.focus(), 300)
        },
        confirmUpdate(material) {
            this.updateForm.id = material.id
            this.updateForm.area = material.area_id
            this.updateForm.period = material.period_id
            this.updateForm.code = material.code
            this.updateForm.description = material.description
            this.updateForm.uom = material.uom
            this.updateForm.mtyp = material.mtyp
            this.updateForm.price = material.price
            this.updateForm.per = material.per
            setTimeout(() => this.confirmingUpdate = true, 150)
            setTimeout(() => this.$refs.updateArea.focus(), 300)
        },
        confirmDestroy(material) {
            this.destroyForm.id = material.id
            setTimeout(() => this.confirmingDestroy = true, 150)
        },
        confirmImport() {
            setTimeout(() => this.confirmingImport = true, 150)
        },
        confirmExport() {
            setTimeout(() => this.confirmingExport = true, 150)
        },
        closeStoreModal() {
            this.confirmingStore = false
            setTimeout(() => {
                this.clearErrors()
                this.storeForm.clearErrors()
                this.storeForm.reset()
                this.storeForm.area = null
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
                this.updateForm.area = null
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
                this.importForm.file = null
            }, 500)
        },
        closeExportModal() {
            this.confirmingExport = false
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
            this.$inertia.get(route(route().current(), route().params), {}, {
                replace: true,
                preserveState: true,
                preserveScroll: true,
                only: [
                    'materials'
                ]
            })
        }
    }
})
</script>
