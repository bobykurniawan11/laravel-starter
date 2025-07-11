<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Silber\Bouncer\BouncerFacade as Bouncer;
use App\Models\User;
use App\Models\Tenant;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create tenant-specific abilities
        $abilities = [
            // Global tenant management (for developers)
            'create-tenants',
            'read-all-tenants',
            'update-all-tenants', 
            'delete-all-tenants',
            
            // Own tenant management
            'read-own-tenant',
            'update-own-tenant',
            
            // User management within tenant
            'create-tenant-users',
            'read-tenant-users',
            'update-tenant-users',
            'delete-tenant-users',
            
            // Tenant data management
            'create-tenant-data',
            'read-tenant-data',
            'update-tenant-data',
            'delete-tenant-data',

            // Role & permission management (Developer only)
            'create-roles',
            'read-roles',
            'update-roles',
            'delete-roles',
            'create-permissions',
            'read-permissions',
            'update-permissions',
            'delete-permissions',
            'create-role-permissions',
            'read-role-permissions',
            'update-role-permissions',
            'delete-role-permissions',
        ];

        foreach ($abilities as $ability) {
            Bouncer::ability()->firstOrCreate(['name' => $ability]);
        }

        // Create roles
        $developer = Bouncer::role()->firstOrCreate(['name' => 'developer']);
        $admin = Bouncer::role()->firstOrCreate(['name' => 'admin']);
        $staff = Bouncer::role()->firstOrCreate(['name' => 'staff']);

        // Assign abilities to roles

        // Developer: CRUD access to ALL tenants
        Bouncer::allow($developer)->to([
            'create-tenants',
            'read-all-tenants',
            'update-all-tenants',
            'delete-all-tenants',
            'create-tenant-users',
            'read-tenant-users',
            'update-tenant-users',
            'delete-tenant-users',
            'create-tenant-data',
            'read-tenant-data',
            'update-tenant-data',
            'delete-tenant-data',
            // Role & permission management
            'create-roles',
            'read-roles',
            'update-roles',
            'delete-roles',
            'create-permissions',
            'read-permissions',
            'update-permissions',
            'delete-permissions',
            'create-role-permissions',
            'read-role-permissions',
            'update-role-permissions',
            'delete-role-permissions',
        ]);

        // Admin: Read/Update (RU) their OWN tenant only
        Bouncer::allow($admin)->to([
            'read-own-tenant',
            'update-own-tenant',
            'create-tenant-users',
            'read-tenant-users',
            'update-tenant-users',
            'create-tenant-data',
            'read-tenant-data',
            'update-tenant-data',
        ]);

        // Staff: Read (R) their OWN tenant only
        Bouncer::allow($staff)->to([
            'read-own-tenant',
            'read-tenant-users',
            'read-tenant-data',
        ]);

        // Create sample tenants
        $tenant1 = Tenant::firstOrCreate(['name' => 'Company A']);
        $tenant2 = Tenant::firstOrCreate(['name' => 'Company B']);

        // Create sample users
        $developer = User::firstOrCreate([
            'email' => 'developer@example.com'
        ], [
            'name' => 'Developer User',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'tenant_id' => null, // Developers can be global
        ]);

        $admin1 = User::firstOrCreate([
            'email' => 'admin1@example.com'
        ], [
            'name' => 'Admin Company A',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'tenant_id' => $tenant1->id,
        ]);

        $admin2 = User::firstOrCreate([
            'email' => 'admin2@example.com'
        ], [
            'name' => 'Admin Company B',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'tenant_id' => $tenant2->id,
        ]);

        $staff1 = User::firstOrCreate([
            'email' => 'staff1@example.com'
        ], [
            'name' => 'Staff Company A',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'tenant_id' => $tenant1->id,
        ]);

        // Assign roles to users
        Bouncer::assign('developer')->to($developer);
        Bouncer::assign('admin')->to($admin1);
        Bouncer::assign('admin')->to($admin2);
        Bouncer::assign('staff')->to($staff1);

        $this->command->info('✅ Multi-tenant roles and abilities created successfully!');
        $this->command->info('📋 Roles created:');
        $this->command->info('   • Developer: CRUD access to ALL tenants');
        $this->command->info('   • Admin: Read/Update access to their OWN tenant');
        $this->command->info('   • Staff: Read-only access to their OWN tenant');
        $this->command->info('');
        $this->command->info('👥 Sample users created:');
        $this->command->info('   • developer@example.com / password (Developer - Global)');
        $this->command->info('   • admin1@example.com / password (Admin - Company A)');
        $this->command->info('   • admin2@example.com / password (Admin - Company B)');
        $this->command->info('   • staff1@example.com / password (Staff - Company A)');
    }
}
