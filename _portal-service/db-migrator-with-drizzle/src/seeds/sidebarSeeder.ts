import db from '../db';
import { sidebarMenuAccessesTable, sidebarMenuGroupsTable, sidebarMenusTable } from '../db/schema';
import { eq } from 'drizzle-orm';

export async function sidebarSeeder(): Promise<void> {
    // 1. Seed Groups
    const groups = [
        {
            id: 1,
            key: 'utama',
            label: 'Utama',
            color: 'blue',
            sort_order: 10,
            created_at: new Date('2026-06-14 13:05:54'),
            updated_at: new Date('2026-06-14 13:05:54'),
        },
    ];

    for (const group of groups) {
        const existing = await db
            .select({ id: sidebarMenuGroupsTable.id })
            .from(sidebarMenuGroupsTable)
            .where(eq(sidebarMenuGroupsTable.id, group.id))
            .limit(1);

        if (existing.length > 0) {
            await db
                .update(sidebarMenuGroupsTable)
                .set(group)
                .where(eq(sidebarMenuGroupsTable.id, group.id));
            console.log(`[sidebarSeeder] Updated existing group: ${group.key}`);
        } else {
            await db.insert(sidebarMenuGroupsTable).values(group);
            console.log(`[sidebarSeeder] Created group: ${group.key}`);
        }
    }

    // 2. Seed Menus
    const menus = [
        {
            id: 1,
            parent_id: null,
            label: 'Dashboard',
            route_name: 'admin.dashboard',
            icon: '_admin._layout.icons.sidebar.dashboard',
            group: 'utama',
            sort_order: 1,
            is_active: 1,
            created_by: null,
            updated_by: null,
            deleted_by: null,
            created_at: new Date('2026-06-14 13:05:54'),
            updated_at: new Date('2026-06-14 13:05:54'),
            deleted_at: null,
        },
        {
            id: 2,
            parent_id: null,
            label: 'Pengguna Aplikasi',
            route_name: 'admin.users.index',
            icon: '_admin._layout.icons.sidebar.user',
            group: 'utama',
            sort_order: 99,
            is_active: 1,
            created_by: null,
            updated_by: null,
            deleted_by: null,
            created_at: new Date('2026-06-14 13:05:54'),
            updated_at: new Date('2026-06-14 13:05:54'),
            deleted_at: null,
        },
    ];

    for (const menu of menus) {
        const existing = await db
            .select({ id: sidebarMenusTable.id })
            .from(sidebarMenusTable)
            .where(eq(sidebarMenusTable.id, menu.id))
            .limit(1);

        if (existing.length > 0) {
            await db
                .update(sidebarMenusTable)
                .set(menu)
                .where(eq(sidebarMenusTable.id, menu.id));
            console.log(`[sidebarSeeder] Updated existing menu: ${menu.label}`);
        } else {
            await db.insert(sidebarMenusTable).values(menu);
            console.log(`[sidebarSeeder] Created menu: ${menu.label}`);
        }
    }

    // 3. Seed Accesses
    const accesses = [
        {
            id: 3,
            sidebar_menu_id: 1,
            access_type: 1,
            created_by: 2,
            created_at: new Date('2026-06-29 12:27:40'),
        },
        {
            id: 4,
            sidebar_menu_id: 2,
            access_type: 1,
            created_by: 2,
            created_at: new Date('2026-06-29 12:27:40'),
        },
    ];

    for (const access of accesses) {
        const existing = await db
            .select({ id: sidebarMenuAccessesTable.id })
            .from(sidebarMenuAccessesTable)
            .where(eq(sidebarMenuAccessesTable.id, access.id))
            .limit(1);

        if (existing.length > 0) {
            await db
                .update(sidebarMenuAccessesTable)
                .set(access)
                .where(eq(sidebarMenuAccessesTable.id, access.id));
            console.log(`[sidebarSeeder] Updated existing access type ${access.access_type} for menu ${access.sidebar_menu_id}`);
        } else {
            await db.insert(sidebarMenuAccessesTable).values(access);
            console.log(`[sidebarSeeder] Created access type ${access.access_type} for menu ${access.sidebar_menu_id}`);
        }
    }
}
