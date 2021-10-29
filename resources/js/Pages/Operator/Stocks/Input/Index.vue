<template>
    <app-layout title="Entry">
        <jet-form-section @submitted="updateProfileInformation">
            <template #title>
                Entry Stock
            </template>
            <template #description>
                Tambah actual stock baru menggunakan Material Code.
            </template>
            <template #form ref="form">
                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="material_code" value="Kode Material"/>
                    <jet-input id="material_code" type="text" class="mt-1 block w-full uppercase"
                               v-model="this.form.material_code"
                               autocomplete="material_code" required/>
                    <jet-input-error :message="form.errors.material_code" class="mt-2"/>
                </div>
                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="batch_code" value="Kode Batch"/>
                    <jet-input id="batch_code" type="text" class="mt-1 block w-full uppercase"
                               v-model="this.form.batch_code"
                               autocomplete="batch_code" required/>
                    <jet-input-error :message="form.errors.batch_code" class="mt-2"/>
                </div>
                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="quantity" value="Kuantitas"/>
                    <jet-input id="quantity" type="number" class="mt-1 block w-full" v-model="this.form.quantity"
                               autocomplete="quantity" required/>
                    <jet-input-error :message="form.errors.quantity" class="mt-2"/>
                </div>
                <div class="col-span-6 sm:col-span-4">
                    <jet-label for="uom" value="UoM"/>
                    <jet-input id="uom" type="text" class="mt-1 block w-full uppercase" v-model="this.form.uom"
                               autocomplete="uom" required/>
                    <jet-input-error :message="form.errors.uom" class="mt-2"/>
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

export default defineComponent({
    components: {
        AppLayout,
        JetActionMessage,
        JetButton,
        JetFormSection,
        JetInput,
        JetInputError,
        JetLabel
    },
    props: {
        areas: Object,
        periods: Object
    },
    data() {
        return {
            form: useForm({
                _method: 'PUT',
                material_code: null,
                batch_code: null,
                quantity: null,
                uom: null
            })
        }
    },
    methods: {
        clearForm() {
            this.form.clearErrors()
            this.form.reset()
            this.form.material_code = null
            this.form.batch_code = null
            this.form.quantity = null
            this.form.uom = null
        }
    }
})
</script>
