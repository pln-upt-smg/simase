<template>
    <div class="font-semibold text-lg">
        Laporan Transfer Aset Terkini
    </div>
    <div v-if="data" class="font-semibold text-lg">
        <jet-table :meta="data" :paginate="false" :clickable-header="false">
            <template #head>
                <th>Techidentno</th>
                <th>Nama Aset</th>
                <th>Tipe Aset</th>
                <th>Funcloc Asal</th>
                <th>Area Asal</th>
                <th>Tipe Area Asal</th>
                <th>Funcloc Tujuan</th>
                <th>Area Tujuan</th>
                <th>Tipe Area Tujuan</th>
                <th>Pengajuan Kuantitas</th>
                <th>Prioritas</th>
            </template>
            <template #body>
                <tr
                    v-if="data && data.length > 0"
                    v-for="entry in data"
                    :key="entry.id"
                >
                    <td>{{ entry.techidentno }}</td>
                    <td>{{ entry.name }}</td>
                    <td>{{ entry.asset_type_name }}</td>
                    <td>{{ entry.area_funcloc }}</td>
                    <td>{{ entry.area_name }}</td>
                    <td>{{ entry.area_type_name }}</td>
                    <td>{{ entry.target_area_funcloc }}</td>
                    <td>{{ entry.target_area_name }}</td>
                    <td>{{ entry.target_area_type_name }}</td>
                    <td>{{ entry.asset_transfer_quantity }}</td>
                    <td>
                        <span
                            class="text-green-600"
                            v-if="entry.asset_transfer_priority === 1"
                        >
                            Rendah
                        </span>
                        <span
                            class="text-yellow-600"
                            v-else-if="entry.asset_transfer_priority === 2"
                        >
                            Sedang
                        </span>
                        <span class="text-red-600" v-else>
                            Tinggi
                        </span>
                    </td>
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
