import { Link, usePage } from '@inertiajs/react';
import { BookOpen, FolderGit2, LayoutGrid, Pill, Truck, Tags, ShoppingCart } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem, Auth } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Users',
        href: '/users',
        icon: LayoutGrid,
    },
    {
        title: 'Kategori Obat',
        href: '/drug-categories',
        icon: Tags,
    },
    {
        title: 'Supplier',
        href: '/suppliers',
        icon: Truck,
    },
    {
        title: 'Data Obat',
        href: '/drugs',
        icon: Pill,
    },
    {
        title: 'Purchase Orders',
        href: '/purchase-orders',
        icon: ShoppingCart,
    },
    {
        title: 'Goods Receipts',
        href: '/goods-receipts',
        icon: Truck,
    },
    {
        title: 'Distribusi Obat',
        href: '/distributions',
        icon: Truck,
    },
    {
        title: 'Kartu Stok',
        href: '/stock-card',
        icon: BookOpen,
    },
    {
        title: 'Reorder Alerts',
        href: '/alerts',
        icon: BookOpen,
    },
    {
        title: 'Dashboard ABC',
        href: '/abc-dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Forecasting',
        href: '/forecast',
        icon: LayoutGrid,
    },
    {
        title: 'Laporan Riset',
        href: '/research-report',
        icon: BookOpen,
    },
    {
        title: 'Laporan & Ekspor',
        href: '/reports',
        icon: BookOpen,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    const { auth } = usePage<{ auth: Auth }>().props;
    const userRoles = auth.roles || [];
    const isAdmin = userRoles.includes('admin');
    const isPetugasGudang = userRoles.includes('petugas-gudang');
    const isKepalaInstalasi = userRoles.includes('kepala-instalasi');
    const isApoteker = userRoles.includes('apoteker');

    const filteredNavItems = mainNavItems.filter((item) => {
        if (isAdmin) return true;

        if (item.title === 'Users') return false; // Admin only

        if (isPetugasGudang) {
            const blocked = ['Dashboard ABC', 'Forecasting', 'Laporan Riset', 'Laporan & Ekspor'];
            if (blocked.includes(item.title)) return false;
        }

        if (isKepalaInstalasi) {
            const blocked = ['Distribusi Obat'];
            if (blocked.includes(item.title)) return false;
        }

        return true;
    });

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={filteredNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
