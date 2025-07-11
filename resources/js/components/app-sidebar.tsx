import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { BookOpen, Folder, LayoutGrid, Shield, Users } from 'lucide-react';
import AppLogo from './app-logo';
import { useAuth } from '@/hooks/use-auth';

export function AppSidebar() {
    const { hasPermission } = useAuth();

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
        ...(hasPermission('read-permissions')
            ? [
                  {
                      title: 'Permissions',
                      href: '/permissions',
                      icon: Shield,
                  },
              ]
            : []),
        ...(hasPermission('read-roles')
            ? [
                  {
                      title: 'Roles',
                      href: '/roles',
                      icon: Folder,
                  },
              ]
            : []),
        ...(hasPermission('read-tenant-users')
            ? [
                  {
                      title: 'Users',
                      href: '/users',
                      icon: Users,
                  },
              ]
            : []),
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
