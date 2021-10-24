<template>
    <app-layout title="Pegawai">
        <grid-header>
            <jet-breadcrumbs :pages="[{name: 'Pegawai', href: 'operators.index', current: true}]"/>
            <div class="text-left lg:text-right">
                <div class="pt-9 lg:pt-0 mt-2">
                    <jet-button @click="confirmStoreOperator" class="ml-0 mr-2">
                        <plus-icon class="h-5 w-5 text-white" aria-hidden="true"/>
                    </jet-button>
                    <jet-button class="mr-2">
                        <upload-icon class="h-5 w-5 text-white" aria-hidden="true"/>
                    </jet-button>
                    <jet-button>
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
                                <button @click="confirmUpdateOperator(user)"
                                        class="text-gray-700 hover:bg-gray-100 group flex items-center px-4 py-2 text-sm w-full">
                                    <pencil-alt-icon class="mr-3 h-5 w-5 text-gray-700" aria-hidden="true"/>
                                    Edit
                                </button>
                            </menu-item>
                            <menu-item>
                                <button @click="confirmDestroyOperator(user)"
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
        <jet-modal :show="confirmingStoreOperator" @close="closeStoreOperatorModal" title="Tambah pegawai">
            <template #content>
                Silakan masukkan data profil dan kredensial pegawai yang ingin ditambahkan.
                <div class="mt-4">
                    <jet-input type="text" class="block w-full" placeholder="Nama Pegawai"
                               ref="storeOperatorName" v-model="storeOperatorForm.name"/>
                    <jet-input-error :message="storeOperatorForm.errors.name" class="mt-2"/>
                    <jet-input type="text" class="mt-4 block w-full" placeholder="Nomor Telepon Pegawai"
                               ref="storeOperatorPhone" v-model="storeOperatorForm.phone"/>
                    <jet-input-error :message="storeOperatorForm.errors.phone" class="mt-2"/>
                    <jet-input type="text" class="mt-4 block w-full" placeholder="Nomor Induk Pegawai"
                               ref="storeOperatorNip" v-model="storeOperatorForm.nip"/>
                    <jet-input-error :message="storeOperatorForm.errors.nip" class="mt-2"/>
                    <jet-input type="password" class="mt-4 block w-full" placeholder="Kata Sandi Akun"
                               ref="storeOperatorPassword" v-model="storeOperatorForm.password"/>
                    <jet-input-error :message="storeOperatorForm.errors.password" class="mt-2"/>
                    <jet-input type="password" class="mt-4 block w-full" placeholder="Konfirmasi Kata Sandi Akun"
                               ref="storeOperatorPasswordConfirmation" v-model="storeOperatorForm.password_confirmation"
                               @keyup.enter="storeOperator"/>
                    <jet-input-error :message="storeOperatorForm.errors.password_confirmation" class="mt-2"/>
                </div>
            </template>
            <template #buttons>
                <jet-button @click="storeOperator"
                            :class="{ 'opacity-25': storeOperatorForm.processing }"
                            :disabled="storeOperatorForm.processing"
                            class="w-full inline-flex justify-center px-4 py-2 mt-2 sm:ml-3 sm:w-auto">
                    Simpan
                </jet-button>
                <jet-secondary-button @click="closeStoreOperatorModal"
                                      class="w-full inline-flex justify-center px-4 py-2 mt-2 sm:ml-3 sm:w-auto">
                    Batalkan
                </jet-secondary-button>
            </template>
        </jet-modal>
        <jet-modal :show="confirmingUpdateOperator" @close="closeUpdateOperatorModal" title="Edit pegawai">
            <template #content>
                Silakan masukkan data profil dan kredensial pegawai yang ingin diubah.
                <div class="mt-4">
                    <jet-input type="text" class="block w-full" placeholder="Nama Pegawai"
                               ref="updateOperatorName" v-model="operator.name"/>
                    <jet-input-error :message="updateOperatorForm.errors.name" class="mt-2"/>
                    <jet-input type="text" class="mt-4 block w-full" placeholder="Nomor Telepon Pegawai"
                               ref="updateOperatorPhone" v-model="operator.phone"/>
                    <jet-input-error :message="updateOperatorForm.errors.phone" class="mt-2"/>
                    <jet-input type="text" class="mt-4 block w-full" placeholder="Nomor Induk Pegawai"
                               ref="updateOperatorNip" v-model="operator.nip"/>
                    <jet-input-error :message="updateOperatorForm.errors.nip" class="mt-2"/>
                    <jet-input type="password" class="mt-4 block w-full" placeholder="Kata Sandi Akun"
                               ref="updateOperatorPasssword"/>
                    <jet-input-error :message="updateOperatorForm.errors.password" class="mt-2"/>
                    <jet-input type="password" class="mt-4 block w-full" placeholder="Konfirmasi Kata Sandi Akun"
                               ref="updateOperatorPassswordConfirmation" @keyup.enter="updateOperator"/>
                    <jet-input-error :message="updateOperatorForm.errors.password_confirmation" class="mt-2"/>
                </div>
            </template>
            <template #buttons>
                <jet-button @click="updateOperator"
                            :class="{ 'opacity-25': updateOperatorForm.processing }"
                            :disabled="updateOperatorForm.processing"
                            class="w-full inline-flex justify-center px-4 py-2 mt-2 sm:ml-3 sm:w-auto">
                    Simpan
                </jet-button>
                <jet-secondary-button @click="closeUpdateOperatorModal"
                                      class="w-full inline-flex justify-center px-4 py-2 mt-2 sm:ml-3 sm:w-auto">
                    Batalkan
                </jet-secondary-button>
            </template>
        </jet-modal>
        <jet-alert-modal :show="confirmingDestroyOperator" @close="closeDestroyOperatorModal" title="Hapus pegawai">
            <template #content>
                Apakah Anda yakin ingin menghapus akun akun pegawai ini? Setelah akun pegawai dihapus, semua sumber daya
                dan datanya akan dihapus secara permanen. Aksi ini tidak dapat dibatalkan.
            </template>
            <template #buttons>
                <jet-danger-button @click="destroyOperator"
                                   :class="{ 'opacity-25': destroyOperatorForm.processing }"
                                   :disabled="destroyOperatorForm.processing"
                                   class="w-full inline-flex justify-center px-4 py-2 mt-2 sm:ml-3 sm:w-auto">
                    Hapus
                </jet-danger-button>
                <jet-secondary-button @click="closeDestroyOperatorModal"
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
import JetInput from '@/Jetstream/Input.vue'
import JetInputError from '@/Jetstream/InputError'
import JetSuccessNotification from '@/Jetstream/SuccessNotification'
import JetDangerNotification from '@/Jetstream/DangerNotification'
import {MenuItem} from '@headlessui/vue'
import {Components, InteractsWithQueryBuilder, Tailwind2} from '@protonemedia/inertiajs-tables-laravel-query-builder'
import {DownloadIcon, PencilAltIcon, PlusIcon, TrashIcon, UploadIcon} from '@heroicons/vue/outline'

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
            operator: null,
            successNotification: {
                title: null,
                description: null
            },
            dangerNotification: {
                title: null,
                description: null
            },
            confirmingStoreOperator: false,
            confirmingUpdateOperator: false,
            confirmingDestroyOperator: false,
            showingSuccessNotification: false,
            showingDangerNotification: false,
            storeOperatorForm: this.$inertia.form({
                name: null,
                phone: null,
                nip: null,
                password: null,
                password_confirmation: null
            }),
            updateOperatorForm: this.$inertia.form({
                name: null,
                phone: null,
                nip: null,
                password: null,
                password_confirmation: null,
                photo: null
            }),
            destroyOperatorForm: this.$inertia.form()
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
        JetInput,
        JetInputError,
        JetSuccessNotification,
        JetDangerNotification,
        JetLink,
        MenuItem,
        PlusIcon,
        UploadIcon,
        DownloadIcon,
        PencilAltIcon,
        TrashIcon
    },
    methods: {
        confirmStoreOperator() {
            setTimeout(() => this.confirmingStoreOperator = true, 150)
            setTimeout(() => this.$refs.storeOperatorName.focus(), 300)
        },
        confirmUpdateOperator(operator) {
            this.operator = operator
            setTimeout(() => this.confirmingUpdateOperator = true, 150)
            setTimeout(() => this.$refs.updateOperatorName.focus(), 300)
        },
        confirmDestroyOperator(operator) {
            this.operator = operator
            setTimeout(() => this.confirmingDestroyOperator = true, 300)
        },
        storeOperator() {
            this.storeOperatorForm.post(route('operators.store'), {
                preserveScroll: true,
                onSuccess: () => {
                    this.$inertia.reload()
                    this.closeStoreOperatorModal()
                    this.showSuccessNotification('Pegawai berhasil ditambahkan', 'Sistem telah berhasil menyimpan data pegawai baru')
                },
                onError: () => this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat menyimpan data pegawai, mohon periksa ulang form')
            })
        },
        updateOperator() {
            this.updateOperatorForm.put(route('operators.update', this.operator.id), {
                preserveScroll: true,
                onSuccess: () => {
                    this.$inertia.reload()
                    this.closeUpdateOperatorModal()
                    this.showSuccessNotification('Pegawai berhasil diedit', 'Sistem telah berhasil mengedit data pegawai')
                },
                onError: () => this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat mengubah data pegawai, mohon periksa ulang form')
            })
        },
        destroyOperator() {
            this.destroyOperatorForm.delete(route('operators.destroy', this.operator.id), {
                preserveScroll: true,
                onSuccess: () => {
                    this.$inertia.reload()
                    this.closeDestroyOperatorModal()
                    this.showSuccessNotification('Pegawai berhasil dihapus', 'Sistem telah berhasil menghapus data pegawai')
                },
                onError: () => this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat menghapus data pegawai')
            })
        },
        closeStoreOperatorModal() {
            this.confirmingStoreOperator = false
            setTimeout(() => {
                this.storeOperatorForm.clearErrors()
                this.storeOperatorForm.reset()
            }, 500)
        },
        closeUpdateOperatorModal() {
            this.confirmingUpdateOperator = false
            setTimeout(() => {
                this.updateOperatorForm.clearErrors()
                this.updateOperatorForm.reset()
            }, 500)
        },
        closeDestroyOperatorModal() {
            this.confirmingDestroyOperator = false
            setTimeout(() => {
                this.destroyOperatorForm.clearErrors()
                this.destroyOperatorForm.reset()
            }, 500)
        },
        showSuccessNotification(title, message) {
            this.successNotification.title = title
            this.successNotification.description = message
            this.showingSuccessNotification = true
            setTimeout(() => this.showingSuccessNotification ? this.closeSuccessNotification() : null, 5000)
        },
        showDangerNotification(title, message) {
            this.dangerNotification.title = title
            this.dangerNotification.description = message
            this.showingDangerNotification = true
            setTimeout(() => this.showingDangerNotification ? this.closeDangerNotification() : null, 5000)
        },
        closeSuccessNotification() {
            this.showingSuccessNotification = false
        },
        closeDangerNotification() {
            this.showingDangerNotification = false
        }
    }
})
</script>
