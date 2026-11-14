import {
    LayoutGrid,
    Users,
    Folder,
    Package,
    WalletCards,
    NotepadText,
    Mail,
} from 'lucide-vue-next';

export const navigation = {
    'super-admin': [
        {
            title: 'Dashboard',
            href: '/admin/dashboard',
            icon: LayoutGrid,
        },
        {
            title: 'Management Admin',
            icon: Users,
            children: [
                {
                    title: 'Data User Admin',
                    href: '/admin/users?type=admin',
                },
            ],
        },
        {
            title: 'Management Client',
            icon: Users,
            children: [
                {
                    title: 'Data User Client',
                    href: '/admin/users?type=client',
                },
                {
                    title: 'Companies',
                    href: '/admin/clients',
                },
            ],
        },
        {
            title: 'Rack Management',
            icon: Folder,

            children: [
                {
                    title: 'Locations',
                    href: '/admin/locations',
                },
                {
                    title: 'Rooms',
                    href: '/admin/rooms',
                },
                {
                    title: 'Racks',
                    href: '/admin/racks',
                },
                {
                    title: 'Suhu Monitoring',
                    href: '/admin/racks/monitoring',
                },
                {
                    title: 'Keluar-Masuk Device',
                    href: '/admin/device-movements',
                },
            ],
        },
        {
            title: 'Interconnection',
            href: '/admin/interconnections',
            icon: Package,
        },
        {
            title: 'Cross Connect',
            href: '/admin/cross-connects',
            icon: Package,
        },
        {
            title: 'Service Management',
            icon: Package,

            children: [
                {
                    title: 'Categories Product',
                    href: '/admin/categories',
                },
                {
                    title: 'Products',
                    href: '/admin/products',
                },
                {
                    title: 'Services',
                    href: '/admin/services',
                },
            ],
        },
        {
            title: 'Billing Management',
            icon: WalletCards,

            children: [
                {
                    title: 'Invoices',
                    href: '/admin/invoices',
                },
            ],
        },
        {
            title: 'Visitor Management',
            icon: NotepadText,

            children: [
                {
                    title: 'Visitor',
                    href: '/admin/visitors',
                },
                {
                    title: 'Pengajuan Kunjungan',
                    href: '/admin/visit-requests',
                    badgeKey: 'visit_requests',
                },
            ],
        },
        {
            title: 'Ticket Management',
            icon: NotepadText,

            children: [
                {
                    title: 'Tickets',
                    href: '/admin/tickets',
                },
                {
                    title: 'Tickets Category',
                    href: '/admin/tickets-category',
                },
                {
                    title: 'Tickets Priority',
                    href: '/admin/tickets-priority',
                },
            ],
        },
        {
            title: 'Riwayat Email',
            href: '/admin/email-logs',
            icon: Mail,
            badgeKey: 'email_failed',
        },
    ],

    'client': [
        {
            title: 'Dashboard',
            href: '/client/dashboard',
            icon: LayoutGrid,
        },
        {
            title: 'Services',
            icon: NotepadText,

            children: [
                {
                    title: 'My Services',
                    href: '/client/services',
                },
                {
                    title: 'My Racks',
                    href: '/client/racks',
                },
                {
                    title: 'My Devices',
                    href: '/client/devices',
                },
            ],
        },
        {
            title: 'Interconnection',
            href: '/client/interconnections',
            icon: Package,
        },
        {
            title: 'Cross Connect',
            href: '/client/cross-connects',
            icon: Package,
        },
        {
            title: 'Pengajuan Kunjungan',
            href: '/client/visit-requests',
            icon: NotepadText,
        },
        {
            title: 'Invoices',
            icon: NotepadText,

            children: [
                {
                    title: 'Invoices',
                    href: '/client/invoices',
                },
            ],
        },
        {
            title: 'Support',
            icon: NotepadText,

            children: [
                {
                    title: 'Tickets',
                    href: '/client/tickets',
                },
            ],
        },
    ],

    'marketing': [
        {
            title: 'Dashboard',
            href: '/marketing/dashboard',
            icon: LayoutGrid,
        },
    ],

    'teknisi': [
        {
            title: 'Dashboard',
            href: '/teknisi/dashboard',
            icon: LayoutGrid,
        },
    ],
};