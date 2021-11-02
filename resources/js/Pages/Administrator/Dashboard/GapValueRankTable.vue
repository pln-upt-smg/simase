<template>
    <div class="font-semibold text-lg">
        Top 10 Gap Value - {{ area ? area.name : 'Semua Area' }}
    </div>
    <div v-if="data" class="font-semibold text-lg">
        <jet-table :meta="data" :paginate="false" :clickable-header="false">
            <template #head>
                <th>Kode Material</th>
                <th>Deskripsi Material</th>
                <th>UoM</th>
                <th>MType</th>
                <th>Unrestricted</th>
                <th>QualInsp</th>
                <th>Total Stock</th>
                <th>Gap Stock</th>
                <th>Gap Value</th>
            </template>
            <template #body>
                <tr v-if="data.data && data.data.length > 0" v-for="entry in data.data" :key="entry.id">
                    <td>{{ entry.material_code }}</td>
                    <td>{{ entry.material_description }}</td>
                    <td>{{ entry.uom }}</td>
                    <td>{{ entry.mtyp }}</td>
                    <td>{{ entry.unrestricted }}</td>
                    <td>{{ entry.qualinsp }}</td>
                    <td>{{ entry.total_stock }}</td>
                    <td>{{ entry.gap_stock }}</td>
                    <td>{{ entry.gap_value }}</td>
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
import {defineComponent} from 'vue'
import JetTable from '@/Jetstream/Table'

export default defineComponent({
    props: {
        area: Object,
        data: Object
    },
    components: {
        JetTable
    }
})
</script>
