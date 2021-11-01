<style scoped>
</style>

<template>
    <app-layout title="Entry Stock">
        <jet-form-section @submitted="store">
            <template #title>
                Input Material Code
            </template>
            <template #description>
                Tambah actual stock baru menggunakan Material Code.
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
                    <jet-label for="material_code" value="Kode Material"/>
                    <v-select
                        id="material_code"
                        label="code"
                        placeholder="Masukkan Kode Material"
                        class="vue-select mt-2"
                        v-model="form.material_code"
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
                            <em v-else style="opacity: 0.5">Mulai mengetik untuk mencari kode material.</em>
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
                    <jet-input id="batch_code" type="text" class="mt-2 block w-full uppercase"
                               v-model="form.batch_code" placeholder="Masukkan Kode Batch"
                               autocomplete="batch_code"/>
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
import JetButton from '@/Jetstream/Button'
import JetFormSection from '@/Jetstream/FormSection'
import JetInput from '@/Jetstream/Input'
import JetInputError from '@/Jetstream/InputError'
import JetLabel from '@/Jetstream/Label'
import JetActionMessage from '@/Jetstream/ActionMessage'
import JetSelect from '@/Jetstream/Select'
import JetSuccessNotification from '@/Jetstream/SuccessNotification'
import JetDangerNotification from '@/Jetstream/DangerNotification'
import vSelect from 'vue-select'

export default defineComponent({
    components: {
        AppLayout,
        JetActionMessage,
        JetButton,
        JetFormSection,
        JetInput,
        JetInputError,
        JetLabel,
        JetSelect,
        JetSuccessNotification,
        JetDangerNotification,
        vSelect
    },
    props: {
        areas: Object,
        periods: Object
    },
    data() {
        return {
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
                description: '-',
                uom: '-'
            },
            form: useForm({
                area: null,
                period: null,
                material_code: null,
                batch_code: null,
                product_code: null,
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
            this.form.material_code = null
            this.form.batch_code = null
            this.form.product_code = null
            this.form.quantity = null
            this.materialData.description = '-'
            this.materialData.uom = '-'
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
        onBarcodeScanned(code) {

        },
        onSelected(material) {
            this.materialData.description = material.description
            this.materialData.uom = material.uom
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
            axios.get(route('api.materials.codes'), {
                params: params
            }).then(res => {
                vm.options = res.data.items
            }).catch(err => {
                console.error(err)
                errorCallback('Kesalahan telah terjadi', 'Sistem tidak dapat mengambil data kode material, mohon coba lagi nanti')
            }).finally(() => {
                loading(false)
            })
        }, 300)
    }
})
</script>
