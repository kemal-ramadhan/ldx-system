const fs = require('fs');
const file = 'resources/js/pages/locations/Locations.vue';
let content = fs.readFileSync(file, 'utf8');

// 1. Add imports
content = content.replace("import { ref, watch } from 'vue';",
`import { ref, watch, computed } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';`);

// 2. Add methods
const methods = `
const deleteConfirmation = ref<{ isOpen: boolean, type: 'single' | 'bulk', location: any | null }>({
    isOpen: false,
    type: 'single',
    location: null,
});

const deleteLocation = (loc: any) => {
    deleteConfirmation.value = {
        isOpen: true,
        type: 'single',
        location: loc,
    };
};

const selectedIds = ref<number[]>([]);

const isAllSelected = computed(() => {
    return props.locations.data.length > 0 && selectedIds.value.length === props.locations.data.length;
});

const toggleAll = (event: Event) => {
    const isChecked = (event.target as HTMLInputElement).checked;
    if (isChecked) {
        selectedIds.value = props.locations.data.map((loc: any) => loc.id);
    } else {
        selectedIds.value = [];
    }
};

const bulkDelete = () => {
    if (selectedIds.value.length === 0) return;
    deleteConfirmation.value = {
        isOpen: true,
        type: 'bulk',
        location: null,
    };
};

const confirmDelete = () => {
    if (deleteConfirmation.value.type === 'single' && deleteConfirmation.value.location) {
        router.delete(\`/admin/locations/\${deleteConfirmation.value.location.id}\`, {
            onSuccess: () => {
                deleteConfirmation.value.isOpen = false;
            }
        });
    } else if (deleteConfirmation.value.type === 'bulk') {
        router.delete('/admin/locations/bulk-destroy', {
            data: { ids: selectedIds.value },
            onSuccess: () => {
                selectedIds.value = [];
                deleteConfirmation.value.isOpen = false;
            },
        });
    }
};

defineOptions({`;

content = content.replace("defineOptions({", methods);

// 3. Add bulk delete button
const bulkDeleteBtn = `
            <button
                v-if="selectedIds.length > 0"
                @click="bulkDelete"
                class="w-full inline-flex items-center justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
            >
                <Trash2 class="w-4 h-4 mr-2" />
                Delete Selected ({{ selectedIds.length }})
            </button>
            <Link
                href="/admin/locations/create"`;

content = content.replace(`<Link
                href="/admin/locations/create"`, bulkDeleteBtn);

// 4. Update table header
const thead = `
                    <th class="px-4 py-2 w-10">
                        <input
                            type="checkbox"
                            :checked="isAllSelected"
                            @change="toggleAll"
                            class="rounded border-gray-300 text-primary focus:ring-primary h-4 w-4"
                        />
                    </th>
                    <th class="px-4 py-2">Code</th>`;

content = content.replace('<th class="px-4 py-2">Code</th>', thead);

// 5. Update table row
const tr = `
                    <td class="px-4 py-2">
                        <input
                            type="checkbox"
                            v-model="selectedIds"
                            :value="location.id"
                            class="rounded border-gray-300 text-primary focus:ring-primary h-4 w-4"
                        />
                    </td>
                    <td class="px-4 py-2">{{ location.code }}</td>`;
content = content.replace('<td class="px-4 py-2">{{ location.code }}</td>', tr);

// 6. Add delete icon to actions
const deleteIcon = `
                        <button
                            @click="deleteLocation(location)"
                            class="text-red-500 hover:underline"
                        >
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </td>`;
content = content.replace('</td>', deleteIcon);

// 7. Add Dialog
const dialog = `    </div>

    <Dialog :open="deleteConfirmation.isOpen" @update:open="(val) => deleteConfirmation.isOpen = val">
        <DialogContent>
            <DialogHeader class="space-y-3">
                <DialogTitle>
                    {{ deleteConfirmation.type === 'single' ? 'Are you sure you want to delete this location?' : 'Are you sure you want to delete selected locations?' }}
                </DialogTitle>
                <DialogDescription>
                    <template v-if="deleteConfirmation.type === 'single'">
                        You are about to delete location <strong>{{ deleteConfirmation.location?.name }}</strong>. 
                    </template>
                    <template v-else>
                        You are about to delete <strong>{{ selectedIds.length }}</strong> locations.
                    </template>
                    <br><br>
                    This action will hide the data but keep it in the history.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="gap-2">
                <Button variant="secondary" @click="deleteConfirmation.isOpen = false">Cancel</Button>
                <Button variant="destructive" @click="confirmDelete">Delete</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>`;

content = content.replace('    </div>\n</template>', dialog);

fs.writeFileSync(file, content);
console.log('Done replacing content.');
