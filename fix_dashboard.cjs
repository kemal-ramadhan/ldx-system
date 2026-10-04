const fs = require('fs');

let content = fs.readFileSync('resources/js/pages/dashboard/Client.vue', 'utf8');

// Fix the " block> to block">
content = content.replace(/" block>/g, ' block">');

// Fix closing tags for Racks Card
content = content.replace(
    /(\{\{\s*stats\.racks\.total_units\s*\}\}\s*Units\s*<\/span>\s*<\/div>\s*)<\/div>/g,
    '$1</Link>'
);

// Devices Card
content = content.replace(
    /(\{\{\s*stats\.devices\.by_type\.length\s*\}\}\s*types\s*<\/span>\s*<\/div>\s*)<\/div>/g,
    '$1</Link>'
);

// Services Card
content = content.replace(
    /(Total:\s*\{\{\s*stats\.services\.total\s*\}\}\s*<\/span>\s*<\/div>\s*)<\/div>/g,
    '$1</Link>'
);

// Invoices Card
content = content.replace(
    /(\{\{\s*stats\.invoices\.overdue\s*\}\}\s*Overdue\s*<\/span>\s*<\/div>\s*)<\/div>/g,
    '$1</Link>'
);

// Total Paid Amount
content = content.replace(
    /(M21 12a9 9 0 11-18 0 9 9 0 0118 0z" \/>\s*<\/svg>\s*<\/div>\s*<\/div>\s*)<\/div>/g,
    '$1</Link>'
);

// Outstanding Amount
content = content.replace(
    /(\{\{\s*stats\.invoices\.upcoming_due\s*\}\}\s*due in 7 days\s*<\/span>\s*<\/div>\s*)<\/div>/g,
    '$1</Link>'
);

// Open Tickets
content = content.replace(
    /(Total:\s*\{\{\s*stats\.tickets\.total\s*\}\}\s*<\/span>\s*<\/div>\s*)<\/div>/g,
    '$1</Link>'
);

// Total Racks Card
content = content.replace(
    /(From\s*\{\{\s*stats\.racks\.total\s*\}\}\s*racks\s*<\/span>\s*<\/div>\s*)<\/div>/g,
    '$1</Link>'
);

// Ticket Status Distribution
content = content.replace(
    /(No ticket data available<\/p>\s*<\/div>\s*)<\/div>/g,
    '$1</Link>'
);

fs.writeFileSync('resources/js/pages/dashboard/Client.vue', content);
