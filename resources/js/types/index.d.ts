import { LucideIcon } from 'lucide-react';
import type { Config } from 'ziggy-js';

export interface SocialAccount {
    id: number;
    user_id: number;
    provider_name: string;
    provider_id: string;
    avatar: string | null;
    created_at: string;
    updated_at: string;
}

export interface Auth {
    user: User;
    social_accounts: SocialAccount[];
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}
