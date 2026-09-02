import {
    LayoutGrid,
    Users,
    Folder,
    Package,
    WalletCards,
    NotepadText,
} from 'lucide-vue-next';

export const navigation = {
    'super-admin': [
        {
            title: 'Dashboard',
            href: '/admin/dashboard',
            icon: LayoutGrid,
        },
        {
            title: 'User Management',
            icon: Users,

            children: [
                {
                    title: 'Users',
                    href: '/admin/users',
                },
                {
                    title: 'Clients Or Companies',
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
            ],
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