<template>
    <app-layout title="Dashboard">
        <grid-header>
            <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
        </grid-header>
        <div class="border-4 border-dashed border-gray-200 rounded-lg h-96"/>
    </app-layout>
</template>

<script>
import {defineComponent} from "vue"
import AppLayout from "@/Layouts/AppLayout.vue"
import GridHeader from "@/Layouts/GridHeader.vue"

export default defineComponent({
    components: {
        AppLayout,
        GridHeader
    }
})
</script>
