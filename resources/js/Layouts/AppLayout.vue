<template>
    <Head :title="title"/>
    <div class="flex h-screen overflow-hidden bg-gray-100 ">
        <!-- Mobile Sidebar -->
        <TransitionRoot as="template" :show="sidebarOpen">
            <Dialog as="div" static class="fixed inset-0 z-40 flex md:hidden" @close="sidebarOpen = false"
                    :open="sidebarOpen">
                <TransitionChild as="template" enter="transition-opacity ease-linear duration-300"
                                 enter-from="opacity-0" enter-to="opacity-100"
                                 leave="transition-opacity ease-linear duration-300" leave-from="opacity-100"
                                 leave-to="opacity-0">
                    <DialogOverlay class="fixed inset-0 bg-gray-600 bg-opacity-75"/>
                </TransitionChild>
                <TransitionChild as="template" enter="transition ease-in-out duration-300 transform"
                                 enter-from="-translate-x-full" enter-to="translate-x-0"
                                 leave="transition ease-in-out duration-300 transform" leave-from="translate-x-0"
                                 leave-to="-translate-x-full">
                    <div class="relative flex flex-col flex-1 w-full max-w-xs pt-5 bg-indigo-700">
                        <TransitionChild as="template" enter="ease-in-out duration-300" enter-from="opacity-0"
                                         enter-to="opacity-100" leave="ease-in-out duration-300"
                                         leave-from="opacity-100" leave-to="opacity-0">
                            <div class="absolute top-0 right-0 pt-2 -mr-12">
                                <button type="button"
                                        class="flex items-center justify-center w-10 h-10 ml-1 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                                        @click="sidebarOpen = false">
                                    <span class="sr-only">Close sidebar</span>
                                    <x-icon class="w-6 h-6 text-white" aria-hidden="true"/>
                                </button>
                            </div>
                        </TransitionChild>
                        <div class="flex items-center flex-shrink-0 px-4">
                            <span class="text-2xl font-bold text-white">📦 SIMASE</span>
                        </div>
                        <div
                            class="flex-1 h-full pb-4 mt-4 overflow-y-auto scrollbar-thin scrollbar-thumb-indigo-800 scrollbar-track-indigo-200">
                            <nav>
                                <div class="px-4 pt-2 space-y-2">
                                    <span class="text-base font-semibold text-white">Menu</span>
                                    <Link v-for="item in this.menuNavigations" :key="item.name" :href="route(item.href)"
                                          :class="[route().current(item.href) || (item.currents && item.currents.includes(route().current())) ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600', 'group flex items-center px-2 py-2 text-base font-medium rounded-md']">
                                        <component :is="item.icon" class="flex-shrink-0 w-6 h-6 mr-4 text-indigo-300"
                                                   aria-hidden="true"/>
                                        {{ item.name }}
                                    </Link>
                                </div>
                                <div class="px-4 pt-6 space-y-2">
                                    <span class="text-base font-semibold text-white">Kelola</span>
                                    <Link v-for="item in this.manageNavigations" :key="item.name"
                                          :href="route(item.href)"
                                          :class="[route().current(item.href) ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600', 'group flex items-center px-2 py-2 text-base font-medium rounded-md']">
                                        <component :is="item.icon" class="flex-shrink-0 w-6 h-6 mr-4 text-indigo-300"
                                                   aria-hidden="true"/>
                                        {{ item.name }}
                                    </Link>
                                </div>
                            </nav>
                        </div>
                    </div>
                </TransitionChild>
                <div class="flex-shrink-0 w-14" aria-hidden="true">
                    <!-- Dummy element to force sidebar to shrink to fit close icon -->
                </div>
            </Dialog>
        </TransitionRoot>
        <!-- Desktop Sidebar -->
        <div class="hidden bg-indigo-700 md:flex md:flex-shrink-0">
            <div class="flex flex-col w-64">
                <div
                    class="flex flex-col flex-grow pt-5 pb-4 overflow-y-auto scrollbar-thin scrollbar-thumb-indigo-800 scrollbar-track-indigo-200">
                    <div class="flex items-center flex-shrink-0 px-4">
                        <span class="text-2xl font-bold text-white">📦 SIMASE</span>
                    </div>
                    <div class="flex flex-col flex-1">
                        <nav>
                            <div class="flex-1 px-4 pt-6 space-y-2">
                                <span class="text-base font-semibold text-white">Menu</span>
                                <Link v-for="item in menuNavigations" :key="item.name" :href="route(item.href)"
                                      :class="[route().current(item.href) || (item.currents && item.currents.includes(route().current())) ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600', 'group flex items-center px-2 py-2 text-sm font-medium rounded-md']">
                                    <component :is="item.icon" class="flex-shrink-0 w-6 h-6 mr-3 text-indigo-300"
                                               aria-hidden="true"/>
                                    {{ item.name }}
                                </Link>
                            </div>
                            <div class="flex-1 px-4 pt-6 space-y-2">
                                <span class="text-base font-semibold text-white">Kelola</span>
                                <Link v-for="item in manageNavigations" :key="item.name" :href="route(item.href)"
                                      :class="[route().current(item.href) ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600', 'group flex items-center px-2 py-2 text-sm font-medium rounded-md']">
                                    <component :is="item.icon" class="flex-shrink-0 w-6 h-6 mr-3 text-indigo-300"
                                               aria-hidden="true"/>
                                    {{ item.name }}
                                </Link>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex flex-col flex-1 w-0 overflow-hidden">
            <div class="relative z-10 flex flex-shrink-0 h-16 bg-white shadow">
                <button type="button"
                        class="px-4 text-gray-500 border-r border-gray-200 md:hidden"
                        @click="sidebarOpen = true">
                    <span class="sr-only">Buka sidebar</span>
                    <menu-alt2-icon class="w-6 h-6" aria-hidden="true"/>
                </button>
                <div class="flex justify-between flex-1 px-4">
                    <div class="flex flex-1 lg:pl-4">
                        <h1 class="py-5 text-xl font-bold text-gray-900 lg:py-4">
                            {{ title }}
                        </h1>
                    </div>
                    <div class="flex items-center ml-4 mr-0 md:ml-6 lg:mr-8">
                        <div class="icon-badge-container">
                            <button
                                @click="showNotificationPanel"
                                class="p-1 mr-4 text-gray-400 bg-white rounded-full icon-badge-icon hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <span class="sr-only">Lihat notifikasi</span>
                                <BellIcon class="w-6 h-6" aria-hidden="true"/>
                            </button>
                            <div v-if="this.$page.props.unreadNotificationCount > 0" class="icon-badge">{{
                                    this.$page.props.unreadNotificationCount > 6 ? '6+' : this.$page.props.unreadNotificationCount
                                }}
                            </div>
                        </div>
                        <Menu as="div" class="relative ml-3">
                            <div>
                                <MenuButton
                                    class="flex items-center max-w-xs text-sm bg-white rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <span class="sr-only">Buka menu</span>
                                    <img v-if="$page.props.jetstream.managesProfilePhotos"
                                         class="w-8 h-8 rounded-full"
                                         :src="$page.props.user.profile_photo_url"
                                         :alt="$page.props.user.name"/>
                                </MenuButton>
                            </div>
                            <transition enter-active-class="transition duration-100 ease-out"
                                        enter-from-class="transform scale-95 opacity-0"
                                        enter-to-class="transform scale-100 opacity-100"
                                        leave-active-class="transition duration-75 ease-in"
                                        leave-from-class="transform scale-100 opacity-100"
                                        leave-to-class="transform scale-95 opacity-0">
                                <MenuItems
                                    class="absolute right-0 w-48 py-1 mt-2 origin-top-right bg-white rounded-md shadow-lg">
                                    <Link :href="route('profile.show')"
                                          class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Profil Saya
                                    </Link>
                                    <Link v-if="$page.props.jetstream.hasApiFeatures"
                                          class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                          :href="route('api-tokens.index')">
                                        API Tokens
                                    </Link>
                                    <form method="POST" @submit.prevent="logout">
                                        <button
                                            class="block w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-100">
                                            Keluar
                                        </button>
                                    </form>
                                </MenuItems>
                            </transition>
                        </Menu>
                    </div>
                </div>
            </div>
            <main
                class="relative flex-1 overflow-x-hidden overflow-y-auto focus:outline-none scrollbar-thin scrollbar-thumb-indigo-800 scrollbar-track-indigo-200">
                <div class="px-6 py-8 mx-auto max-w-7xl lg:px-8">
                    <slot></slot>
                </div>
            </main>
        </div>
    </div>
    <notification-panel :show="showingNotificationPanel" @close="closeNotificationPanel"/>
</template>

<script>
import {defineComponent, ref} from 'vue'
import {Head, Link} from '@inertiajs/inertia-vue3'
import {Dialog, DialogOverlay, Menu, MenuButton, MenuItems, TransitionChild, TransitionRoot} from '@headlessui/vue'
import {
    ArchiveIcon,
    BellIcon,
    CalendarIcon,
    CogIcon,
    CollectionIcon,
    DocumentReportIcon,
    DocumentSearchIcon,
    DocumentTextIcon,
    HomeIcon,
    LocationMarkerIcon,
    MenuAlt2Icon,
    SaveIcon,
    SearchIcon,
    TicketIcon,
    UsersIcon,
    XIcon,
    CubeIcon,
    CubeTransparentIcon,
    OfficeBuildingIcon,
    ColorSwatchIcon
} from '@heroicons/vue/outline'
import NotificationPanel from '@/Layouts/NotificationPanel'

const navigations = {
    administrator: {
        menu: [
            {name: 'Dashboard', href: 'dashboard', icon: HomeIcon},
            {name: 'Area', href: 'areas.index', icon: LocationMarkerIcon},
            {name: 'Sub Area', href: 'subareas.index', icon: OfficeBuildingIcon},
            {name: 'Periode', href: 'periods.index', icon: CalendarIcon},
            {name: 'Batch', href: 'batches.index', icon: TicketIcon},
            {name: 'Material Master', href: 'materials.index', icon: ArchiveIcon},
            {name: 'FG Master', href: 'products.index', icon: CubeIcon},
            {name: 'FG to Material', href: 'products.materials.index', icon: CubeTransparentIcon},
            {name: 'FG Material Breakdown', href: 'products.breakdowns.index', icon: ColorSwatchIcon},
            {name: 'Actual Stock', href: 'stocks.actuals.index', icon: CollectionIcon},
            {name: 'Book Stock', href: 'stocks.books.index', icon: CollectionIcon},
            {name: 'PID', href: 'pids.index', icon: DocumentTextIcon},
            // {name: 'PID Detail', href: 'pids.details.index', icon: DocumentSearchIcon},
            {name: 'Final Summary', href: 'summaries.index', icon: DocumentReportIcon}
        ],
        manage: [
            {name: 'Pegawai', href: 'employees.index', icon: UsersIcon},
            {name: 'Pengaturan', href: 'profile.show', icon: CogIcon}
        ]
    },
    operator: {
        menu: [
            {name: 'Entry Stock', href: 'stocks.create', icon: SaveIcon, currents: ['stocks.sku.create']},
            {name: 'Hasil Stock', href: 'stocks.index', icon: DocumentTextIcon}
        ],
        manage: [
            {name: 'Pengaturan', href: 'profile.show', icon: CogIcon}
        ]
    }
}

export default defineComponent({
    props: {
        title: String
    },
    components: {
        NotificationPanel,
        Head,
        Link,
        Dialog,
        DialogOverlay,
        Menu,
        MenuButton,
        MenuItems,
        TransitionChild,
        TransitionRoot,
        BellIcon,
        MenuAlt2Icon,
        SearchIcon,
        XIcon,
        HomeIcon,
        CalendarIcon,
        CollectionIcon,
        DocumentTextIcon,
        DocumentReportIcon,
        UsersIcon,
        CogIcon,
        LocationMarkerIcon,
        ArchiveIcon,
        DocumentSearchIcon,
        TicketIcon,
        SaveIcon,
        CubeIcon,
        CubeTransparentIcon,
        OfficeBuildingIcon,
        ColorSwatchIcon
    },
    setup() {
        const sidebarOpen = ref(false)
        return {
            sidebarOpen
        }
    },
    data() {
        return {
            menuNavigations: this.$page.props.user.role_id === 1 ? navigations.administrator.menu : navigations.operator.menu,
            manageNavigations: this.$page.props.user.role_id === 1 ? navigations.administrator.manage : navigations.operator.manage,
            showingNotificationPanel: false
        }
    },
    methods: {
        logout() {
            this.$inertia.post(route('logout'), {}, {
                replace: false,
                preserveState: false,
                preserveScroll: false
            })
        },
        showNotificationPanel() {
            this.showingNotificationPanel = true
        },
        closeNotificationPanel() {
            this.showingNotificationPanel = false
        }
    }
})
</script>
