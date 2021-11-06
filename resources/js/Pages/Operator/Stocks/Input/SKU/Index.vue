<template>
    <app-layout title="Entry Stock">
        <tabs class="mb-6 lg:mb-8 lg:-mt-3"/>
        <jet-form-section @submitted="store">
            <template #title>
                Input SKU Code
            </template>
            <template #description>
                Tambah actual stock baru menggunakan SKU Code.
            </template>
            <template #form ref="form">
                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="area" value="Area"/>
                    <jet-select id="area" placeholder="Pilih Area" v-model="form.area"
                                :data="areas" class="mt-2 block w-full normal-case text-base lg:text-lg"/>
                    <jet-input-error :message="form.errors.area" class="mt-2"/>
                </div>
                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="period" value="Periode"/>
                    <jet-select id="period" placeholder="Pilih Periode" v-model="form.period"
                                :data="periods" class="mt-2 block w-full normal-case text-base lg:text-lg"/>
                    <jet-input-error :message="form.errors.period" class="mt-2"/>
                </div>
                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="product_code" value="Kode SKU"/>
                    <div class="flex mt-2">
                        <div class="w-full">
                            <v-select
                                id="product_code"
                                label="code"
                                placeholder="Masukkan Kode SKU"
                                class="vue-select rounded-md"
                                v-model="form.product_code"
                                v-on:option:selected="onSelected"
                                :filterable="false"
                                :clearable="false"
                                :options="options"
                                :reduce="option => option.code"
                                @search="onSearch">
                                <template slot="no-options">
                                    Tidak ada hasil tersedia.
                                </template>
                                <template v-slot:no-options="{ search, searching }">
                                    <template v-if="searching">
                                        Tidak ada hasil untuk <em>{{ search }}</em>.
                                    </template>
                                    <em v-else style="opacity: 0.5">Mulai mengetik untuk mencari kode SKU.</em>
                                </template>
                                <template slot="option" slot-scope="option">
                                    <div class="d-center">
                                        {{ option.code }}
                                    </div>
                                </template>
                                <template slot="selected-option" slot-scope="option">
                                    <div class="selected d-center">
                                        {{ option.code }}
                                    </div>
                                </template>
                            </v-select>
                        </div>
                        <div class="ml-2">
                            <jet-secondary-button type="button" @click="confirmScanProductBarcode" class="h-full">
                                <qrcode-icon class="h-5 w-5 text-gray-800" aria-hidden="true"/>
                            </jet-secondary-button>
                        </div>
                    </div>
                    <jet-input-error :message="form.errors.product_code" class="mt-2"/>
                </div>
                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="product_description" value="Deskripsi Produk"/>
                    <jet-input id="product_description" type="text"
                               class="mt-2 block w-full bg-gray-100 cursor-not-allowed capitalize"
                               v-model="productData.description" disabled
                               title="Data ini diambil berdasarkan Kode SKU yang diberikan."/>
                </div>
                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="batch_code" value="Kode Batch"/>
                    <div class="flex mt-2">
                        <div class="w-full">
                            <jet-input id="batch_code" type="text" class="block w-full uppercase"
                                       v-model="form.batch_code" placeholder="Masukkan Kode Batch"
                                       autocomplete="batch_code"/>
                        </div>
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
                               v-model="productData.uom" disabled
                               title="Data ini diambil berdasarkan Kode SKU yang diberikan."/>
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
            title="Scan SKU Barcode"
            :show="confirmingProductBarcodeScanning"
            @close="closeProductBarcodeScanner"
            @decode="onProductBarcodeDecoded"/>
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
            confirmingProductBarcodeScanning: false,
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
            productData: {
                description: '-',
                uom: '-'
            },
            form: useForm({
                sku: true,
                area: null,
                period: null,
                product_code: null,
                batch_code: null,
                quantity: null
            }),
            options: []
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
            this.form.product_code = null
            this.form.batch_code = null
            this.form.quantity = null
            this.productData.description = '-'
            this.productData.uom = '-'
        },
        confirmScanProductBarcode() {
            this.confirmingProductBarcodeScanning = true
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
        closeProductBarcodeScanner() {
            this.confirmingProductBarcodeScanning = false
        },
        closeBatchBarcodeScanner() {
            this.confirmingBatchBarcodeScanning = false
        },
        onProductBarcodeDecoded(code) {
            this.form.product_code = code
            this.resolveProductData()
        },
        onBatchBarcodeDecoded(code) {
            this.form.batch_code = code
        },
        onSelected(product) {
            this.productData.description = product.description
            this.productData.uom = product.uom
        },
        onSearch(search, loading) {
            if (search.length) {
                loading(true)
                this.search(loading, search, this, this.showDangerNotification, {
                    q: escape(search),
                    area: this.form.area ?? 0,
                    period: this.form.period ?? 0
                })
            }
        },
        search: _.debounce((loading, search, vm, errorCallback, params) => {
            axios.get(route('api.products.codes'), {
                params: params
            }).then(res => {
                vm.options = res.data.items
            }).catch(() => {
                errorCallback('Kesalahan telah terjadi', 'Sistem tidak dapat mengambil data kode SKU, mohon coba lagi nanti')
            }).finally(() => {
                loading(false)
            })
        }, 300),
        resolveProductData() {
            axios.get(route('api.products.code'), {
                params: {
                    q: escape(this.form.product_code),
                    area: this.form.area ?? 0,
                    period: this.form.period ?? 0
                }
            }).then(res => {
                if (_.isEmpty(res.data)) this.showDangerNotification('Kode SKU tidak valid!', 'Sistem tidak dapat mengenali kode SKU yang diberikan, mohon periksa kembali')
                this.productData.description = res.data.description ?? '-'
                this.productData.uom = res.data.uom ?? '-'
            }).catch(() => {
                this.showDangerNotification('Kesalahan telah terjadi', 'Sistem tidak dapat mengambil data SKU, mohon coba lagi nanti')
            })
        }
    }
})
</script>