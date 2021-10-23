<template>
    <app-layout title="Pegawai">
        <grid-header>
            <jet-breadcrumbs :pages="[{name: 'Pegawai', href: 'operators.index', current: true}]"/>
            <div class="text-left lg:text-right">
                <div class="pt-9 lg:pt-0 mt-2">
                    <jet-button class="ml-0 mr-2">
                        <plus-icon class="h-5 w-5 text-white" aria-hidden="true"/>
                    </jet-button>
                    <jet-button class="mr-2">
                        <upload-icon class="h-5 w-5 text-white" aria-hidden="true"/>
                    </jet-button>
                    <jet-button>
                        <download-icon class="h-5 w-5 text-white" aria-hidden="true"/>
                    </jet-button>
                </div>
            </div>
        </grid-header>
        <Table
            :filters="queryBuilderProps.filters"
            :search="queryBuilderProps.search"
            :on-update="setQueryBuilder"
            :meta="users"
            class="pt-14 lg:pt-0"
        >
            <template #head>
                <tr>
                    <th v-show="showColumn('name')" @click.prevent="sortBy('name')">Nama</th>
                    <th v-show="showColumn('phone')" @click.prevent="sortBy('phone')">Nomor Telepon</th>
                    <th v-show="showColumn('nip')" @click.prevent="sortBy('nip')">NIP</th>
                    <th v-show="showColumn('role')">Peran</th>
                    <th v-show="showColumn('action')"></th>
                </tr>
            </template>
            <template #body>
                <tr v-for="user in users.data" :key="user.id">
                    <td v-show="showColumn('name')">{{ user.name }}</td>
                    <td v-show="showColumn('phone')">{{ user.phone }}</td>
                    <td v-show="showColumn('nip')">{{ user.nip }}</td>
                    <td v-show="showColumn('role')">Operator</td>
                    <td v-show="showColumn('action')">
                        <jet-dropdown name="Opsi">
                            <menu-item>
                                <jet-link :href="route('login')"
                                          class="text-gray-700 hover:bg-gray-100 group flex items-center px-4 py-2 text-sm">
                                    <pencil-alt-icon class="mr-3 h-5 w-5 text-gray-700" aria-hidden="true"/>
                                    Edit
                                </jet-link>
                            </menu-item>
                            <menu-item>
                                <jet-link :href="route('login')"
                                          class="text-gray-700 hover:bg-gray-100 group flex items-center px-4 py-2 text-sm">
                                    <trash-icon class="mr-3 h-5 w-5 text-gray-700" aria-hidden="true"/>
                                    Hapus
                                </jet-link>
                            </menu-item>
                        </jet-dropdown>
                    </td>
                </tr>
            </template>
        </Table>
    </app-layout>
</template>

<script>
import {defineComponent} from 'vue'
import {Link as JetLink} from "@inertiajs/inertia-vue3";
import AppLayout from "@/Layouts/AppLayout.vue"
import GridHeader from "@/Layouts/GridHeader.vue"
import JetButton from "@/Jetstream/Button";
import JetBreadcrumbs from "@/Jetstream/Breadcrumbs";
import JetDropdown from "@/Jetstream/Dropdown.vue"
import {MenuItem} from '@headlessui/vue'
import {Components, InteractsWithQueryBuilder, Tailwind2} from "@protonemedia/inertiajs-tables-laravel-query-builder"
import {DownloadIcon, PencilAltIcon, PlusIcon, TrashIcon, UploadIcon} from "@heroicons/vue/outline"

Components.Pagination.setTranslations({
    no_results_found: "Tidak ada data tersedia",
    previous: "Sebelumnya",
    next: "Selanjutnya",
    to: "hingga",
    of: "dari",
    results: "data"
});

export default defineComponent({
    mixins: [InteractsWithQueryBuilder],
    props: {
        users: Object
    },
    components: {
        Table: Tailwind2.Table,
        AppLayout,
        GridHeader,
        JetBreadcrumbs,
        JetDropdown,
        JetButton,
        JetLink,
        MenuItem,
        PlusIcon,
        UploadIcon,
        DownloadIcon,
        PencilAltIcon,
        TrashIcon
    }
})
</script>
