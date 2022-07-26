<template>
    <app-layout title="Sertifikat">
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
            :meta="certificates"
            ref="table"
        >
            <template #head>
                <jet-table-header
                    v-show="showColumn('name')"
                    :cell="sortableHeader('name')"
                >
                    Nama Aset Tanah
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('urban_village_name')"
                    :cell="sortableHeader('urban_village_name')"
                >
                    Kelurahan
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('sub_district_name')"
                    :cell="sortableHeader('sub_district_name')"
                >
                    Kecamatan
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('district_name')"
                    :cell="sortableHeader('district_name')"
                >
                    Kabupaten / Kotamadya
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('province_name')"
                    :cell="sortableHeader('province_name')"
                >
                    Provinsi
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('area_code')"
                    :cell="sortableHeader('area_code')"
                >
                    Kode Wilayah
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('certificate_print_number')"
                    :cell="sortableHeader('certificate_print_number')"
                >
                    No. Cetak Sertifikat
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('certificate_type')"
                    :cell="sortableHeader('certificate_type')"
                >
                    Tipe Sertifikat
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('certificate_number')"
                    :cell="sortableHeader('certificate_number')"
                >
                    No. Sertifikat
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('nib')"
                    :cell="sortableHeader('nib')"
                >
                    NIB
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('origin_right_category')"
                    :cell="sortableHeader('origin_right_category')"
                >
                    Kategori Asal Hak
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('base_registration_decree_number')"
                    :cell="sortableHeader('base_registration_decree_number')"
                >
                    Surat Keputusan
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('base_registration_date')"
                    :cell="sortableHeader('base_registration_date')"
                >
                    Tanggal Dasar Pendaftaran
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('measuring_letter_number')"
                    :cell="sortableHeader('measuring_letter_number')"
                >
                    No. Surat Ukur
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('measuring_letter_date')"
                    :cell="sortableHeader('measuring_letter_date')"
                >
                    Tanggal Surat Ukur
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('measuring_letter_status')"
                    :cell="sortableHeader('measuring_letter_status')"
                >
                    Status Surat Ukur
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('field_map_status')"
                    :cell="sortableHeader('field_map_status')"
                >
                    Status Peta Bidang
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('wide')"
                    :cell="sortableHeader('wide')"
                >
                    Luas (M2)
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('certificate_bookkeeping_date')"
                    :cell="sortableHeader('certificate_bookkeeping_date')"
                >
                    Tanggal Pembukuan
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('certificate_publishing_date')"
                    :cell="sortableHeader('certificate_publishing_date')"
                >
                    Tanggal Penerbitan
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('certificate_type')"
                    :cell="sortableHeader('certificate_type')"
                >
                    Tanggal Akhir
                </jet-table-header>
                <jet-table-header
                    v-show="showColumn('holder_name')"
                    :cell="sortableHeader('holder_name')"
                >
                    Pemegang Hak
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
                <tr
                    v-for="certificate in certificates.data"
                    :key="certificate.id"
                >
                    <td v-show="showColumn('name')">
                        {{ certificate.name }}
                    </td>
                    <td v-show="showColumn('urban_village_name')">
                        {{ certificate.urban_village_name }}
                    </td>
                    <td v-show="showColumn('sub_district_name')">
                        {{ certificate.sub_district_name }}
                    </td>
                    <td v-show="showColumn('district_name')">
                        {{ certificate.district_name }}
                    </td>
                    <td v-show="showColumn('province_name')">
                        {{ certificate.province_name }}
                    </td>
                    <td v-show="showColumn('area_code')">
                        {{ certificate.area_code }}
                    </td>
                    <td v-show="showColumn('certificate_print_number')">
                        {{ certificate.certificate_print_number }}
                    </td>
                    <td v-show="showColumn('certificate_type')">
                        {{ certificate.certificate_type }}
                    </td>
                    <td v-show="showColumn('certificate_number')">
                        {{ certificate.certificate_number }}
                    </td>
                    <td v-show="showColumn('nib')">
                        {{ certificate.nib }}
                    </td>
                    <td v-show="showColumn('origin_right_category')">
                        {{ certificate.origin_right_category }}
                    </td>
                    <td v-show="showColumn('base_registration_decree_number')">
                        {{ certificate.base_registration_decree_number }}
                    </td>
                    <td v-show="showColumn('base_registration_date')">
                        {{ certificate.base_registration_date }}
                    </td>
                    <td v-show="showColumn('measuring_letter_number')">
                        {{ certificate.measuring_letter_number }}
                    </td>
                    <td v-show="showColumn('measuring_letter_date')">
                        {{ certificate.measuring_letter_date }}
                    </td>
                    <td v-show="showColumn('measuring_letter_status')">
                        <span
                            class="text-green-600"
                            v-if="certificate.measuring_letter_status"
                        >
                            Ada
                        </span>
                        <span class="text-red-600" v-else>
                            Tidak Ada
                        </span>
                    </td>
                    <td v-show="showColumn('field_map_status')">
                        <span
                            class="text-green-600"
                            v-if="certificate.field_map_status"
                        >
                            Ada
                        </span>
                        <span class="text-red-600" v-else>
                            Tidak Ada
                        </span>
                    </td>
                    <td v-show="showColumn('wide')">
                        {{ certificate.wide }}
                    </td>
                    <td v-show="showColumn('certificate_bookkeeping_date')">
                        {{ certificate.certificate_bookkeeping_date }}
                    </td>
                    <td v-show="showColumn('certificate_publishing_date')">
                        {{ certificate.certificate_publishing_date }}
                    </td>
                    <td v-show="showColumn('certificate_final_date')">
                        {{ certificate.certificate_final_date }}
                    </td>
                    <td v-show="showColumn('holder_name')">
                        {{ certificate.holder_name }}
                    </td>
                    <td v-show="showColumn('user_name')">
                        {{ certificate.user_name }}
                    </td>
                    <td v-show="showColumn('update_date')">
                        {{ certificate.update_date }}
                    </td>
                    <td v-show="showColumn('action')" class="text-center">
                        <jet-dropdown name="Opsi">
                            <menu-item>
                                <a
                                    :href="certificate.certificate_file"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 group"
                                >
                                    <download-icon
                                        class="w-5 h-5 mr-3 text-gray-700"
                                        aria-hidden="true"
                                    />
                                    Unduh File
                                </a>
                            </menu-item>
                            <menu-item>
                                <button
                                    @click="confirmUpdate(certificate)"
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
                                    @click="confirmDestroy(certificate)"
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
        <jet-modal :show="confirmingStore" title="Tambah sertifikat">
            <template #content>
                Silakan masukkan data sertifikat yang ingin ditambahkan.
                <jet-validation-errors class="mt-4" />
                <div class="mt-4">
                    <jet-input
                        type="text"
                        class="block w-full normal-case"
                        placeholder="Nama Aset Tanah"
                        ref="storeName"
                        v-model="storeForm.name"
                    />
                    <jet-select
                        ref="storeUrbanVillage"
                        class="block w-full mt-4"
                        placeholder="Pilih Kelurahan"
                        v-model="storeForm.urban_village"
                        :data="urban_villages"
                    />
                    <jet-select
                        ref="storeSubDistrict"
                        class="block w-full mt-4"
                        placeholder="Pilih Kecamatan"
                        v-model="storeForm.sub_district"
                        :data="sub_districts"
                    />
                    <jet-select
                        ref="storeDistrict"
                        class="block w-full mt-4"
                        placeholder="Pilih Kabupaten / Kotamadya"
                        v-model="storeForm.district"
                        :data="districts"
                    />
                    <jet-select
                        ref="storeProvince"
                        class="block w-full mt-4"
                        placeholder="Pilih Provinsi"
                        v-model="storeForm.province"
                        :data="provinces"
                    />
                    <jet-input
                        type="text"
                        class="block w-full mt-4 normal-case"
                        placeholder="Kode Wilayah"
                        ref="storeAreaCode"
                        v-model="storeForm.area_code"
                    />
                    <jet-input
                        type="text"
                        class="block w-full mt-4 normal-case"
                        placeholder="No. Cetak Sertifikat"
                        ref="storeCertificatePrintNumber"
                        v-model="storeForm.certificate_print_number"
                    />
                    <jet-input
                        type="text"
                        class="block w-full mt-4 normal-case"
                        placeholder="Tipe Sertifikat"
                        ref="storeCertificateType"
                        v-model="storeForm.certificate_type"
                    />
                    <jet-input
                        type="text"
                        class="block w-full mt-4 normal-case"
                        placeholder="No. Sertifikat"
                        ref="storeCertificateNumber"
                        v-model="storeForm.certificate_number"
                    />
                    <jet-input
                        type="text"
                        class="block w-full mt-4 normal-case"
                        placeholder="NIB"
                        ref="storeNIB"
                        v-model="storeForm.nib"
                    />
                    <jet-input
                        type="text"
                        class="block w-full mt-4 normal-case"
                        placeholder="Kategori Asal Hak"
                        ref="storeOriginRightCategory"
                        v-model="storeForm.origin_right_category"
                    />
                    <jet-input
                        type="text"
                        class="block w-full mt-4 normal-case"
                        placeholder="Surat Keputusan"
                        ref="storeBaseRegistrationDecreeNumber"
                        v-model="storeForm.base_registration_decree_number"
                    />
                    <datepicker
                        class="block w-full mt-4"
                        ref="storeBaseRegistrationDate"
                        placeholder="Tanggal Dasar Pendaftaran"
                        v-model="storeForm.base_registration_date"
                        :flow="dateFlow"
                        :format="dateFormat"
                        :enableTimePicker="false"
                    />
                    <jet-input
                        type="text"
                        class="block w-full mt-4 normal-case"
                        placeholder="No. Surat Ukur"
                        ref="storeMeasuringLetterNumber"
                        v-model="storeForm.measuring_letter_number"
                    />
                    <datepicker
                        class="block w-full mt-4"
                        ref="storeMeasuringLetterDate"
                        placeholder="Tanggal Surat Ukur"
                        v-model="storeForm.measuring_letter_date"
                        :flow="dateFlow"
                        :format="dateFormat"
                        :enableTimePicker="false"
                    />
                    <jet-select
                        ref="storeMeasuringLetterStatus"
                        class="block w-full mt-4"
                        placeholder="Status Surat Ukur"
                        v-model="storeForm.measuring_letter_status"
                        :data="optionalities"
                    />
                    <jet-select
                        ref="storeFieldMapStatus"
                        class="block w-full mt-4"
                        placeholder="Status Peta Bidang"
                        v-model="storeForm.field_map_status"
                        :data="optionalities"
                    />
                    <jet-input
                        type="number"
                        class="block w-full mt-4 normal-case"
                        placeholder="Luas (M2)"
                        ref="storeWide"
                        v-model="storeForm.wide"
                    />
                    <datepicker
                        class="block w-full mt-4"
                        ref="storeCertificateBookkeepingDate"
                        placeholder="Tanggal Pembukuan"
                        v-model="storeForm.certificate_bookkeeping_date"
                        :flow="dateFlow"
                        :format="dateFormat"
                        :enableTimePicker="false"
                    />
                    <datepicker
                        class="block w-full mt-4"
                        ref="storeCertificatePublishingDate"
                        placeholder="Tanggal Penerbitan"
                        v-model="storeForm.certificate_publishing_date"
                        :flow="dateFlow"
                        :format="dateFormat"
                        :enableTimePicker="false"
                    />
                    <datepicker
                        class="block w-full mt-4"
                        ref="storeCertificateFinalDate"
                        placeholder="Tanggal Akhir"
                        v-model="storeForm.certificate_final_date"
                        :flow="dateFlow"
                        :format="dateFormat"
                        :enableTimePicker="false"
                    />
                    <jet-select
                        ref="storeHolder"
                        class="block w-full mt-4"
                        placeholder="Pilih Pemegang Hak"
                        v-model="storeForm.holder"
                        :data="holders"
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
        <jet-modal :show="confirmingUpdate" title="Edit sertifikat">
            <template #content>
                Silakan masukkan data sertifikat yang ingin diubah.
                <jet-validation-errors class="mt-4" />
                <div class="mt-4">
                    <jet-input
                        type="text"
                        class="block w-full normal-case"
                        placeholder="Nama Aset Tanah"
                        ref="updateName"
                        v-model="updateForm.name"
                    />
                    <jet-select
                        ref="updateUrbanVillage"
                        class="block w-full mt-4"
                        placeholder="Pilih Kelurahan"
                        v-model="updateForm.urban_village"
                        :data="urban_villages"
                    />
                    <jet-select
                        ref="updateSubDistrict"
                        class="block w-full mt-4"
                        placeholder="Pilih Kecamatan"
                        v-model="updateForm.sub_district"
                        :data="sub_districts"
                    />
                    <jet-select
                        ref="updateDistrict"
                        class="block w-full mt-4"
                        placeholder="Pilih Kabupaten / Kotamadya"
                        v-model="updateForm.district"
                        :data="districts"
                    />
                    <jet-select
                        ref="updateProvince"
                        class="block w-full mt-4"
                        placeholder="Pilih Provinsi"
                        v-model="updateForm.province"
                        :data="provinces"
                    />
                    <jet-input
                        type="text"
                        class="block w-full mt-4 normal-case"
                        placeholder="Kode Wilayah"
                        ref="updateAreaCode"
                        v-model="updateForm.area_code"
                    />
                    <jet-input
                        type="text"
                        class="block w-full mt-4 normal-case"
                        placeholder="No. Cetak Sertifikat"
                        ref="updateCertificatePrintNumber"
                        v-model="updateForm.certificate_print_number"
                    />
                    <jet-input
                        type="text"
                        class="block w-full mt-4 normal-case"
                        placeholder="Tipe Sertifikat"
                        ref="updateCertificateType"
                        v-model="updateForm.certificate_type"
                    />
                    <jet-input
                        type="text"
                        class="block w-full mt-4 normal-case"
                        placeholder="No. Sertifikat"
                        ref="updateCertificateNumber"
                        v-model="updateForm.certificate_number"
                    />
                    <jet-input
                        type="text"
                        class="block w-full mt-4 normal-case"
                        placeholder="NIB"
                        ref="updateNIB"
                        v-model="updateForm.nib"
                    />
                    <jet-input
                        type="text"
                        class="block w-full mt-4 normal-case"
                        placeholder="Kategori Asal Hak"
                        ref="updateOriginRightCategory"
                        v-model="updateForm.origin_right_category"
                    />
                    <jet-input
                        type="text"
                        class="block w-full mt-4 normal-case"
                        placeholder="Surat Keputusan"
                        ref="updateBaseRegistrationDecreeNumber"
                        v-model="updateForm.base_registration_decree_number"
                    />
                    <datepicker
                        class="block w-full mt-4"
                        ref="updateBaseRegistrationDate"
                        placeholder="Tanggal Dasar Pendaftaran"
                        v-model="updateForm.base_registration_date"
                        :flow="dateFlow"
                        :format="dateFormat"
                        :enableTimePicker="false"
                    />
                    <jet-input
                        type="text"
                        class="block w-full mt-4 normal-case"
                        placeholder="No. Surat Ukur"
                        ref="updateMeasuringLetterNumber"
                        v-model="updateForm.measuring_letter_number"
                    />
                    <datepicker
                        class="block w-full mt-4"
                        ref="updateMeasuringLetterDate"
                        placeholder="Tanggal Surat Ukur"
                        v-model="updateForm.measuring_letter_date"
                        :flow="dateFlow"
                        :format="dateFormat"
                        :enableTimePicker="false"
                    />
                    <jet-select
                        ref="updateMeasuringLetterStatus"
                        class="block w-full mt-4"
                        placeholder="Status Surat Ukur"
                        v-model="updateForm.measuring_letter_status"
                        :data="optionalities"
                    />
                    <jet-select
                        ref="updateFieldMapStatus"
                        class="block w-full mt-4"
                        placeholder="Status Peta Bidang"
                        v-model="updateForm.field_map_status"
                        :data="optionalities"
                    />
                    <jet-input
                        type="number"
                        class="block w-full mt-4 normal-case"
                        placeholder="Luas (M2)"
                        ref="updateWide"
                        v-model="updateForm.wide"
                    />
                    <datepicker
                        class="block w-full mt-4"
                        ref="updateCertificateBookkeepingDate"
                        placeholder="Tanggal Pembukuan"
                        v-model="updateForm.certificate_bookkeeping_date"
                        :flow="dateFlow"
                        :format="dateFormat"
                        :enableTimePicker="false"
                    />
                    <datepicker
                        class="block w-full mt-4"
                        ref="updateCertificatePublishingDate"
                        placeholder="Tanggal Penerbitan"
                        v-model="updateForm.certificate_publishing_date"
                        :flow="dateFlow"
                        :format="dateFormat"
                        :enableTimePicker="false"
                    />
                    <datepicker
                        class="block w-full mt-4"
                        ref="updateCertificateFinalDate"
                        placeholder="Tanggal Akhir"
                        v-model="updateForm.certificate_final_date"
                        :flow="dateFlow"
                        :format="dateFormat"
                        :enableTimePicker="false"
                    />
                    <jet-select
                        ref="updateHolder"
                        class="block w-full mt-4"
                        placeholder="Pilih Pemegang Hak"
                        v-model="updateForm.holder"
                        :data="holders"
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
            title="Hapus sertifikat"
        >
            <template #content>
                Apakah Anda yakin ingin menghapus sertifikat ini? Setelah
                sertifikat dihapus, semua sumber daya dan datanya akan dihapus
                secara permanen. Aksi ini tidak dapat dibatalkan.
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
            title="Impor data sertifikat"
        >
            <template #content>
                <p>
                    Silakan unggah file data sertifikat yang ingin di-impor.
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
            title="Ekspor data sertifikat"
        >
            <template #content>
                <p>
                    Apakah Anda yakin ingin mengekspor semua data kabupaten /
                    kotamadya? Proses ekspor dapat memakan waktu lama,
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
        urban_villages: Object,
        sub_districts: Object,
        districts: Object,
        provinces: Object,
        holders: Object,
        optionalities: Object,
        certificates: Object,
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
                urban_village: null,
                sub_district: null,
                district: null,
                province: null,
                holder: null,
                name: null,
                area_code: null,
                certificate_type: null,
                certificate_number: null,
                certificate_print_number: null,
                certificate_bookkeeping_date: null,
                certificate_publishing_date: null,
                certificate_final_date: null,
                nib: null,
                origin_right_category: null,
                base_registration_decree_number: null,
                base_registration_date: null,
                measuring_letter_number: null,
                measuring_letter_date: null,
                measuring_letter_status: null,
                field_map_status: null,
                wide: null,
            }),
            updateForm: useForm({
                id: null,
                urban_village: null,
                sub_district: null,
                district: null,
                province: null,
                holder: null,
                name: null,
                area_code: null,
                certificate_type: null,
                certificate_number: null,
                certificate_print_number: null,
                certificate_bookkeeping_date: null,
                certificate_publishing_date: null,
                certificate_final_date: null,
                nib: null,
                origin_right_category: null,
                base_registration_decree_number: null,
                base_registration_date: null,
                measuring_letter_number: null,
                measuring_letter_date: null,
                measuring_letter_status: null,
                field_map_status: null,
                wide: null,
            }),
            destroyForm: useForm({
                id: null,
            }),
            importForm: useForm({
                file: null,
            }),
            dateFlow: ["month", "year", "calendar"],
            dateFormat: (date) => {
                const day = date.getDate();
                const month = date.getMonth() + 1;
                const year = date.getFullYear();
                return `${day}/${month}/${year}`;
            },
        };
    },
    methods: {
        store() {
            this.storeForm.post(route("certificates.store"), {
                preserveScroll: true,
                onSuccess: () => {
                    this.reloadData();
                    this.closeStoreModal();
                    this.showSuccessNotification(
                        "Sertifikat berhasil ditambahkan",
                        "Sistem telah berhasil menyimpan data sertifikat baru"
                    );
                },
                onError: () =>
                    this.showDangerNotification(
                        "Kesalahan telah terjadi",
                        "Sistem tidak dapat menyimpan data sertifikat, mohon periksa ulang form"
                    ),
            });
        },
        update() {
            this.updateForm.put(
                route("certificates.update", this.updateForm.id),
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        this.reloadData();
                        this.closeUpdateModal();
                        this.showSuccessNotification(
                            "Sertifikat berhasil diedit",
                            "Sistem telah berhasil mengedit data sertifikat"
                        );
                    },
                    onError: () =>
                        this.showDangerNotification(
                            "Kesalahan telah terjadi",
                            "Sistem tidak dapat mengubah data sertifikat, mohon periksa ulang form"
                        ),
                }
            );
        },
        destroy() {
            this.destroyForm.delete(
                route("certificates.destroy", this.destroyForm.id),
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        this.reloadData();
                        this.closeDestroyModal();
                        this.showSuccessNotification(
                            "Sertifikat berhasil dihapus",
                            "Sistem telah berhasil menghapus data sertifikat"
                        );
                    },
                    onError: () =>
                        this.showDangerNotification(
                            "Kesalahan telah terjadi",
                            "Sistem tidak dapat menghapus data sertifikat"
                        ),
                }
            );
        },
        importFile() {
            this.importForm.post(route("certificates.import"), {
                preserveScroll: true,
                onSuccess: () => {
                    this.reloadData();
                    this.closeImportModal();
                    this.showSuccessNotification(
                        "Permintaan impor data telah dijadwalkan",
                        "Sistem berhasil menjadwalkan permintaan impor data sertifikat di latar belakang"
                    );
                },
                onError: () =>
                    this.showDangerNotification(
                        "Kesalahan telah terjadi",
                        "Sistem tidak dapat mengimpor data sertifikat, mohon periksa kesalahan yang telah dideteksi"
                    ),
            });
        },
        exportFile() {
            window.open(route("certificates.export"));
            this.closeExportModal();
        },
        confirmStore() {
            setTimeout(() => (this.confirmingStore = true), 150);
            setTimeout(() => this.$refs.storeName.focus(), 300);
        },
        confirmUpdate(certificate) {
            this.updateForm.id = certificate.id;
            this.updateForm.urban_village = certificate.urban_village_id;
            this.updateForm.sub_district = certificate.sub_district_id;
            this.updateForm.district = certificate.district_id;
            this.updateForm.province = certificate.province_id;
            this.updateForm.holder = certificate.holder_id;
            this.updateForm.name = certificate.name;
            this.updateForm.area_code = certificate.area_code;
            this.updateForm.certificate_type = certificate.certificate_type;
            this.updateForm.certificate_number = certificate.certificate_number;
            this.updateForm.certificate_print_number =
                certificate.certificate_print_number;
            this.updateForm.certificate_bookkeeping_date =
                certificate.certificate_bookkeeping_date;
            this.updateForm.certificate_publishing_date =
                certificate.certificate_publishing_date;
            this.updateForm.certificate_final_date =
                certificate.certificate_final_date;
            this.updateForm.nib = certificate.nib;
            this.updateForm.origin_right_category =
                certificate.origin_right_category;
            this.updateForm.base_registration_decree_number =
                certificate.base_registration_decree_number;
            this.updateForm.base_registration_date =
                certificate.base_registration_date;
            this.updateForm.measuring_letter_number =
                certificate.measuring_letter_number;
            this.updateForm.measuring_letter_date =
                certificate.measuring_letter_date;
            this.updateForm.measuring_letter_status =
                certificate.measuring_letter_status;
            this.updateForm.field_map_status = certificate.field_map_status;
            this.updateForm.wide = certificate.wide;
            setTimeout(() => (this.confirmingUpdate = true), 150);
            setTimeout(() => this.$refs.updateName.focus(), 300);
        },
        confirmDestroy(certificate) {
            this.destroyForm.id = certificate.id;
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
                this.storeForm.urban_village = null;
                this.storeForm.sub_district = null;
                this.storeForm.district = null;
                this.storeForm.province = null;
                this.storeForm.holder = null;
                this.storeForm.name = null;
                this.storeForm.area_code = null;
                this.storeForm.certificate_type = null;
                this.storeForm.certificate_number = null;
                this.storeForm.certificate_print_number = null;
                this.storeForm.certificate_bookkeeping_date = null;
                this.storeForm.certificate_publishing_date = null;
                this.storeForm.certificate_final_date = null;
                this.storeForm.nib = null;
                this.storeForm.origin_right_category = null;
                this.storeForm.base_registration_decree_number = null;
                this.storeForm.base_registration_date = null;
                this.storeForm.measuring_letter_number = null;
                this.storeForm.measuring_letter_date = null;
                this.storeForm.measuring_letter_status = null;
                this.storeForm.field_map_status = null;
                this.storeForm.wide = null;
            }, 500);
        },
        closeUpdateModal() {
            this.confirmingUpdate = false;
            setTimeout(() => {
                this.clearErrors();
                this.updateForm.clearErrors();
                this.updateForm.reset();
                this.updateForm.id = null;
                this.updateForm.urban_village = null;
                this.updateForm.sub_district = null;
                this.updateForm.district = null;
                this.updateForm.province = null;
                this.updateForm.holder = null;
                this.updateForm.name = null;
                this.updateForm.area_code = null;
                this.updateForm.certificate_type = null;
                this.updateForm.certificate_number = null;
                this.updateForm.certificate_print_number = null;
                this.updateForm.certificate_bookkeeping_date = null;
                this.updateForm.certificate_publishing_date = null;
                this.updateForm.certificate_final_date = null;
                this.updateForm.nib = null;
                this.updateForm.origin_right_category = null;
                this.updateForm.base_registration_decree_number = null;
                this.updateForm.base_registration_date = null;
                this.updateForm.measuring_letter_number = null;
                this.updateForm.measuring_letter_date = null;
                this.updateForm.measuring_letter_status = null;
                this.updateForm.field_map_status = null;
                this.updateForm.wide = null;
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
            this.$refs.table.reload("certificates");
        },
    },
});
</script>
