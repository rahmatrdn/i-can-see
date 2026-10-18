import db from '../db';
import { usersTable } from '../db/schema';
import { eq } from 'drizzle-orm';

const SUPER_ADMIN_EMAIL = process.env.SEED_SUPERADMIN_EMAIL ?? 'admin@admin.com';
const SUPER_ADMIN_PASSWORD = process.env.SEED_SUPERADMIN_PASSWORD ?? 'password';
const SUPER_ADMIN_NAME = process.env.SEED_SUPERADMIN_NAME ?? 'Super User';

export async function superAdminSeeder(): Promise<void> {
    const hash = await Bun.password.hash(SUPER_ADMIN_PASSWORD, {
        algorithm: 'bcrypt',
        cost: 12,
    });
    // PHP Hash::make() uses $2y$ prefix; Bun produces $2b$ — both are valid bcrypt but must match
    const hashedPassword = hash.replace(/^\$2b\$/, '$2y$');

    const existing = await db
        .select({ id: usersTable.id })
        .from(usersTable)
        .where(eq(usersTable.email, SUPER_ADMIN_EMAIL))
        .limit(1);

    if (existing.length > 0) {
        await db
            .update(usersTable)
            .set({
                name: SUPER_ADMIN_NAME,
                password: hashedPassword,
                access_type: 1,
                updated_at: new Date(),
            })
            .where(eq(usersTable.email, SUPER_ADMIN_EMAIL));

        console.log(`[superAdminSeeder] Updated existing user: ${SUPER_ADMIN_EMAIL}`);
    } else {
        await db.insert(usersTable).values({
            name: SUPER_ADMIN_NAME,
            email: SUPER_ADMIN_EMAIL,
            password: hashedPassword,
            access_type: 1,
            created_at: new Date(),
            updated_at: new Date(),
        });

        console.log(`[superAdminSeeder] Created super admin: ${SUPER_ADMIN_EMAIL}`);
    }
}
