<template>
    <app-layout title="Aset">
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
            :meta="assets"
            ref="table"
        >
            <template #head>
                <jet-table-header
                    v-show="showColumn('techidentno')"
                    :cell="sortableHeader('techidentno')"
                >
                    Techidentno
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('name')"
                    :cell="sortableHeader('name')"
                >
                    Nama Aset
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('asset_type_name')"
                    :cell="sortableHeader('asset_type_name')"
                >
                    Tipe Aset
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('area_name')"
                    :cell="sortableHeader('area_name')"
                >
                    Area
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('area_type_name')"
                    :cell="sortableHeader('area_type_name')"
                >
                    Tipe Area
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('quantity')"
                    :cell="sortableHeader('quantity')"
                >
                    Kuantitas
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
                <tr v-for="asset in assets.data" :key="asset.id">
                    <td v-show="showColumn('techidentno')">
                        {{ asset.techidentno }}
                    </td>
                    <td v-show="showColumn('name')">{{ asset.name }}</td>
                    <td v-show="showColumn('asset_type_name')">
                        {{ asset.asset_type_name }}
                    </td>
                    <td v-show="showColumn('area_name')">
                        {{ asset.area_name }}
                    </td>
                    <td v-show="showColumn('area_type_name')">
                        {{ asset.area_type_name }}
                    </td>
                    <td v-show="showColumn('quantity')">
                        {{ asset.quantity }}
                    </td>
                    <td v-show="showColumn('user_name')">
                        {{ asset.user_name }}
                    </td>
                    <td v-show="showColumn('update_date')">
                        {{ asset.update_date }}
                    </td>
                    <td v-show="showColumn('action')" class="text-center">
                        <jet-dropdown name="Opsi">
                            <menu-item>
                                <button
                                    @click="confirmUpdate(asset)"
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
                                    @click="confirmDestroy(asset)"
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
            title="Tambah aset"
        >
            <template #content>
                Silakan masukkan data aset yang ingin ditambahkan.
                <jet-validation-errors class="mt-4" />
                <div class="mt-4">
                    <jet-input
                        type="text"
                        class="block w-full normal-case"
                        placeholder="Techidentno"
                        ref="storeTechidentno"
                        v-model="storeForm.techidentno"
                    />
                    <jet-input
                        type="text"
                        class="block w-full normal-case mt-4"
                        placeholder="Nama Aset"
                        ref="storeName"
                        v-model="storeForm.name"
                    />
                    <jet-input
                        type="number"
                        class="block w-full mt-4"
                        placeholder="Kuantitas"
                        ref="storeQuantity"
                        v-model="storeForm.quantity"
                    />
                    <jet-select
                        ref="storeType"
                        class="block w-full mt-4"
                        placeholder="Pilih Tipe Aset"
                        v-model="storeForm.type"
                        :data="asset_types"
                    />
                    <v-select
                        placeholder="Cari & Pilih Area"
                        class="vue-select rounded-md block w-full mt-4"
                        v-model="storeForm.area"
                        :filterable="false"
                        :clearable="false"
                        :options="areaOptions"
                        @search="onAreaSearch"
                        @keyup.enter="store"
                    >
                        <template slot="no-options">
                            Tidak ada hasil tersedia.
                        </template>
                        <template v-slot:no-options="{ search, searching }">
                            <template v-if="searching">
                                Tidak ada hasil untuk <em>{{ search }}</em
                                >.
                            </template>
                            <em v-else style="opacity: 0.5;"
                                >Mulai mengetik untuk mencari area.</em
                            >
                        </template>
                    </v-select>
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
            title="Edit aset"
        >
            <template #content>
                Silakan masukkan data aset yang ingin diubah.
                <jet-validation-errors class="mt-4" />
                <div class="mt-4">
                    <jet-input
                        type="text"
                        class="block w-full normal-case"
                        placeholder="Techidentno"
                        ref="updateTechidentno"
                        v-model="updateForm.techidentno"
                    />
                    <jet-input
                        type="text"
                        class="block w-full normal-case mt-4"
                        placeholder="Nama Aset"
                        ref="updateName"
                        v-model="updateForm.name"
                    />
                    <jet-input
                        type="number"
                        class="block w-full mt-4"
                        placeholder="Kuantitas"
                        ref="updateQuantity"
                        v-model="updateForm.quantity"
                    />
                    <jet-select
                        ref="updateType"
                        class="block w-full mt-4"
                        placeholder="Pilih Tipe Aset"
                        v-model="updateForm.type"
                        :data="asset_types"
                    />
                    <v-select
                        placeholder="Cari & Pilih Area"
                        class="vue-select rounded-md block w-full mt-4"
                        v-model="updateForm.area"
                        :filterable="false"
                        :clearable="false"
                        :options="areaOptions"
                        @search="onAreaSearch"
                        @keyup.enter="update"
                    >
                        <template slot="no-options">
                            Tidak ada hasil tersedia.
                        </template>
                        <template v-slot:no-options="{ search, searching }">
                            <template v-if="searching">
                                Tidak ada hasil untuk <em>{{ search }}</em
                                >.
                            </template>
                            <em v-else style="opacity: 0.5;"
                                >Mulai mengetik untuk mencari area.</em
                            >
                        </template>
                    </v-select>
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
            title="Hapus aset"
        >
            <template #content>
                Apakah Anda yakin ingin menghapus aset ini? Setelah tipe area
                dihapus, semua sumber daya dan datanya akan dihapus secara
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
            title="Impor data aset"
        >
            <template #content>
                <p>
                    Silakan unggah file data aset yang ingin di-impor. Pastikan
                    Anda sudah menggunakan template spreadsheet yang ditentukan.
                    Sistem hanya memproses data yang ada pada sheet
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
            title="Ekspor data aset"
        >
            <template #content>
                <p>
                    Apakah Anda yakin ingin mengekspor semua data aset? Proses
                    ekspor dapat memakan waktu lama, tergantung dari banyaknya
                    data yang tersedia.
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
import vSelect from "vue-select";

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
        vSelect,
    },
    props: {
        asset_types: Object,
        assets: Object,
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
                techidentno: null,
                type: null,
                area: null,
                name: null,
                quantity: null,
            }),
            updateForm: useForm({
                id: null,
                techidentno: null,
                type: null,
                area: null,
                name: null,
                quantity: null,
            }),
            destroyForm: useForm({
                id: null,
            }),
            importForm: useForm({
                file: null,
            }),
            areaOptions: [],
        };
    },
    methods: {
        store() {
            this.storeForm.post(route("assets.store"), {
                preserveScroll: true,
                onSuccess: () => {
                    this.reloadData();
                    this.closeStoreModal();
                    this.showSuccessNotification(
                        "Area berhasil ditambahkan",
                        "Sistem telah berhasil menyimpan data aset baru"
                    );
                },
                onError: () =>
                    this.showDangerNotification(
                        "Kesalahan telah terjadi",
                        "Sistem tidak dapat menyimpan data aset, mohon periksa ulang form"
                    ),
            });
        },
        update() {
            this.updateForm.put(route("assets.update", this.updateForm.id), {
                preserveScroll: true,
                onSuccess: () => {
                    this.reloadData();
                    this.closeUpdateModal();
                    this.showSuccessNotification(
                        "Area berhasil diedit",
                        "Sistem telah berhasil mengedit data aset"
                    );
                },
                onError: () =>
                    this.showDangerNotification(
                        "Kesalahan telah terjadi",
                        "Sistem tidak dapat mengubah data aset, mohon periksa ulang form"
                    ),
            });
        },
        destroy() {
            this.destroyForm.delete(
                route("assets.destroy", this.destroyForm.id),
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        this.reloadData();
                        this.closeDestroyModal();
                        this.showSuccessNotification(
                            "Area berhasil dihapus",
                            "Sistem telah berhasil menghapus data aset"
                        );
                    },
                    onError: () =>
                        this.showDangerNotification(
                            "Kesalahan telah terjadi",
                            "Sistem tidak dapat menghapus data aset"
                        ),
                }
            );
        },
        importFile() {
            this.importForm.post(route("assets.import"), {
                preserveScroll: true,
                onSuccess: () => {
                    this.reloadData();
                    this.closeImportModal();
                    this.showSuccessNotification(
                        "Permintaan impor data telah dijadwalkan",
                        "Sistem berhasil menjadwalkan permintaan impor data aset di latar belakang"
                    );
                },
                onError: () =>
                    this.showDangerNotification(
                        "Kesalahan telah terjadi",
                        "Sistem tidak dapat mengimpor data aset, mohon periksa kesalahan yang telah dideteksi"
                    ),
            });
        },
        exportFile() {
            window.open(route("assets.export"));
            this.closeExportModal();
        },
        confirmStore() {
            setTimeout(() => (this.confirmingStore = true), 150);
            setTimeout(() => this.$refs.storeTechidenno.focus(), 300);
        },
        confirmUpdate(asset) {
            this.updateForm.id = asset.id;
            this.updateForm.techidentno = asset.techidentno;
            this.updateForm.name = asset.name;
            this.updateForm.quantity = asset.quantity;
            this.updateForm.type = asset.asset_type_id;
            this.updateForm.area = {
                id: asset.area_id,
                label: `${asset.area_code} - ${asset.area_name} (${asset.area_type_name})`,
            };
            setTimeout(() => (this.confirmingUpdate = true), 150);
            setTimeout(() => this.$refs.updateTechidentno.focus(), 300);
        },
        confirmDestroy(asset) {
            this.destroyForm.id = asset.id;
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
                this.storeForm.techidentno = null;
                this.storeForm.name = null;
                this.storeForm.quantity = null;
                this.storeForm.type = null;
                this.storeForm.area = null;
            }, 500);
        },
        closeUpdateModal() {
            this.confirmingUpdate = false;
            setTimeout(() => {
                this.clearErrors();
                this.updateForm.clearErrors();
                this.updateForm.reset();
                this.updateForm.id = null;
                this.updateForm.techidentno = null;
                this.updateForm.name = null;
                this.updateForm.quantity = null;
                this.updateForm.type = null;
                this.updateForm.area = null;
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
            this.$refs.table.reload("assets");
        },
        onAreaSearch(search, loading) {
            if (search.length) {
                loading(true);
                this.areaSearch(loading, this, this.showDangerNotification, {
                    q: escape(search),
                });
            }
        },
        areaSearch: _.debounce((loading, vm, errorCallback, params) => {
            axios
                .get(route("api.areas"), {
                    params: params,
                })
                .then((res) => {
                    vm.areaOptions = res.data.items.map((item) => {
                        return {
                            id: item.id,
                            label: `${item.funcloc} - ${item.name} (${item.area_type})`,
                        };
                    });
                })
                .catch(() => {
                    errorCallback(
                        "Kesalahan telah terjadi",
                        "Sistem tidak dapat mengambil data area, mohon coba lagi nanti"
                    );
                })
                .finally(() => {
                    if (loading) loading(false);
                });
        }, 1000),
    },
});
</script>
