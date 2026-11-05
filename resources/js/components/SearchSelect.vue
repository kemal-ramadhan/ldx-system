<script setup lang="ts">
import { computed } from 'vue';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';

/**
 * Searchable select that binds a primitive value (e.g. id),
 * so it can replace a native <select v-model="form.x_id">.
 */
const props = withDefaults(
    defineProps<{
        options: any[];
        label?: string;
        valueKey?: string;
        placeholder?: string;
        searchable?: boolean;
        customLabel?: (option: any) => string;
    }>(),
    {
        label: 'name',
        valueKey: 'id',
        placeholder: 'Search...',
        searchable: true,
    },
);

const model = defineModel<string | number | null>();

const emit = defineEmits<{
    change: [option: any];
}>();

const selected = computed({
    get: () =>
        props.options.find(
            (option) => String(option[props.valueKey]) === String(model.value),
        ) ?? null,
    set: (option: any) => {
        model.value = option ? option[props.valueKey] : '';
        emit('change', option);
    },
});

const optionLabel = (option: any) =>
    props.customLabel ? props.customLabel(option) : String(option?.[props.label] ?? '');
</script>

<template>
    <Multiselect v-model="selected" :options="options" :track-by="valueKey" :label="label"
        :custom-label="optionLabel" :placeholder="placeholder" :searchable="searchable" :close-on-select="true"
        :allow-empty="false" :show-labels="false" class="search-select">
        <template #noResult>
            <span class="text-sm text-muted-foreground">Data tidak ditemukan.</span>
        </template>
        <template #noOptions>
            <span class="text-sm text-muted-foreground">Tidak ada data.</span>
        </template>
    </Multiselect>
</template>

<style>
.search-select.multiselect {
    min-height: 38px;
    font-size: 0.875rem;
}

.search-select .multiselect__tags {
    min-height: 38px;
    border-radius: 0.5rem;
    border-color: #e5e7eb;
    padding: 8px 40px 0 12px;
    font-size: 0.875rem;
}

.search-select .multiselect__single,
.search-select .multiselect__input,
.search-select .multiselect__placeholder {
    font-size: 0.875rem;
    margin-bottom: 8px;
    padding-left: 0;
}

.search-select .multiselect__placeholder {
    color: #9ca3af;
    padding-top: 0;
}

.search-select .multiselect__select {
    height: 36px;
}

.search-select .multiselect__option {
    font-size: 0.875rem;
    min-height: 36px;
    padding: 10px 12px;
}

.search-select .multiselect__option--highlight {
    background: #f3f4f6;
    color: #111827;
}

.search-select .multiselect__option--selected,
.search-select .multiselect__option--selected.multiselect__option--highlight {
    background: #e5e7eb;
    color: #111827;
    font-weight: 600;
}

.search-select.multiselect--active .multiselect__tags {
    border-color: var(--primary, #1f3fb6);
}

.search-select .multiselect__content-wrapper {
    border-color: #e5e7eb;
    border-radius: 0 0 0.5rem 0.5rem;
    z-index: 50;
}

html.dark .search-select .multiselect__tags,
html.dark .search-select .multiselect__input,
html.dark .search-select .multiselect__single,
html.dark .search-select .multiselect__content-wrapper {
    background-color: #1f2937;
    border-color: #374151;
    color: #f9fafb;
}

html.dark .search-select .multiselect__option {
    color: #f9fafb;
}

html.dark .search-select .multiselect__option--highlight,
html.dark .search-select .multiselect__option--selected {
    background-color: #374151;
    color: #ffffff;
}
</style>
