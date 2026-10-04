const fs = require('fs');

let content = fs.readFileSync('resources/js/pages/dashboard/Client.vue', 'utf8');

function replaceCard(content, startMarker, href) {
    const regex = new RegExp(`(${startMarker}\\s*)<div(\\s*\\n\\s*class="[^"]*transition-all[^"]*")`, 'g');
    content = content.replace(regex, `$1<Link href="${href}" $2 block`);
    return content;
}

content = replaceCard(content, "<!-- Racks Card -->", "/client/racks");
content = replaceCard(content, "<!-- Devices Card -->", "/client/devices");
content = replaceCard(content, "<!-- Services Card -->", "/client/services");
content = replaceCard(content, "<!-- Invoices Card -->", "/client/invoices");
content = replaceCard(content, "<!-- Total Paid Amount -->", "/client/invoices");
content = replaceCard(content, "<!-- Outstanding Amount -->", "/client/invoices");
content = replaceCard(content, "<!-- Open Tickets -->", "/client/tickets");
content = replaceCard(content, "<!-- Total Racks Card -->", "/client/racks");

content = content.replace(
    '<!-- Ticket Status Distribution -->\n            <div\n                class="rounded-[32px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6">',
    '<!-- Ticket Status Distribution -->\n            <Link href="/client/tickets"\n                class="block rounded-[32px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6 hover:shadow-md transition-shadow">'
);

content = content.replace('href="/support/tickets"', 'href="/client/tickets"');
content = content.replace('href="/billings/invoices"', 'href="/client/invoices"');

content = content.replace(
    '<div v-for="ticket in stats.recent.tickets" :key="ticket.id" \n                         class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50">',
    '<Link v-for="ticket in stats.recent.tickets" :key="ticket.id" :href="`/client/tickets/${ticket.id}`"\n                         class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">'
);

content = content.replace(
    '<div v-for="invoice in stats.recent.invoices" :key="invoice.id" \n                         class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50">',
    '<Link v-for="invoice in stats.recent.invoices" :key="invoice.id" :href="`/client/invoices/${invoice.id}`"\n                         class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">'
);

fs.writeFileSync('resources/js/pages/dashboard/Client.vue', content);
