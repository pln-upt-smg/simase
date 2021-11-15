<template>
    <app-layout title="Entry Stock">
        <tabs class="mb-6 lg:mb-8 lg:-mt-3"/>
        <jet-form-section @submitted="store">
            <template #title>
                Input Material Code
            </template>
            <template #description>
                Tambah actual stock baru menggunakan Material Code.
            </template>
            <template #form ref="form">
                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="sub_area" value="Sub Area"/>
                    <div class="flex mt-2">
                        <v-select
                            id="sub_area"
                            placeholder="Cari Sub Area"
                            class="vue-select rounded-md block w-full"
                            v-model="form.sub_area"
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
                    </div>
                    <jet-input-error :message="form.errors.sub_area" class="mt-2"/>
                </div>
                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="period" value="Periode"/>
                    <jet-select id="period" placeholder="Pilih Periode" v-model="form.period"
                                :data="periods" class="mt-2 block w-full normal-case text-base lg:text-lg"/>
                    <jet-input-error :message="form.errors.period" class="mt-2"/>
                </div>
                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="material_code" value="Kode Material"/>
                    <div class="flex mt-2">
                        <v-select
                            id="material_code"
                            placeholder="Masukkan Kode Material"
                            class="vue-select rounded-md block w-full"
                            label="code"
                            v-model="form.material_code"
                            v-on:option:selected="onMaterialCodeSelected"
                            :filterable="false"
                            :clearable="false"
                            :options="materialCodeOptions"
                            :reduce="option => option.code"
                            @search="onMaterialCodeSearch">
                            <template slot="no-options">
                                Tidak ada hasil tersedia.
                            </template>
                            <template v-slot:no-options="{ search, searching }">
                                <template v-if="searching">
                                    Tidak ada hasil untuk <em>{{ search }}</em>.
                                </template>
                                <em v-else style="opacity: 0.5">Mulai mengetik untuk mencari kode material.</em>
                            </template>
                        </v-select>
                        <div class="ml-2">
                            <jet-secondary-button type="button" @click="confirmScanMaterialBarcode" class="h-full">
                                <qrcode-icon class="h-5 w-5 text-gray-800" aria-hidden="true"/>
                            </jet-secondary-button>
                        </div>
                    </div>
                    <jet-input-error :message="form.errors.material_code" class="mt-2"/>
                </div>
                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="material_description" value="Deskripsi Material"/>
                    <jet-input id="material_description" type="text"
                               class="mt-2 block w-full bg-gray-100 cursor-not-allowed capitalize"
                               v-model="materialData.description" disabled
                               title="Data ini diambil berdasarkan Kode Material yang diberikan."/>
                </div>
                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="batch_code" value="Kode Batch"/>
                    <div class="flex mt-2">
                        <v-select
                            id="batch_code"
                            placeholder="Masukkan Kode Batch"
                            class="vue-select rounded-md block w-full"
                            label="code"
                            v-model="form.batch_code"
                            :filterable="false"
                            :clearable="false"
                            :options="batchCodeOptions"
                            :reduce="option => option.code"
                            @search="onBatchCodeSearch">
                            <template slot="no-options">
                                Tidak ada hasil tersedia.
                            </template>
                            <template v-slot:no-options="{ search, searching }">
                                <template v-if="searching">
                                    Tidak ada hasil untuk <em>{{ search }}</em>.
                                </template>
                                <em v-else style="opacity: 0.5">Mulai mengetik untuk mencari kode batch.</em>
                            </template>
                        </v-select>
                        <div class="ml-2">
                            <jet-secondary-button type="button" @click="confirmScanBatchBarcode" class="h-full">
                                <qrcode-icon class="h-5 w-5 text-gray-800" aria-hidden="true"/>
                            </jet-secondary-button>
                        </div>
                    </div>
                    <jet-input-error :message="form.errors.batch_code" class="mt-2"/>
                </div>
                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="quantity" value="Kuantitas"/>
                    <jet-input id="quantity" type="number" class="mt-2 block w-full" v-model="form.quantity"
                               autocomplete="quantity" placeholder="Masukkan Kuantitas"/>
                    <jet-input-error :message="form.errors.quantity" class="mt-2"/>
                </div>
                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="uom" value="UoM"/>
                    <jet-input id="uom" type="text"
                               class="mt-2 block w-full bg-gray-100 cursor-not-allowed capitalize"
                               v-model="materialData.uom" disabled
                               title="Data ini diambil berdasarkan Kode Material yang diberikan."/>
                </div>
            </template>
            <template #actions>
                <jet-action-message :on="form.recentlySuccessful" class="mr-3">
                    Data berhasil disimpan.
                </jet-action-message>
                <jet-button :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Simpan
                </jet-button>
            </template>
        </jet-form-section>
        <barcode-scanner
            title="Scan Material Barcode"
            :show="confirmingMaterialBarcodeScanning"
            @close="closeMaterialBarcodeScanner"
            @decode="onMaterialBarcodeDecoded"/>
        <barcode-scanner
            title="Scan Batch Barcode"
            :show="confirmingBatchBarcodeScanning"
            @close="closeBatchBarcodeScanner"
            @decode="onBatchBarcodeDecoded"/>
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
import AppLayout from '@/Layouts/AppLayout'
import Tabs from '@/Pages/Operator/Stocks/Input/Tabs'
import BarcodeScanner from '@/Pages/Operator/Stocks/Input/BarcodeScanner'
import JetButton from '@/Jetstream/Button'
import JetSecondaryButton from '@/Jetstream/SecondaryButton'
import JetFormSection from '@/Jetstream/FormSection'
import JetInput from '@/Jetstream/Input'
import JetInputError from '@/Jetstream/InputError'
import JetLabel from '@/Jetstream/Label'
import JetActionMessage from '@/Jetstream/ActionMessage'
import JetSelect from '@/Jetstream/Select'
import JetSuccessNotification from '@/Jetstream/SuccessNotification'
import JetDangerNotification from '@/Jetstream/DangerNotification'
import vSelect from 'vue-select'
import {QrcodeIcon} from '@heroicons/vue/outline'

export default defineComponent({
    components: {
        AppLayout,
        Tabs,
        BarcodeScanner,
        JetActionMessage,
        JetButton,
        JetSecondaryButton,
        JetFormSection,
        JetInput,
        JetInputError,
        JetLabel,
        JetSelect,
        JetSuccessNotification,
        JetDangerNotification,
        vSelect,
        QrcodeIcon
    },
    props: {
        areas: Object,
        periods: Object
    },
    data() {
        return {
            confirmingMaterialBarcodeScanning: false,
            confirmingBatchBarcodeScanning: false,
            showingSuccessNotification: false,
            showingDangerNotification: false,
            successNotification: {
                title: null,
                description: null
            },
            dangerNotification: {
                title: null,
                description: null
            },
            materialData: {
                id: null,
                description: '-',
                uom: '-'
            },
            form: useForm({
                sub_area: null,
                period: null,
                material_code: null,
                batch_code: null,
                quantity: null
            }),
            subAreaOptions: [],
            materialCodeOptions: [],
            batchCodeOptions: []
        }
    },
    methods: {
        store() {
            this.form.post(route('stocks.store'), {
                replace: true,
                preserveScroll: true,
                onSuccess: () => {
                    this.showSuccessNotification('Stock berhasil ditambahkan', 'Sistem telah berhasil menyimpan data stock baru')
                    this.reset()
                },
                onError: () => this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat menyimpan data stock, mohon periksa ulang form')
            })
        },
        reset() {
            this.form.clearErrors()
            this.form.material_code = null
            this.form.batch_code = null
            this.form.quantity = null
            this.materialData.id = null
            this.materialData.description = '-'
            this.materialData.uom = '-'
        },
        confirmScanMaterialBarcode() {
            this.confirmingMaterialBarcodeScanning = true
        },
        confirmScanBatchBarcode() {
            this.confirmingBatchBarcodeScanning = true
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
        closeMaterialBarcodeScanner() {
            this.confirmingMaterialBarcodeScanning = false
        },
        closeBatchBarcodeScanner() {
            this.confirmingBatchBarcodeScanning = false
        },
        onMaterialBarcodeDecoded(code) {
            this.form.material_code = code
            this.resolveMaterialData()
        },
        onBatchBarcodeDecoded(code) {
            this.form.batch_code = code
            this.resolveBatchData()
        },
        onSubAreaSearch(search, loading) {
            if (search.length) {
                loading(true)
                this.subAreaSearch(loading, search, this, this.showDangerNotification, {
                    q: escape(search)
                })
            }
        },
        subAreaSearch: _.debounce((loading, search, vm, errorCallback, params) => {
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
        }, 300),
        onMaterialCodeSearch(search, loading) {
            if (search.length) {
                loading(true)
                this.materialCodeSearch(loading, search, this, this.showDangerNotification, {
                    q: escape(search),
                    area: this.form.area ?? 0,
                    period: this.form.period ?? 0
                })
            }
        },
        materialCodeSearch: _.debounce((loading, search, vm, errorCallback, params) => {
            axios.get(route('api.materials'), {
                params: params
            }).then(res => {
                vm.materialCodeOptions = res.data.items
            }).catch(() => {
                errorCallback('Kesalahan telah terjadi', 'Sistem tidak dapat mengambil data kode material, mohon coba lagi nanti')
            }).finally(() => {
                loading(false)
            })
        }, 300),
        onBatchCodeSearch(search, loading) {
            if (search.length) {
                loading(true)
                this.batchCodeSearch(loading, search, this, this.showDangerNotification, {
                    q: escape(search),
                    subarea: this.form.sub_area?.id ?? 0,
                    material: this.materialData.id ?? 0
                })
            }
        },
        batchCodeSearch: _.debounce((loading, search, vm, errorCallback, params) => {
            axios.get(route('api.batches'), {
                params: params
            }).then(res => {
                vm.batchCodeOptions = res.data.items
            }).catch(() => {
                errorCallback('Kesalahan telah terjadi', 'Sistem tidak dapat mengambil data kode batch, mohon coba lagi nanti')
            }).finally(() => {
                loading(false)
            })
        }, 300),
        onMaterialCodeSelected(material) {
            this.materialData.id = material.id
            this.materialData.description = material.description
            this.materialData.uom = material.uom
        },
        resolveMaterialData() {
            axios.get(route('api.material'), {
                params: {
                    q: escape(this.form.material_code),
                    period: this.form.period ?? 0
                }
            }).then(res => {
                if (_.isEmpty(res.data)) this.showDangerNotification('Kode material tidak valid!', 'Sistem tidak dapat mengenali kode material yang diberikan, mohon periksa kembali')
                this.materialData.description = res.data.description ?? '-'
                this.materialData.uom = res.data.uom ?? '-'
            }).catch(() => {
                this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat mengambil data material, mohon coba lagi nanti')
            })
        },
        resolveBatchData() {
            axios.get(route('api.batch'), {
                params: {
                    q: escape(this.form.batch_code),
                    subarea: this.form.sub_area?.id ?? 0,
                    material: this.materialData.id ?? 0
                }
            }).then(res => {
                if (_.isEmpty(res.data)) this.showDangerNotification('Kode batch tidak valid!', 'Sistem tidak dapat mengenali kode batch yang diberikan, mohon periksa kembali')
                this.materialData.description = res.data.description ?? '-'
                this.materialData.uom = res.data.uom ?? '-'
            }).catch(() => {
                this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat mengambil data batch, mohon coba lagi nanti')
            })
        }
    }
})
</script>
