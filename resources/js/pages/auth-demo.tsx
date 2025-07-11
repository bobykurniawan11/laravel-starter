import AppLayout from '@/layouts/app-layout';
import { useAuth, AuthGuard, RoleGuard } from '@/hooks/use-auth';
import AuthInfo from '@/components/auth-info';
import { Head } from '@inertiajs/react';

export default function AuthDemo() {
    const { user, tenant, isDeveloper, isAdmin, isStaff, can } = useAuth();

    return (
        <AppLayout>
            <Head title="Auth Demo" />
            
            <div className="max-w-7xl mx-auto p-6 space-y-8">
                <div className="mb-8">
                    <h1 className="text-3xl font-bold text-gray-900">Auth & Permissions Demo</h1>
                    <p className="text-gray-600 mt-2">
                        Demonstration of role-based access control and permissions in action
                    </p>
                </div>

                {/* Auth Info Component */}
                <AuthInfo />

                {/* Hook Usage Examples */}
                <div className="bg-white p-6 rounded-lg shadow">
                    <h3 className="text-lg font-semibold mb-4">Hook Usage Examples</h3>
                    
                    <div className="space-y-4">
                        <div className="p-4 bg-gray-50 rounded">
                            <h4 className="font-medium mb-2">Basic Auth Checks:</h4>
                            <div className="space-y-1 text-sm">
                                <p>Current User: <span className="font-mono">{user?.name}</span></p>
                                <p>Current Tenant: <span className="font-mono">{tenant?.name}</span></p>
                                <p>Is Developer: <span className="font-mono">{isDeveloper.toString()}</span></p>
                                <p>Is Admin: <span className="font-mono">{isAdmin.toString()}</span></p>
                                <p>Is Staff: <span className="font-mono">{isStaff.toString()}</span></p>
                            </div>
                        </div>

                        <div className="p-4 bg-gray-50 rounded">
                            <h4 className="font-medium mb-2">Permission Checks:</h4>
                            <div className="grid grid-cols-2 gap-2 text-sm">
                                <p>Can manage all tenants: <span className="font-mono">{can.manageAllTenants.toString()}</span></p>
                                <p>Can manage own tenant: <span className="font-mono">{can.manageOwnTenant.toString()}</span></p>
                                <p>Can update own tenant: <span className="font-mono">{can.updateOwnTenant.toString()}</span></p>
                                <p>Can create users: <span className="font-mono">{can.createTenantUsers.toString()}</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Guard Components Examples */}
                <div className="space-y-6">
                    <h3 className="text-lg font-semibold">Guard Components Examples</h3>

                    {/* Role-based Guards */}
                    <div className="space-y-4">
                        <RoleGuard 
                            role="developer" 
                            fallback={<div className="p-4 bg-red-50 text-red-700 rounded">❌ Developer access required</div>}
                        >
                            <div className="p-4 bg-purple-50 text-purple-700 rounded">
                                ✅ <strong>Developer Content:</strong> This is only visible to developers
                            </div>
                        </RoleGuard>

                        <RoleGuard 
                            role="admin" 
                            fallback={<div className="p-4 bg-red-50 text-red-700 rounded">❌ Admin access required</div>}
                        >
                            <div className="p-4 bg-blue-50 text-blue-700 rounded">
                                ✅ <strong>Admin Content:</strong> This is only visible to admins
                            </div>
                        </RoleGuard>

                        <RoleGuard 
                            role="staff" 
                            fallback={<div className="p-4 bg-red-50 text-red-700 rounded">❌ Staff access required</div>}
                        >
                            <div className="p-4 bg-green-50 text-green-700 rounded">
                                ✅ <strong>Staff Content:</strong> This is only visible to staff
                            </div>
                        </RoleGuard>
                    </div>

                    {/* Permission-based Guards */}
                    <div className="space-y-4">
                        <AuthGuard 
                            permissions={['read-all-tenants']}
                            fallback={<div className="p-4 bg-red-50 text-red-700 rounded">❌ Global tenant access required</div>}
                        >
                            <div className="p-4 bg-purple-50 text-purple-700 rounded">
                                ✅ <strong>Global Access:</strong> You can view all tenants
                            </div>
                        </AuthGuard>

                        <AuthGuard 
                            permissions={['update-own-tenant']}
                            fallback={<div className="p-4 bg-red-50 text-red-700 rounded">❌ Tenant update permission required</div>}
                        >
                            <div className="p-4 bg-blue-50 text-blue-700 rounded">
                                ✅ <strong>Tenant Management:</strong> You can update your tenant
                            </div>
                        </AuthGuard>

                        <AuthGuard 
                            permissions={['create-tenant-users']}
                            fallback={<div className="p-4 bg-red-50 text-red-700 rounded">❌ User creation permission required</div>}
                        >
                            <div className="p-4 bg-green-50 text-green-700 rounded">
                                ✅ <strong>User Management:</strong> You can create new users in your tenant
                            </div>
                        </AuthGuard>
                    </div>

                    {/* Multiple Roles/Permissions */}
                    <AuthGuard 
                        roles={['admin', 'developer']}
                        fallback={<div className="p-4 bg-red-50 text-red-700 rounded">❌ Admin or Developer role required</div>}
                    >
                        <div className="p-4 bg-indigo-50 text-indigo-700 rounded">
                            ✅ <strong>Admin or Developer:</strong> This content is visible to both admins and developers
                        </div>
                    </AuthGuard>

                    <AuthGuard 
                        roles={['developer']}
                        permissions={['read-all-tenants']}
                        requireAll={true}
                        fallback={<div className="p-4 bg-red-50 text-red-700 rounded">❌ Must be developer AND have global tenant access</div>}
                    >
                        <div className="p-4 bg-purple-50 text-purple-700 rounded">
                            ✅ <strong>Strict Access:</strong> Developer with global tenant permissions
                        </div>
                    </AuthGuard>
                </div>

                {/* Navigation Example */}
                <div className="bg-white p-6 rounded-lg shadow">
                    <h3 className="text-lg font-semibold mb-4">Conditional Navigation</h3>
                    <nav className="flex flex-wrap gap-2">
                        {can.manageAllTenants && (
                            <a href="#" className="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">
                                🌐 All Tenants
                            </a>
                        )}
                        
                        {can.manageOwnTenant && (
                            <a href="#" className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                🏢 My Tenant
                            </a>
                        )}
                        
                        {can.manageTenantUsers && (
                            <a href="#" className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                👥 Users
                            </a>
                        )}
                        
                        {can.manageTenantData && (
                            <a href="#" className="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                                📊 Data
                            </a>
                        )}
                    </nav>
                </div>
            </div>
        </AppLayout>
    );
} 