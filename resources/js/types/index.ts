export * from './auth';
export * from './navigation';
export * from './ui';

export interface NavItem {
    title: string;
    href?: string;
    icon?: any;
    /** key into page.props.navBadges (e.g. 'visit_requests') */
    badgeKey?: string;

    children?: NavItem[];
}