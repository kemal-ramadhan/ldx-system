export * from './auth';
export * from './navigation';
export * from './ui';

export interface NavItem {
    title: string;
    href?: string;
    icon?: any;

    children?: NavItem[];
}