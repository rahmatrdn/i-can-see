import {
    bigint,
    index,
    int,
    longtext,
    mediumtext,
    mysqlTable,
    smallint,
    text,
    timestamp,
    tinyint,
    uniqueIndex,
    varchar,
} from 'drizzle-orm/mysql-core';
import type { AnyMySqlColumn } from 'drizzle-orm/mysql-core';

export const migrationsTable = mysqlTable('migrations', {
    id: int({ unsigned: true }).autoincrement().primaryKey(),
    migration: varchar({ length: 255 }).notNull(),
    batch: int().notNull(),
});

export const usersTable = mysqlTable('users', {
    id: bigint({ mode: 'number', unsigned: true }).autoincrement().primaryKey(),
    name: varchar({ length: 255 }).notNull(),
    email: varchar({ length: 255 }).notNull(),
    email_verified_at: timestamp(),
    password: varchar({ length: 255 }).notNull(),
    remember_token: varchar({ length: 100 }),
    access_type: tinyint(), // 1: Super Admin // 2: User
    is_active: tinyint().notNull().default(1),
    created_by: bigint({ mode: 'number', unsigned: true }).references((): AnyMySqlColumn => usersTable.id),
    created_at: timestamp(),
    updated_by: bigint({ mode: 'number', unsigned: true }).references((): AnyMySqlColumn => usersTable.id),
    updated_at: timestamp(),
    deleted_by: bigint({ mode: 'number', unsigned: true }).references((): AnyMySqlColumn => usersTable.id),
    deleted_at: timestamp(),
}, (t) => [
    uniqueIndex('users_email_unique').on(t.email),
]);

export const passwordResetTokensTable = mysqlTable('password_reset_tokens', {
    email: varchar({ length: 255 }).notNull().primaryKey(),
    token: varchar({ length: 255 }).notNull(),
    created_at: timestamp(),
});

export const sessionsTable = mysqlTable('sessions', {
    id: varchar({ length: 255 }).notNull().primaryKey(),
    user_id: bigint({ mode: 'number', unsigned: true }),
    ip_address: varchar({ length: 45 }),
    user_agent: text(),
    payload: longtext().notNull(),
    last_activity: int().notNull(),
}, (t) => [
    index('sessions_user_id_index').on(t.user_id),
    index('sessions_last_activity_index').on(t.last_activity),
]);

export const cacheTable = mysqlTable('cache', {
    key: varchar({ length: 255 }).notNull().primaryKey(),
    value: mediumtext().notNull(),
    expiration: bigint({ mode: 'number' }).notNull(),
}, (t) => [
    index('cache_expiration_index').on(t.expiration),
]);

export const cacheLocksTable = mysqlTable('cache_locks', {
    key: varchar({ length: 255 }).notNull().primaryKey(),
    owner: varchar({ length: 255 }).notNull(),
    expiration: bigint({ mode: 'number' }).notNull(),
}, (t) => [
    index('cache_locks_expiration_index').on(t.expiration),
]);

export const jobsTable = mysqlTable('jobs', {
    id: bigint({ mode: 'number', unsigned: true }).autoincrement().primaryKey(),
    queue: varchar({ length: 255 }).notNull(),
    payload: longtext().notNull(),
    attempts: smallint({ unsigned: true }).notNull(),
    reserved_at: int({ unsigned: true }),
    available_at: int({ unsigned: true }).notNull(),
    created_at: int({ unsigned: true }).notNull(),
}, (t) => [
    index('jobs_queue_index').on(t.queue),
]);

export const jobBatchesTable = mysqlTable('job_batches', {
    id: varchar({ length: 255 }).notNull().primaryKey(),
    name: varchar({ length: 255 }).notNull(),
    total_jobs: int().notNull(),
    pending_jobs: int().notNull(),
    failed_jobs: int().notNull(),
    failed_job_ids: longtext().notNull(),
    options: mediumtext(),
    cancelled_at: int(),
    created_at: int().notNull(),
    finished_at: int(),
});

export const failedJobsTable = mysqlTable('failed_jobs', {
    id: bigint({ mode: 'number', unsigned: true }).autoincrement().primaryKey(),
    uuid: varchar({ length: 255 }).notNull(),
    connection: varchar({ length: 255 }).notNull(),
    queue: varchar({ length: 255 }).notNull(),
    payload: longtext().notNull(),
    exception: longtext().notNull(),
    failed_at: timestamp().notNull().defaultNow(),
}, (t) => [
    uniqueIndex('failed_jobs_uuid_unique').on(t.uuid),
    index('failed_jobs_connection_queue_failed_at_index').on(t.connection, t.queue, t.failed_at),
]);

export const sidebarMenuAccessesTable = mysqlTable('sidebar_menu_accesses', {
    id: bigint({ mode: 'number', unsigned: true }).autoincrement().primaryKey(),
    sidebar_menu_id: bigint({ mode: 'number', unsigned: true }).notNull(),
    // 1=Superadmin,2=User
    access_type: tinyint().notNull(),
    created_by: bigint({ mode: 'number', unsigned: true }),
    created_at: timestamp(),
}, (t) => [
    uniqueIndex('sidebar_menu_accesses_sidebar_menu_id_access_type_unique').on(t.sidebar_menu_id, t.access_type),
    index('sidebar_menu_accesses_sidebar_menu_id_index').on(t.sidebar_menu_id),
]);

export const sidebarMenuGroupsTable = mysqlTable('sidebar_menu_groups', {
    id: bigint({ mode: 'number', unsigned: true }).autoincrement().primaryKey(),
    key: varchar({ length: 50 }).notNull(),
    label: varchar({ length: 100 }).notNull(),
    color: varchar({ length: 50 }).notNull().default('blue'),
    sort_order: smallint().notNull().default(0),
    created_at: timestamp(),
    updated_at: timestamp(),
}, (t) => [
    uniqueIndex('sidebar_menu_groups_key_unique').on(t.key),
]);

export const sidebarMenusTable = mysqlTable('sidebar_menus', {
    id: bigint({ mode: 'number', unsigned: true }).autoincrement().primaryKey(),
    // Self-referencing, max 1 level deep
    parent_id: bigint({ mode: 'number', unsigned: true }),
    label: varchar({ length: 255 }).notNull(),
    // Laravel named route, e.g. admin.dashboard
    route_name: varchar({ length: 255 }),
    // Icon blade include path
    icon: varchar({ length: 255 }),
    // utama|lembaga|keuangan|bendahara
    group: varchar({ length: 50 }).notNull(),
    sort_order: smallint().notNull().default(0),
    is_active: tinyint().notNull().default(1),
    created_by: bigint({ mode: 'number', unsigned: true }),
    updated_by: bigint({ mode: 'number', unsigned: true }),
    deleted_by: bigint({ mode: 'number', unsigned: true }),
    created_at: timestamp(),
    updated_at: timestamp(),
    deleted_at: timestamp(),
}, (t) => [
    index('sidebar_menus_parent_id_index').on(t.parent_id),
    index('sidebar_menus_group_sort_order_index').on(t.group, t.sort_order),
]);

export * from './registration-partners';
export * from './app-users';

