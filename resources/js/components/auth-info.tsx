import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types/global';

export default function AuthInfo() {
    const { auth } = usePage<PageProps>().props;

    if (!auth.user) {
        return (
            <div className="bg-gray-100 p-4 rounded-lg">
                <p className="text-gray-600">No user logged in</p>
            </div>
        );
    }

    return (
        <div className="space-y-6">
            {/* User Info */}
            <div className="bg-white p-6 rounded-lg shadow">
                <h3 className="text-lg font-semibold mb-4">User Information</h3>
                <div className="space-y-2">
                    <p><span className="font-medium">Name:</span> {auth.user.name}</p>
                    <p><span className="font-medium">Email:</span> {auth.user.email}</p>
                    {auth.tenant && (
                        <p><span className="font-medium">Tenant:</span> {auth.tenant.name}</p>
                    )}
                </div>
            </div>

            {/* Roles */}
            <div className="bg-white p-6 rounded-lg shadow">
                <h3 className="text-lg font-semibold mb-4">Roles</h3>
                <div className="flex flex-wrap gap-2">
                    {auth.roles?.map((role) => (
                        <span
                            key={role}
                            className={`px-3 py-1 rounded-full text-sm font-medium ${
                                role === 'developer'
                                    ? 'bg-purple-100 text-purple-800'
                                    : role === 'admin'
                                    ? 'bg-blue-100 text-blue-800'
                                    : 'bg-green-100 text-green-800'
                            }`}
                        >
                            {role}
                        </span>
                    ))}
                </div>
            </div>

            {/* Role-based UI */}
            <div className="bg-white p-6 rounded-lg shadow">
                <h3 className="text-lg font-semibold mb-4">Available Actions</h3>
                <div className="space-y-3">
                    {/* Developer only */}
                    {auth.is?.developer && (
                        <div className="p-4 bg-purple-50 rounded-lg">
                            <h4 className="font-medium text-purple-800 mb-2">🛠️ Developer Access</h4>
                            <ul className="text-sm text-purple-700 space-y-1">
                                <li>• Manage all tenants</li>
                                <li>• Create/Update/Delete any tenant</li>
                                <li>• Access all tenant data</li>
                                <li>• Global system administration</li>
                            </ul>
                        </div>
                    )}

                    {/* Admin */}
                    {auth.is?.admin && (
                        <div className="p-4 bg-blue-50 rounded-lg">
                            <h4 className="font-medium text-blue-800 mb-2">👑 Admin Access</h4>
                            <ul className="text-sm text-blue-700 space-y-1">
                                <li>• Manage your tenant: {auth.tenant?.name}</li>
                                {auth.can?.update_own_tenant && <li>• Update tenant settings</li>}
                                {auth.can?.manage_tenant_users && <li>• Manage tenant users</li>}
                                {auth.can?.manage_tenant_data && <li>• Manage tenant data</li>}
                            </ul>
                        </div>
                    )}

                    {/* Staff */}
                    {auth.is?.staff && (
                        <div className="p-4 bg-green-50 rounded-lg">
                            <h4 className="font-medium text-green-800 mb-2">📋 Staff Access</h4>
                            <ul className="text-sm text-green-700 space-y-1">
                                <li>• View tenant: {auth.tenant?.name}</li>
                                {auth.can?.manage_tenant_users && <li>• View tenant users</li>}
                                {auth.can?.manage_tenant_data && <li>• View tenant data</li>}
                            </ul>
                        </div>
                    )}
                </div>
            </div>

            {/* Conditional Navigation/Actions */}
            <div className="bg-white p-6 rounded-lg shadow">
                <h3 className="text-lg font-semibold mb-4">Quick Actions</h3>
                <div className="flex flex-wrap gap-2">
                    {auth.can?.manage_all_tenants && (
                        <button className="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">
                            View All Tenants
                        </button>
                    )}
                    
                    {auth.can?.manage_own_tenant && (
                        <button className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Manage My Tenant
                        </button>
                    )}
                    
                    {auth.can?.update_own_tenant && (
                        <button className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Edit Tenant Settings
                        </button>
                    )}
                    
                    {auth.can?.create_tenant_users && (
                        <button className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            Add Users
                        </button>
                    )}
                    
                    {auth.can?.manage_tenant_data && (
                        <button className="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                            Manage Data
                        </button>
                    )}
                </div>
            </div>

            {/* Permissions List */}
            <div className="bg-white p-6 rounded-lg shadow">
                <h3 className="text-lg font-semibold mb-4">Permissions</h3>
                <div className="grid grid-cols-2 md:grid-cols-3 gap-2">
                    {auth.permissions?.map((permission) => (
                        <span
                            key={permission}
                            className="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded"
                        >
                            {permission}
                        </span>
                    ))}
                </div>
            </div>
        </div>
    );
} 