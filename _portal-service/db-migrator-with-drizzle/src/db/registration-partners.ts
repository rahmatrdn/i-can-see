import {
    bigint,
    index,
    mysqlTable,
    text,
    timestamp,
    tinyint,
    varchar,
} from 'drizzle-orm/mysql-core';
import type { AnyMySqlColumn } from 'drizzle-orm/mysql-core';
import { usersTable } from './schema';

export const registrationPartnersTable = mysqlTable('registration_partners', {
    id: bigint({ mode: 'number', unsigned: true }).autoincrement().primaryKey(),
    // Associated user login account
    user_id: bigint({ mode: 'number', unsigned: true }).references((): AnyMySqlColumn => usersTable.id),
    // Partner, foundation, or organization name
    name: varchar({ length: 255 }).notNull(),
    // Type: foundation, community, school, institution, individual, other
    type: varchar({ length: 50 }).notNull().default('foundation'),
    // Person in charge (PIC) full name
    pic_name: varchar({ length: 255 }).notNull(),
    // Primary phone or WhatsApp number of PIC
    pic_phone: varchar({ length: 50 }).notNull(),
    // Primary email of PIC or organization
    pic_email: varchar({ length: 255 }),
    // Full office or organization address
    address: text(),
    // City or regency
    city: varchar({ length: 100 }),
    // Province or state
    province: varchar({ length: 100 }),
    // Postal code
    postal_code: varchar({ length: 20 }),
    // 0: Pending, 1: Verified, 2: Rejected
    verification_status: tinyint().notNull().default(1),
    // Additional notes or partner description
    notes: text(),
    // 1: Active, 0: Inactive
    is_active: tinyint().notNull().default(1),
    created_by: bigint({ mode: 'number', unsigned: true }).references((): AnyMySqlColumn => usersTable.id),
    created_at: timestamp(),
    updated_by: bigint({ mode: 'number', unsigned: true }).references((): AnyMySqlColumn => usersTable.id),
    updated_at: timestamp(),
    deleted_by: bigint({ mode: 'number', unsigned: true }).references((): AnyMySqlColumn => usersTable.id),
    deleted_at: timestamp(),
}, (t) => [
    index('registration_partners_user_id_index').on(t.user_id),
    index('registration_partners_type_index').on(t.type),
    index('registration_partners_pic_phone_index').on(t.pic_phone),
    index('registration_partners_is_active_index').on(t.is_active),
]);
