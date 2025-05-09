<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, Code, Terminal, Layers, Box, Ruler, ChevronDown, ChevronRight, Briefcase, Warehouse, Truck, User2, ShoppingBagIcon, Package2, FileText, HandCoins, PackageMinus, ClipboardPen, LockKeyhole } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed, ref, watch } from 'vue';
import { useSidebar } from '@/components/ui/sidebar';

const page = usePage();
const laravelVersion = computed(() => page.props.laravelVersion);
const phpVersion = computed(() => page.props.phpVersion);
const currentRoute = computed(() => page.url);
const sidebar = useSidebar();

// Navigation
const mainNavItems: NavItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard', icon: LayoutGrid },
    { title: 'Setting Periode', href: '/admin/account/closed_period', icon: LockKeyhole }
];

const masterNavItems: NavItem[] = [
    { title: 'Master Unit', href: '/admin/master/unit', icon: Layers },
    { title: 'Master Produk', href: '/admin/master/product', icon: Box },
    { title: 'Konversi Produk', href: '/admin/master/unit_conversion', icon: Ruler },
];

const inventoryNavItems: NavItem[] = [
    { title: 'Supplier', href: '/admin/inventory/supplier', icon: Warehouse },
    { title: 'Order Pembelian', href: '/admin/inventory/purchase_order', icon: Package2 },
    { title: 'Penerimaan Barang', href: '/admin/inventory/good_receipt', icon: HandCoins },
    { title: 'Pengeluaran Barang', href: '/admin/inventory/stock_out', icon: PackageMinus },
    { title: 'Stock Opname', href: '/admin/inventory/stock_opname', icon: ClipboardPen },
    { title: 'Kartu Stock', href: '/admin/inventory/stock_card', icon: FileText },
];

const transaksiNavItems: NavItem[] = [
    { title: 'Pelanggan', href: '/admin/transaksi/customer', icon: User2 }
];

const footerNavItems: NavItem[] = [
    { title: `Laravel v${laravelVersion.value}`, href: '', icon: Code },
    { title: `PHP v${phpVersion.value}`, href: '', icon: Terminal },
];

// Dropdown
const isMasterDropdownOpen = ref(false);
const isInventoryDropdownOpen = ref(false);
const isTransaksiDropdownOpen = ref(false);

const isActive = (href: string) => {
    const currentPath = currentRoute.value;
    return currentPath === href || currentPath === `${href}/` || currentPath.startsWith(`${href}/`);
};

const isMasterActive = computed(() => masterNavItems.some(item => isActive(item.href)));
const isInventoryActive = computed(() => inventoryNavItems.some(item => isActive(item.href)));
const isTransaksiActive = computed(() => transaksiNavItems.some(item => isActive(item.href)));

watch(isMasterActive, (active) => { if (active) isMasterDropdownOpen.value = true; }, { immediate: true });
watch(isInventoryActive, (active) => { if (active) isInventoryDropdownOpen.value = true; }, { immediate: true });
watch(isTransaksiActive, (active) => { if (active) isTransaksiDropdownOpen.value = true; }, { immediate: true });

const toggleMasterDropdown = () => { isMasterDropdownOpen.value = !isMasterDropdownOpen.value; };
const toggleInventoryDropdown = () => { isInventoryDropdownOpen.value = !isInventoryDropdownOpen.value; };
const toggleTransaksiDropdown = () => { isTransaksiDropdownOpen.value = !isTransaksiDropdownOpen.value; };
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('admin.dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />

            <div class="py-0">
                <nav class="grid gap-1 px-1">

                    <!-- Master Data -->
                    <button
                        v-if="sidebar.state.value === 'expanded'"
                        @click="toggleMasterDropdown"
                        :class="[
                            'flex items-center gap-2 px-2 py-1 rounded-md text-sm font-medium transition-colors hover:bg-gray-100 dark:hover:bg-gray-800 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring',
                            isMasterActive ? 'bg-gray-100 dark:bg-gray-800 text-primary' : ''
                        ]"
                    >
                        <Briefcase class="h-5 w-5" />
                        <span>Master Data</span>
                        <ChevronDown v-if="isMasterDropdownOpen" class="ml-auto w-4 h-4" />
                        <ChevronRight v-else class="ml-auto w-4 h-4" />
                    </button>

                    <div v-if="isMasterDropdownOpen && sidebar.state.value === 'expanded'" class="pl-2">
                        <Link
                            v-for="item in masterNavItems"
                            :key="item.title"
                            :href="item.href"
                            :class="[
                                'flex items-center gap-2 px-2 py-1 rounded-md text-sm font-medium transition-colors hover:bg-gray-100 dark:hover:bg-gray-800 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring',
                                isActive(item.href) ? 'bg-gray-100 dark:bg-gray-800 text-primary' : ''
                            ]"
                        >
                            <component :is="item.icon" class="h-4 w-4" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </div>

                    <!-- Transaksi -->
                    <button
                        v-if="sidebar.state.value === 'expanded'"
                        @click="toggleTransaksiDropdown"
                        :class="[
                            'flex items-center gap-2 px-2 py-1 rounded-md text-sm font-medium transition-colors hover:bg-gray-100 dark:hover:bg-gray-800 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring',
                            isTransaksiActive ? 'bg-gray-100 dark:bg-gray-800 text-primary' : ''
                        ]"
                    >
                        <ShoppingBagIcon class="h-5 w-5" />
                        <span>Transaksi</span>
                        <ChevronDown v-if="isTransaksiDropdownOpen" class="ml-auto w-4 h-4" />
                        <ChevronRight v-else class="ml-auto w-4 h-4" />
                    </button>

                    <div v-if="isTransaksiDropdownOpen && sidebar.state.value === 'expanded'" class="pl-2">
                        <Link
                            v-for="item in transaksiNavItems"
                            :key="item.title"
                            :href="item.href"
                            :class="[
                                'flex items-center gap-2 px-2 py-1 rounded-md text-sm font-medium transition-colors hover:bg-gray-100 dark:hover:bg-gray-800 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring',
                                isActive(item.href) ? 'bg-gray-100 dark:bg-gray-800 text-primary' : ''
                            ]"
                        >
                            <component :is="item.icon" class="h-4 w-4" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </div>

                    <!-- Inventory -->
                    <button
                        v-if="sidebar.state.value === 'expanded'"
                        @click="toggleInventoryDropdown"
                        :class="[
                            'flex items-center gap-2 px-2 py-1 rounded-md text-sm font-medium transition-colors hover:bg-gray-100 dark:hover:bg-gray-800 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring',
                            isInventoryActive ? 'bg-gray-100 dark:bg-gray-800 text-primary' : ''
                        ]"
                    >
                        <Truck class="h-5 w-5" />
                        <span>Inventory</span>
                        <ChevronDown v-if="isInventoryDropdownOpen" class="ml-auto w-4 h-4" />
                        <ChevronRight v-else class="ml-auto w-4 h-4" />
                    </button>

                    <div v-if="isInventoryDropdownOpen && sidebar.state.value === 'expanded'" class="pl-2">
                        <Link
                            v-for="item in inventoryNavItems"
                            :key="item.title"
                            :href="item.href"
                            :class="[
                                'flex items-center gap-2 px-2 py-1 rounded-md text-sm font-medium transition-colors hover:bg-gray-100 dark:hover:bg-gray-800 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring',
                                isActive(item.href) ? 'bg-gray-100 dark:bg-gray-800 text-primary' : ''
                            ]"
                        >
                            <component :is="item.icon" class="h-4 w-4" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </div>

                </nav>
            </div>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
