import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types/global';

export function useAuth() {
    const { auth } = usePage<PageProps>().props;

    const isAuthenticated = !!auth.user;

    // Role checking helpers
    const isDeveloper = auth.is?.developer ?? false;
    const isAdmin = auth.is?.admin ?? false;
    const isStaff = auth.is?.staff ?? false;

    // Permission checking helpers
    const can = {
        manageAllTenants: auth.can?.manage_all_tenants ?? false,
        manageOwnTenant: auth.can?.manage_own_tenant ?? false,
        updateOwnTenant: auth.can?.update_own_tenant ?? false,
        manageTenantUsers: auth.can?.manage_tenant_users ?? false,
        createTenantUsers: auth.can?.create_tenant_users ?? false,
        updateTenantUsers: auth.can?.update_tenant_users ?? false,
        deleteTenantUsers: auth.can?.delete_tenant_users ?? false,
        manageTenantData: auth.can?.manage_tenant_data ?? false,
        createTenantData: auth.can?.create_tenant_data ?? false,
        updateTenantData: auth.can?.update_tenant_data ?? false,
        deleteTenantData: auth.can?.delete_tenant_data ?? false,
    };

    // Helper functions
    const hasRole = (role: string): boolean => {
        return auth.roles?.includes(role) ?? false;
    };

    const hasPermission = (permission: string): boolean => {
        return auth.permissions?.includes(permission) ?? false;
    };

    const hasAnyRole = (roles: string[]): boolean => {
        return roles.some(role => hasRole(role));
    };

    const hasAnyPermission = (permissions: string[]): boolean => {
        return permissions.some(permission => hasPermission(permission));
    };

    const hasAllRoles = (roles: string[]): boolean => {
        return roles.every(role => hasRole(role));
    };

    const hasAllPermissions = (permissions: string[]): boolean => {
        return permissions.every(permission => hasPermission(permission));
    };

    return {
        // Auth data
        user: auth.user,
        tenant: auth.tenant,
        roles: auth.roles ?? [],
        permissions: auth.permissions ?? [],
        isAuthenticated,

        // Role checks
        isDeveloper,
        isAdmin,
        isStaff,

        // Permission checks
        can,

        // Helper functions
        hasRole,
        hasPermission,
        hasAnyRole,
        hasAnyPermission,
        hasAllRoles,
        hasAllPermissions,

        // Raw auth data
        auth,
    };
}

// Helper components for conditional rendering
export function AuthGuard({ 
    roles, 
    permissions, 
    requireAll = false,
    fallback = null,
    children 
}: {
    roles?: string[];
    permissions?: string[];
    requireAll?: boolean;
    fallback?: React.ReactNode;
    children: React.ReactNode;
}) {
    const { hasAnyRole, hasAnyPermission, hasAllRoles, hasAllPermissions } = useAuth();

    let hasAccess = true;

    if (roles && roles.length > 0) {
        hasAccess = requireAll ? hasAllRoles(roles) : hasAnyRole(roles);
    }

    if (permissions && permissions.length > 0 && hasAccess) {
        hasAccess = requireAll ? hasAllPermissions(permissions) : hasAnyPermission(permissions);
    }

    return hasAccess ? <>{children}</> : <>{fallback}</>;
}

export function RoleGuard({ 
    role, 
    fallback = null, 
    children 
}: {
    role: 'developer' | 'admin' | 'staff';
    fallback?: React.ReactNode;
    children: React.ReactNode;
}) {
    const { isDeveloper, isAdmin, isStaff } = useAuth();

    const hasRole = {
        developer: isDeveloper,
        admin: isAdmin,
        staff: isStaff,
    }[role];

    return hasRole ? <>{children}</> : <>{fallback}</>;
} 