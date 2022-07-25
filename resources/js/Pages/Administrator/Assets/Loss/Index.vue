<template>
    <app-layout title="Laporan Kehilangan Aset">
        <div class="mb-6 lg:text-right">
            <jet-button
                type="button"
                @click="confirmStore"
                class="mb-2 mr-2 lg:mb-0"
            >
                <plus-icon class="w-5 h-5 mr-2 text-white" aria-hidden="true" />
                Tambah
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
            :meta="asset_loss_damages"
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
                    v-show="showColumn('area_funcloc')"
                    :cell="sortableHeader('area_funcloc')"
                >
                    Funcloc
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
                    v-show="showColumn('asset_loss_damage_priority')"
                    :cell="sortableHeader('asset_loss_damage_priority')"
                >
                    Prioritas
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('user_name')"
                    :cell="sortableHeader('user_name')"
                >
                    Pelapor
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
                <tr
                    v-for="asset_loss in asset_loss_damages.data"
                    :key="asset_loss.id"
                >
                    <td v-show="showColumn('techidentno')">
                        {{ asset_loss.techidentno }}
                    </td>
                    <td v-show="showColumn('name')">
                        {{ asset_loss.name }}
                    </td>
                    <td v-show="showColumn('asset_type_name')">
                        {{ asset_loss.asset_type_name }}
                    </td>
                    <td v-show="showColumn('area_funcloc')">
                        {{ asset_loss.area_funcloc }}
                    </td>
                    <td v-show="showColumn('area_name')">
                        {{ asset_loss.area_name }}
                    </td>
                    <td v-show="showColumn('area_type_name')">
                        {{ asset_loss.area_type_name }}
                    </td>
                    <td v-show="showColumn('asset_loss_damage_priority')">
                        <span
                            class="text-green-600 font-bold"
                            v-if="asset_loss.asset_loss_damage_priority === 1"
                        >
                            Rendah
                        </span>
                        <span
                            class="text-yellow-600 font-bold"
                            v-else-if="
                                asset_loss.asset_loss_damage_priority === 2
                            "
                        >
                            Sedang
                        </span>
                        <span class="text-red-600 font-bold" v-else>
                            Tinggi
                        </span>
                    </td>
                    <td v-show="showColumn('user_name')">
                        {{ asset_loss.user_name }}
                    </td>
                    <td v-show="showColumn('update_date')">
                        {{ asset_loss.update_date }}
                    </td>
                    <td v-show="showColumn('action')" class="text-center">
                        <jet-dropdown name="Opsi">
                            <menu-item>
                                <button
                                    @click="confirmUpdate(asset_loss)"
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
                                    @click="confirmDestroy(asset_loss)"
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
            title="Tambah laporan kehilangan aset"
        >
            <template #content>
                Silakan masukkan data laporan kehilangan aset yang ingin
                ditambahkan.
                <jet-validation-errors class="mt-4" />
                <div class="mt-4">
                    <v-select
                        placeholder="Cari & Pilih Aset"
                        class="vue-select rounded-md block w-full"
                        v-model="storeForm.asset"
                        :filterable="false"
                        :clearable="false"
                        :options="assetOptions"
                        @search="onAssetSearch"
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
                                >Mulai mengetik untuk mencari aset.</em
                            >
                        </template>
                    </v-select>
                    <jet-select
                        ref="storePriority"
                        class="block w-full mt-4"
                        placeholder="Pilih Prioritas"
                        v-model="storeForm.priority"
                        :data="priorities"
                    />
                    <jet-input
                        type="text"
                        class="block w-full mt-4 normal-case"
                        placeholder="Keterangan (Opsional)"
                        ref="storeNote"
                        v-model="storeForm.note"
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
            title="Edit laporan kehilangan aset"
        >
            <template #content>
                Silakan masukkan data laporan kehilangan aset yang ingin diubah.
                <jet-validation-errors class="mt-4" />
                <div class="mt-4">
                    <v-select
                        placeholder="Cari & Pilih Aset"
                        class="vue-select rounded-md block w-full"
                        v-model="updateForm.asset"
                        :filterable="false"
                        :clearable="false"
                        :options="assetOptions"
                        @search="onAssetSearch"
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
                                >Mulai mengetik untuk mencari aset.</em
                            >
                        </template>
                    </v-select>
                    <jet-select
                        ref="updatePriority"
                        class="block w-full mt-4"
                        placeholder="Pilih Prioritas"
                        v-model="updateForm.priority"
                        :data="priorities"
                    />
                    <jet-input
                        type="text"
                        class="block w-full mt-4 normal-case"
                        placeholder="Keterangan (Opsional)"
                        ref="updateNote"
                        v-model="updateForm.note"
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
            title="Hapus laporan kehilangan aset"
        >
            <template #content>
                Apakah Anda yakin ingin menghapus laporan kehilangan aset ini?
                Setelah laporan kehilangan aset dihapus, semua sumber daya dan
                datanya akan dihapus secara permanen. Aksi ini tidak dapat
                dibatalkan.
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
        <jet-export-modal
            :show="confirmingExport"
            @close="closeExportModal"
            title="Ekspor data laporan kehilangan aset"
        >
            <template #content>
                <p>
                    Apakah Anda yakin ingin mengekspor semua data laporan
                    pengajuan aset? Proses ekspor dapat memakan waktu lama,
                    tergantung dari banyaknya data yang tersedia.
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
        asset_loss_damages: Object,
        priorities: Object,
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
            confirmingExport: false,
            showingSuccessNotification: false,
            showingDangerNotification: false,
            storeForm: useForm({
                asset: null,
                priority: null,
                note: null,
            }),
            updateForm: useForm({
                id: null,
                asset: null,
                priority: null,
                note: null,
            }),
            destroyForm: useForm({
                id: null,
            }),
            assetOptions: [],
        };
    },
    methods: {
        store() {
            this.storeForm.post(route("assets.loss.store"), {
                preserveScroll: true,
                onSuccess: () => {
                    this.reloadData();
                    this.closeStoreModal();
                    this.showSuccessNotification(
                        "laporan kehilangan aset berhasil ditambahkan",
                        "Sistem telah berhasil menyimpan data laporan kehilangan aset baru"
                    );
                },
                onError: () =>
                    this.showDangerNotification(
                        "Kesalahan telah terjadi",
                        "Sistem tidak dapat menyimpan data laporan kehilangan aset, mohon periksa ulang form"
                    ),
            });
        },
        update() {
            this.updateForm.put(
                route("assets.loss.update", this.updateForm.id),
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        this.reloadData();
                        this.closeUpdateModal();
                        this.showSuccessNotification(
                            "laporan kehilangan aset berhasil diedit",
                            "Sistem telah berhasil mengedit data laporan kehilangan aset"
                        );
                    },
                    onError: () =>
                        this.showDangerNotification(
                            "Kesalahan telah terjadi",
                            "Sistem tidak dapat mengubah data laporan kehilangan aset, mohon periksa ulang form"
                        ),
                }
            );
        },
        destroy() {
            this.destroyForm.delete(
                route("assets.loss.destroy", this.destroyForm.id),
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        this.reloadData();
                        this.closeDestroyModal();
                        this.showSuccessNotification(
                            "laporan kehilangan aset berhasil dihapus",
                            "Sistem telah berhasil menghapus data laporan kehilangan aset"
                        );
                    },
                    onError: () =>
                        this.showDangerNotification(
                            "Kesalahan telah terjadi",
                            "Sistem tidak dapat menghapus data laporan kehilangan aset"
                        ),
                }
            );
        },
        exportFile() {
            window.open(route("assets.loss.export"));
            this.closeExportModal();
        },
        confirmStore() {
            setTimeout(() => (this.confirmingStore = true), 150);
            setTimeout(() => this.$refs.storeName.focus(), 300);
        },
        confirmUpdate(asset_loss) {
            this.updateForm.id = asset_loss.id;
            this.updateForm.note = asset_loss.asset_loss_damage_note;
            this.updateForm.priority = asset_loss.asset_loss_damage_priority;
            this.updateForm.asset = {
                id: asset_loss.asset_id,
                label: `${asset_loss.name} (${asset_loss.asset_type_name}) - ${asset_loss.area_name}`,
            };
            setTimeout(() => (this.confirmingUpdate = true), 150);
            setTimeout(() => this.$refs.updateName.focus(), 300);
        },
        confirmDestroy(asset_loss) {
            this.destroyForm.id = asset_loss.id;
            setTimeout(() => (this.confirmingDestroy = true), 150);
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
                this.storeForm.asset = null;
                this.storeForm.note = null;
                this.storeForm.priority = null;
            }, 500);
        },
        closeUpdateModal() {
            this.confirmingUpdate = false;
            setTimeout(() => {
                this.clearErrors();
                this.updateForm.clearErrors();
                this.updateForm.reset();
                this.updateForm.id = null;
                this.updateForm.asset = null;
                this.updateForm.note = null;
                this.updateForm.priority = null;
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
        clearErrors() {
            this.$page.props.errors = [];
        },
        reloadData() {
            this.$refs.table.reload("asset_loss_damages");
        },
        onAssetSearch(search, loading) {
            if (search.length) {
                loading(true);
                this.assetSearch(loading, this, this.showDangerNotification, {
                    q: escape(search),
                });
            }
        },
        assetSearch: _.debounce((loading, vm, errorCallback, params) => {
            axios
                .get(route("api.assets"), {
                    params: params,
                })
                .then((res) => {
                    vm.assetOptions = res.data.items.map((item) => {
                        return {
                            id: item.id,
                            label: `${item.name} (${item.asset_type_name}) - ${item.area_name}`,
                        };
                    });
                })
                .catch(() => {
                    errorCallback(
                        "Kesalahan telah terjadi",
                        "Sistem tidak dapat mengambil data aset, mohon coba lagi nanti"
                    );
                })
                .finally(() => {
                    if (loading) loading(false);
                });
        }, 1000),
    },
});
</script>
