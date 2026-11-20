<script setup lang="ts">
import { computed } from 'vue'
import { AlertTriangle, CalendarClock, Info } from 'lucide-vue-next'

/**
 * Explains what the scheduler will do with the "Next Due Date" being entered:
 *  - passed      -> NOT invoiced automatically (service flagged "Perlu dicek")
 *  - within H-7  -> invoice is created & e-mailed at the next run (08:00)
 *  - later       -> invoice is created automatically 7 days before
 * Mirrors app/Console/Commands/GenerateInvoices.php.
 */
const props = defineProps<{
    nextDueDate?: string | null
    status?: string | null
}>()

const DAYS_BEFORE = 7

const pad = (n: number) => String(n).padStart(2, '0')
const toYmd = (d: Date) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
const pretty = (ymd: string) => {
    const [y, m, d] = ymd.split('-').map(Number)
    return `${d} ${MONTHS[m - 1]} ${y}`
}

const hint = computed(() => {
    const due = props.nextDueDate?.slice(0, 10)
    if (!due) return null

    const today = toYmd(new Date())
    const [y, m, d] = due.split('-').map(Number)
    const autoDate = toYmd(new Date(y, m - 1, d - DAYS_BEFORE))
    const active = props.status === 'active'

    if (due < today) {
        return {
            level: 'warn',
            text: `Tanggal ini sudah lewat. Sistem tidak akan membuat invoice otomatis untuk ${pretty(due)}` +
                (active ? ' — service akan ditandai "Perlu dicek".' : '.') +
                ' Isi dengan jatuh tempo tagihan pertama yang BELUM pernah ditagih ke client.',
        }
    }

    if (autoDate <= today) {
        return {
            level: 'soon',
            text: active
                ? `Invoice untuk jatuh tempo ${pretty(due)} akan langsung dibuat dan dikirim ke email client pada proses berikutnya (pukul 08:00). Pastikan periode ini belum pernah ditagih.`
                : `Saat service aktif, invoice untuk jatuh tempo ${pretty(due)} langsung dibuat dan dikirim ke client. Pastikan periode ini belum pernah ditagih.`,
        }
    }

    return {
        level: 'info',
        text: `Invoice dibuat & dikirim otomatis tanggal ${pretty(autoDate)} (7 hari sebelum jatuh tempo)` +
            (active ? '.' : ', setelah service berstatus Active.'),
    }
})
</script>

<template>
    <div v-if="hint" :class="[
        'flex items-start gap-2 rounded-lg px-3 py-2 text-xs',
        hint.level === 'warn'
            ? 'border border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-300'
            : hint.level === 'soon'
                ? 'border border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300'
                : 'bg-muted text-muted-foreground',
    ]">
        <AlertTriangle v-if="hint.level === 'warn'" class="mt-0.5 h-3.5 w-3.5 shrink-0" />
        <CalendarClock v-else-if="hint.level === 'soon'" class="mt-0.5 h-3.5 w-3.5 shrink-0" />
        <Info v-else class="mt-0.5 h-3.5 w-3.5 shrink-0" />
        <span>{{ hint.text }}</span>
    </div>
    <p v-else class="text-xs text-muted-foreground">
        Jatuh tempo tagihan pertama yang belum pernah ditagih ke client (bukan tanggal mulai service).
    </p>
</template>
