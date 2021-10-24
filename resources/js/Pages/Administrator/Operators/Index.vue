<template>
    <app-layout title="Pegawai">
        <grid-header>
            <jet-breadcrumbs :pages="[{name: 'Pegawai', href: 'operators.index', current: true}]"/>
            <div class="text-left lg:text-right">
                <div class="pt-9 lg:pt-0 mt-2">
                    <jet-button @click="confirmStore" class="ml-0 mr-2">
                        <plus-icon class="h-5 w-5 text-white" aria-hidden="true"/>
                    </jet-button>
                    <jet-button @click="confirmImport" class="mr-2">
                        <upload-icon class="h-5 w-5 text-white" aria-hidden="true"/>
                    </jet-button>
                    <jet-button @click="confirmExport">
                        <download-icon class="h-5 w-5 text-white" aria-hidden="true"/>
                    </jet-button>
                </div>
            </div>
        </grid-header>
        <Table
            :filters="queryBuilderProps.filters"
            :search="queryBuilderProps.search"
            :on-update="setQueryBuilder"
            :meta="users"
            class="pt-14 lg:pt-0"
        >
            <template #head>
                <tr>
                    <th v-show="showColumn('name')" @click.prevent="sortBy('name')">Nama</th>
                    <th v-show="showColumn('phone')" @click.prevent="sortBy('phone')">Nomor Telepon</th>
                    <th v-show="showColumn('nip')" @click.prevent="sortBy('nip')">NIP</th>
                    <th v-show="showColumn('role')">Peran</th>
                    <th v-show="showColumn('action')"></th>
                </tr>
            </template>
            <template #body>
                <tr v-for="user in users.data" :key="user.id">
                    <td v-show="showColumn('name')">{{ user.name }}</td>
                    <td v-show="showColumn('phone')">{{ user.phone }}</td>
                    <td v-show="showColumn('nip')">{{ user.nip }}</td>
                    <td v-show="showColumn('role')">Operator</td>
                    <td v-show="showColumn('action')">
                        <jet-dropdown name="Opsi">
                            <menu-item>
                                <button @click="confirmUpdate(user)"
                                        class="text-gray-700 hover:bg-gray-100 group flex items-center px-4 py-2 text-sm w-full">
                                    <pencil-alt-icon class="mr-3 h-5 w-5 text-gray-700" aria-hidden="true"/>
                                    Edit
                                </button>
                            </menu-item>
                            <menu-item>
                                <button @click="confirmDestroy(user)"
                                        class="text-gray-700 hover:bg-gray-100 group flex items-center px-4 py-2 text-sm w-full">
                                    <trash-icon class="mr-3 h-5 w-5 text-gray-700" aria-hidden="true"/>
                                    Hapus
                                </button>
                            </menu-item>
                        </jet-dropdown>
                    </td>
                </tr>
            </template>
        </Table>
        <jet-modal :show="confirmingStore" @close="closeStoreModal" title="Tambah pegawai">
            <template #content>
                Silakan masukkan data profil dan kredensial pegawai yang ingin ditambahkan.
                <jet-validation-errors class="mt-4"/>
                <div class="mt-4">
                    <jet-input type="text" class="block w-full" placeholder="Nama Pegawai"
                               ref="storeName" v-model="storeForm.name"/>
                    <jet-input type="text" class="mt-4 block w-full" placeholder="Nomor Telepon Pegawai"
                               ref="storePhone" v-model="storeForm.phone"/>
                    <jet-input type="text" class="mt-4 block w-full" placeholder="Nomor Induk Pegawai"
                               ref="storeNip" v-model="storeForm.nip"/>
                    <jet-input type="password" class="mt-4 block w-full" placeholder="Kata Sandi Akun"
                               ref="storePassword" v-model="storeForm.password"/>
                    <jet-input type="password" class="mt-4 block w-full" placeholder="Konfirmasi Kata Sandi Akun"
                               ref="storePasswordConfirmation" v-model="storeForm.password_confirmation"
                               @keyup.enter="store"/>
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
        <jet-modal :show="confirmingUpdateOperator" @close="closeUpdateModal" title="Edit pegawai">
            <template #content>
                Silakan masukkan data profil dan kredensial pegawai yang ingin diubah.
                <jet-validation-errors class="mt-4"/>
                <div class="mt-4">
                    <jet-input type="text" class="block w-full" placeholder="Nama Pegawai"
                               ref="updateName" v-model="updateForm.name"/>
                    <jet-input type="text" class="mt-4 block w-full" placeholder="Nomor Telepon Pegawai"
                               ref="updatePhone" v-model="updateForm.phone"/>
                    <jet-input type="text" class="mt-4 block w-full" placeholder="Nomor Induk Pegawai"
                               ref="updateNip" v-model="updateForm.nip"/>
                    <jet-input type="password" class="mt-4 block w-full" placeholder="Kata Sandi Akun"
                               ref="updatePassword" v-model="updateForm.password"/>
                    <jet-input type="password" class="mt-4 block w-full" placeholder="Konfirmasi Kata Sandi Akun"
                               ref="updatePasswordConfirmation" @keyup.enter="update"
                               v-model="updateForm.password_confirmation"/>
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
        <jet-alert-modal :show="confirmingDestroy" @close="closeDestroyModal" title="Hapus pegawai">
            <template #content>
                Apakah Anda yakin ingin menghapus akun akun pegawai ini? Setelah akun pegawai dihapus, semua sumber daya
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
        <jet-import-modal :show="confirmingImport" @close="closeImportModal" title="Impor data pegawai">
            <template #content>
                <p>
                    Silakan unggah file data pegawai yang ingin di-impor. Pastikan Anda sudah menggunakan template
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
        <jet-export-modal :show="confirmingExport" @close="closeExportModal" title="Ekspor data pegawai">
            <template #content>
                Apakah Anda yakin ingin mengekspor semua data pegawai? Proses ekspor dapat memakan waktu lama,
                tergantung dari banyaknya data yang tersedia.
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
import {Link as JetLink} from '@inertiajs/inertia-vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import GridHeader from '@/Layouts/GridHeader.vue'
import JetButton from '@/Jetstream/Button'
import JetDangerButton from '@/Jetstream/DangerButton'
import JetSecondaryButton from '@/Jetstream/SecondaryButton'
import JetBreadcrumbs from '@/Jetstream/Breadcrumbs'
import JetDropdown from '@/Jetstream/Dropdown.vue'
import JetModal from '@/Jetstream/Modal.vue'
import JetAlertModal from '@/Jetstream/AlertModal.vue'
import JetImportModal from '@/Jetstream/ImportModal'
import JetExportModal from '@/Jetstream/ExportModal';
import JetInput from '@/Jetstream/Input.vue'
import JetSuccessNotification from '@/Jetstream/SuccessNotification'
import JetDangerNotification from '@/Jetstream/DangerNotification'
import JetValidationErrors from '@/Jetstream/ValidationErrors';
import {MenuItem} from '@headlessui/vue'
import {Components, InteractsWithQueryBuilder, Tailwind2} from '@protonemedia/inertiajs-tables-laravel-query-builder'
import {DocumentAddIcon, DownloadIcon, PencilAltIcon, PlusIcon, TrashIcon, UploadIcon} from '@heroicons/vue/outline'

Components.Pagination.setTranslations({
    no_results_found: "Tidak ada data tersedia",
    previous: "Sebelumnya",
    next: "Selanjutnya",
    to: "hingga",
    of: "dari",
    results: "data"
});

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
            confirmingUpdateOperator: false,
            confirmingDestroy: false,
            confirmingImport: false,
            confirmingExport: false,
            showingSuccessNotification: false,
            showingDangerNotification: false,
            storeForm: this.$inertia.form({
                name: null,
                phone: null,
                nip: null,
                password: null,
                password_confirmation: null
            }),
            updateForm: this.$inertia.form({
                name: null,
                phone: null,
                nip: null,
                password: null,
                password_confirmation: null,
                photo: null
            }),
            destroyForm: this.$inertia.form(),
            importForm: this.$inertia.form({
                file: null
            })
        }
    },
    mixins: [InteractsWithQueryBuilder],
    props: {
        users: Object
    },
    components: {
        Table: Tailwind2.Table,
        AppLayout,
        GridHeader,
        JetBreadcrumbs,
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
        JetLink,
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
            this.storeForm.post(route('operators.store'), {
                preserveScroll: true,
                onSuccess: () => {
                    this.$inertia.reload()
                    this.closeStoreModal()
                    this.showSuccessNotification('Pegawai berhasil ditambahkan', 'Sistem telah berhasil menyimpan data pegawai baru')
                },
                onError: () => this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat menyimpan data pegawai, mohon periksa ulang form')
            })
        },
        update() {
            this.updateForm.put(route('operators.update', this.updateForm.id), {
                preserveScroll: true,
                onSuccess: () => {
                    this.$inertia.reload()
                    this.closeUpdateModal()
                    this.showSuccessNotification('Pegawai berhasil diedit', 'Sistem telah berhasil mengedit data pegawai')
                },
                onError: () => this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat mengubah data pegawai, mohon periksa ulang form')
            })
        },
        destroy() {
            this.destroyForm.delete(route('operators.destroy', this.destroyForm.id), {
                preserveScroll: true,
                onSuccess: () => {
                    this.$inertia.reload()
                    this.closeDestroyModal()
                    this.showSuccessNotification('Pegawai berhasil dihapus', 'Sistem telah berhasil menghapus data pegawai')
                },
                onError: () => this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat menghapus data pegawai')
            })
        },
        importFile() {
            this.importForm.post(route('operators.import'), {
                preserveScroll: true,
                onSuccess: () => {
                    this.$inertia.reload()
                    this.closeImportModal()
                    this.showSuccessNotification('Data pegawai berhasil di-impor', 'Sistem telah berhasil mengimpor data pegawai')
                },
                onError: () => this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat mengimpor data pegawai, mohon gunakan template yang sudah ditentukan')
            })
        },
        exportFile() {
            window.open(route('operators.export'))
            this.closeExportModal()
        },
        confirmStore() {
            setTimeout(() => this.confirmingStore = true, 150)
            setTimeout(() => this.$refs.storeName.focus(), 300)
        },
        confirmUpdate(operator) {
            this.updateForm.id = operator.id
            this.updateForm.name = operator.name
            this.updateForm.phone = operator.phone
            this.updateForm.nip = operator.nip
            setTimeout(() => this.confirmingUpdateOperator = true, 150)
            setTimeout(() => this.$refs.updateName.focus(), 300)
        },
        confirmDestroy(operator) {
            this.destroyForm.id = operator.id
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
                this.storeForm.reset()
            }, 500)
        },
        closeUpdateModal() {
            this.confirmingUpdateOperator = false
            setTimeout(() => {
                this.clearErrors()
                this.updateForm.reset()
            }, 500)
        },
        closeDestroyModal() {
            this.confirmingDestroy = false
            setTimeout(() => {
                this.clearErrors()
                this.destroyForm.reset()
            }, 500)
        },
        closeImportModal() {
            this.confirmingImport = false
            setTimeout(() => {
                this.clearErrors()
                this.importForm.reset()
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
            window.open('https://docs.google.com/spreadsheets/d/1uOA5ear--StRXSFf_iIYVW-50daP4KmA1vOcDxIRZoo/edit?usp=sharing').focus()
        },
        clearErrors() {
            this.$page.props.errors = []
        }
    }
})
</script>
