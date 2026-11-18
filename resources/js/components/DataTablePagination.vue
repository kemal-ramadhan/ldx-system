<script setup lang="ts">
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'

/**
 * Pagination for admin data tables (Laravel LengthAwarePaginator JSON):
 *  - "Baris per halaman" 10 / 25 / 50 / 100 (default 10, kept in ?per_page)
 *  - numbered pages with "..." so you can jump straight to a page
 *  - "Menampilkan x–y dari z"
 * Filters in the URL are kept (controllers use ->withQueryString()).
 */
interface PaginatorLink {
    url: string | null
    label: string
    active: boolean
}

const props = withDefaults(
    defineProps<{
        paginator: {
            links?: PaginatorLink[]
            from?: number | null
            to?: number | null
            total?: number
            per_page?: number | string
            current_page?: number
            last_page?: number
        } | null | undefined
        options?: number[]
        /** Inertia partial reload keys (e.g. a tab on a detail page) */
        only?: string[]
    }>(),
    { options: () => [10, 25, 50, 100] },
)

const perPage = computed(() => Number(props.paginator?.per_page ?? 10))

// Laravel sends [« Previous, 1, 2, ..., n, Next »]
const pages = computed(() => (props.paginator?.links ?? []).slice(1, -1))
const prev = computed(() => props.paginator?.links?.[0]?.url ?? null)
const next = computed(() => {
    const links = props.paginator?.links ?? []
    return links.length ? links[links.length - 1].url : null
})

const changePerPage = (event: Event) => {
    const value = Number((event.target as HTMLSelectElement).value)
    const query = Object.fromEntries(new URLSearchParams(window.location.search))

    delete query.page
    query.per_page = String(value)

    router.get(window.location.pathname, query, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        ...(props.only ? { only: props.only } : {}),
    })
}

const linkOptions = computed(() => ({
    preserveScroll: true,
    preserveState: true,
    ...(props.only ? { only: props.only } : {}),
}))
</script>

<template>
    <div v-if="paginator" class="flex flex-col gap-3 py-3 text-sm sm:flex-row sm:items-center sm:justify-between">
        <!-- rows per page + range -->
        <div class="flex flex-wrap items-center gap-3 text-muted-foreground">
            <label class="flex items-center gap-2">
                <span>Baris per halaman</span>
                <select :value="perPage" @change="changePerPage"
                    class="rounded-lg border border-input bg-background px-2 py-1.5 text-sm text-foreground dark:scheme-dark">
                    <option v-for="option in options" :key="option" :value="option">{{ option }}</option>
                </select>
            </label>
            <span v-if="paginator.total">
                Menampilkan <strong class="text-foreground">{{ paginator.from ?? 0 }}–{{ paginator.to ?? 0 }}</strong>
                dari <strong class="text-foreground">{{ paginator.total }}</strong>
            </span>
            <span v-else>Tidak ada data</span>
        </div>

        <!-- numbered pages -->
        <nav v-if="(paginator.last_page ?? 1) > 1" class="flex flex-wrap items-center gap-1" aria-label="Pagination">
            <component :is="prev ? Link : 'span'" :href="prev ?? undefined" v-bind="prev ? linkOptions : {}"
                :class="['inline-flex h-8 w-8 items-center justify-center rounded-lg border',
                    prev ? 'hover:bg-muted' : 'cursor-not-allowed opacity-40']" aria-label="Sebelumnya">
                <ChevronLeft class="h-4 w-4" />
            </component>

            <template v-for="(link, index) in pages" :key="`${index}-${link.label}`">
                <span v-if="!link.url" class="inline-flex h-8 min-w-8 items-center justify-center px-1 text-muted-foreground">
                    {{ link.label }}
                </span>
                <Link v-else :href="link.url" v-bind="linkOptions" :aria-current="link.active ? 'page' : undefined"
                    :class="['inline-flex h-8 min-w-8 items-center justify-center rounded-lg border px-2.5 font-medium',
                        link.active ? 'border-primary bg-primary text-primary-foreground' : 'hover:bg-muted']">
                    {{ link.label }}
                </Link>
            </template>

            <component :is="next ? Link : 'span'" :href="next ?? undefined" v-bind="next ? linkOptions : {}"
                :class="['inline-flex h-8 w-8 items-center justify-center rounded-lg border',
                    next ? 'hover:bg-muted' : 'cursor-not-allowed opacity-40']" aria-label="Berikutnya">
                <ChevronRight class="h-4 w-4" />
            </component>
        </nav>
    </div>
</template>
