import type { route as routeFn } from 'ziggy-js';

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    tenant_id?: number;
    created_at: string;
    updated_at: string;
}

export interface Tenant {
    id: number;
    name: string;
    created_at: string;
    updated_at: string;
}

export interface AuthData {
    user: User | null;
    roles?: string[];
    permissions?: string[];
    tenant?: Tenant | null;
    tenant_id?: number | null;
    can?: {
        manage_all_tenants: boolean;
        manage_own_tenant: boolean;
        update_own_tenant: boolean;
        manage_tenant_users: boolean;
        create_tenant_users: boolean;
        update_tenant_users: boolean;
        delete_tenant_users: boolean;
        manage_tenant_data: boolean;
        create_tenant_data: boolean;
        update_tenant_data: boolean;
        delete_tenant_data: boolean;
    };
    is?: {
        developer: boolean;
        admin: boolean;
        staff: boolean;
    };
}

export interface PageProps {
    auth: AuthData;
    name: string;
    quote: {
        message: string;
        author: string;
    };
    ziggy: any;
    sidebarOpen: boolean;
    [key: string]: any;
}

declare global {
    const route: typeof routeFn;
}

declare module '@inertiajs/react' {
    export function usePage<T = PageProps>(): {
        props: T;
        url: string;
        component: string;
        version: string;
        rememberedState: any;
        scrollRegions: any;
    };
}
