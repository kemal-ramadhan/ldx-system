<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'

import {
    DollarSign,
    FileText,
    AlertTriangle,
    RefreshCw,
} from 'lucide-vue-next'

import {
    Chart as ChartJS,
    ArcElement,
    Tooltip,
    Legend,
    CategoryScale,
    LinearScale,
    BarElement,
    PointElement,
    LineElement,
} from 'chart.js'

import { Bar, Pie, Line } from 'vue-chartjs'

ChartJS.register(
    ArcElement,
    Tooltip,
    Legend,
    CategoryScale,
    LinearScale,
    BarElement,
    PointElement,
    LineElement
)

const props = defineProps<{
    title: string
    year: number
    monthlyRevenue: number
    yearlyRevenue: number
    invoiceStats: Record<string, number>
    overdueInvoices: any[]
    upcomingServices: any[]
    recentInvoices: any[]
    revenueChart: number[]
}>()

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '#' }
        ],
    },
})

/**
 * =========================================
 * YEAR FILTER
 * =========================================
 */
const selectedYear = ref(props.year)

const changeYear = () => {
    router.get('/admin/dashboard', {
        year: selectedYear.value
    }, {
        preserveState: true,
        replace: true
    })
}

/**
 * =========================================
 * CHART DATA
 * =========================================
 */
const revenueChartData = {
    labels: [
        'Jan','Feb','Mar','Apr','May','Jun',
        'Jul','Aug','Sep','Oct','Nov','Dec'
    ],
    datasets: [
        {
            label: 'Revenue',
            data: props.revenueChart,
            borderColor: '#111827',
            backgroundColor: 'rgba(17,24,39,0.08)',
            tension: 0.4,
            fill: true,
        }
    ]
}

const invoiceChartData = {
    labels: Object.keys(props.invoiceStats),
    datasets: [
        {
            label: 'Invoices',
            data: Object.values(props.invoiceStats),
            backgroundColor: [
                '#111827',
                '#6b7280',
                '#9ca3af',
                '#d1d5db'
            ],
        }
    ]
}
</script>

<template>

    <Head :title="title" />

    <div class="p-6 space-y-6 bg-gray-50 dark:bg-black min-h-screen">

        <!-- HEADER -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ title }}
                </h1>
                <p class="text-sm text-gray-500">
                    Billing • Revenue • Service Control Center
                </p>
            </div>

            <div class="flex items-center gap-3">

                <select
                    v-model="selectedYear"
                    @change="changeYear"
                    class="rounded-xl border px-3 py-2 text-sm bg-white dark:bg-gray-900">

                    <option :value="2026">2026</option>
                    <option :value="2025">2025</option>
                    <option :value="2024">2024</option>

                </select>

                <button class="flex items-center gap-2 rounded-xl bg-black text-white px-4 py-2 text-sm hover:bg-gray-800">
                    <RefreshCw class="h-4 w-4" />
                    Refresh
                </button>

            </div>

        </div>

        <!-- KPI CARDS -->
        <div class="grid md:grid-cols-3 gap-4">

            <div class="rounded-2xl border bg-white dark:bg-gray-900 p-5 shadow-sm">
                <p class="text-xs text-gray-500">Monthly Revenue</p>
                <h2 class="text-xl font-bold mt-1">
                    Rp {{ Number(monthlyRevenue).toLocaleString('id-ID') }}
                </h2>
                <DollarSign class="h-5 w-5 text-gray-400 mt-2" />
            </div>

            <div class="rounded-2xl border bg-white dark:bg-gray-900 p-5 shadow-sm">
                <p class="text-xs text-gray-500">Yearly Revenue</p>
                <h2 class="text-xl font-bold mt-1">
                    Rp {{ Number(yearlyRevenue).toLocaleString('id-ID') }}
                </h2>
                <DollarSign class="h-5 w-5 text-gray-400 mt-2" />
            </div>

            <div class="rounded-2xl border bg-white dark:bg-gray-900 p-5 shadow-sm">
                <p class="text-xs text-gray-500">Total Invoices</p>
                <h2 class="text-xl font-bold mt-1">
                    {{ Object.values(invoiceStats).reduce((a,b)=>a+b,0) }}
                </h2>
                <FileText class="h-5 w-5 text-gray-400 mt-2" />
            </div>

        </div>

        <!-- CHART SECTION -->
        <div class="grid md:grid-cols-3 gap-6">

            <div class="rounded-2xl border bg-white dark:bg-gray-900 p-5 shadow-sm md:col-span-2">
                <h3 class="font-semibold mb-4 text-gray-900 dark:text-white">
                    Revenue Growth
                </h3>
                <Line :data="revenueChartData" />
            </div>

            <div class="rounded-2xl border bg-white dark:bg-gray-900 p-5 shadow-sm">
                <h3 class="font-semibold mb-4 text-gray-900 dark:text-white">
                    Invoice Status
                </h3>
                <Pie :data="invoiceChartData" />
            </div>

        </div>

        <!-- LOWER SECTION -->
        <div class="grid md:grid-cols-2 gap-6">

            <!-- OVERDUE -->
            <div class="rounded-2xl border bg-white dark:bg-gray-900 p-5 shadow-sm">

                <div class="flex items-center gap-2 text-red-600 font-semibold mb-4">
                    <AlertTriangle class="h-5 w-5" />
                    Overdue Invoices
                </div>

                <div v-if="overdueInvoices.length === 0" class="text-sm text-gray-500">
                    No overdue invoices
                </div>

                <div v-for="inv in overdueInvoices" :key="inv.id"
                    class="flex justify-between py-2 border-b">

                    <div>
                        <p class="font-semibold">{{ inv.invoice_number }}</p>
                        <p class="text-xs text-gray-500">
                            {{ inv.client?.company_name }}
                        </p>
                    </div>

                    <span class="text-red-600 font-bold">
                        Rp {{ Number(inv.total).toLocaleString('id-ID') }}
                    </span>

                </div>

            </div>

            <!-- UPCOMING -->
            <div class="rounded-2xl border bg-white dark:bg-gray-900 p-5 shadow-sm">

                <h3 class="font-semibold mb-4 text-gray-900 dark:text-white">
                    Upcoming Renewals
                </h3>

                <div v-for="service in upcomingServices" :key="service.id"
                    class="flex justify-between py-2 border-b">

                    <div>
                        <p class="font-semibold">{{ service.name }}</p>
                        <p class="text-xs text-gray-500">
                            {{ service.client?.company_name }}
                        </p>
                    </div>

                    <span class="text-gray-600 text-sm">
                        {{ service.next_due_date }}
                    </span>

                </div>

            </div>

        </div>

        <!-- RECENT -->
        <div class="rounded-2xl border bg-white dark:bg-gray-900 p-5 shadow-sm">

            <h3 class="font-semibold mb-4 text-gray-900 dark:text-white">
                Recent Invoices
            </h3>

            <div v-for="inv in recentInvoices" :key="inv.id"
                class="flex justify-between py-2 border-b hover:bg-gray-50 dark:hover:bg-gray-800 px-2 rounded-lg">

                <div>
                    <p class="font-semibold">{{ inv.invoice_number }}</p>
                    <p class="text-xs text-gray-500">{{ inv.status }}</p>
                </div>

                <span class="font-bold">
                    Rp {{ Number(inv.total).toLocaleString('id-ID') }}
                </span>

            </div>

        </div>

    </div>

</template>