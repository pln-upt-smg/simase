<template>
    <app-layout title="Dashboard">
        <div class="border-4 border-dashed border-gray-200 rounded-lg h-96"/>
        <div class="border-4 border-dashed border-gray-200 rounded-lg h-96 mt-6"/>
        <div class="border-4 border-dashed border-gray-200 rounded-lg h-96 mt-6"/>
    </app-layout>
</template>

<script>
import {defineComponent} from 'vue'
import AppLayout from '@/Layouts/AppLayout'

export default defineComponent({
    components: {
        AppLayout
    }
})
</script>
