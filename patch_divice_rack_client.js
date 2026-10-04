const fs = require('fs');
let file = fs.readFileSync('resources/js/components/vanila/menu/tabs/DiviceRackClient.vue', 'utf8');

file = file.replace("import {\n    Plus,\n    Search,\n    Trash2,\n    Pencil,\n    X\n} from 'lucide-vue-next'", "import {\n    Plus,\n    Search,\n    Trash2,\n    Pencil,\n    X,\n    Eye,\n    Network,\n    Cable,\n    ArrowRight\n} from 'lucide-vue-next'");

file = file.replace(
"const search = ref('')",
"const search = ref('')\n\nconst openPorts = ref(false)\nconst selectedDevice = ref<any>(null)\n\nconst openViewPorts = (device: any) => {\n    selectedDevice.value = device\n    openPorts.value = true\n}"
);

file = file.replace(
"<th class=\"px-4 py-3\">\n                            Status\n                        </th>",
"<th class=\"px-4 py-3\">\n                            Status\n                        </th>\n\n                        <th class=\"px-4 py-3 text-right\">\n                            Actions\n                        </th>"
);

file = file.replace(
"<!-- STATUS -->\n                        <td class=\"px-4 py-3\">\n\n                            <span :class=\"[\n                                'inline-flex rounded-full px-3 py-1 text-xs font-medium',\n                                device.status === 'active'\n                                    ? 'bg-green-100 text-green-700'\n                                    : device.status === 'maintenance'\n                                        ? 'bg-yellow-100 text-yellow-700'\n                                        : 'bg-gray-100 text-gray-700'\n                            ]\">\n                                {{ device.status }}\n                            </span>\n\n                        </td>",
"<!-- STATUS -->\n                        <td class=\"px-4 py-3\">\n\n                            <span :class=\"[\n                                'inline-flex rounded-full px-3 py-1 text-xs font-medium',\n                                device.status === 'active'\n                                    ? 'bg-green-100 text-green-700'\n                                    : device.status === 'maintenance'\n                                        ? 'bg-yellow-100 text-yellow-700'\n                                        : 'bg-gray-100 text-gray-700'\n                            ]\">\n                                {{ device.status }}\n                            </span>\n\n                        </td>\n\n                        <!-- ACTIONS -->\n                        <td class=\"px-4 py-3 text-right\">\n                            <button @click=\"openViewPorts(device)\"\n                                class=\"inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-medium hover:bg-gray-50 text-gray-700\">\n                                <Network class=\"h-3.5 w-3.5\" />\n                                View Ports\n                            </button>\n                        </td>"
);

const modalHtml = `
    <!-- Modal Ports -->
    <div v-if="openPorts && selectedDevice" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="openPorts = false"></div>

        <!-- Modal -->
        <div class="relative w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">
            <!-- Header -->
            <div class="flex items-center justify-between border-b px-6 py-5">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40">
                            <Network class="h-5 w-5" />
                        </div>
                        <div>
                            <h2 class="text-base font-semibold">
                                View Device Ports
                            </h2>
                            <p class="mt-0.5 text-xs text-muted-foreground">
                                {{ selectedDevice.divice_name }}
                            </p>
                        </div>
                    </div>
                </div>
                <button type="button" @click="openPorts = false" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <!-- Summary -->
            <div class="border-b bg-gray-50/70 px-6 py-4 dark:bg-gray-800/40">
                <div class="flex items-center gap-6">
                    <div>
                        <p class="text-xs text-muted-foreground">Total Ports</p>
                        <p class="mt-1 text-lg font-semibold">{{ selectedDevice.ports?.length || 0 }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Available</p>
                        <p class="mt-1 text-lg font-semibold text-green-600">
                            {{ selectedDevice.ports?.filter((port: any) => port.status === 'available').length || 0 }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Connected</p>
                        <p class="mt-1 text-lg font-semibold text-blue-600">
                            {{ selectedDevice.ports?.filter((port: any) => port.status === 'connected').length || 0 }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Port List -->
            <div class="max-h-[60vh] overflow-y-auto px-6 py-5">
                <!-- Empty -->
                <div v-if="!selectedDevice.ports?.length" class="flex flex-col items-center justify-center rounded-2xl border border-dashed py-16 text-center">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                        <Network class="h-6 w-6 text-gray-400" />
                    </div>
                    <h3 class="text-sm font-semibold">No ports configured</h3>
                </div>

                <!-- Table -->
                <div v-else class="overflow-hidden rounded-xl border">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">Port</th>
                                <th class="px-4 py-3 text-left font-medium">Type</th>
                                <th class="px-4 py-3 text-left font-medium">Connector</th>
                                <th class="px-4 py-3 text-left font-medium">Status</th>
                                <th class="px-4 py-3 text-left font-medium">Connection</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="port in selectedDevice.ports" :key="port.id" class="hover:bg-gray-50/70 dark:hover:bg-gray-800/50">
                                <!-- Port -->
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                            <Cable class="h-4 w-4" />
                                        </div>
                                        <div>
                                            <p class="font-medium">{{ port.port_name }}</p>
                                            <p v-if="port.port_number" class="text-xs text-muted-foreground">#{{ port.port_number }}</p>
                                        </div>
                                    </div>
                                </td>
                                <!-- Type -->
                                <td class="px-4 py-3"><span class="capitalize">{{ port.port_type?.replace('_', ' ') }}</span></td>
                                <!-- Connector -->
                                <td class="px-4 py-3 text-muted-foreground">{{ port.connector_type || '-' }}</td>
                                <!-- Status -->
                                <td class="px-4 py-3">
                                    <span v-if="port.status === 'available'" class="inline-flex rounded-full bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700 dark:bg-green-950/30 dark:text-green-400">Available</span>
                                    <span v-else-if="port.status === 'connected'" class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-950/30 dark:text-blue-400">Connected</span>
                                    <span v-else-if="port.status === 'maintenance'" class="inline-flex rounded-full bg-yellow-50 px-2.5 py-1 text-xs font-medium text-yellow-700 dark:bg-yellow-950/30 dark:text-yellow-400">Maintenance</span>
                                    <span v-else class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-400 capitalize">{{ port.status }}</span>
                                </td>
                                <!-- Connection Detail -->
                                <td class="px-4 py-3">
                                    <div v-if="port.active_source_cross_connect" class="flex flex-col text-xs">
                                        <div class="flex items-center gap-1 text-gray-600">
                                            <ArrowRight class="w-3 h-3 text-blue-500" />
                                            <span class="font-medium truncate max-w-[150px]" :title="port.active_source_cross_connect.destination_port?.device?.divice_name">
                                                {{ port.active_source_cross_connect.destination_port?.device?.divice_name || 'Unknown Device' }}
                                            </span>
                                        </div>
                                        <div class="text-gray-400 ml-4">
                                            Port: {{ port.active_source_cross_connect.destination_port?.port_name || '?' }}
                                        </div>
                                    </div>
                                    <div v-else-if="port.active_destination_cross_connect" class="flex flex-col text-xs">
                                        <div class="flex items-center gap-1 text-gray-600">
                                            <ArrowRight class="w-3 h-3 text-green-500" />
                                            <span class="font-medium truncate max-w-[150px]" :title="port.active_destination_cross_connect.source_port?.device?.divice_name">
                                                {{ port.active_destination_cross_connect.source_port?.device?.divice_name || 'Unknown Device' }}
                                            </span>
                                        </div>
                                        <div class="text-gray-400 ml-4">
                                            Port: {{ port.active_destination_cross_connect.source_port?.port_name || '?' }}
                                        </div>
                                    </div>
                                    <div v-else class="text-gray-400 text-xs">
                                        -
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
`;

file = file.replace("</template>", modalHtml);

fs.writeFileSync('resources/js/components/vanila/menu/tabs/DiviceRackClient.vue', file);
console.log('patched');
