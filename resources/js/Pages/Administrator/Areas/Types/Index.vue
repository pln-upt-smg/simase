<template>
    <app-layout title="Tipe Area">
        <div class="mb-6 lg:text-right">
            <jet-button
                type="button"
                @click="confirmStore"
                class="mb-2 mr-2 lg:mb-0"
            >
                <plus-icon class="w-5 h-5 mr-2 text-white" aria-hidden="true" />
                Tambah
            </jet-button>
            <jet-button
                type="button"
                @click="confirmImport"
                class="mb-2 mr-2 lg:mb-0"
            >
                <upload-icon
                    class="w-5 h-5 mr-2 text-white"
                    aria-hidden="true"
                />
                Impor
            </jet-button>
            <jet-button type="button" @click="confirmExport">
                <download-icon
                    class="w-5 h-5 mr-2 text-white"
                    aria-hidden="true"
                />
                Ekspor
            </jet-button>
        </div>
        <jet-table
            :search="queryBuilderProps.search"
            :on-update="setQueryBuilder"
            :meta="area_types"
            ref="table"
        >
            <template #head>
                <jet-table-header
                    v-show="showColumn('name')"
                    :cell="sortableHeader('name')"
                >
                    Nama Tipe Area
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('user_name')"
                    :cell="sortableHeader('user_name')"
                >
                    Pembuat
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('update_date')"
                    :cell="sortableHeader('update_date')"
                >
                    Tanggal Pembaruan
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('action')"
                    :cell="staticHeader('action')"
                />
            </template>
            <template #body>
                <tr v-for="area_type in area_types.data" :key="area_type.id">
                    <td v-show="showColumn('name')">{{ area_type.name }}</td>
                    <td v-show="showColumn('user_name')">
                        {{ area_type.user_name }}
                    </td>
                    <td v-show="showColumn('update_date')">
                        {{ area_type.update_date }}
                    </td>
                    <td v-show="showColumn('action')" class="text-center">
                        <jet-dropdown name="Opsi">
                            <menu-item>
                                <button
                                    @click="confirmUpdate(area_type)"
                                    class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 group"
                                >
                                    <pencil-alt-icon
                                        class="w-5 h-5 mr-3 text-gray-700"
                                        aria-hidden="true"
                                    />
                                    Edit
                                </button>
                            </menu-item>
                            <menu-item>
                                <button
                                    @click="confirmDestroy(area_type)"
                                    class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 group"
                                >
                                    <trash-icon
                                        class="w-5 h-5 mr-3 text-gray-700"
                                        aria-hidden="true"
                                    />
                                    Hapus
                                </button>
                            </menu-item>
                        </jet-dropdown>
                    </td>
                </tr>
            </template>
        </jet-table>
        <jet-modal
            :show="confirmingStore"
            @close="closeStoreModal"
            title="Tambah tipe area"
        >
            <template #content>
                Silakan masukkan data tipe area yang ingin ditambahkan.
                <jet-validation-errors class="mt-4" />
                <div class="mt-4">
                    <jet-input
                        type="text"
                        class="block w-full normal-case"
                        placeholder="Nama Tipe Area"
                        ref="storeName"
                        v-model="storeForm.name"
                        @keyup.enter="store"
                    />
                </div>
            </template>
            <template #buttons>
                <jet-button
                    type="button"
                    @click="store"
                    :class="{ 'opacity-25': storeForm.processing }"
                    :disabled="storeForm.processing"
                    class="inline-flex justify-center w-full px-4 py-2 mt-2 sm:ml-3 sm:w-auto"
                >
                    Simpan
                </jet-button>
                <jet-secondary-button
                    @click="closeStoreModal"
                    class="inline-flex justify-center w-full px-4 py-2 mt-2 sm:ml-3 sm:w-auto"
                >
                    Batalkan
                </jet-secondary-button>
            </template>
        </jet-modal>
        <jet-modal
            :show="confirmingUpdate"
            @close="closeUpdateModal"
            title="Edit tipe area"
        >
            <template #content>
                Silakan masukkan data tipe area yang ingin diubah.
                <jet-validation-errors class="mt-4" />
                <div class="mt-4">
                    <jet-input
                        type="text"
                        class="block w-full normal-case"
                        placeholder="Nama Tipe Area"
                        ref="updateName"
                        v-model="updateForm.name"
                        @keyup.enter="update"
                    />
                </div>
            </template>
            <template #buttons>
                <jet-button
                    type="button"
                    @click="update"
                    :class="{ 'opacity-25': updateForm.processing }"
                    :disabled="updateForm.processing"
                    class="inline-flex justify-center w-full px-4 py-2 mt-2 sm:ml-3 sm:w-auto"
                >
                    Simpan
                </jet-button>
                <jet-secondary-button
                    @click="closeUpdateModal"
                    class="inline-flex justify-center w-full px-4 py-2 mt-2 sm:ml-3 sm:w-auto"
                >
                    Batalkan
                </jet-secondary-button>
            </template>
        </jet-modal>
        <jet-alert-modal
            :show="confirmingDestroy"
            @close="closeDestroyModal"
            title="Hapus tipe area"
        >
            <template #content>
                Apakah Anda yakin ingin menghapus tipe area ini? Setelah tipe
                area dihapus, semua sumber daya dan datanya akan dihapus secara
                permanen. Aksi ini tidak dapat dibatalkan.
            </template>
            <template #buttons>
                <jet-danger-button
                    @click="destroy"
                    :class="{ 'opacity-25': destroyForm.processing }"
                    :disabled="destroyForm.processing"
                    class="inline-flex justify-center w-full px-4 py-2 mt-2 sm:ml-3 sm:w-auto"
                >
                    Hapus
                </jet-danger-button>
                <jet-secondary-button
                    @click="closeDestroyModal"
                    class="inline-flex justify-center w-full px-4 py-2 mt-2 sm:ml-3 sm:w-auto"
                >
                    Batalkan
                </jet-secondary-button>
            </template>
        </jet-alert-modal>
        <jet-import-modal
            :show="confirmingImport"
            @close="closeImportModal"
            title="Impor data tipe area"
        >
            <template #content>
                <p>
                    Silakan unggah file data tipe area yang ingin di-impor.
                    Pastikan Anda sudah menggunakan template spreadsheet yang
                    ditentukan. Sistem hanya memproses data yang ada pada sheet
                    <b>Worksheet</b>.
                </p>
                <p class="mt-2">
                    Mengimpor data baru dapat memperbarui data lama yang sudah
                    tersedia. Aksi ini tidak dapat dibatalkan.
                </p>
                <jet-validation-errors class="mt-4" />
                <div
                    @click="this.$refs.importInput.click()"
                    class="flex justify-center px-6 pt-5 pb-6 mt-4 border-2 border-gray-300 border-dashed rounded-md cursor-pointer"
                >
                    <div class="space-y-1 text-center">
                        <document-add-icon
                            class="w-12 h-12 mx-auto text-gray-400"
                            aria-hidden="true"
                        />
                        <div class="flex justify-center text-sm text-gray-600">
                            <label
                                for="import-file"
                                class="relative font-medium text-amber-600 bg-white rounded-md cursor-pointer hover:text-amber-500"
                            >
                                <span>
                                    {{
                                        importForm.file === null
                                            ? "Unggah file dokumen"
                                            : importForm.file.name
                                    }}
                                </span>
                                <input
                                    for="import-file"
                                    ref="importInput"
                                    type="file"
                                    class="sr-only"
                                    accept=".xlsx, .csv"
                                    @input="
                                        importForm.file = $event.target.files[0]
                                    "
                                />
                            </label>
                        </div>
                        <p class="text-xs text-gray-500">
                            XLSX, CSV hingga 50MB
                        </p>
                    </div>
                </div>
            </template>
            <template #buttons>
                <jet-button
                    type="button"
                    @click="importFile"
                    :class="{ 'opacity-25': importForm.processing }"
                    :disabled="importForm.processing"
                    class="inline-flex justify-center w-full px-4 py-2 mt-2 sm:ml-3 sm:w-auto"
                >
                    Impor
                </jet-button>
                <jet-secondary-button
                    @click="openImportTemplate"
                    class="inline-flex justify-center w-full px-4 py-2 mt-2 sm:ml-3 sm:w-auto"
                >
                    Unduh Template
                </jet-secondary-button>
                <jet-secondary-button
                    @click="closeImportModal"
                    class="inline-flex justify-center w-full px-4 py-2 mt-2 sm:ml-3 sm:w-auto"
                >
                    Batalkan
                </jet-secondary-button>
            </template>
        </jet-import-modal>
        <jet-export-modal
            :show="confirmingExport"
            @close="closeExportModal"
            title="Ekspor data tipe area"
        >
            <template #content>
                <p>
                    Apakah Anda yakin ingin mengekspor semua data tipe area?
                    Proses ekspor dapat memakan waktu lama, tergantung dari
                    banyaknya data yang tersedia.
                </p>
                <p class="mt-2">
                    Sistem akan mengekspor data berupa file spreadsheet dengan
                    format <b>XLSX</b>.
                </p>
            </template>
            <template #buttons>
                <jet-button
                    type="button"
                    @click="exportFile"
                    class="inline-flex justify-center w-full px-4 py-2 mt-2 sm:ml-3 sm:w-auto"
                >
                    Ekspor
                </jet-button>
                <jet-secondary-button
                    @click="closeExportModal"
                    class="inline-flex justify-center w-full px-4 py-2 mt-2 sm:ml-3 sm:w-auto"
                >
                    Batalkan
                </jet-secondary-button>
            </template>
        </jet-export-modal>
        <jet-success-notification
            :show="showingSuccessNotification"
            :title="successNotification.title"
            :description="successNotification.description"
            @close="closeSuccessNotification"
        />
        <jet-danger-notification
            :show="showingDangerNotification"
            :title="dangerNotification.title"
            :description="dangerNotification.description"
            @close="closeDangerNotification"
        />
    </app-layout>
</template>

<script>
import { defineComponent } from "vue";
import { Link as JetLink, useForm } from "@inertiajs/inertia-vue3";
import { MenuItem } from "@headlessui/vue";
import {
    DocumentAddIcon,
    DownloadIcon,
    PencilAltIcon,
    PlusIcon,
    TrashIcon,
    UploadIcon,
} from "@heroicons/vue/outline";
import AppLayout from "@/Layouts/AppLayout";
import JetButton from "@/Jetstream/Button";
import JetDangerButton from "@/Jetstream/DangerButton";
import JetSecondaryButton from "@/Jetstream/SecondaryButton";
import JetDropdown from "@/Jetstream/Dropdown";
import JetModal from "@/Jetstream/Modal";
import JetAlertModal from "@/Jetstream/AlertModal";
import JetImportModal from "@/Jetstream/ImportModal";
import JetExportModal from "@/Jetstream/ExportModal";
import JetInput from "@/Jetstream/Input";
import JetSuccessNotification from "@/Jetstream/SuccessNotification";
import JetDangerNotification from "@/Jetstream/DangerNotification";
import JetValidationErrors from "@/Jetstream/ValidationErrors";
import JetTable from "@/Jetstream/Table";
import JetTableEngine from "@/Jetstream/TableEngine";
import JetTableHeader from "@/Jetstream/TableHeader";
import JetSelect from "@/Jetstream/Select";

export default defineComponent({
    mixins: [JetTableEngine],
    components: {
        AppLayout,
        JetTable,
        JetTableHeader,
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
        JetSelect,
        JetLink,
        MenuItem,
        PlusIcon,
        UploadIcon,
        DownloadIcon,
        PencilAltIcon,
        TrashIcon,
        DocumentAddIcon,
    },
    props: {
        area_types: Object,
    },
    data() {
        return {
            successNotification: {
                title: null,
                description: null,
            },
            dangerNotification: {
                title: null,
                description: null,
            },
            confirmingStore: false,
            confirmingUpdate: false,
            confirmingDestroy: false,
            confirmingImport: false,
            confirmingExport: false,
            showingSuccessNotification: false,
            showingDangerNotification: false,
            storeForm: useForm({
                code: null,
                name: null,
                latitude: null,
                longitude: null,
                type: null,
            }),
            updateForm: useForm({
                id: null,
                code: null,
                name: null,
                latitude: null,
                longitude: null,
                type: null,
            }),
            destroyForm: useForm({
                id: null,
            }),
            importForm: useForm({
                file: null,
            }),
        };
    },
    methods: {
        store() {
            this.storeForm.post(route("areas.types.store"), {
                preserveScroll: true,
                onSuccess: () => {
                    this.reloadData();
                    this.closeStoreModal();
                    this.showSuccessNotification(
                        "Area berhasil ditambahkan",
                        "Sistem telah berhasil menyimpan data tipe area baru"
                    );
                },
                onError: () =>
                    this.showDangerNotification(
                        "Kesalahan telah terjadi",
                        "Sistem tidak dapat menyimpan data tipe area, mohon periksa ulang form"
                    ),
            });
        },
        update() {
            this.updateForm.put(
                route("areas.types.update", this.updateForm.id),
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        this.reloadData();
                        this.closeUpdateModal();
                        this.showSuccessNotification(
                            "Area berhasil diedit",
                            "Sistem telah berhasil mengedit data tipe area"
                        );
                    },
                    onError: () =>
                        this.showDangerNotification(
                            "Kesalahan telah terjadi",
                            "Sistem tidak dapat mengubah data tipe area, mohon periksa ulang form"
                        ),
                }
            );
        },
        destroy() {
            this.destroyForm.delete(
                route("areas.types.destroy", this.destroyForm.id),
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        this.reloadData();
                        this.closeDestroyModal();
                        this.showSuccessNotification(
                            "Area berhasil dihapus",
                            "Sistem telah berhasil menghapus data tipe area"
                        );
                    },
                    onError: () =>
                        this.showDangerNotification(
                            "Kesalahan telah terjadi",
                            "Sistem tidak dapat menghapus data tipe area"
                        ),
                }
            );
        },
        importFile() {
            this.importForm.post(route("areas.types.import"), {
                preserveScroll: true,
                onSuccess: () => {
                    this.reloadData();
                    this.closeImportModal();
                    this.showSuccessNotification(
                        "Permintaan impor data telah dijadwalkan",
                        "Sistem berhasil menjadwalkan permintaan impor data tipe area di latar belakang"
                    );
                },
                onError: () =>
                    this.showDangerNotification(
                        "Kesalahan telah terjadi",
                        "Sistem tidak dapat mengimpor data tipe area, mohon periksa kesalahan yang telah dideteksi"
                    ),
            });
        },
        exportFile() {
            window.open(route("areas.types.export"));
            this.closeExportModal();
        },
        confirmStore() {
            setTimeout(() => (this.confirmingStore = true), 150);
            setTimeout(() => this.$refs.storeName.focus(), 300);
        },
        confirmUpdate(area_type) {
            this.updateForm.id = area_type.id;
            this.updateForm.name = area_type.name;
            setTimeout(() => (this.confirmingUpdate = true), 150);
            setTimeout(() => this.$refs.updateName.focus(), 300);
        },
        confirmDestroy(area_type) {
            this.destroyForm.id = area_type.id;
            setTimeout(() => (this.confirmingDestroy = true), 150);
        },
        confirmImport() {
            setTimeout(() => (this.confirmingImport = true), 150);
        },
        confirmExport() {
            setTimeout(() => (this.confirmingExport = true), 150);
        },
        closeStoreModal() {
            this.confirmingStore = false;
            setTimeout(() => {
                this.clearErrors();
                this.storeForm.clearErrors();
                this.storeForm.reset();
                this.storeForm.name = null;
            }, 500);
        },
        closeUpdateModal() {
            this.confirmingUpdate = false;
            setTimeout(() => {
                this.clearErrors();
                this.updateForm.clearErrors();
                this.updateForm.reset();
                this.updateForm.id = null;
                this.updateForm.name = null;
            }, 500);
        },
        closeDestroyModal() {
            this.confirmingDestroy = false;
            setTimeout(() => {
                this.clearErrors();
                this.destroyForm.clearErrors();
                this.destroyForm.reset();
                this.destroyForm.id = null;
            }, 500);
        },
        closeImportModal() {
            this.confirmingImport = false;
            setTimeout(() => {
                this.clearErrors();
                this.importForm.clearErrors();
                this.importForm.reset();
                this.importForm.file = null;
            }, 500);
        },
        closeExportModal() {
            this.confirmingExport = false;
        },
        showSuccessNotification(title, description) {
            this.successNotification.title = title;
            this.successNotification.description = description;
            this.showingSuccessNotification = true;
            setTimeout(
                () =>
                    this.showingSuccessNotification
                        ? this.closeSuccessNotification()
                        : null,
                5000
            );
        },
        showDangerNotification(title, description) {
            this.dangerNotification.title = title;
            this.dangerNotification.description = description;
            this.showingDangerNotification = true;
            setTimeout(
                () =>
                    this.showingDangerNotification
                        ? this.closeDangerNotification()
                        : null,
                5000
            );
        },
        closeSuccessNotification() {
            this.showingSuccessNotification = false;
        },
        closeDangerNotification() {
            this.showingDangerNotification = false;
        },
        openImportTemplate() {
            if (this.$page.props.template)
                window.open(this.$page.props.template);
            else
                this.showDangerNotification(
                    "Kesalahan telah terjadi",
                    "Sistem tidak dapat membaca template, mohon coba lagi nanti"
                );
        },
        clearErrors() {
            this.$page.props.errors = [];
        },
        reloadData() {
            this.$refs.table.reload("area_types");
        },
    },
});
</script>
