<template>
    <app-layout title="Hasil Stock">
        <div class="grid grid-cols-1 mb-6 lg:grid-cols-2 lg:gap-6">
            <div class="grid grid-cols-1 mb-4 lg:grid-cols-2 lg:mb-0">
                <jet-area-dropdown :selected="area" :areas="areas" partial="stocks" class="mb-4 lg:mb-0"/>
                <jet-period-dropdown :selected="period" :periods="periods" partial="stocks" class="mb-2 lg:mb-0"/>
            </div>
            <div class="lg:text-right">
                <jet-button type="button" @click="confirmStore">
                    <plus-icon class="w-5 h-5 mr-2 text-white" aria-hidden="true"/>
                    Tambah
                </jet-button>
            </div>
        </div>
        <jet-table
            :filters="queryBuilderProps.filters"
            :search="queryBuilderProps.search"
            :on-update="setQueryBuilder"
            :meta="stocks"
            ref="table">
            <template #head>
                <jet-table-header
                    v-show="showColumn('sub_area_name')"
                    :cell="sortableHeader('sub_area_name')">
                    Sub Area
                </jet-table-header>
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
                    v-show="showColumn('uom')"
                    :cell="sortableHeader('mtyp')">
                    MType
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('action')"
                    :cell="staticHeader('action')"/>
            </template>
            <template #body>
                <tr v-for="stock in stocks.data" :key="stock.id">
                    <td v-show="showColumn('sub_area_name')">{{ stock.sub_area_name }}</td>
                    <td v-show="showColumn('material_code')">{{ stock.material_code }}</td>
                    <td v-show="showColumn('batch_code')">{{ stock.batch_code }}</td>
                    <td v-show="showColumn('material_description')">{{ stock.material_description }}</td>
                    <td v-show="showColumn('quantity')">{{ stock.quantity }}</td>
                    <td v-show="showColumn('uom')">{{ stock.uom }}</td>
                    <td v-show="showColumn('mtyp')">{{ stock.mtyp }}</td>
                    <td v-show="showColumn('action')" class="text-center">
                        <jet-dropdown name="Opsi">
                            <menu-item>
                                <button @click="confirmUpdate(stock)"
                                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 group">
                                    <pencil-alt-icon class="w-5 h-5 mr-3 text-gray-700" aria-hidden="true"/>
                                    Edit
                                </button>
                            </menu-item>
                            <menu-item>
                                <button @click="confirmDestroy(stock)"
                                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 group">
                                    <trash-icon class="w-5 h-5 mr-3 text-gray-700" aria-hidden="true"/>
                                    Hapus
                                </button>
                            </menu-item>
                        </jet-dropdown>
                    </td>
                </tr>
            </template>
        </jet-table>
        <jet-modal :show="confirmingUpdate" @close="closeUpdateModal" title="Edit hasil stock">
            <template #content>
                Silakan masukkan data hasil stock yang ingin diubah.
                <jet-validation-errors class="mt-4"/>
                <div class="mt-4">
                    <v-select
                        placeholder="Cari Sub Area"
                        class="block w-full rounded-md vue-select"
                        v-model="updateForm.sub_area"
                        :filterable="false"
                        :clearable="false"
                        :options="subAreaOptions"
                        @search="onSubAreaSearch">
                        <template slot="no-options">
                            Tidak ada hasil tersedia.
                        </template>
                        <template v-slot:no-options="{ search, searching }">
                            <template v-if="searching">
                                Tidak ada hasil untuk <em>{{ search }}</em>.
                            </template>
                            <em v-else style="opacity: 0.5">Mulai mengetik untuk mencari sub area.</em>
                        </template>
                    </v-select>
                    <jet-select ref="updatePeriod" placeholder="Pilih Periode" v-model="updateForm.period"
                                :data="periods" class="block w-full mt-4"/>
                    <jet-input type="text" class="block w-full mt-4 uppercase" placeholder="Kode Material"
                               ref="updateMaterialCode" v-model="updateForm.material_code"/>
                    <jet-input type="text" class="block w-full mt-4 uppercase" placeholder="Kode Batch"
                               ref="updateBatch" v-model="updateForm.batch_code"/>
                    <jet-input type="number" class="block w-full mt-4" placeholder="Kuantitas"
                               ref="updateQuantity" v-model="updateForm.quantity" @keyup.enter="update"/>
                </div>
            </template>
            <template #buttons>
                <jet-button type="button" @click="update"
                            :class="{ 'opacity-25': updateForm.processing }"
                            :disabled="updateForm.processing"
                            class="inline-flex justify-center w-full px-4 py-2 mt-2 sm:ml-3 sm:w-auto">
                    Simpan
                </jet-button>
                <jet-secondary-button @click="closeUpdateModal"
                                      class="inline-flex justify-center w-full px-4 py-2 mt-2 sm:ml-3 sm:w-auto">
                    Batalkan
                </jet-secondary-button>
            </template>
        </jet-modal>
        <jet-alert-modal :show="confirmingDestroy" @close="closeDestroyModal" title="Hapus hasil stock">
            <template #content>
                Apakah Anda yakin ingin menghapus hasil stock ini? Setelah hasil stock dihapus, semua sumber daya
                dan datanya akan dihapus secara permanen. Aksi ini tidak dapat dibatalkan.
            </template>
            <template #buttons>
                <jet-danger-button @click="destroy"
                                   :class="{ 'opacity-25': destroyForm.processing }"
                                   :disabled="destroyForm.processing"
                                   class="inline-flex justify-center w-full px-4 py-2 mt-2 sm:ml-3 sm:w-auto">
                    Hapus
                </jet-danger-button>
                <jet-secondary-button @click="closeDestroyModal"
                                      class="inline-flex justify-center w-full px-4 py-2 mt-2 sm:ml-3 sm:w-auto">
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
import {PencilAltIcon, PlusIcon, TrashIcon} from '@heroicons/vue/outline'
import AppLayout from '@/Layouts/AppLayout'
import JetButton from '@/Jetstream/Button'
import JetDisabledButton from '@/Jetstream/DisabledButton'
import JetDangerButton from '@/Jetstream/DangerButton'
import JetSecondaryButton from '@/Jetstream/SecondaryButton'
import JetDropdown from '@/Jetstream/Dropdown'
import JetModal from '@/Jetstream/Modal'
import JetAlertModal from '@/Jetstream/AlertModal'
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
import vSelect from 'vue-select'

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
            confirmingUpdate: false,
            confirmingDestroy: false,
            showingSuccessNotification: false,
            showingDangerNotification: false,
            updateForm: useForm({
                id: null,
                sub_area: null,
                period: null,
                material_code: null,
                batch_code: null,
                quantity: null
            }),
            destroyForm: useForm({
                id: null
            }),
            subAreaOptions: []
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
        JetDropdown,
        JetButton,
        JetDisabledButton,
        JetDangerButton,
        JetSecondaryButton,
        JetModal,
        JetAlertModal,
        JetInput,
        JetSuccessNotification,
        JetDangerNotification,
        JetValidationErrors,
        JetAreaDropdown,
        JetPeriodDropdown,
        JetSelect,
        JetTableHeader,
        MenuItem,
        PlusIcon,
        PencilAltIcon,
        TrashIcon,
        vSelect
    },
    methods: {
        update() {
            this.updateForm.put(route('stocks.update', this.updateForm.id), {
                preserveScroll: true,
                onSuccess: () => {
                    this.reloadData()
                    this.closeUpdateModal()
                    this.showSuccessNotification('Hasil stock berhasil diedit', 'Sistem telah berhasil mengedit data hasil stock')
                },
                onError: () => this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat mengubah data hasil stock, mohon periksa ulang form')
            })
        },
        destroy() {
            this.destroyForm.delete(route('stocks.destroy', this.destroyForm.id), {
                preserveScroll: true,
                onSuccess: () => {
                    this.reloadData()
                    this.closeDestroyModal()
                    this.showSuccessNotification('Hasil stock berhasil dihapus', 'Sistem telah berhasil menghapus data hasil stock')
                },
                onError: () => this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat menghapus data hasil stock')
            })
        },
        confirmStore() {
            this.$inertia.get(route('stocks.create'), {}, {
                replace: false,
                preserveState: true,
                preserveScroll: true
            })
        },
        confirmUpdate(stock) {
            this.updateForm.id = stock.id
            this.updateForm.sub_area = {
                id: stock.sub_area_id,
                label: `${stock.area_name} - ${stock.sub_area_name} - ${stock.sloc}`
            }
            this.updateForm.period = stock.period_id
            this.updateForm.material_code = stock.material_code
            this.updateForm.batch_code = stock.batch_code
            this.updateForm.quantity = stock.quantity
            setTimeout(() => this.confirmingUpdate = true, 150)
            setTimeout(() => this.$refs.updateArea.focus(), 300)
        },
        confirmDestroy(stock) {
            this.destroyForm.id = stock.id
            setTimeout(() => this.confirmingDestroy = true, 150)
        },
        closeUpdateModal() {
            this.confirmingUpdate = false
            setTimeout(() => {
                this.clearErrors()
                this.updateForm.clearErrors()
                this.updateForm.reset()
                this.updateForm.id = null
                this.updateForm.sub_area = null
                this.updateForm.period = null
                this.updateForm.material_code = null
                this.updateForm.batch_code = null
                this.updateForm.quantity = null
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
        },
        reloadData() {
            this.$refs.table.reload('stocks')
        },
        onSubAreaSearch(search, loading) {
            if (search.length) {
                loading(true)
                this.subAreaSearch(loading, this, this.showDangerNotification, {
                    q: escape(search)
                })
            }
        },
        subAreaSearch: _.debounce((loading, vm, errorCallback, params) => {
            axios.get(route('api.subareas'), {
                params: params
            }).then(res => {
                vm.subAreaOptions = res.data.items.map((item) => {
                    return {
                        id: item.id,
                        label: `${item.area_name} - ${item.name} - ${item.sloc}`
                    }
                })
            }).catch(() => {
                errorCallback('Kesalahan telah terjadi', 'Sistem tidak dapat mengambil data sub area, mohon coba lagi nanti')
            }).finally(() => {
                loading(false)
            })
        }, 1000)
    }
})
</script>
