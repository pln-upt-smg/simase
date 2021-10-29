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
                                :data="areas" class="mt-1 block w-full normal-case text-base lg:text-lg"/>
                    <jet-input-error :message="form.errors.area" class="mt-2"/>
                </div>
                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="period" value="Periode"/>
                    <jet-select id="period" placeholder="Pilih Periode" v-model="form.period"
                                :data="periods" class="mt-1 block w-full normal-case text-base lg:text-lg"/>
                    <jet-input-error :message="form.errors.period" class="mt-2"/>
                </div>
                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="material_code" value="Kode Material"/>
                    <jet-input id="material_code" type="text" class="mt-1 block w-full uppercase"
                               v-model="form.material_code"
                               autocomplete="material_code"/>
                    <jet-input-error :message="form.errors.material_code" class="mt-2"/>
                </div>
                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="batch_code" value="Kode Batch"/>
                    <jet-input id="batch_code" type="text" class="mt-1 block w-full uppercase"
                               v-model="form.batch_code"
                               autocomplete="batch_code"/>
                    <jet-input-error :message="form.errors.batch_code" class="mt-2"/>
                </div>
                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="quantity" value="Kuantitas"/>
                    <jet-input id="quantity" type="number" class="mt-1 block w-full" v-model="form.quantity"
                               autocomplete="quantity"/>
                    <jet-input-error :message="form.errors.quantity" class="mt-2"/>
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
        JetDangerNotification
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
            form: useForm({
                area: null,
                period: null,
                material_code: null,
                batch_code: null,
                product_code: null,
                quantity: null
            })
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
        }
    }
})
</script>
