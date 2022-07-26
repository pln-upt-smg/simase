<template>
    <div class="font-semibold text-lg">
        Sertifikat Terkini
    </div>
    <div v-if="data" class="font-semibold text-lg">
        <jet-table :meta="data" :paginate="false" :clickable-header="false">
            <template #head>
                <th>Nama Aset Tanah</th>
                <th>No. Sertifikat</th>
                <th>Kelurahan</th>
                <th>Kecamatan</th>
                <th>Kabupaten / Kota Madya</th>
                <th>Provinsi</th>
                <th>Kode Wilayah</th>
            </template>
            <template #body>
                <tr
                    v-if="data && data.length > 0"
                    v-for="entry in data"
                    :key="entry.id"
                >
                    <td>{{ entry.name }}</td>
                    <td>{{ entry.certificate_number }}</td>
                    <td>{{ entry.urban_village_name }}</td>
                    <td>{{ entry.sub_district_name }}</td>
                    <td>{{ entry.district_name }}</td>
                    <td>{{ entry.province_name }}</td>
                    <td>{{ entry.area_code }}</td>
                </tr>
                <tr v-else>
                    <td colspan="9" class="font-normal text-black">
                        Tidak ada data tersedia
                    </td>
                </tr>
            </template>
        </jet-table>
    </div>
    <div v-else class="text-center font-normal text-base mt-6">
        Tidak ada data tersedia
    </div>
</template>

<script>
import { defineComponent } from "vue";
import JetTable from "@/Jetstream/Table";

export default defineComponent({
    props: {
        data: Object,
    },
    components: {
        JetTable,
    },
});
</script>
