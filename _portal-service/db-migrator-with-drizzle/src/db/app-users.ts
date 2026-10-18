import {
    bigint,
    date,
    index,
    mysqlTable,
    text,
    timestamp,
    tinyint,
    varchar,
} from 'drizzle-orm/mysql-core';
import type { AnyMySqlColumn } from 'drizzle-orm/mysql-core';
import { usersTable } from './schema';
import { registrationPartnersTable } from './registration-partners';

export const appUsersTable = mysqlTable('app_users', {
    id: bigint({ mode: 'number', unsigned: true }).autoincrement().primaryKey(),
    // Associated registration partner or foundation
    registration_partner_id: bigint({ mode: 'number', unsigned: true }).references((): AnyMySqlColumn => registrationPartnersTable.id),
    // Full name of the visually impaired user
    name: varchar({ length: 255 }).notNull(),
    // Preferred nickname / calling name
    nickname: varchar({ length: 100 }),
    // Primary phone number for app access or emergency
    phone_number: varchar({ length: 50 }),
    // Optional email address
    email: varchar({ length: 255 }),
    // 1: Male, 2: Female
    gender: tinyint(),
    // Date of birth
    date_of_birth: date({ mode: 'string' }),
    // National Identity Number (NIK / KTP)
    national_id_number: varchar({ length: 50 }),
    // 1: Totally Blind, 2: Low Vision
    visual_impairment_level: tinyint().notNull().default(1),
    // Cause of impairment (e.g. Congenital, Illness, Accident)
    visual_impairment_cause: varchar({ length: 100 }),
    // Home street address
    address: text(),
    // City or regency
    city: varchar({ length: 100 }),
    // Province or state
    province: varchar({ length: 100 }),
    // Name of guardian or primary contact person
    guardian_name: varchar({ length: 255 }),
    // Phone number of guardian or emergency contact
    guardian_phone: varchar({ length: 50 }),
    // Relationship with guardian (e.g. Parent, Sibling, Spouse)
    guardian_relation: varchar({ length: 100 }),
    // Unique device identifier
    device_id: varchar({ length: 255 }),
    // Firebase Cloud Messaging token for push notifications
    fcm_token: text(),
    // Currently installed application version
    app_version: varchar({ length: 50 }),
    // Timestamp of user's last app activity
    last_active_at: timestamp(),
    // 1: Active, 0: Inactive
    is_active: tinyint().notNull().default(1),
    // 0: Pending, 1: Approved, 2: Rejected
    approval_status: tinyint().notNull().default(0),
    // Reason for rejection if registration request is rejected
    rejection_reason: text(),
    // Special assistance notes or observations
    notes: text(),
    created_by: bigint({ mode: 'number', unsigned: true }).references((): AnyMySqlColumn => usersTable.id),
    created_at: timestamp(),
    updated_by: bigint({ mode: 'number', unsigned: true }).references((): AnyMySqlColumn => usersTable.id),
    updated_at: timestamp(),
    deleted_by: bigint({ mode: 'number', unsigned: true }).references((): AnyMySqlColumn => usersTable.id),
    deleted_at: timestamp(),
}, (t) => [
    index('app_users_registration_partner_id_index').on(t.registration_partner_id),
    index('app_users_phone_number_index').on(t.phone_number),
    index('app_users_visual_impairment_level_index').on(t.visual_impairment_level),
    index('app_users_is_active_index').on(t.is_active),
    index('app_users_approval_status_index').on(t.approval_status),
    index('app_users_last_active_at_index').on(t.last_active_at),
]);
