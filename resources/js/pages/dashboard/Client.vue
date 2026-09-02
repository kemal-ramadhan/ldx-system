<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { 
    Chart as ChartJS, 
    CategoryScale, 
    LinearScale, 
    PointElement, 
    LineElement, 
    Title, 
    Tooltip, 
    Legend,
    BarElement,
    ArcElement
} from 'chart.js'
import { Bar, Doughnut, Line } from 'vue-chartjs'

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    BarElement,
    ArcElement
)

const props = defineProps<{
    title: string
    user: any
    stats: {
        racks: {
            total: number
            active: number
            total_units: number
        }
        devices: {
            total: number
            by_type: Array<{ divice_type: string, total: number }>
        }
        services: {
            total: number
            active: number
            by_status: Array<{ status: string, total: number }>
        }
        invoices: {
            total: number
            paid: number
            overdue: number
            waiting: number
            total_paid: number
            total_outstanding: number
            upcoming_due: number
        }
        tickets: {
            total: number
            open: number
            by_status: Array<{ status: string, total: number }>
        }
        trends: {
            monthly_racks: Array<{ month: number, total: number }>
        }
        recent: {
            tickets: Array<any>
            invoices: Array<any>
        }
    }
}>()

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '#' }
        ],
    },
})

// Chart configurations
const deviceTypeChartData = computed(() => {
    const labels = props.stats.devices.by_type.map(item => item.divice_type || 'Unknown')
    const data = props.stats.devices.by_type.map(item => item.total)
    return {
        labels,
        datasets: [{
            data,
            backgroundColor: ['#3b82f6', '#8b5cf6', '#06b6d4', '#10b981', '#f59e0b', '#ef4444'],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    }
})

const ticketStatusChartData = computed(() => {
    const labels = props.stats.tickets.by_status.map(item => item.status)
    const data = props.stats.tickets.by_status.map(item => item.total)
    const colors = {
        open: '#ef4444',
        on_progress: '#f59e0b',
        waiting_customer: '#8b5cf6',
        waiting_technician: '#06b6d4',
        waiting_vendor: '#3b82f6',
        resolved: '#10b981',
        cancelled: '#6b7280',
        closed: '#6b7280'
    }
    return {
        labels,
        datasets: [{
            data,
            backgroundColor: labels.map(label => colors[label as keyof typeof colors] || '#6b7280'),
            borderWidth: 2,
            borderColor: '#fff'
        }]
    }
})

const monthlyRackTrendData = computed(() => {
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
    const labels = props.stats.trends.monthly_racks.map(item => monthNames[item.month - 1])
    const data = props.stats.trends.monthly_racks.map(item => item.total)
    return {
        labels,
        datasets: [{
            label: 'Racks Added',
            data,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            fill: true,
            tension: 0.4
        }]
    }
})

// Helper to format currency
const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount)
}

// Helper to get status color
const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        paid: 'bg-emerald-100 text-emerald-800',
        overdue: 'bg-red-100 text-red-800',
        waiting: 'bg-yellow-100 text-yellow-800',
        sent: 'bg-blue-100 text-blue-800',
        cancelled: 'bg-gray-100 text-gray-800',
        processed: 'bg-purple-100 text-purple-800',
        rejected: 'bg-red-100 text-red-800',
        active: 'bg-emerald-100 text-emerald-800',
        inactive: 'bg-gray-100 text-gray-800',
        pending: 'bg-yellow-100 text-yellow-800',
        suspended: 'bg-orange-100 text-orange-800',
        terminated: 'bg-red-100 text-red-800',
        open: 'bg-red-100 text-red-800',
        on_progress: 'bg-yellow-100 text-yellow-800',
        waiting_customer: 'bg-purple-100 text-purple-800',
        waiting_technician: 'bg-blue-100 text-blue-800',
        waiting_vendor: 'bg-indigo-100 text-indigo-800',
        resolved: 'bg-emerald-100 text-emerald-800',
        closed: 'bg-gray-100 text-gray-800'
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}
</script>

<template>
    <Head :title="title" />

    <div class="p-6 space-y-6 dark:bg-black">
        <!-- Welcome Section -->
        <div class="grid">
            <div class="col-span-12">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Hallo {{ user.name }}</h1>
                <label class="text-sm text-gray-500 dark:text-gray-400">Welcome to your dashboard, {{ user.name }}!</label>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Racks Card -->
            <div
                class="group relative overflow-hidden rounded-[32px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Total Rack
                        </h3>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {{ stats.racks.total }}
                        </h2>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-50 dark:bg-blue-900/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-3">
                    <div class="flex items-center gap-2 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 px-3 py-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        </svg>
                        <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">
                            {{ stats.racks.active }} Active
                        </span>
                    </div>
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        {{ stats.racks.total_units }} Units
                    </span>
                </div>
            </div>

            <!-- Devices Card -->
            <div
                class="group relative overflow-hidden rounded-[32px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Total Devices
                        </h3>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {{ stats.devices.total }}
                        </h2>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-50 dark:bg-purple-900/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-3">
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        {{ stats.devices.by_type.length }} types
                    </span>
                </div>
            </div>

            <!-- Services Card -->
            <div
                class="group relative overflow-hidden rounded-[32px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Active Services
                        </h3>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {{ stats.services.active }}
                        </h2>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 dark:bg-emerald-900/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-3">
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        Total: {{ stats.services.total }}
                    </span>
                </div>
            </div>

            <!-- Invoices Card -->
            <div
                class="group relative overflow-hidden rounded-[32px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Total Invoices
                        </h3>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {{ stats.invoices.total }}
                        </h2>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-orange-50 dark:bg-orange-900/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-3">
                    <div class="flex items-center gap-2 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 px-3 py-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        </svg>
                        <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">
                            {{ stats.invoices.paid }} Paid
                        </span>
                    </div>
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        {{ stats.invoices.overdue }} Overdue
                    </span>
                </div>
            </div>
        </div>

        <!-- Second Row Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Paid Amount -->
            <div
                class="group relative overflow-hidden rounded-[32px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Total Paid Amount
                        </h3>
                        <h2 class="mt-2 text-xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {{ formatCurrency(stats.invoices.total_paid) }}
                        </h2>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 dark:bg-emerald-900/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Outstanding Amount -->
            <div
                class="group relative overflow-hidden rounded-[32px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Outstanding Amount
                        </h3>
                        <h2 class="mt-2 text-xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {{ formatCurrency(stats.invoices.total_outstanding) }}
                        </h2>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-50 dark:bg-red-900/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-3">
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        {{ stats.invoices.upcoming_due }} due in 7 days
                    </span>
                </div>
            </div>

            <!-- Open Tickets -->
            <div
                class="group relative overflow-hidden rounded-[32px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Open Tickets
                        </h3>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {{ stats.tickets.open }}
                        </h2>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-50 dark:bg-red-900/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-3">
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        Total: {{ stats.tickets.total }}
                    </span>
                </div>
            </div>

            <!-- Total Racks Card -->
            <div
                class="group relative overflow-hidden rounded-[32px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Total Rented Units
                        </h3>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {{ stats.racks.total_units }}
                        </h2>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-50 dark:bg-indigo-900/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-3">
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        From {{ stats.racks.total }} racks
                    </span>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Device Type Distribution -->
            <div
                class="rounded-[32px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">Device Type Distribution</h3>
                <div class="h-64 flex items-center justify-center">
                    <Doughnut 
                        v-if="stats.devices.by_type.length > 0" 
                        :data="deviceTypeChartData" 
                        :options="{ responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }"
                    />
                    <p v-else class="text-slate-500 dark:text-slate-400">No device data available</p>
                </div>
            </div>

            <!-- Ticket Status Distribution -->
            <div
                class="rounded-[32px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">Ticket Status Distribution</h3>
                <div class="h-64 flex items-center justify-center">
                    <Doughnut 
                        v-if="stats.tickets.by_status.length > 0" 
                        :data="ticketStatusChartData" 
                        :options="{ responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }"
                    />
                    <p v-else class="text-slate-500 dark:text-slate-400">No ticket data available</p>
                </div>
            </div>
        </div>

        <!-- Monthly Trend -->
        <div
            class="rounded-[32px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">Monthly Rack Addition Trend</h3>
            <div class="h-64">
                <Line 
                    v-if="stats.trends.monthly_racks.length > 0" 
                    :data="monthlyRackTrendData" 
                    :options="{ 
                        responsive: true, 
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { 
                            y: { beginAtZero: true }
                        }
                    }"
                />
                <p v-else class="text-slate-500 dark:text-slate-400 text-center py-8">No trend data available</p>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Tickets -->
            <div
                class="rounded-[32px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Recent Tickets</h3>
                    <Link href="/support/tickets" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                        View All
                    </Link>
                </div>
                <div class="space-y-3">
                    <div v-for="ticket in stats.recent.tickets" :key="ticket.id" 
                         class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-900 dark:text-white truncate">{{ ticket.subject }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ ticket.code }} • {{ new Date(ticket.created_at).toLocaleDateString() }}
                            </p>
                        </div>
                        <span :class="['px-2.5 py-1 rounded-full text-xs font-medium', getStatusColor(ticket.status)]">
                            {{ ticket.status.replace('_', ' ') }}
                        </span>
                    </div>
                    <p v-if="stats.recent.tickets.length === 0" class="text-slate-500 dark:text-slate-400 text-center py-4">
                        No recent tickets
                    </p>
                </div>
            </div>

            <!-- Recent Invoices -->
            <div
                class="rounded-[32px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Recent Invoices</h3>
                    <Link href="/billings/invoices" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                        View All
                    </Link>
                </div>
                <div class="space-y-3">
                    <div v-for="invoice in stats.recent.invoices" :key="invoice.id" 
                         class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-900 dark:text-white">{{ invoice.invoice_number }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ formatCurrency(invoice.total) }} • {{ new Date(invoice.issue_date).toLocaleDateString() }}
                            </p>
                        </div>
                        <span :class="['px-2.5 py-1 rounded-full text-xs font-medium', getStatusColor(invoice.status)]">
                            {{ invoice.status }}
                        </span>
                    </div>
                    <p v-if="stats.recent.invoices.length === 0" class="text-slate-500 dark:text-slate-400 text-center py-4">
                        No recent invoices                    </p>
                </div>
            </div>
        </div>
    </div>
</template>