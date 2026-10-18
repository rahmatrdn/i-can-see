# Social Media DB — Drizzle + MySQL Migration Research

Research project demonstrating database schema design and migration workflow for a social media app using **Drizzle ORM**, **MySQL**, **Hono**, and **Bun**.

## Stack

| Tool | Role |
|---|---|
| [Bun](https://bun.sh) | Runtime & package manager |
| [Hono](https://hono.dev) | Minimal HTTP framework |
| [Drizzle ORM](https://orm.drizzle.team) | Type-safe ORM + migration kit |
| MySQL | Database |

## Schema Overview

10 tables covering a typical social media domain:

- **users** — accounts with roles, verification, avatar/cover
- **user_follows** — follower/following graph
- **posts** — content with visibility control (`public` / `followers` / `private`) + soft delete
- **post_media** — image, video, or audio attachments per post
- **comments** — nested replies (self-referencing `parent_id`) + soft delete
- **reactions** — polymorphic (`post` | `comment`) with 6 reaction types
- **conversations** — direct messages and group chats
- **conversation_participants** — members + last-read tracking
- **messages** — content/media with soft delete
- **notifications** — polymorphic events (`follow`, `like`, `comment`, `message`, `mention`)

## Project Structure

```
.
├── src/
│   ├── index.ts          # Hono app entry point
│   └── db/
│       ├── index.ts      # Drizzle client (mysql2)
│       └── schema.ts     # All table definitions + relations
├── scripts/
│   └── generate.ts       # Interactive migration name prompt
├── drizzle/
│   ├── 0000_init.sql     # Generated SQL migration
│   └── meta/             # Drizzle migration metadata
└── drizzle.config.ts     # Drizzle Kit config
```

## Setup

**1. Install dependencies**

```bash
bun install
```

**2. Configure environment**

```bash
cp .env.example .env
# Edit DATABASE_URL
```

`.env.example`:
```
DATABASE_URL=mysql://root:root@localhost:3306/test_socmed_db
```

**3. Generate migration**

```bash
bun db:generate
# Prompts for a migration name, then runs drizzle-kit generate
```

**4. Run migration**

```bash
bun db:migrate
# Applies pending migrations to the database
```

**5. Start dev server**

```bash
bun dev
# open http://localhost:3000
```

## Scripts

| Command | Description |
|---|---|
| `bun dev` | Start Hono server with hot reload |
| `bun db:generate` | Generate new SQL migration (interactive name prompt + auto comments) |
| `bun db:comments` | Inject column comments from schema.ts into generated SQL migrations |
| `bun db:migrate` | Apply pending migrations to the database |
