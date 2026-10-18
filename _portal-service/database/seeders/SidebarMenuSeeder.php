<?php

namespace Database\Seeders;

use App\Constants\DatabaseConst;
use App\Constants\UserConst;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SidebarMenuSeeder extends Seeder
{
    /**
     * Seed sidebar_menu_groups, sidebar_menus, and sidebar_menu_accesses tables.
     */
    public function run(): void
    {
        DB::table(DatabaseConst::SIDEBAR_MENU_GROUP())->truncate();
        DB::table(DatabaseConst::SIDEBAR_MENU())->truncate();
        DB::table(DatabaseConst::SIDEBAR_MENU_ACCESS())->truncate();

        $now = now();

        // =====================================================================
        // GROUPS
        // =====================================================================
        DB::table(DatabaseConst::SIDEBAR_MENU_GROUP())->insert([
            ['key' => 'utama',     'label' => 'Utama',     'color' => 'blue',    'sort_order' => 10, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $superadmin = UserConst::SUPERADMIN;
        $superadminOnly = [$superadmin];

        // =====================================================================
        // GROUP: utama
        // =====================================================================
        $dashboardId = DB::table(DatabaseConst::SIDEBAR_MENU())->insertGetId([
            'label' => 'Dashboard',
            'route_name' => 'admin.dashboard',
            'icon' => '_admin._layout.icons.sidebar.dashboard',
            'group' => 'utama',
            'sort_order' => 10,
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->assignAccess($dashboardId, $superadminOnly, $now);

        $penggunaId = DB::table(DatabaseConst::SIDEBAR_MENU())->insertGetId([
            'label' => 'Pengguna Aplikasi',
            'route_name' => 'admin.users.index',
            'icon' => '_admin._layout.icons.sidebar.user',
            'group' => 'utama',
            'sort_order' => 40,
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->assignAccess($penggunaId, $superadminOnly, $now);

        $sidebarMgmtId = DB::table(DatabaseConst::SIDEBAR_MENU())->insertGetId([
            'label' => 'Manajemen Sidebar',
            'route_name' => 'admin.sidebar_menu.index',
            'icon' => '_admin._layout.icons.sidebar.data_master',
            'group' => 'utama',
            'sort_order' => 50,
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->assignAccess($sidebarMgmtId, $superadminOnly, $now);

    }

    /**
     * Insert access records for a given sidebar menu item.
     *
     * @param  array<int>  $accessTypes
     */
    private function assignAccess(int $sidebarMenuId, array $accessTypes, mixed $now): void
    {
        $inserts = array_map(fn ($type) => [
            'sidebar_menu_id' => $sidebarMenuId,
            'access_type' => $type,
            'created_at' => $now,
        ], $accessTypes);

        DB::table(DatabaseConst::SIDEBAR_MENU_ACCESS())->insert($inserts);
    }
}
