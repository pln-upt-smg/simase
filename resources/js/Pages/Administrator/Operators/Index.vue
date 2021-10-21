<template>
    <app-layout title="Pegawai">
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <Table
                :filters="queryBuilderProps.filters"
                :search="queryBuilderProps.search"
                :columns="queryBuilderProps.columns"
                :on-update="setQueryBuilder"
                :meta="users">
                <template #head>
                    <tr>
                        <th v-show="showColumn('name')" @click.prevent="sortBy('name')">Nama</th>
                        <th v-show="showColumn('nip')" @click.prevent="sortBy('nip')">NIP</th>
                        <th v-show="showColumn('role')">Peran</th>
                    </tr>
                </template>
                <template #body>
                    <tr v-for="user in users.data" :key="user.id">
                        <td v-show="showColumn('name')">{{ user.name }}</td>
                        <td v-show="showColumn('nip')">{{ user.nip }}</td>
                        <th v-show="showColumn('role')">Operator</th>
                    </tr>
                </template>
            </Table>
        </div>
    </app-layout>
</template>

<script>
import {defineComponent} from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import {Components, InteractsWithQueryBuilder, Tailwind2} from '@protonemedia/inertiajs-tables-laravel-query-builder';

Components.Pagination.setTranslations({
    no_results_found: "Tidak ada hasil tersedia",
    previous: "Sebelumnya",
    next: "Lanjut",
    to: "hingga",
    of: "dari",
    results: "hasil"
});

export default defineComponent({
    mixins: [InteractsWithQueryBuilder],
    props: {
        users: Object
    },
    components: {
        AppLayout,
        Table: Tailwind2.Table
    }
})
</script>
