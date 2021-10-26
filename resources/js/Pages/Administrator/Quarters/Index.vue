<template>
    <app-layout title="Quarter">
        <grid-header>
            <jet-breadcrumbs :pages="[{name: 'Quarter', href: 'quarters.index', current: true}]"/>
            <div class="text-left lg:text-right">
                <div class="pt-4 lg:pt-0 mt-2">
                    <jet-button @click="confirmStore" class="ml-0 mr-2">
                        <plus-icon class="h-5 w-5 text-white" aria-hidden="true"/>
                    </jet-button>
                </div>
            </div>
        </grid-header>
        <jet-table
            :filters="queryBuilderProps.filters"
            :search="queryBuilderProps.search"
            :on-update="setQueryBuilder"
            :meta="quarters"
            class="pt-12 lg:pt-0">
            <template #head>
                <th v-show="showColumn('name')" @click.prevent="sortBy('name')">Nama Quarter</th>
                <th v-show="showColumn('action')"></th>
            </template>
            <template #body>
                <tr v-for="quarter in quarters.data" :key="quarter.id">
                    <td v-show="showColumn('name')">{{ quarter.name }}</td>
                    <td v-show="showColumn('action')" class="text-center">
                        <jet-dropdown name="Opsi">
                            <menu-item>
                                <button @click="confirmUpdate(quarter)"
                                        class="text-gray-700 hover:bg-gray-100 group flex items-center px-4 py-2 text-sm w-full">
                                    <pencil-alt-icon class="mr-3 h-5 w-5 text-gray-700" aria-hidden="true"/>
                                    Edit
                                </button>
                            </menu-item>
                            <menu-item>
                                <button @click="confirmDestroy(quarter)"
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
        <jet-modal :show="confirmingStore" @close="closeStoreModal" title="Tambah quarter">
            <template #content>
                Silakan masukkan data quarter yang ingin ditambahkan.
                <jet-validation-errors class="mt-4"/>
                <div class="mt-4">
                    <jet-input type="text" class="block w-full" placeholder="Nama Quarter"
                               ref="storeName" v-model="storeForm.name"
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
        <jet-modal :show="confirmingUpdate" @close="closeUpdateModal" title="Edit quarter">
            <template #content>
                Silakan masukkan data quarter yang ingin diubah.
                <jet-validation-errors class="mt-4"/>
                <div class="mt-4">
                    <jet-input type="text" class="block w-full" placeholder="Nama Quarter"
                               ref="updateName" v-model="updateForm.name"
                               @keyup.enter="update"/>
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
        <jet-alert-modal :show="confirmingDestroy" @close="closeDestroyModal" title="Hapus quarter">
            <template #content>
                Apakah Anda yakin ingin menghapus quarter ini? Setelah quarter dihapus, semua sumber daya
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
import {Link as JetLink, useForm} from '@inertiajs/inertia-vue3'
import {MenuItem} from '@headlessui/vue'
import {DocumentAddIcon, DownloadIcon, PencilAltIcon, PlusIcon, TrashIcon, UploadIcon} from '@heroicons/vue/outline'
import AppLayout from '@/Layouts/AppLayout'
import GridHeader from '@/Layouts/GridHeader'
import JetButton from '@/Jetstream/Button'
import JetDangerButton from '@/Jetstream/DangerButton'
import JetSecondaryButton from '@/Jetstream/SecondaryButton'
import JetBreadcrumbs from '@/Jetstream/Breadcrumbs'
import JetDropdown from '@/Jetstream/Dropdown'
import JetModal from '@/Jetstream/Modal'
import JetAlertModal from '@/Jetstream/AlertModal'
import JetInput from '@/Jetstream/Input'
import JetSuccessNotification from '@/Jetstream/SuccessNotification'
import JetDangerNotification from '@/Jetstream/DangerNotification'
import JetValidationErrors from '@/Jetstream/ValidationErrors'
import JetTable from '@/Jetstream/Table'

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
            showingSuccessNotification: false,
            showingDangerNotification: false,
            storeForm: useForm({
                name: null
            }),
            updateForm: useForm({
                id: null,
                name: null
            }),
            destroyForm: useForm({
                id: null
            })
        }
    },
    mixins: [JetTable.props.engine],
    props: {
        quarters: Object
    },
    components: {
        AppLayout,
        GridHeader,
        JetTable,
        JetBreadcrumbs,
        JetDropdown,
        JetButton,
        JetDangerButton,
        JetSecondaryButton,
        JetModal,
        JetAlertModal,
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
            this.storeForm.post(route('quarters.store'), {
                preserveScroll: true,
                onSuccess: () => {
                    this.$inertia.reload()
                    this.closeStoreModal()
                    this.showSuccessNotification('Quarter berhasil ditambahkan', 'Sistem telah berhasil menyimpan data quarter baru')
                },
                onError: () => this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat menyimpan data quarter, mohon periksa ulang form')
            })
        },
        update() {
            this.updateForm.put(route('quarters.update', this.updateForm.id), {
                preserveScroll: true,
                onSuccess: () => {
                    this.$inertia.reload()
                    this.closeUpdateModal()
                    this.showSuccessNotification('Quarter berhasil diedit', 'Sistem telah berhasil mengedit data quarter')
                },
                onError: () => this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat mengubah data quarter, mohon periksa ulang form')
            })
        },
        destroy() {
            this.destroyForm.delete(route('quarters.destroy', this.destroyForm.id), {
                preserveScroll: true,
                onSuccess: () => {
                    this.$inertia.reload()
                    this.closeDestroyModal()
                    this.showSuccessNotification('Quarter berhasil dihapus', 'Sistem telah berhasil menghapus data quarter')
                },
                onError: () => this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat menghapus data quarter')
            })
        },
        confirmStore() {
            setTimeout(() => this.confirmingStore = true, 150)
            setTimeout(() => this.$refs.storeName.focus(), 300)
        },
        confirmUpdate(quarter) {
            this.updateForm.id = quarter.id
            this.updateForm.name = quarter.name
            setTimeout(() => this.confirmingUpdate = true, 150)
            setTimeout(() => this.$refs.updateName.focus(), 300)
        },
        confirmDestroy(quarter) {
            this.destroyForm.id = quarter.id
            setTimeout(() => this.confirmingDestroy = true, 150)
        },
        closeStoreModal() {
            this.confirmingStore = false
            setTimeout(() => {
                this.clearErrors()
                this.storeForm.clearErrors()
                this.storeForm.reset()
                this.storeForm.name = null
            }, 500)
        },
        closeUpdateModal() {
            this.confirmingUpdate = false
            setTimeout(() => {
                this.clearErrors()
                this.updateForm.clearErrors()
                this.updateForm.reset()
                this.updateForm.id = null
                this.updateForm.name = null
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
        clearErrors() {
            this.$page.props.errors = []
        }
    }
})
</script>
