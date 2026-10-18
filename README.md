# iCanSee

![iCanSee — AI-powered visual assistance for visually impaired individuals](_docs/banner.png)

**AI-powered visual assistance for visually impaired individuals.**
See objects, read text, and describe surroundings using only voice and a smartphone camera.

## About iCanSee

People with visual impairments face various challenges in carrying out daily activities, particularly when accessing visual information such as identifying objects, reading text, or understanding their surroundings.

Meanwhile, advances in Artificial Intelligence (AI), particularly Computer Vision and Generative AI, create opportunities to develop solutions that are more accessible and easier to use.

iCanSee aims to leverage these technologies to empower visually impaired individuals to access visual information independently through their smartphones.

## Platform Overview

iCanSee consists of three parts:

| # | Component | Description | Location |
|---|-----------|-------------|----------|
| 1 | **Landing Page** | Public website that introduces iCanSee, its mission, features, and how to get started. | `_portal-service` (`/`) |
| 2 | **Partner & Admin Portal** | Back office for administrators and partners (foundations, communities, schools, institutions) to register and manage visually impaired app users. | `_portal-service` (`/admin`, `/partner`) |
| 3 | **User App** | Voice-first mobile web app for visually impaired users, powered by Google Gemini. | `_app-service` |

```mermaid
flowchart LR
    subgraph Portal["_portal-service (Laravel)"]
        LP["Landing Page"]
        AP["Admin & Partner Portal"]
    end

    subgraph App["_app-service (React + Express)"]
        UA["User App"]
    end

    MIG["db-migrator-with-drizzle"] -->|schema, migrations, seed| DB[("MySQL<br/>shared database")]
    AP -->|register & approve app users| DB
    UA -->|log in against users table| DB
    UA -->|vision, Q&A, transcription, TTS| GEM["Google Gemini API"]
```

## Features

### User App

- **Voice Q&A** – Ask anything by voice. The assistant answers in natural, warm spoken English and keeps short conversation context.
- **See Objects & Describe Surroundings** – Capture a photo and get a spatial description: what is in the center, left, or right, approximate distance, colors, and obstacles or safety hazards.
- **Read Text** – Point the camera at printed text and ask the assistant to read it.
- **Voice-triggered camera** – Saying phrases such as "take a picture" or "read this" (also Indonesian phrases like "ambil foto" or "baca ini") switches the app to camera mode automatically.
- **Hands-free listening** – Voice activity detection starts recording when you speak and submits automatically after a short silence.
- **Audio & haptic feedback** – Pre-recorded voice prompts, chimes, a shutter sound, and vibration patterns confirm every step so the screen never needs to be seen.
- **Screen reader friendly** – The whole screen is one large, focusable touch target with `aria-live` status announcements. Keyboard (Enter / Space) is supported too.
- **Speech output with fallbacks** – Server-side text-to-speech, with Gemini TTS and the browser's speech synthesis as fallbacks.

### Partner & Admin Portal

- **Partner self-registration** – Foundations, communities, schools, institutions, or individuals can sign up as partners.
- **App user management** – Partners register visually impaired users (impairment level, guardian info, contact details, and more).
- **Approval workflow** – Super Admins approve or reject registrations. Only approved users can log in to the User App. Partners can resubmit rejected requests.
- **User & role management** – Super Admin, Partner, and App User roles, with a database-driven, role-based sidebar menu.
- **Dashboard** – Statistics for users and app users.

## How the User App Works

The entire screen acts as a single button, so users never have to find a small control.

| Goal | Gesture |
|------|---------|
| Talk to the assistant | **Tap once** anywhere, wait for "Hi there.", then speak |
| Open the camera | **Double-tap** anywhere, or say "take a picture" |
| Take the photo | **Tap once** while the camera is aiming |
| Stop or cancel | **Tap once** while the assistant is processing or speaking |

Interaction flow:

```mermaid
stateDiagram-v2
    [*] --> IDLE
    IDLE --> ASKING_INTENT: tap once
    ASKING_INTENT --> LISTENING_VOICE: greeting ends
    LISTENING_VOICE --> PROCESSING_AI: user stops speaking
    LISTENING_VOICE --> IDLE: no speech (7s)
    IDLE --> AIMING_CAMERA: double-tap
    PROCESSING_AI --> AIMING_CAMERA: camera intent detected
    AIMING_CAMERA --> CAPTURING_CAMERA: tap once
    CAPTURING_CAMERA --> PROCESSING_AI
    PROCESSING_AI --> SPEAKING_RESPONSE
    SPEAKING_RESPONSE --> IDLE: answer finished
```

Users log in with an account created through the Partner & Admin Portal. The account must be approved (active) before login succeeds.

## Repository Structure

```
i-can-see/
├── _app-service/                  # User App (voice & vision assistant)
│   ├── server.ts                  # Express server: Gemini assist, TTS, auth routes
│   ├── server/                    # JWT auth and MySQL connection
│   ├── src/
│   │   ├── features/              # assistant, camera, permissions, auth
│   │   ├── components/layout/     # Header and layout components
│   │   └── utils/                 # Sound effects, haptics, audio playback
│   └── public/audio/              # Pre-recorded voice prompts and shutter sound
├── _portal-service/               # Landing Page + Partner & Admin Portal
│   ├── app/                       # Controllers, Usecases, Middleware, Constants
│   ├── resources/views/           # Blade views (landing, _admin, _partner)
│   ├── routes/web.php             # All web routes
│   ├── db-migrator-with-drizzle/  # MySQL schema, migrations, and seeders
│   └── deploy/                    # php.ini and SQL patches
└── _docs/                         # Project assets
```

## Tech Stack

| Area | Technology |
|------|------------|
| User App frontend | React 19, TypeScript, Vite, Tailwind CSS 4, Motion, Lucide icons |
| User App backend | Express, `@google/genai` (Gemini), JWT, bcrypt, mysql2 |
| Portal | Laravel 13, PHP 8.2+, Blade, Tailwind CSS 4, Preline UI, Vite |
| Database | MySQL, schema managed with Drizzle ORM |
| Tooling | Bun, Composer, Pest (PHP tests) |
| Deployment | Docker (Bun image for the User App, FrankenPHP for the Portal) |

## Getting Started

### Prerequisites

- [Bun](https://bun.sh) and Node.js 20+
- PHP 8.2+ and Composer
- MySQL 8
- A [Google Gemini API key](https://aistudio.google.com/apikey)

All three services must point to the **same MySQL database**.

### 1. Set up the database

```bash
cd _portal-service/db-migrator-with-drizzle
cp .env.example .env        # set DATABASE_URL, e.g. mysql://user:password@localhost:3306/icansee_db
bun install
bun run db:migrate          # apply migrations
bun run db:seed             # create the Super Admin account and sidebar menus
```

Run `bun run db:generate` only after changing the schema in `src/db/`. The Super Admin seed can be customized with `SEED_SUPERADMIN_EMAIL`, `SEED_SUPERADMIN_NAME`, and `SEED_SUPERADMIN_PASSWORD`. Check the defaults in `src/seeds/superAdminSeeder.ts` and change them before any non-local use.

### 2. Run the Landing Page and Portal

```bash
cd _portal-service
composer setup              # install dependencies, create .env, generate app key, build assets
```

Set the database keys in `.env` (`DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`), then start the dev servers:

```bash
composer dev                # Laravel server, queue listener, log viewer, and Vite
```

- Landing page: `http://localhost:8000`
- Portal login: `http://localhost:8000/login`

### 3. Run the User App

```bash
cd _app-service
cp .env.example .env
bun install
bun run dev
```

Open `http://localhost:3000`.

| Variable | Description |
|----------|-------------|
| `GEMINI_API_KEY` | Google Gemini API key |
| `APP_URL` | Public URL of the app |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Shared MySQL database connection |
| `JWT_SECRET` | Secret used to sign login tokens. Always set a strong value. |
| `PORT` | Server port (default `3000`) |

## Docker

```bash
# User App (default: build context _app-service)
docker build -t icansee-app _app-service

# User App (jika build context dari root repository)
docker build -f _app-service/Dockerfile --build-arg SERVICE_DIR=_app-service -t icansee-app .

# Portal (build context is the repository root)
docker build -f _portal-service/Dockerfile -t icansee-portal .
```
