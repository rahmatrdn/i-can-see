<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>iCanSee — See Beyond Sight, Empower Independence</title>
    <meta name="description" content="iCanSee is an AI-powered visual assistant for people with visual impairments. Tap once to ask, double tap to see — right from your smartphone's web browser.">
    <meta name="theme-color" content="#F6F9FC">

    @include('_admin._layout.favicon')

    {{-- Typography: Plus Jakarta Sans (display) + Atkinson Hyperlegible Next (body, built for low-vision readers) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible+Next:wght@400;500;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    {{-- Progressive enhancement flag: reveal/accordion styles only apply when JS runs --}}
    <script>document.documentElement.classList.add('js');</script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* =========================================================
           V5 — "Soft Aurora" design tokens
           Calm sky / aqua / peach gradients on a cool canvas.
           All text pairs measured >= 4.5:1 (body) / >= 3:1 (display).
           ========================================================= */
        :root {
            --v5-canvas: #F6F9FC;
            --v5-surface: #FFFFFF;
            --v5-ink: #15203B;          /* 15.9:1 on white */
            --v5-ink-2: #46526B;        /* 7.8:1 on white */
            --v5-ink-3: #5E6A84;        /* 5.4:1 on white */
            --v5-line: #E3EBF4;
            --v5-line-soft: #EEF3F8;
            --v5-navy: #26335C;
            --v5-navy-deep: #1A233F;
            --v5-blue: #2F6DA6;         /* 5.4:1 on white */
            --v5-sky: #77B1DC;
            --v5-sky-100: #DDEBF8;
            --v5-sky-50: #F0F7FD;
            --v5-aqua: #7FD1C7;
            --v5-ease: cubic-bezier(0.22, 1, 0.36, 1);
            --v5-ease-soft: cubic-bezier(0.4, 0, 0.2, 1);
            --v5-ease-hover: cubic-bezier(0.25, 1, 0.5, 1);   /* gentle ease-out, no overshoot */
            --v5-hover-dur: 0.55s;
            --v5-header-h: 88px;
        }

        html { scroll-behavior: smooth; scroll-padding-top: var(--v5-header-h); }
        body {
            font-family: 'Atkinson Hyperlegible Next', 'Atkinson Hyperlegible', ui-sans-serif, system-ui, -apple-system, sans-serif;
            font-size: 17px;
            line-height: 1.65;
            background: var(--v5-canvas);
            color: var(--v5-ink);
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }
        h1, h2, h3, h4, .v5-display {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
            letter-spacing: -0.022em;
            text-wrap: balance;
        }
        p { text-wrap: pretty; }
        ::selection { background: rgba(119, 177, 220, 0.35); color: var(--v5-navy-deep); }

        /* ---------- Focus (WCAG 2.4.7 / 2.4.11) ---------- */
        :focus-visible { outline: 3px solid var(--v5-blue); outline-offset: 3px; }
        .v5-on-dark :focus-visible { outline-color: #BFE0F7; }

        /* ---------- Gradients ---------- */
        .v5-text-gradient {
            background: linear-gradient(100deg, #26335C 0%, #2F6DA6 55%, #2A8B8C 100%);
            -webkit-background-clip: text; background-clip: text;
            color: transparent;
        }
        .v5-text-gradient-light {
            background: linear-gradient(100deg, #FFFFFF 0%, #CFE6F8 50%, #BDEDE5 100%);
            -webkit-background-clip: text; background-clip: text;
            color: transparent;
        }
        .v5-grad-dark {
            background:
                radial-gradient(60% 80% at 100% 0%, rgba(127, 209, 199, 0.28), transparent 60%),
                radial-gradient(55% 70% at 0% 100%, rgba(119, 177, 220, 0.35), transparent 60%),
                linear-gradient(135deg, #1E2A4D 0%, #26335C 40%, #2D5A8C 100%);
        }
        .v5-grad-soft {
            background:
                radial-gradient(60% 90% at 0% 0%, rgba(119, 177, 220, 0.30), transparent 60%),
                radial-gradient(50% 80% at 100% 10%, rgba(127, 209, 199, 0.26), transparent 60%),
                radial-gradient(60% 80% at 80% 100%, rgba(253, 214, 190, 0.38), transparent 60%),
                linear-gradient(180deg, #FFFFFF, #F4F8FC);
        }
        .v5-hero-mesh {
            background:
                radial-gradient(42% 46% at 12% 12%, rgba(119, 177, 220, 0.38), transparent 70%),
                radial-gradient(36% 40% at 92% 18%, rgba(127, 209, 199, 0.30), transparent 70%),
                radial-gradient(40% 44% at 72% 92%, rgba(253, 214, 190, 0.42), transparent 70%),
                radial-gradient(34% 36% at 4% 88%, rgba(185, 198, 242, 0.32), transparent 70%);
        }
        .v5-dots {
            background-image: radial-gradient(rgba(38, 51, 92, 0.09) 1px, transparent 1px);
            background-size: 22px 22px;
            -webkit-mask-image: radial-gradient(ellipse 60% 55% at 50% 40%, #000 20%, transparent 75%);
                    mask-image: radial-gradient(ellipse 60% 55% at 50% 40%, #000 20%, transparent 75%);
        }

        /* ---------- Elevation (blue-tinted, whisper soft) ---------- */
        .v5-shadow-sm { box-shadow: 0 1px 2px rgba(21, 32, 59, 0.04), 0 4px 12px -4px rgba(38, 51, 92, 0.08); }
        .v5-shadow    { box-shadow: 0 2px 4px rgba(21, 32, 59, 0.03), 0 14px 34px -14px rgba(38, 51, 92, 0.16); }
        .v5-shadow-lg { box-shadow: 0 4px 10px rgba(21, 32, 59, 0.04), 0 34px 70px -24px rgba(38, 51, 92, 0.28); }

        /* ---------- Surfaces ---------- */
        .v5-glass {
            background: rgba(255, 255, 255, 0.72);
            -webkit-backdrop-filter: blur(18px) saturate(1.4);
                    backdrop-filter: blur(18px) saturate(1.4);
            border: 1px solid rgba(255, 255, 255, 0.75);
        }
        .v5-band {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.92), rgba(255, 255, 255, 0.7));
            border: 1px solid var(--v5-line-soft);
        }
        .v5-card {
            position: relative;
            /* background-color (not shorthand) so gradient utilities like .v5-grad-dark still apply */
            background-color: var(--v5-surface);
            border: 1px solid var(--v5-line);
            border-radius: 28px;
            transition:
                transform var(--v5-hover-dur) var(--v5-ease-hover),
                box-shadow var(--v5-hover-dur) var(--v5-ease-hover),
                border-color 0.4s var(--v5-ease-soft);
        }
        /* Gradient ring that fades in on hover */
        .v5-card::after {
            content: "";
            position: absolute; inset: -1px;
            border-radius: inherit;
            padding: 1.5px;
            background: linear-gradient(135deg, rgba(119, 177, 220, 0.9), rgba(127, 209, 199, 0.8), rgba(250, 196, 166, 0.85));
            -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
            -webkit-mask-composite: xor;
                    mask-composite: exclude;
            opacity: 0;
            transition: opacity 0.5s var(--v5-ease-soft);
            pointer-events: none;
        }

        /* Lightweight lift for non-card surfaces (flow nodes, step cards) */
        .v5-lift {
            transition:
                transform var(--v5-hover-dur) var(--v5-ease-hover),
                box-shadow var(--v5-hover-dur) var(--v5-ease-hover),
                background-color 0.4s var(--v5-ease-soft);
        }

        /* ---------- Hover (pointer devices only — avoids sticky hover on touch) ---------- */
        @media (hover: hover) and (pointer: fine) {
            .v5-card-hover:hover {
                transform: translate3d(0, -6px, 0);
                box-shadow: 0 6px 14px -6px rgba(21, 32, 59, 0.08), 0 32px 64px -30px rgba(38, 51, 92, 0.34);
            }
            .v5-card-hover:hover::after { opacity: 1; }
            .v5-card-hover:hover .v5-tile { transform: translate3d(0, -2px, 0) scale(1.06); }

            .v5-lift:hover,
            .v5-step:hover .v5-step-card {
                transform: translate3d(0, -4px, 0);
                background-color: #FFFFFF;
                box-shadow: 0 4px 10px -4px rgba(21, 32, 59, 0.06), 0 22px 44px -22px rgba(38, 51, 92, 0.3);
            }
            .v5-lift:hover .v5-tile,
            .v5-step:hover .v5-tile { transform: translate3d(0, -3px, 0) scale(1.05); }

            [data-faq-item]:hover { border-color: #CFE2F3; }
        }

        /* ---------- Icon tiles ---------- */
        .v5-tile {
            display: inline-flex; align-items: center; justify-content: center; border-radius: 18px; flex-shrink: 0;
            transition: transform var(--v5-hover-dur) var(--v5-ease-hover);
        }
        .v5-step-card {
            transition:
                transform var(--v5-hover-dur) var(--v5-ease-hover),
                box-shadow var(--v5-hover-dur) var(--v5-ease-hover),
                background-color 0.4s var(--v5-ease-soft);
        }
        .v5-tile-sky   { background: linear-gradient(135deg, #E6F1FB, #CFE3F5); color: #26335C; }
        .v5-tile-aqua  { background: linear-gradient(135deg, #E3F6F2, #C7ECE4); color: #17605B; }
        .v5-tile-peach { background: linear-gradient(135deg, #FFF2E9, #F9DAC6); color: #874623; }
        .v5-tile-peri  { background: linear-gradient(135deg, #EEF0FC, #D9DEF7); color: #33408A; }
        .v5-tile-navy  { background: linear-gradient(135deg, #26335C, #2F5C93); color: #FFFFFF; }

        /* ---------- Eyebrow ---------- */
        .v5-eyebrow {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.4rem 0.9rem 0.4rem 0.6rem;
            border-radius: 999px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem; font-weight: 700; letter-spacing: 0.01em;
            color: var(--v5-blue);
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid var(--v5-sky-100);
            box-shadow: 0 1px 2px rgba(38, 51, 92, 0.05);
        }
        .v5-eyebrow-dot {
            width: 8px; height: 8px; border-radius: 999px;
            background: linear-gradient(135deg, #77B1DC, #7FD1C7);
            box-shadow: 0 0 0 4px rgba(119, 177, 220, 0.18);
        }
        .v5-on-dark .v5-eyebrow { color: #D6EAF9; background: rgba(255, 255, 255, 0.08); border-color: rgba(255, 255, 255, 0.16); box-shadow: none; }

        /* ---------- Buttons ---------- */
        .v5-btn {
            position: relative;
            display: inline-flex; align-items: center; justify-content: center; gap: 0.625rem;
            min-height: 52px; padding: 0 1.5rem;
            border-radius: 999px;
            font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; font-size: 0.975rem;
            cursor: pointer; white-space: nowrap;
            transition: transform 0.35s var(--v5-ease), box-shadow 0.35s var(--v5-ease), background-color 0.25s var(--v5-ease-soft), background-position 0.6s var(--v5-ease), color 0.25s;
        }
        .v5-btn:active { transform: translateY(0) scale(0.98); }
        .v5-btn-primary {
            color: #FFFFFF;
            background-image: linear-gradient(120deg, #26335C 0%, #2B4A80 45%, #2F6DA6 100%);
            background-size: 180% 100%;
            background-position: 0% 50%;
            box-shadow: 0 12px 26px -12px rgba(38, 51, 92, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.16);
        }
        .v5-btn-primary:hover { background-position: 100% 50%; transform: translateY(-2px); box-shadow: 0 18px 34px -14px rgba(47, 109, 166, 0.65), inset 0 1px 0 rgba(255, 255, 255, 0.2); }
        .v5-btn-secondary {
            color: var(--v5-navy);
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid var(--v5-line);
            box-shadow: 0 1px 2px rgba(21, 32, 59, 0.04);
        }
        .v5-btn-secondary:hover { background: #FFFFFF; border-color: #C9DDF0; transform: translateY(-2px); box-shadow: 0 12px 24px -14px rgba(38, 51, 92, 0.3); }
        .v5-btn-sky { color: var(--v5-navy-deep); background: linear-gradient(135deg, #9CCBEB, #77B1DC); box-shadow: 0 12px 26px -12px rgba(119, 177, 220, 0.8), inset 0 1px 0 rgba(255, 255, 255, 0.5); }
        .v5-btn-sky:hover { transform: translateY(-2px); background: linear-gradient(135deg, #B0D6F0, #86BBE1); }
        .v5-btn-ghost-light { color: #FFFFFF; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.24); }
        .v5-btn-ghost-light:hover { background: rgba(255, 255, 255, 0.14); transform: translateY(-2px); }
        .v5-btn-arrow {
            display: inline-flex; align-items: center; justify-content: center;
            width: 28px; height: 28px; border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            transition: transform 0.35s var(--v5-ease);
        }
        .v5-btn:hover .v5-btn-arrow { transform: translateX(3px); }

        /* ---------- Header ---------- */
        .v5-header-bar { transition: background-color 0.4s var(--v5-ease-soft), box-shadow 0.4s var(--v5-ease-soft), border-color 0.4s; }
        [data-scrolled="true"] .v5-header-bar { background: rgba(255, 255, 255, 0.88); box-shadow: 0 10px 30px -16px rgba(38, 51, 92, 0.25); border-color: var(--v5-line); }
        .v5-nav-link {
            position: relative;
            display: inline-flex; align-items: center;
            min-height: 40px; padding: 0 0.875rem;
            border-radius: 999px;
            font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 600; font-size: 0.9rem;
            color: var(--v5-ink-2);
            transition: color 0.25s, background-color 0.25s;
        }
        .v5-nav-link:hover { color: var(--v5-navy); background: rgba(221, 235, 248, 0.6); }
        .v5-nav-link[aria-current="true"] { color: var(--v5-navy); background: var(--v5-sky-100); }
        #v5-menu {
            transform-origin: top center;
            transition: opacity 0.3s var(--v5-ease-soft), transform 0.4s var(--v5-ease), visibility 0s linear 0s;
        }
        #v5-menu[data-open="false"] {
            opacity: 0; visibility: hidden; pointer-events: none;
            transform: translateY(-8px) scale(0.98);
            transition: opacity 0.2s var(--v5-ease-soft), transform 0.3s var(--v5-ease), visibility 0s linear 0.3s;
        }

        /* ---------- Hero phone demo ---------- */
        @keyframes v5Float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        /* 6s loop: one tap at 8%, then a double tap at 56% / 68% */
        @keyframes v5TapPress {
            0%, 4%, 14%, 52%, 62%, 64%, 74%, 100% { transform: scale(1); }
            8%, 57%, 69% { transform: scale(0.92); }
        }
        @keyframes v5TapRingA { 0%, 7.9% { transform: scale(1); opacity: 0; } 8% { transform: scale(1); opacity: 0.7; } 40%, 100% { transform: scale(1.85); opacity: 0; } }
        @keyframes v5TapRingB { 0%, 56.9% { transform: scale(1); opacity: 0; } 57% { transform: scale(1); opacity: 0.7; } 88%, 100% { transform: scale(1.85); opacity: 0; } }
        @keyframes v5TapRingC { 0%, 68.9% { transform: scale(1); opacity: 0; } 69% { transform: scale(1); opacity: 0.7; } 99%, 100% { transform: scale(1.85); opacity: 0; } }
        .v5-float { animation: v5Float 7s ease-in-out infinite; }
        .v5-tap-press { animation: v5TapPress 6s var(--v5-ease-soft) infinite; }
        .v5-tap-ring { opacity: 0; }
        .v5-tap-ring-a { animation: v5TapRingA 6s var(--v5-ease) infinite; }
        .v5-tap-ring-b { animation: v5TapRingB 6s var(--v5-ease) infinite; }
        .v5-tap-ring-c { animation: v5TapRingC 6s var(--v5-ease) infinite; }
        [data-paused="true"] .v5-anim,
        [data-paused="true"] .v5-anim * { animation-play-state: paused !important; }

        /* ---------- Scroll reveal (JS only) ---------- */
        /* Uses an animation on `translate` (not a transition on `transform`) so it never
           overrides the hover transitions of the element it is applied to. */
        @keyframes v5RevealIn { from { opacity: 0; translate: 0 22px; } to { opacity: 1; translate: 0 0; } }
        .js .v5-reveal { opacity: 0; }
        .js .v5-reveal.is-in { animation: v5RevealIn 0.9s var(--v5-ease) var(--d, 0ms) both; }

        /* ---------- FAQ accordion (JS only) ---------- */
        .v5-faq-panel { display: grid; grid-template-rows: 1fr; }
        .v5-faq-panel > div { overflow: hidden; }
        .js [data-faq-item] .v5-faq-panel {
            grid-template-rows: 0fr; visibility: hidden;
            transition: grid-template-rows 0.45s var(--v5-ease), visibility 0s linear 0.45s;
        }
        .js [data-faq-item][data-open="true"] .v5-faq-panel {
            grid-template-rows: 1fr; visibility: visible;
            transition: grid-template-rows 0.45s var(--v5-ease), visibility 0s linear 0s;
        }
        .v5-faq-icon { transition: transform 0.45s var(--v5-ease), background-color 0.3s, color 0.3s; }
        [data-faq-item][data-open="true"] .v5-faq-icon { transform: rotate(45deg); background: var(--v5-navy); color: #FFFFFF; }
        [data-faq-item] { transition: box-shadow 0.4s var(--v5-ease), border-color 0.3s; }
        [data-faq-item][data-open="true"] { border-color: #CFE2F3; box-shadow: 0 18px 40px -24px rgba(38, 51, 92, 0.3); }

        /* ---------- Dialog ---------- */
        .v5-dialog { border: 0; padding: 0; background: transparent; max-width: min(560px, calc(100vw - 2rem)); width: 100%; overflow: visible; }
        .v5-dialog::backdrop { background: rgba(21, 32, 59, 0.45); -webkit-backdrop-filter: blur(6px); backdrop-filter: blur(6px); }
        .v5-dialog[open] { animation: v5DialogIn 0.45s var(--v5-ease); }
        .v5-dialog[open]::backdrop { animation: v5Fade 0.35s var(--v5-ease-soft); }
        @keyframes v5DialogIn { from { opacity: 0; transform: translateY(14px) scale(0.97); } to { opacity: 1; transform: none; } }
        @keyframes v5Fade { from { opacity: 0; } to { opacity: 1; } }
        .v5-input {
            width: 100%; min-height: 50px;
            padding: 0.75rem 1rem;
            border-radius: 16px;
            border: 1px solid #D5E1EE;
            background: #FBFDFF;
            color: var(--v5-ink);
            font-size: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s, background-color 0.2s;
        }
        .v5-input::placeholder { color: #7C879E; }
        .v5-input:hover { border-color: #BCD2E8; }
        .v5-input:focus { outline: none; border-color: var(--v5-blue); background: #FFFFFF; box-shadow: 0 0 0 4px rgba(47, 109, 166, 0.18); }
        .v5-input[aria-invalid="true"] { border-color: #C2410C; box-shadow: 0 0 0 4px rgba(194, 65, 12, 0.12); }

        /* ---------- Reduced motion (WCAG 2.3.3) ---------- */
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            *, *::before, *::after { animation-duration: 0.01ms !important; animation-iteration-count: 1 !important; transition-duration: 0.01ms !important; transition-delay: 0ms !important; }
            .js .v5-reveal, .js .v5-reveal.is-in { opacity: 1; animation: none !important; }
            .v5-card-hover:hover, .v5-lift:hover, .v5-step:hover .v5-step-card,
            .v5-card-hover:hover .v5-tile, .v5-lift:hover .v5-tile, .v5-step:hover .v5-tile { transform: none !important; }
            .v5-motion-only { display: none !important; }
        }
    </style>
</head>
@php
    /*
     | Inline icon set (Lucide-style, 24px grid, round caps).
     | Paths are static strings, so rendering them unescaped is safe.
     */
    $icons = [
        'arrow-right' => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
        'check' => '<path d="M20 6 9 17l-5-5"/>',
        'plus' => '<path d="M5 12h14"/><path d="M12 5v14"/>',
        'x' => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
        'menu' => '<path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h10"/>',
        'eye' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
        'pointer' => '<path d="M22 14a8 8 0 0 1-8 8"/><path d="M18 11v-1a2 2 0 0 0-4 0"/><path d="M14 10V9a2 2 0 0 0-4 0v1"/><path d="M10 9.5V4a2 2 0 0 0-4 0v10"/><path d="M18 11a2 2 0 1 1 4 0v3a8 8 0 0 1-8 8h-2c-2.8 0-4.5-.86-5.99-2.34l-3.6-3.6a2 2 0 0 1 2.83-2.82L7 15"/>',
        'sparkles' => '<path d="M9.94 15.5A2 2 0 0 0 8.5 14.06l-6.14-1.58a.5.5 0 0 1 0-.96L8.5 9.94A2 2 0 0 0 9.94 8.5l1.58-6.14a.5.5 0 0 1 .96 0l1.58 6.14a2 2 0 0 0 1.44 1.44l6.14 1.58a.5.5 0 0 1 0 .96l-6.14 1.58a2 2 0 0 0-1.44 1.44l-1.58 6.14a.5.5 0 0 1-.96 0z"/><path d="M20 3v4"/><path d="M22 5h-4"/>',
        'mic' => '<path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><path d="M12 19v3"/>',
        'globe' => '<circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/>',
        'smartphone' => '<rect width="14" height="20" x="5" y="2" rx="3"/><path d="M12 18h.01"/>',
        'heart' => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
        'camera' => '<path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/>',
        'scan' => '<path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><circle cx="12" cy="12" r="3"/>',
        'scan-text' => '<path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><path d="M7 8h8"/><path d="M7 12h10"/><path d="M7 16h6"/>',
        'message' => '<path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/><path d="M8 12h.01"/><path d="M12 12h.01"/><path d="M16 12h.01"/>',
        'volume' => '<path d="M11 4.7a.7.7 0 0 0-1.2-.5L6.41 7.59A1.4 1.4 0 0 1 5.42 8H3a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h2.42a1.4 1.4 0 0 1 .99.41l3.39 3.39a.7.7 0 0 0 1.2-.5z"/><path d="M16 9a5 5 0 0 1 0 6"/><path d="M19.36 18.36a9 9 0 0 0 0-12.72"/>',
        'accessibility' => '<circle cx="16" cy="4" r="1"/><path d="m18 19 1-7-6 1"/><path d="m5 8 3-3 5.5 3-2.36 3.5"/><path d="M4.24 14.5a5 5 0 0 0 6.88 6"/><path d="M13.76 17.5a5 5 0 0 0-6.88-6"/>',
        'languages' => '<path d="m5 8 6 6"/><path d="m4 14 6-6 2-3"/><path d="M2 5h12"/><path d="M7 2h1"/><path d="m22 22-5-10-5 10"/><path d="M14 18h6"/>',
        'user' => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'building' => '<path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/>',
        'megaphone' => '<path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>',
        'user-plus' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6"/><path d="M22 11h-6"/>',
        'badge-check' => '<path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m9 12 2 2 4-4"/>',
        'compass' => '<circle cx="12" cy="12" r="10"/><path d="m16.24 7.76-2.12 6.36-6.36 2.12 2.12-6.36z"/>',
        'shield' => '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>',
        'chart' => '<path d="M3 3v16a2 2 0 0 0 2 2h16"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/>',
        'activity' => '<path d="M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2"/>',
        'key' => '<path d="M2.59 17.41A2 2 0 0 0 2 18.83V21a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h1a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h.17a2 2 0 0 0 1.42-.59l.81-.81a6.5 6.5 0 1 0-4-4z"/><circle cx="16.5" cy="7.5" r=".5" fill="currentColor"/>',
        'cpu' => '<rect width="16" height="16" x="4" y="4" rx="2"/><rect width="6" height="6" x="9" y="9" rx="1"/><path d="M15 2v2"/><path d="M15 20v2"/><path d="M2 15h2"/><path d="M2 9h2"/><path d="M20 15h2"/><path d="M20 9h2"/><path d="M9 2v2"/><path d="M9 20v2"/>',
        'lightbulb' => '<path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/>',
        'info' => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>',
        'login' => '<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="m10 17 5-5-5-5"/><path d="M15 12H3"/>',
        'pause' => '<rect x="14" y="4" width="4" height="16" rx="1"/><rect x="6" y="4" width="4" height="16" rx="1"/>',
        'play' => '<path d="M6 4.5v15a1 1 0 0 0 1.53.85l12-7.5a1 1 0 0 0 0-1.7l-12-7.5A1 1 0 0 0 6 4.5z"/>',
        'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
        'alert' => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
        'hand' => '<path d="M18 11V6a2 2 0 0 0-4 0"/><path d="M14 10V4a2 2 0 0 0-4 0v2"/><path d="M10 10.5V6a2 2 0 0 0-4 0v8"/><path d="M18 8a2 2 0 1 1 4 0v6a8 8 0 0 1-8 8h-2c-2.8 0-4.5-.86-5.99-2.34l-3.6-3.6a2 2 0 0 1 2.83-2.82L7 15"/>',
        'switch-camera' => '<path d="M11 19H4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h5"/><path d="M13 5h7a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-5"/><circle cx="12" cy="12" r="3"/><path d="m18 22-3-3 3-3"/><path d="m6 2 3 3-3 3"/>',
        'mail' => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
    ];

    $svg = function (string $name, string $class = 'size-5', float $stroke = 1.9) use ($icons): string {
        return '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="' . $stroke . '" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . ($icons[$name] ?? '') . '</svg>';
    };

    $navPrimary = [
        ['href' => '#about', 'label' => 'About'],
        ['href' => '#how-it-works', 'label' => 'How It Works'],
        ['href' => '#features', 'label' => 'Features'],
        ['href' => '#foundations', 'label' => 'For Foundations'],
        ['href' => '#faq', 'label' => 'FAQ'],
    ];

    // Temporarily hidden: "For Foundations & Communities" section + its menu/footer links.
    // Set to true to bring everything back.
    $showFoundations = false;

    // Donations & partnerships are handled over WhatsApp
    $waUrl = 'https://wa.me/6282244213171';

    $navMobile = [
        ['href' => '#about', 'label' => 'About'],
        ['href' => '#why', 'label' => 'Why iCanSee?'],
        ['href' => '#how-it-works', 'label' => 'How It Works'],
        ['href' => '#features', 'label' => 'Features'],
        ['href' => '#who', 'label' => 'Who Is iCanSee For?'],
        ['href' => '#getting-started', 'label' => 'Getting Started'],
        ['href' => '#support', 'label' => 'Support Our Mission'],
        ['href' => '#foundations', 'label' => 'For Foundations'],
        ['href' => '#faq', 'label' => 'FAQ'],
    ];

    if (! $showFoundations) {
        $withoutFoundations = fn (array $links): array => array_values(array_filter($links, fn ($l) => $l['href'] !== '#foundations'));
        $navPrimary = $withoutFoundations($navPrimary);
        $navMobile = $withoutFoundations($navMobile);
    }
@endphp

<body class="min-h-screen overflow-x-hidden">

    {{-- WCAG 2.4.1 — Bypass blocks --}}
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[200] focus:px-5 focus:py-3 focus:rounded-full focus:bg-[#26335C] focus:text-white focus:font-bold focus:shadow-2xl">
        Skip to main content
    </a>

    {{-- Ambient canvas (static, no motion) --}}
    <div class="pointer-events-none fixed inset-0 -z-10" aria-hidden="true">
        <div class="absolute -top-48 -left-40 size-[720px] rounded-full opacity-60 blur-3xl" style="background: radial-gradient(circle, rgba(119,177,220,0.30), transparent 65%);"></div>
        <div class="absolute top-[40%] -right-56 size-[680px] rounded-full opacity-60 blur-3xl" style="background: radial-gradient(circle, rgba(127,209,199,0.22), transparent 65%);"></div>
        <div class="absolute -bottom-56 left-[20%] size-[640px] rounded-full opacity-60 blur-3xl" style="background: radial-gradient(circle, rgba(253,214,190,0.30), transparent 65%);"></div>
    </div>

    {{-- =========================================================
         HEADER — floating glass pill
         ========================================================= --}}
    {{-- Fixed (out of flow) so the hero background runs behind the glass bar --}}
    <header id="v5-header" class="fixed inset-x-0 top-0 z-50 px-3 pt-3 sm:px-5 sm:pt-4" data-scrolled="false">
        <div class="mx-auto max-w-[1200px]">
            <div class="v5-header-bar v5-glass flex items-center justify-between gap-3 rounded-[26px] py-2 pl-3 pr-2 sm:pl-4">

                <a href="{{ route('landing') }}" class="flex min-h-11 items-center rounded-2xl px-1" aria-label="iCanSee home">
                    <img src="{{ asset('images/logo-icansee.png') }}" alt="iCanSee" width="489" height="147" class="h-9 w-auto sm:h-10" decoding="async">
                </a>

                <nav class="hidden items-center gap-0.5 lg:flex" aria-label="Primary">
                    @foreach ($navPrimary as $link)
                        <a href="{{ $link['href'] }}" class="v5-nav-link" data-nav-link>{{ $link['label'] }}</a>
                    @endforeach
                </nav>

                <div class="flex items-center gap-1.5">
                    <a href="#getting-started" class="v5-btn v5-btn-primary hidden !min-h-[44px] !px-4 !text-sm sm:inline-flex">
                        Get Started
                        <span class="v5-btn-arrow !size-6">{!! $svg('arrow-right', 'size-3.5', 2.6) !!}</span>
                    </a>
                    <button type="button" id="v5-menu-btn" class="inline-flex size-11 items-center justify-center rounded-2xl bg-[#EEF4FA] text-[#26335C] transition-colors hover:bg-[#DDEBF8] lg:hidden" aria-expanded="false" aria-controls="v5-menu" aria-label="Open menu">
                        <span data-menu-icon="open">{!! $svg('menu', 'size-5', 2.2) !!}</span>
                        <span data-menu-icon="close" class="hidden">{!! $svg('x', 'size-5', 2.2) !!}</span>
                    </button>
                </div>
            </div>

            {{-- Mobile menu --}}
            <div id="v5-menu" data-open="false" class="v5-glass v5-shadow-lg absolute inset-x-3 mt-2 rounded-[24px] p-3 sm:inset-x-5 lg:hidden">
                <nav aria-label="Mobile">
                    <ul class="grid gap-1 sm:grid-cols-2">
                        @foreach ($navMobile as $link)
                            <li>
                                <a href="{{ $link['href'] }}" class="flex min-h-12 items-center justify-between rounded-2xl px-4 font-semibold text-[#26335C] transition-colors hover:bg-[#EEF4FA]" data-nav-link>
                                    {{ $link['label'] }}
                                    {!! $svg('chevron-right', 'size-4 text-[#5E6A84]', 2.2) !!}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
                <div class="mt-3 border-t border-[#E3EBF4] pt-3">
                    <a href="#getting-started" class="v5-btn v5-btn-primary w-full">
                        Get Started <span class="v5-btn-arrow">{!! $svg('arrow-right', 'size-4', 2.4) !!}</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main id="main-content" tabindex="-1" class="outline-none">

        {{-- =========================================================
             1. HERO
             ========================================================= --}}
        {{-- Top padding = original spacing + header height (~72–76px), since the header is now fixed --}}
        <section class="relative overflow-hidden pb-20 pt-32 sm:pt-40 lg:pb-28 lg:pt-44" aria-labelledby="hero-heading">
            <div class="v5-hero-mesh pointer-events-none absolute inset-0" aria-hidden="true"></div>
            <div class="v5-dots pointer-events-none absolute inset-0" aria-hidden="true"></div>

            <div class="relative mx-auto grid max-w-[1200px] items-center gap-16 px-5 sm:px-8 lg:grid-cols-12 lg:gap-10">

                {{-- Copy --}}
                <div class="lg:col-span-7">
                    <p class="v5-eyebrow v5-reveal">
                        <span class="v5-eyebrow-dot" aria-hidden="true"></span>
                        AI-powered visual assistant
                    </p>

                    <h1 id="hero-heading" class="v5-reveal mt-6 text-[2.6rem] font-extrabold leading-[1.04] tracking-[-0.035em] text-[#15203B] sm:text-6xl lg:text-[4.25rem]" style="--d: 80ms">
                        See Beyond Sight,
                        <span class="v5-text-gradient block pb-1">Empower Independence.</span>
                    </h1>

                    <p class="v5-reveal mt-6 max-w-xl text-xl font-medium leading-relaxed text-[#26335C]" style="--d: 160ms">
                        An AI-powered visual assistant designed for people with visual impairments.
                    </p>
                    <p class="v5-reveal mt-3 max-w-xl text-lg leading-relaxed text-[#46526B]" style="--d: 220ms">
                        Experience the world through the power of AI and voice. Simply tap your screen to ask questions, recognize objects, and understand your surroundings — all through your smartphone's web browser.
                    </p>

                    <div class="v5-reveal mt-9 flex flex-col gap-3 sm:flex-row" style="--d: 300ms">
                        <a href="#getting-started" class="v5-btn v5-btn-primary !min-h-14 !px-7 !text-base">
                            Get Started
                            <span class="v5-btn-arrow">{!! $svg('arrow-right', 'size-4', 2.4) !!}</span>
                        </a>
                        <a href="#support" class="v5-btn v5-btn-secondary !min-h-14 !px-7 !text-base">
                            {!! $svg('heart', 'size-[18px] text-[#C2557A]', 2.1) !!}
                            Support Our Mission
                        </a>
                    </div>

                    <ul class="v5-reveal mt-10 flex flex-wrap gap-x-6 gap-y-3 text-[0.95rem] font-medium text-[#46526B]" style="--d: 380ms" aria-label="Highlights">
                        @foreach (['No app installation', 'Voice-first experience', 'Works in your phone\'s browser'] as $point)
                            <li class="flex items-center gap-2">
                                <span class="inline-flex size-6 items-center justify-center rounded-full bg-gradient-to-br from-[#E3F6F2] to-[#C7ECE4] text-[#17605B]">{!! $svg('check', 'size-3.5', 2.8) !!}</span>
                                {{ $point }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Interactive demo --}}
                <div class="v5-reveal lg:col-span-5" style="--d: 200ms" data-demo-root data-paused="false">
                    <div class="relative mx-auto w-[288px] sm:w-[308px]">
                        <div class="pointer-events-none absolute -inset-16 rounded-full opacity-80 blur-3xl" style="background: radial-gradient(circle at 50% 45%, rgba(119,177,220,0.55), rgba(127,209,199,0.25) 45%, transparent 70%);" aria-hidden="true"></div>

                        {{-- Phone — mirrors the real iCanSee home screen --}}
                        <div class="v5-anim v5-float relative">
                            <div class="relative rounded-[46px] bg-gradient-to-b from-[#EEF2F7] to-[#E1E7EF] p-[9px] shadow-[0_50px_90px_-40px_rgba(38,51,92,0.45),inset_0_0_0_1px_rgba(255,255,255,0.9)]"
                                 role="img" aria-label="Preview of the iCanSee app home screen: a large Tap button with the instruction “Tap once to speak, or double-tap to take a picture.”">
                                {{-- Speaker bar --}}
                                <span class="absolute left-1/2 top-[5px] z-20 h-[5px] w-16 -translate-x-1/2 rounded-full bg-[#C9D2DD]" aria-hidden="true"></span>

                                <div class="relative flex aspect-[9/18.5] flex-col overflow-hidden rounded-[38px] bg-gradient-to-b from-white via-white to-[#F6F8FB]" aria-hidden="true">
                                    {{-- App header --}}
                                    <div class="flex items-center justify-between border-b border-[#EDF1F6] px-4 pb-3 pt-6">
                                        <img src="{{ asset('images/logo-icansee.png') }}" alt="" width="489" height="147" class="h-[26px] w-auto" decoding="async">
                                        <span class="flex items-center gap-1.5">
                                            <span class="inline-flex h-[30px] items-center gap-1 rounded-[9px] border border-[#A9E2C6] bg-[#E9F8F1] px-2 text-[10.5px] font-bold text-[#15803D]">
                                                {!! $svg('mic', 'size-3', 2.4) !!} Mic OK
                                            </span>
                                            <span class="inline-flex h-[30px] items-center gap-1 rounded-[9px] border border-[#DCE3EC] bg-[#F1F4F8] px-2 text-[10.5px] font-bold text-[#26335C]">
                                                {!! $svg('switch-camera', 'size-3.5', 2.2) !!} Rear
                                            </span>
                                        </span>
                                    </div>

                                    {{-- Tap area --}}
                                    <div class="flex flex-1 flex-col items-center justify-center px-5 pb-14">
                                        <div class="relative flex size-[176px] items-center justify-center">
                                            {{-- Soft outer halo --}}
                                            <span class="absolute inset-0 rounded-full border border-[#E2E8F0]" style="background: radial-gradient(circle, rgba(38,51,92,0.16) 0%, rgba(38,51,92,0.06) 38%, rgba(238,242,247,0.5) 62%, rgba(255,255,255,0) 72%);"></span>
                                            {{-- Tap ripples (single press, then double press) --}}
                                            <span class="v5-anim v5-tap-ring v5-tap-ring-a absolute size-[92px] rounded-full border-2 border-[#26335C]/25"></span>
                                            <span class="v5-anim v5-tap-ring v5-tap-ring-b absolute size-[92px] rounded-full border-2 border-[#26335C]/25"></span>
                                            <span class="v5-anim v5-tap-ring v5-tap-ring-c absolute size-[92px] rounded-full border-2 border-[#26335C]/25"></span>
                                            {{-- Static rings --}}
                                            <span class="absolute size-[116px] rounded-full border border-[#D3DBE6]"></span>
                                            <span class="absolute size-[106px] rounded-full border border-[#C6D0DD] bg-white/40"></span>
                                            {{-- TAP button --}}
                                            <span class="v5-anim v5-tap-press relative flex size-[92px] flex-col items-center justify-center rounded-full text-white shadow-[0_18px_30px_-8px_rgba(26,35,63,0.6),inset_0_2px_6px_rgba(255,255,255,0.18)]"
                                                  style="background: radial-gradient(circle at 38% 30%, #5D7CA9 0%, #34497A 48%, #1F2C52 100%);">
                                                {!! $svg('hand', 'size-7', 2) !!}
                                                <span class="v5-display mt-1 text-[9px] font-extrabold tracking-[0.16em]">TAP</span>
                                            </span>
                                        </div>

                                        <p class="v5-display -mt-1 text-center text-[17.5px] font-extrabold leading-[1.22] tracking-[-0.02em] text-[#1F2A4A]">
                                            Tap once to speak,<br>or double-tap<br>to take a picture
                                        </p>
                                        <p class="mt-2.5 text-center text-[11.5px] leading-[1.5] text-[#46526B]">
                                            Tap 1× anywhere to greet the AI by voice, or double-tap (2×) anywhere to capture a photo.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Gesture callouts --}}
                        <div class="v5-glass v5-shadow absolute -left-6 top-[30%] hidden items-center gap-3 rounded-[20px] py-2.5 pl-2.5 pr-4 sm:flex xl:-left-24" aria-hidden="true">
                            <span class="v5-tile v5-tile-sky size-10 rounded-[14px]">{!! $svg('mic', 'size-5') !!}</span>
                            <span class="leading-tight">
                                <span class="block text-[0.8rem] font-bold text-[#15203B]">Tap 1×</span>
                                <span class="block text-[0.75rem] text-[#5E6A84]">Speak to the AI</span>
                            </span>
                        </div>
                        <div class="v5-glass v5-shadow absolute -right-6 bottom-[20%] hidden items-center gap-3 rounded-[20px] py-2.5 pl-2.5 pr-4 sm:flex xl:-right-20" aria-hidden="true">
                            <span class="v5-tile v5-tile-aqua size-10 rounded-[14px]">{!! $svg('camera', 'size-5') !!}</span>
                            <span class="leading-tight">
                                <span class="block text-[0.8rem] font-bold text-[#15203B]">Tap 2×</span>
                                <span class="block text-[0.75rem] text-[#5E6A84]">Take a picture</span>
                            </span>
                        </div>
                    </div>

                    {{-- WCAG 2.2.2 — Pause control for looping motion --}}
                    <div class="v5-motion-only mt-8 flex justify-center">
                        <button type="button" id="v5-demo-toggle" class="inline-flex min-h-11 items-center gap-2 rounded-full border border-[#E3EBF4] bg-white/80 px-4 text-sm font-semibold text-[#46526B] transition-colors hover:bg-white hover:text-[#26335C]">
                            <span data-toggle-icon="pause">{!! $svg('pause', 'size-4', 2) !!}</span>
                            <span data-toggle-icon="play" class="hidden">{!! $svg('play', 'size-4', 2) !!}</span>
                            <span data-toggle-label>Pause animation</span>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        {{-- =========================================================
             2. ABOUT
             ========================================================= --}}
        <section id="about" class="relative py-24 lg:py-32" aria-labelledby="about-heading">
            <div class="mx-auto grid max-w-[1200px] items-center gap-14 px-5 sm:px-8 lg:grid-cols-2 lg:gap-20">
                <div>
                    <p class="v5-eyebrow v5-reveal"><span class="v5-eyebrow-dot" aria-hidden="true"></span>About iCanSee</p>
                    <h2 id="about-heading" class="v5-reveal mt-5 text-[2rem] font-extrabold leading-[1.1] text-[#15203B] sm:text-[2.5rem] lg:text-[2.875rem]" style="--d: 60ms">
                        Making Visual Information <span class="v5-text-gradient">Accessible to Everyone</span>
                    </h2>
                    <div class="v5-reveal mt-6 space-y-5 text-lg leading-relaxed text-[#46526B]" style="--d: 120ms">
                        <p>Millions of people with visual impairments face challenges accessing everyday visual information, from identifying objects and reading text to understanding their surroundings.</p>
                        <p><strong class="font-bold text-[#15203B]">iCanSee</strong> is a web-based, AI-powered visual assistant designed to bridge this accessibility gap.</p>
                        <p>By combining Artificial Intelligence, Computer Vision, and voice technology, iCanSee transforms visual information into spoken descriptions, helping users navigate daily activities with greater confidence and independence.</p>
                    </div>
                </div>

                <div class="v5-reveal relative" style="--d: 120ms">
                    <div class="v5-grad-soft v5-shadow-lg relative overflow-hidden rounded-[36px] border border-white p-6 sm:p-8">
                        <p class="text-sm font-bold uppercase tracking-[0.14em] text-[#2F6DA6]">From sight to sound</p>

                        {{-- Flow: Capture → Understand → Describe --}}
                        <ol class="relative mt-6 grid gap-3 sm:grid-cols-3" aria-label="How iCanSee transforms visual information">
                            @foreach ([
                                ['icon' => 'camera', 'tone' => 'sky', 'title' => 'Capture', 'desc' => 'Your phone camera'],
                                ['icon' => 'sparkles', 'tone' => 'peri', 'title' => 'Understand', 'desc' => 'AI & computer vision'],
                                ['icon' => 'volume', 'tone' => 'aqua', 'title' => 'Describe', 'desc' => 'Spoken out loud'],
                            ] as $i => $node)
                                <li class="v5-lift relative flex items-center gap-4 rounded-[22px] bg-white/85 p-4 v5-shadow-sm sm:flex-col sm:items-start sm:gap-3">
                                    <span class="v5-tile v5-tile-{{ $node['tone'] }} size-12">{!! $svg($node['icon'], 'size-6') !!}</span>
                                    <span>
                                        <span class="block font-bold text-[#15203B]">{{ $node['title'] }}</span>
                                        <span class="block text-sm text-[#5E6A84]">{{ $node['desc'] }}</span>
                                    </span>
                                    @if ($i < 2)
                                        <span class="absolute -right-[13px] top-1/2 z-10 hidden size-6 -translate-y-1/2 items-center justify-center rounded-full bg-white text-[#2F6DA6] ring-1 ring-[#E3EBF4] sm:inline-flex" aria-hidden="true">{!! $svg('chevron-right', 'size-3.5', 2.6) !!}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ol>

                        {{-- Mission --}}
                        <figure class="v5-grad-dark v5-on-dark relative mt-5 overflow-hidden rounded-[26px] p-7 text-white">
                            <div class="pointer-events-none absolute -right-10 -top-10 size-40 rounded-full border border-white/10" aria-hidden="true"></div>
                            <div class="pointer-events-none absolute -right-2 -top-2 size-24 rounded-full border border-white/10" aria-hidden="true"></div>
                            <figcaption class="text-sm font-bold uppercase tracking-[0.14em] text-[#BFE0F7]">Our mission</figcaption>
                            <blockquote class="v5-display mt-3 text-[1.35rem] font-bold leading-snug sm:text-2xl">
                                Make visual information accessible to everyone, regardless of their ability to see.
                            </blockquote>
                        </figure>
                    </div>
                </div>
            </div>
        </section>

        {{-- =========================================================
             3. WHY ICANSEE — bento
             ========================================================= --}}
        <section id="why" class="relative px-3 sm:px-5" aria-labelledby="why-heading">
            <div class="v5-band mx-auto max-w-[1320px] rounded-[40px] py-24 lg:rounded-[56px] lg:py-28">
                <div class="mx-auto max-w-[1200px] px-5 sm:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <p class="v5-eyebrow v5-reveal"><span class="v5-eyebrow-dot" aria-hidden="true"></span>Why iCanSee?</p>
                        <h2 id="why-heading" class="v5-reveal mt-5 text-[2rem] font-extrabold leading-[1.1] text-[#15203B] sm:text-[2.5rem] lg:text-[3rem]" style="--d: 60ms">
                            Technology That <span class="v5-text-gradient">Understands Your Needs</span>
                        </h2>
                    </div>

                    <div class="mt-14 grid gap-5 md:grid-cols-2 lg:grid-cols-3 lg:grid-rows-2">
                        {{-- Featured: One-Tap Simplicity --}}
                        <article class="v5-card v5-card-hover v5-reveal v5-shadow-sm relative flex flex-col overflow-hidden !border-transparent p-8 md:col-span-2 lg:col-span-1 lg:row-span-2" style="background: linear-gradient(160deg, #EAF4FC 0%, #E4F6F2 55%, #FDF0E7 100%);">
                            <span class="v5-tile v5-tile-navy size-14 rounded-[20px]">{!! $svg('pointer', 'size-7') !!}</span>
                            <h3 class="mt-6 text-2xl font-extrabold text-[#15203B]">One-Tap Simplicity</h3>
                            <p class="mt-3 text-[1.0625rem] leading-relaxed text-[#46526B]">No complicated menus or navigation. Interact with iCanSee through simple screen gestures designed to minimize visual interaction.</p>

                            <div class="relative mt-10 flex flex-1 items-end justify-center pb-2" aria-hidden="true">
                                <div class="relative flex size-48 items-center justify-center">
                                    <span class="absolute inset-0 rounded-full border border-[#77B1DC]/35"></span>
                                    <span class="absolute inset-6 rounded-full border border-[#77B1DC]/45"></span>
                                    <span class="absolute inset-12 rounded-full border border-[#77B1DC]/60"></span>
                                    <span class="relative inline-flex size-20 items-center justify-center rounded-full bg-white text-[#26335C] v5-shadow">
                                        {!! $svg('pointer', 'size-9', 1.7) !!}
                                    </span>
                                </div>
                                <span class="absolute bottom-6 left-2 rounded-full bg-white/90 px-3 py-1.5 text-xs font-bold text-[#26335C] v5-shadow-sm">1× Ask</span>
                                <span class="absolute right-2 top-6 rounded-full bg-white/90 px-3 py-1.5 text-xs font-bold text-[#17605B] v5-shadow-sm">2× See</span>
                            </div>
                        </article>

                        @foreach ([
                            ['icon' => 'sparkles', 'tone' => 'peri', 'title' => 'AI-Powered Visual Assistance', 'desc' => 'Understand objects, text, and your surroundings through intelligent image analysis.'],
                            ['icon' => 'mic', 'tone' => 'sky', 'title' => 'Voice-First Experience', 'desc' => 'Ask questions, receive answers, and hear descriptions through natural voice interactions.'],
                            ['icon' => 'globe', 'tone' => 'aqua', 'title' => 'Accessible Anywhere', 'desc' => 'Access iCanSee directly from your smartphone\'s web browser. No app installation required.'],
                            ['icon' => 'accessibility', 'tone' => 'peach', 'title' => 'Greater Everyday Independence', 'desc' => 'Gain easier access to visual information and reduce reliance on others for everyday tasks.'],
                        ] as $i => $item)
                            <article class="v5-card v5-card-hover v5-reveal v5-shadow-sm p-7 sm:p-8" style="--d: {{ 80 + $i * 70 }}ms">
                                <span class="v5-tile v5-tile-{{ $item['tone'] }} size-12">{!! $svg($item['icon'], 'size-6') !!}</span>
                                <h3 class="mt-5 text-xl font-bold text-[#15203B]">{{ $item['title'] }}</h3>
                                <p class="mt-2 leading-relaxed text-[#46526B]">{{ $item['desc'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- =========================================================
             4. HOW IT WORKS
             ========================================================= --}}
        <section id="how-it-works" class="relative py-24 lg:py-32" aria-labelledby="how-heading">
            <div class="mx-auto max-w-[1200px] px-5 sm:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="v5-eyebrow v5-reveal"><span class="v5-eyebrow-dot" aria-hidden="true"></span>How It Works</p>
                    <h2 id="how-heading" class="v5-reveal mt-5 text-[2rem] font-extrabold leading-[1.1] text-[#15203B] sm:text-[2.5rem] lg:text-[3rem]" style="--d: 60ms">
                        Simple Interactions. <span class="v5-text-gradient">Powerful Assistance.</span>
                    </h2>
                    <p class="v5-reveal mt-5 text-lg text-[#46526B]" style="--d: 120ms">iCanSee is designed around two intuitive interactions.</p>
                </div>

                @php
                    $modes = [
                        [
                            'num' => '1',
                            'taps' => 1,
                            'tone' => 'sky',
                            'icon' => 'mic',
                            'title' => 'Single Tap — Ask Anything',
                            'lead' => 'Have a question? Just tap once.',
                            'steps' => [
                                'Tap anywhere on the screen.',
                                'Ask your question using your voice.',
                                'AI processes your question.',
                                'Listen to the spoken answer.',
                            ],
                            'example' => [
                                ['who' => 'You', 'text' => '"What can you tell me about this object?"'],
                                ['who' => 'iCanSee', 'text' => '"I can help identify it. Open the camera so I can take a look."'],
                            ],
                        ],
                        [
                            'num' => '2',
                            'taps' => 2,
                            'tone' => 'aqua',
                            'icon' => 'camera',
                            'title' => 'Double Tap — Explore Your Surroundings',
                            'lead' => 'Want to understand what\'s in front of you?',
                            'steps' => [
                                'Double-tap anywhere to activate the camera.',
                                'Point your smartphone toward an object or area.',
                                'Tap once to capture an image.',
                                'AI analyzes the captured image.',
                                'Listen to a spoken description.',
                            ],
                            'example' => [
                                ['who' => 'iCanSee', 'text' => '"There is a small green plant in a white pot on a wooden table."'],
                            ],
                        ],
                    ];
                @endphp

                <div class="mt-14 grid gap-6 lg:grid-cols-2">
                    @foreach ($modes as $m => $mode)
                        <article class="v5-card v5-card-hover v5-reveal v5-shadow flex flex-col overflow-hidden !rounded-[34px] p-2" style="--d: {{ $m * 100 }}ms" aria-labelledby="mode-{{ $mode['num'] }}-title">
                            {{-- Header --}}
                            <div class="relative overflow-hidden rounded-[28px] p-6 sm:p-8" style="background: {{ $mode['tone'] === 'sky' ? 'linear-gradient(140deg, #EAF4FC, #DCEBF8)' : 'linear-gradient(140deg, #E8F7F3, #D3F0E9)' }};">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-bold uppercase tracking-[0.14em] {{ $mode['tone'] === 'sky' ? 'text-[#2F6DA6]' : 'text-[#17605B]' }}">Interaction {{ $mode['num'] }}</p>
                                        <h3 id="mode-{{ $mode['num'] }}-title" class="mt-2 text-2xl font-extrabold leading-tight text-[#15203B] sm:text-[1.75rem]">{{ $mode['title'] }}</h3>
                                        <p class="mt-2 text-[1.0625rem] text-[#46526B]">{{ $mode['lead'] }}</p>
                                    </div>
                                    {{-- Tap indicator --}}
                                    <div class="flex shrink-0 flex-col items-center gap-2" aria-hidden="true">
                                        <span class="v5-tile v5-tile-navy size-14 rounded-[20px]">{!! $svg($mode['icon'], 'size-7') !!}</span>
                                        <span class="flex gap-1.5">
                                            @for ($t = 0; $t < $mode['taps']; $t++)
                                                <span class="size-2.5 rounded-full {{ $mode['tone'] === 'sky' ? 'bg-[#2F6DA6]' : 'bg-[#17605B]' }}"></span>
                                            @endfor
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Steps --}}
                            <div class="flex flex-1 flex-col p-6 sm:p-8">
                                <div class="relative">
                                    <span class="absolute bottom-4 left-[17px] top-4 w-px bg-gradient-to-b from-[#CFE3F5] via-[#C7ECE4] to-transparent" aria-hidden="true"></span>
                                    <ol class="relative space-y-4">
                                        @foreach ($mode['steps'] as $s => $step)
                                            <li class="relative flex items-start gap-4">
                                                <span class="v5-display relative z-10 inline-flex size-9 shrink-0 items-center justify-center rounded-full bg-white text-sm font-extrabold text-[#26335C] ring-1 ring-[#DCE7F2] v5-shadow-sm" aria-hidden="true">{{ $s + 1 }}</span>
                                                <span class="pt-1.5 text-[1.0625rem] text-[#15203B]">{{ $step }}</span>
                                            </li>
                                        @endforeach
                                    </ol>
                                </div>

                                {{-- Example --}}
                                <figure class="mt-8 rounded-[24px] border border-[#E3EBF4] bg-[#F8FBFE] p-5">
                                    <figcaption class="flex items-center gap-2 text-sm font-bold text-[#5E6A84]">
                                        {!! $svg('message', 'size-4', 2) !!} Example
                                    </figcaption>
                                    <div class="mt-4 space-y-3">
                                        @foreach ($mode['example'] as $line)
                                            @if ($line['who'] === 'You')
                                                <div class="ml-auto max-w-[88%] rounded-[20px] rounded-br-md bg-gradient-to-br from-[#E3F0FB] to-[#D3E6F6] px-4 py-3">
                                                    <p class="text-xs font-bold uppercase tracking-wider text-[#2F6DA6]">You</p>
                                                    <p class="mt-1 text-[#15203B]">{{ $line['text'] }}</p>
                                                </div>
                                            @else
                                                <div class="max-w-[92%] rounded-[20px] rounded-bl-md bg-white px-4 py-3 ring-1 ring-[#E3EBF4] v5-shadow-sm">
                                                    <p class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-[#17605B]">{!! $svg('volume', 'size-3.5', 2.2) !!} iCanSee</p>
                                                    <p class="mt-1 text-[#15203B]">{{ $line['text'] }}</p>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </figure>
                            </div>
                        </article>
                    @endforeach
                </div>

                {{-- Tagline --}}
                <div class="v5-reveal mt-10 flex justify-center">
                    <p class="v5-display inline-flex flex-wrap items-center justify-center gap-x-3 gap-y-2 rounded-full border border-white bg-white/80 px-6 py-4 text-center text-lg font-extrabold text-[#15203B] v5-shadow sm:text-xl">
                        <span class="inline-flex items-center gap-2"><span class="v5-tile v5-tile-sky size-8 rounded-full">{!! $svg('pointer', 'size-4', 2.2) !!}</span>One Tap to Ask.</span>
                        <span class="inline-flex items-center gap-2"><span class="v5-tile v5-tile-aqua size-8 rounded-full">{!! $svg('eye', 'size-4', 2.2) !!}</span><span class="v5-text-gradient">Double Tap to See.</span></span>
                    </p>
                </div>
            </div>
        </section>

        {{-- =========================================================
             5. FEATURES
             ========================================================= --}}
        <section id="features" class="relative px-3 sm:px-5" aria-labelledby="features-heading">
            <div class="v5-band mx-auto max-w-[1320px] rounded-[40px] py-24 lg:rounded-[56px] lg:py-28">
                <div class="mx-auto max-w-[1200px] px-5 sm:px-8">
                    <div class="max-w-2xl">
                        <p class="v5-eyebrow v5-reveal"><span class="v5-eyebrow-dot" aria-hidden="true"></span>Features</p>
                        <h2 id="features-heading" class="v5-reveal mt-5 text-[2rem] font-extrabold leading-[1.1] text-[#15203B] sm:text-[2.5rem] lg:text-[3rem]" style="--d: 60ms">
                            Everything You Need, <span class="v5-text-gradient">Made Simple</span>
                        </h2>
                    </div>

                    @php
                        $features = [
                            ['icon' => 'scan', 'tone' => 'navy', 'title' => 'AI Visual Recognition', 'desc' => 'Identify everyday objects and receive detailed descriptions of your surroundings.', 'wide' => true],
                            ['icon' => 'message', 'tone' => 'sky', 'title' => 'Voice Questions & Answers', 'desc' => 'Ask questions naturally and receive AI-generated spoken responses.'],
                            ['icon' => 'camera', 'tone' => 'aqua', 'title' => 'Smart Camera Assistance', 'desc' => 'Capture images directly from your smartphone\'s camera using simple gestures.'],
                            ['icon' => 'scan-text', 'tone' => 'peri', 'title' => 'Text Recognition', 'desc' => 'Recognize and read printed text, such as signs, product labels, and documents.'],
                            ['icon' => 'volume', 'tone' => 'peach', 'title' => 'Audio Descriptions', 'desc' => 'Turn visual information into clear, understandable spoken descriptions.'],
                            ['icon' => 'smartphone', 'tone' => 'sky', 'title' => 'Accessible Web Experience', 'desc' => 'Enjoy a responsive, accessibility-focused interface designed for smartphone browsers.'],
                            ['icon' => 'languages', 'tone' => 'aqua', 'title' => 'Multilingual Support', 'desc' => 'Access visual assistance in multiple languages.'],
                        ];
                    @endphp

                    <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($features as $i => $f)
                            @if (! empty($f['wide']))
                                <article class="v5-card v5-card-hover v5-reveal v5-grad-dark v5-on-dark relative overflow-hidden !border-transparent p-8 text-white sm:col-span-2" style="--d: 0ms">
                                    <div class="pointer-events-none absolute -bottom-16 -right-10 size-56 rounded-full border border-white/10" aria-hidden="true"></div>
                                    <div class="pointer-events-none absolute -bottom-6 right-6 size-32 rounded-full border border-white/10" aria-hidden="true"></div>
                                    <div class="relative flex h-full flex-col gap-6 sm:flex-row sm:items-center">
                                        <div class="flex-1">
                                            <span class="v5-tile size-12 bg-white/12 text-white ring-1 ring-white/20">{!! $svg($f['icon'], 'size-6') !!}</span>
                                            <h3 class="mt-5 text-2xl font-extrabold">{{ $f['title'] }}</h3>
                                            <p class="mt-2 max-w-sm leading-relaxed text-white/85">{{ $f['desc'] }}</p>
                                        </div>
                                        {{-- Illustration: camera view → object detected → spoken description --}}
                                        <div class="relative mx-auto w-full max-w-[260px] shrink-0 sm:w-[240px]" aria-hidden="true">
                                            <div class="relative aspect-[220/170] overflow-hidden rounded-[22px] shadow-[0_18px_36px_-16px_rgba(10,16,35,0.6)] ring-1 ring-white/25">
                                                <svg class="absolute inset-0 size-full" viewBox="0 0 220 170" fill="none">
                                                    <defs>
                                                        <linearGradient id="feat-wall" x1="0" y1="0" x2="0" y2="1">
                                                            <stop offset="0" stop-color="#F1F7FD"/>
                                                            <stop offset="1" stop-color="#DCEAF6"/>
                                                        </linearGradient>
                                                    </defs>
                                                    {{-- Wall + wooden table --}}
                                                    <rect width="220" height="170" fill="url(#feat-wall)"/>
                                                    <rect y="124" width="220" height="46" fill="#C99A6E"/>
                                                    <rect y="124" width="220" height="5" fill="#DDB48A"/>
                                                    {{-- White pot --}}
                                                    <ellipse cx="110" cy="127" rx="32" ry="3.5" fill="#000" fill-opacity="0.12"/>
                                                    <path d="M86 94h48l-6 32H92z" fill="#FFFFFF"/>
                                                    <rect x="82" y="88" width="56" height="10" rx="4" fill="#F6F8FB" stroke="#E2E8F0"/>
                                                    {{-- Green plant --}}
                                                    <path d="M110 89C108 67 96 55 79 51c4 16 14 30 31 38Z" fill="#5DBB8E"/>
                                                    <path d="M110 89c2-26 16-40 34-44-4 20-16 35-34 44Z" fill="#46A57A"/>
                                                    <path d="M110 89c-2-30 3-46 0-61-6 15-6 35 0 61Z" fill="#7ACFA3"/>
                                                    {{-- Detection box --}}
                                                    <rect x="68" y="22" width="84" height="108" rx="12" stroke="#2F6DA6" stroke-width="2" stroke-dasharray="6 5"/>
                                                </svg>

                                                {{-- Viewfinder corners --}}
                                                <span class="absolute left-2.5 top-2.5 size-5 rounded-tl-lg border-l-[2.5px] border-t-[2.5px] border-[#26335C]/70"></span>
                                                <span class="absolute right-2.5 top-2.5 size-5 rounded-tr-lg border-r-[2.5px] border-t-[2.5px] border-[#26335C]/70"></span>
                                                <span class="absolute bottom-2.5 left-2.5 size-5 rounded-bl-lg border-b-[2.5px] border-l-[2.5px] border-white/90"></span>
                                                <span class="absolute bottom-2.5 right-2.5 size-5 rounded-br-lg border-b-[2.5px] border-r-[2.5px] border-white/90"></span>

                                                {{-- Detection label (aligned to the box's top-left) --}}
                                                <span class="absolute left-[31%] top-[13%] inline-flex -translate-y-1/2 items-center gap-1 rounded-full bg-[#26335C] px-2 py-0.5 text-[10px] font-bold text-white shadow-sm">
                                                    {!! $svg('sparkles', 'size-3', 2.2) !!} Plant
                                                </span>
                                            </div>

                                            {{-- Spoken result --}}
                                            <div class="mt-2.5 flex items-start gap-2 rounded-[16px] bg-white px-3 py-2.5 text-[12px] leading-snug text-[#15203B] shadow-[0_12px_24px_-14px_rgba(10,16,35,0.6)]">
                                                <span class="mt-px text-[#2F6DA6]">{!! $svg('volume', 'size-4', 2.2) !!}</span>
                                                <span>"A small green plant in a white pot."</span>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            @else
                                <article class="v5-card v5-card-hover v5-reveal v5-shadow-sm p-7" style="--d: {{ 60 + ($i % 4) * 70 }}ms">
                                    <span class="v5-tile v5-tile-{{ $f['tone'] }} size-12">{!! $svg($f['icon'], 'size-6') !!}</span>
                                    <h3 class="mt-5 text-lg font-bold text-[#15203B]">{{ $f['title'] }}</h3>
                                    <p class="mt-2 leading-relaxed text-[#46526B]">{{ $f['desc'] }}</p>
                                </article>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- =========================================================
             6. WHO IS ICANSEE FOR
             ========================================================= --}}
        <section id="who" class="relative py-24 lg:py-32" aria-labelledby="who-heading">
            <div class="mx-auto max-w-[1200px] px-5 sm:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="v5-eyebrow v5-reveal"><span class="v5-eyebrow-dot" aria-hidden="true"></span>Who Is iCanSee For?</p>
                    <h2 id="who-heading" class="v5-reveal mt-5 text-[2rem] font-extrabold leading-[1.1] text-[#15203B] sm:text-[2.5rem] lg:text-[3rem]" style="--d: 60ms">
                        Designed for Accessibility. <span class="v5-text-gradient">Built for Independence.</span>
                    </h2>
                </div>

                <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([
                        ['icon' => 'user', 'tone' => 'sky', 'bg' => 'linear-gradient(170deg, #EEF6FD 0%, #FFFFFF 60%)', 'title' => 'People with Visual Impairments', 'desc' => 'Individuals who are blind or have low vision and need assistance understanding visual information.'],
                        ['icon' => 'users', 'tone' => 'peach', 'bg' => 'linear-gradient(170deg, #FFF4EC 0%, #FFFFFF 60%)', 'title' => 'Parents & Families', 'desc' => 'Families looking for accessible technology to support their loved ones.'],
                        ['icon' => 'building', 'tone' => 'aqua', 'bg' => 'linear-gradient(170deg, #ECF8F5 0%, #FFFFFF 60%)', 'title' => 'Foundations & Nonprofit Organizations', 'desc' => 'Organizations supporting visually impaired communities through accessible technology.'],
                        ['icon' => 'megaphone', 'tone' => 'peri', 'bg' => 'linear-gradient(170deg, #F1F3FD 0%, #FFFFFF 60%)', 'title' => 'Communities & Accessibility Advocates', 'desc' => 'People and organizations committed to creating a more inclusive world.'],
                    ] as $i => $a)
                        <article class="v5-card v5-card-hover v5-reveal v5-shadow-sm flex flex-col p-7" style="background: {{ $a['bg'] }}; --d: {{ $i * 80 }}ms">
                            <span class="v5-tile v5-tile-{{ $a['tone'] }} size-14 rounded-[20px]">{!! $svg($a['icon'], 'size-7') !!}</span>
                            <h3 class="mt-6 text-lg font-bold leading-snug text-[#15203B]">{{ $a['title'] }}</h3>
                            <p class="mt-2 leading-relaxed text-[#46526B]">{{ $a['desc'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- =========================================================
             7. GETTING STARTED
             ========================================================= --}}
        <section id="getting-started" class="relative px-3 sm:px-5" aria-labelledby="start-heading">
            <div class="v5-grad-soft mx-auto max-w-[1320px] overflow-hidden rounded-[40px] border border-white py-24 v5-shadow lg:rounded-[56px] lg:py-28">
                <div class="mx-auto max-w-[1200px] px-5 sm:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <p class="v5-eyebrow v5-reveal"><span class="v5-eyebrow-dot" aria-hidden="true"></span>Getting Started</p>
                        <h2 id="start-heading" class="v5-reveal mt-5 text-[2rem] font-extrabold leading-[1.1] text-[#15203B] sm:text-[2.5rem] lg:text-[3rem]" style="--d: 60ms">
                            Your Journey <span class="v5-text-gradient">with iCanSee</span>
                        </h2>
                    </div>

                    <div class="relative mt-16">
                        {{-- Connector (desktop) --}}
                        <span class="pointer-events-none absolute left-[12.5%] right-[12.5%] top-8 hidden h-[2px] rounded-full bg-gradient-to-r from-[#9CCBEB] via-[#7FD1C7] to-[#F6C2A4] lg:block" aria-hidden="true"></span>

                        <ol class="relative grid gap-5 sm:grid-cols-2 lg:grid-cols-4 lg:gap-6">
                            @foreach ([
                                ['icon' => 'user-plus', 'tone' => 'sky', 'title' => 'Registration', 'desc' => 'Parents, foundations, or community organizations register visually impaired individuals through the iCanSee Management Portal.'],
                                ['icon' => 'badge-check', 'tone' => 'peri', 'title' => 'Account Activation', 'desc' => 'Once registration is approved, users receive access to the iCanSee platform.'],
                                ['icon' => 'smartphone', 'tone' => 'aqua', 'title' => 'Open iCanSee', 'desc' => 'Access iCanSee directly through a smartphone\'s web browser without installing an application.'],
                                ['icon' => 'compass', 'tone' => 'peach', 'title' => 'Start Exploring', 'desc' => 'Use simple taps, voice commands, and AI-powered descriptions to access visual information independently.'],
                            ] as $i => $step)
                                <li class="v5-step v5-reveal flex flex-col items-center text-center" style="--d: {{ $i * 90 }}ms">
                                    <span class="v5-tile v5-tile-{{ $step['tone'] }} relative size-16 rounded-full ring-8 ring-white/80 v5-shadow">
                                        {!! $svg($step['icon'], 'size-7') !!}
                                    </span>
                                    <div class="v5-step-card mt-6 w-full flex-1 rounded-[26px] bg-white/85 p-6 v5-shadow-sm">
                                        <p class="text-sm font-bold uppercase tracking-[0.14em] text-[#2F6DA6]">Step {{ $i + 1 }}</p>
                                        <h3 class="mt-1.5 text-lg font-bold text-[#15203B]">{{ $step['title'] }}</h3>
                                        <p class="mt-2 leading-relaxed text-[#46526B]">{{ $step['desc'] }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </div>

                    <div class="v5-reveal mt-12 flex flex-col items-center gap-3 text-center">
                        <a href="{{ route('login') }}" class="v5-btn v5-btn-primary !min-h-14 !px-7 !text-base">
                            {!! $svg('user-plus', 'size-5', 2) !!}
                            Register a User
                            <span class="v5-btn-arrow">{!! $svg('arrow-right', 'size-4', 2.4) !!}</span>
                        </a>
                        <p class="text-[0.95rem] text-[#5E6A84]">Opens the iCanSee Management Portal.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- =========================================================
             8. SUPPORT OUR MISSION
             ========================================================= --}}
        <section id="support" class="relative py-24 lg:py-32" aria-labelledby="support-heading">
            <div class="mx-auto max-w-[1200px] px-5 sm:px-8">
                <div class="v5-grad-dark v5-on-dark v5-reveal relative overflow-hidden rounded-[40px] p-8 text-white v5-shadow-lg sm:p-12 lg:rounded-[48px] lg:p-16">
                    <div class="pointer-events-none absolute -left-24 -top-24 size-80 rounded-full border border-white/10" aria-hidden="true"></div>
                    <div class="pointer-events-none absolute -left-10 -top-10 size-52 rounded-full border border-white/10" aria-hidden="true"></div>

                    <div class="relative grid items-center gap-12 lg:grid-cols-12">
                        <div class="lg:col-span-6">
                            <p class="v5-eyebrow"><span class="v5-eyebrow-dot" aria-hidden="true"></span>Support Our Mission</p>
                            <h2 id="support-heading" class="mt-5 text-[2rem] font-extrabold leading-[1.1] sm:text-[2.5rem] lg:text-[2.875rem]">
                                Help Us Make <span class="v5-text-gradient-light">Accessibility Possible</span>
                            </h2>
                            <div class="mt-6 space-y-4 text-lg leading-relaxed text-white/85">
                                <p>We believe that access to visual information should not be a privilege.</p>
                                <p>Through your support, we aim to make AI-powered visual assistance more accessible to people with visual impairments.</p>
                            </div>

                            <div class="mt-8 rounded-[24px] bg-white/8 p-6 ring-1 ring-white/12">
                                <h3 class="text-xl font-bold">Every Contribution Makes a Difference</h3>
                                <p class="mt-2 leading-relaxed text-white/85">Whether you're an individual donor, organization, or corporate partner, your support can help create a more accessible future.</p>
                            </div>

                            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                                <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="v5-btn v5-btn-sky !min-h-14 !px-7 !text-base">
                                    {!! $svg('heart', 'size-5', 2.1) !!} Make a Donation
                                    <span class="sr-only">(opens WhatsApp in a new tab)</span>
                                </a>
                                <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="v5-btn v5-btn-ghost-light !min-h-14 !px-7 !text-base">
                                    Become a Partner <span class="v5-btn-arrow">{!! $svg('arrow-right', 'size-4', 2.4) !!}</span>
                                    <span class="sr-only">(opens WhatsApp in a new tab)</span>
                                </a>
                            </div>
                        </div>

                        <div class="lg:col-span-6">
                            <div class="rounded-[32px] bg-white/[0.07] p-6 ring-1 ring-white/15 backdrop-blur-sm sm:p-8">
                                <p class="text-sm font-bold uppercase tracking-[0.14em] text-[#BFE0F7]">Your contributions can help us</p>
                                <ul class="mt-6 space-y-3">
                                    @foreach ([
                                        ['icon' => 'users', 'text' => 'Provide access to visually impaired individuals.'],
                                        ['icon' => 'cpu', 'text' => 'Support AI processing and infrastructure costs.'],
                                        ['icon' => 'accessibility', 'text' => 'Improve accessibility features and user experience.'],
                                        ['icon' => 'building', 'text' => 'Expand access through foundations and communities.'],
                                        ['icon' => 'lightbulb', 'text' => 'Develop new assistive technology capabilities.'],
                                    ] as $c)
                                        <li class="flex items-center gap-4 rounded-[20px] bg-white/[0.06] p-3.5 pr-5 ring-1 ring-white/10 transition-colors duration-500 ease-out hover:bg-white/10">
                                            <span class="v5-tile size-11 rounded-[14px] bg-gradient-to-br from-[#9CCBEB] to-[#7FD1C7] text-[#1A233F]">{!! $svg($c['icon'], 'size-5', 2) !!}</span>
                                            <span class="leading-snug text-white">{{ $c['text'] }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- =========================================================
             9. FOR FOUNDATIONS & COMMUNITIES (hidden while $showFoundations = false)
             ========================================================= --}}
        @if ($showFoundations)
        <section id="foundations" class="relative px-3 sm:px-5" aria-labelledby="foundations-heading">
            <div class="v5-band mx-auto max-w-[1320px] rounded-[40px] py-24 lg:rounded-[56px] lg:py-28">
                <div class="mx-auto grid max-w-[1200px] items-center gap-14 px-5 sm:px-8 lg:grid-cols-2 lg:gap-20">
                    <div>
                        <p class="v5-eyebrow v5-reveal"><span class="v5-eyebrow-dot" aria-hidden="true"></span>For Foundations & Communities</p>
                        <h2 id="foundations-heading" class="v5-reveal mt-5 text-[2rem] font-extrabold leading-[1.1] text-[#15203B] sm:text-[2.5rem] lg:text-[2.875rem]" style="--d: 60ms">
                            Empower Your Community <span class="v5-text-gradient">with iCanSee</span>
                        </h2>
                        <div class="v5-reveal mt-6 space-y-4 text-lg leading-relaxed text-[#46526B]" style="--d: 120ms">
                            <p>Are you part of a foundation, nonprofit organization, or community supporting visually impaired individuals?</p>
                            <p>Partner with iCanSee to help more people access AI-powered visual assistance.</p>
                        </div>
                        <div class="v5-reveal mt-8" style="--d: 180ms">
                            <button type="button" class="v5-btn v5-btn-primary !min-h-14 !px-7 !text-base" data-contact="Partner With Us">
                                Partner With Us <span class="v5-btn-arrow">{!! $svg('arrow-right', 'size-4', 2.4) !!}</span>
                            </button>
                        </div>
                    </div>

                    {{-- Portal preview --}}
                    <div class="v5-reveal relative" style="--d: 120ms">
                        <div class="pointer-events-none absolute -inset-6 rounded-[48px] opacity-80 blur-2xl" style="background: radial-gradient(60% 60% at 30% 30%, rgba(119,177,220,0.35), transparent 70%), radial-gradient(50% 50% at 80% 80%, rgba(127,209,199,0.3), transparent 70%);" aria-hidden="true"></div>
                        <div class="relative overflow-hidden rounded-[32px] border border-white bg-white/90 v5-shadow-lg">
                            <div class="flex items-center justify-between border-b border-[#EEF3F8] px-6 py-4" aria-hidden="true">
                                <span class="flex gap-1.5"><span class="size-3 rounded-full bg-[#F6C2A4]"></span><span class="size-3 rounded-full bg-[#F4DFA0]"></span><span class="size-3 rounded-full bg-[#A8DEC9]"></span></span>
                                <span class="rounded-full bg-[#F0F7FD] px-3 py-1 text-xs font-semibold text-[#46526B]">portal.icansee</span>
                                <span class="w-12"></span>
                            </div>
                            <div class="p-6 sm:p-8">
                                <div class="flex items-center gap-3">
                                    <span class="v5-tile v5-tile-navy size-11 rounded-[14px]">{!! $svg('shield', 'size-5', 2) !!}</span>
                                    <div>
                                        <p class="font-bold text-[#15203B]">iCanSee Management Portal</p>
                                        <p class="text-sm text-[#5E6A84]">Authorized organizations can:</p>
                                    </div>
                                </div>
                                <ul class="mt-6 space-y-2.5">
                                    @foreach ([
                                        ['icon' => 'user-plus', 'tone' => 'sky', 'text' => 'Register and manage beneficiaries.'],
                                        ['icon' => 'key', 'tone' => 'peri', 'text' => 'Manage user access.'],
                                        ['icon' => 'activity', 'tone' => 'aqua', 'text' => 'Monitor program participation.'],
                                        ['icon' => 'users', 'tone' => 'peach', 'text' => 'Coordinate community support.'],
                                        ['icon' => 'chart', 'tone' => 'sky', 'text' => 'Access reports and insights.'],
                                    ] as $cap)
                                        <li class="flex items-center gap-4 rounded-[18px] border border-[#EEF3F8] bg-[#FAFCFE] p-3 pr-4 transition-colors duration-500 ease-out hover:border-[#D5E5F3] hover:bg-white">
                                            <span class="v5-tile v5-tile-{{ $cap['tone'] }} size-10 rounded-[13px]">{!! $svg($cap['icon'], 'size-5', 2) !!}</span>
                                            <span class="flex-1 text-[#15203B]">{{ $cap['text'] }}</span>
                                            <span class="text-[#17605B]">{!! $svg('check', 'size-4', 2.6) !!}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @endif

        {{-- =========================================================
             10. OUR VISION
             ========================================================= --}}
        <section id="vision" class="relative overflow-hidden py-28 lg:py-36" aria-labelledby="vision-heading">
            <div class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2" aria-hidden="true">
                <div class="relative size-[720px] max-w-[100vw]">
                    <span class="absolute inset-0 rounded-full border border-[#DCE7F2]"></span>
                    <span class="absolute inset-[90px] rounded-full border border-[#DCE7F2]"></span>
                    <span class="absolute inset-[180px] rounded-full border border-[#D3E4F2]"></span>
                    <span class="absolute inset-[240px] rounded-full opacity-70 blur-2xl" style="background: radial-gradient(circle, rgba(119,177,220,0.4), rgba(127,209,199,0.2) 55%, transparent 75%);"></span>
                </div>
            </div>

            <div class="relative mx-auto max-w-3xl px-5 text-center sm:px-8">
                <p class="v5-eyebrow v5-reveal"><span class="v5-eyebrow-dot" aria-hidden="true"></span>Our Vision</p>
                <h2 id="vision-heading" class="v5-reveal mt-6 text-[2.25rem] font-extrabold leading-[1.08] text-[#15203B] sm:text-5xl lg:text-[3.5rem]" style="--d: 60ms">
                    A World Where Visual Information <span class="v5-text-gradient">Has No Barriers</span>
                </h2>
                <div class="v5-reveal mx-auto mt-7 max-w-2xl space-y-4 text-lg leading-relaxed text-[#46526B]" style="--d: 120ms">
                    <p>We envision a future where people with visual impairments can access information, explore their surroundings, and engage with everyday life with greater confidence and independence.</p>
                    <p>Through accessible technology and community collaboration, iCanSee aims to make that future possible.</p>
                </div>
                <p class="v5-reveal v5-display mt-10 inline-flex flex-wrap items-center justify-center gap-x-2 rounded-full bg-white/85 px-6 py-3.5 text-base font-bold text-[#26335C] v5-shadow sm:text-lg" style="--d: 180ms">
                    Technology for Accessibility. <span class="v5-text-gradient">Innovation for Independence.</span>
                </p>
            </div>
        </section>

        {{-- =========================================================
             11. FAQ
             ========================================================= --}}
        <section id="faq" class="relative pb-24 lg:pb-32" aria-labelledby="faq-heading">
            <div class="mx-auto grid max-w-[1200px] gap-12 px-5 sm:px-8 lg:grid-cols-12 lg:gap-16">
                <div class="lg:col-span-4">
                    <div class="lg:sticky lg:top-32">
                        <p class="v5-eyebrow v5-reveal"><span class="v5-eyebrow-dot" aria-hidden="true"></span>FAQ</p>
                        <h2 id="faq-heading" class="v5-reveal mt-5 text-[2rem] font-extrabold leading-[1.1] text-[#15203B] sm:text-[2.5rem]" style="--d: 60ms">
                            Frequently Asked <span class="v5-text-gradient">Questions</span>
                        </h2>
                        <div class="v5-reveal mt-8 rounded-[28px] border border-white bg-white/80 p-6 v5-shadow-sm" style="--d: 120ms">
                            <p class="font-bold text-[#15203B]">Still have questions?</p>
                            <p class="mt-1 text-[#46526B]">Our team is happy to help families, foundations, and partners.</p>
                            <button type="button" class="v5-btn v5-btn-secondary mt-5 w-full" data-contact="General Inquiry">
                                {!! $svg('mail', 'size-[18px]', 2) !!} Contact Us
                            </button>
                        </div>
                    </div>
                </div>

                @php
                    $faqs = [
                        ['q' => 'What is iCanSee?', 'a' => 'iCanSee is a web-based AI visual assistant that helps people with visual impairments understand visual information through voice descriptions.'],
                        ['q' => 'Do I need to install an application?', 'a' => 'No. iCanSee works directly through a supported smartphone web browser.'],
                        ['q' => 'How do I use iCanSee?', 'a' => 'Tap once to ask a question using your voice, or double-tap to open the camera. Once the camera is open, tap again to capture an image and hear its description.'],
                        ['q' => 'Is iCanSee free?', 'a' => 'Access and sponsorship options will be announced as the platform develops. Our goal is to make visual assistance as accessible as possible.'],
                        ['q' => 'How can I register someone?', 'a' => 'Parents, foundations, and community organizations can register users through the iCanSee Management Portal.'],
                        ['q' => 'Can I support iCanSee through donations?', 'a' => 'Yes. iCanSee plans to support donations and partnerships to help expand access and sustain the platform.'],
                        ['q' => 'Can iCanSee replace a mobility aid?', 'a' => 'No. iCanSee is an assistive tool for accessing visual information. AI-generated descriptions may be inaccurate and should not replace mobility aids or professional guidance in safety-critical situations.', 'note' => true],
                    ];
                @endphp

                <div class="space-y-3 lg:col-span-8">
                    @foreach ($faqs as $i => $faq)
                        <div class="v5-reveal rounded-[24px] border border-[#E3EBF4] bg-white" data-faq-item data-open="{{ $i === 0 ? 'true' : 'false' }}" style="--d: {{ $i * 50 }}ms">
                            <h3 class="text-base">
                                <button type="button" id="faq-q-{{ $i }}" aria-controls="faq-a-{{ $i }}" data-faq-trigger
                                        class="flex min-h-16 w-full items-center justify-between gap-5 rounded-[24px] px-6 py-4 text-left text-[1.0625rem] font-bold text-[#15203B] transition-colors hover:text-[#26335C]">
                                    <span>{{ $faq['q'] }}</span>
                                    <span class="v5-faq-icon inline-flex size-9 shrink-0 items-center justify-center rounded-full bg-[#F0F7FD] text-[#26335C]" aria-hidden="true">{!! $svg('plus', 'size-4', 2.4) !!}</span>
                                </button>
                            </h3>
                            <div id="faq-a-{{ $i }}" role="region" aria-labelledby="faq-q-{{ $i }}" class="v5-faq-panel">
                                <div>
                                    @if (! empty($faq['note']))
                                        <div class="mx-6 mb-6 flex gap-3 rounded-[18px] bg-[#FFF6EF] p-4 text-[#46526B]">
                                            <span class="mt-0.5 text-[#874623]">{!! $svg('alert', 'size-5', 2) !!}</span>
                                            <p class="leading-relaxed">{{ $faq['a'] }}</p>
                                        </div>
                                    @else
                                        <p class="px-6 pb-6 leading-relaxed text-[#46526B]">{{ $faq['a'] }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- =========================================================
             12. FINAL CTA
             ========================================================= --}}
        <section class="relative px-3 pb-24 sm:px-5 lg:pb-28" aria-labelledby="cta-heading">
            <div class="v5-reveal relative mx-auto max-w-[1200px] overflow-hidden rounded-[40px] border border-white px-6 py-20 text-center v5-shadow-lg sm:px-12 lg:rounded-[56px] lg:py-24"
                 style="background: radial-gradient(55% 80% at 0% 0%, rgba(119,177,220,0.45), transparent 60%), radial-gradient(45% 70% at 100% 0%, rgba(127,209,199,0.38), transparent 60%), radial-gradient(60% 80% at 50% 110%, rgba(253,214,190,0.55), transparent 60%), linear-gradient(180deg, #FFFFFF, #F3F8FC);">
                <div class="v5-dots pointer-events-none absolute inset-0" aria-hidden="true"></div>
                <div class="relative mx-auto max-w-2xl">
                    <span class="v5-tile v5-tile-navy mx-auto size-16 rounded-[22px] shadow-[0_18px_36px_-14px_rgba(38,51,92,0.7)]" aria-hidden="true">{!! $svg('eye', 'size-8', 2) !!}</span>
                    <h2 id="cta-heading" class="mt-8 text-[2.25rem] font-extrabold leading-[1.08] text-[#15203B] sm:text-5xl lg:text-[3.5rem]">
                        Let's Make the World More Accessible, <span class="v5-text-gradient">Together.</span>
                    </h2>
                    <div class="mx-auto mt-6 max-w-xl space-y-3 text-lg leading-relaxed text-[#46526B]">
                        <p>Everyone deserves access to information, independence, and opportunity.</p>
                        <p>Join us in building a future where technology helps people experience more of the world — beyond sight.</p>
                    </div>
                    <div class="mt-10 flex flex-col justify-center gap-3 sm:flex-row">
                        <a href="#getting-started" class="v5-btn v5-btn-primary !min-h-14 !px-7 !text-base">
                            Get Started with iCanSee <span class="v5-btn-arrow">{!! $svg('arrow-right', 'size-4', 2.4) !!}</span>
                        </a>
                        <a href="#support" class="v5-btn v5-btn-secondary !min-h-14 !px-7 !text-base">
                            {!! $svg('heart', 'size-[18px] text-[#C2557A]', 2.1) !!} Support Our Mission
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    {{-- =========================================================
         FOOTER
         ========================================================= --}}
    <footer class="border-t border-[#E3EBF4] bg-white/80" aria-labelledby="footer-heading">
        <h2 id="footer-heading" class="sr-only">Footer</h2>
        <div class="mx-auto max-w-[1200px] px-5 py-16 sm:px-8">
            <div class="grid gap-12 lg:grid-cols-12">
                <div class="lg:col-span-5">
                    <a href="{{ route('landing') }}" class="inline-flex min-h-11 items-center rounded-2xl" aria-label="iCanSee home">
                        <img src="{{ asset('images/logo-icansee.png') }}" alt="iCanSee" width="489" height="147" class="h-11 w-auto" loading="lazy" decoding="async">
                    </a>
                    <p class="v5-display mt-5 text-lg font-bold text-[#26335C]">iCanSee — See Beyond Sight.</p>
                    <p class="mt-2 max-w-sm leading-relaxed text-[#46526B]">Empowering people with visual impairments through accessible AI technology.</p>
                </div>

                @php
                    $footerCols = [
                        'Platform' => [
                            ['label' => 'About Us', 'href' => '#about'],
                            ['label' => 'How It Works', 'href' => '#how-it-works'],
                            ['label' => 'Features', 'href' => '#features'],
                            ['label' => 'Get Started', 'href' => '#getting-started'],
                        ],
                        'Community' => array_values(array_filter([
                            $showFoundations ? ['label' => 'For Foundations', 'href' => '#foundations'] : null,
                            ['label' => 'Become a Partner', 'href' => $waUrl, 'external' => true],
                            ['label' => 'Support Our Mission', 'href' => '#support'],
                            ['label' => 'Donate', 'href' => $waUrl, 'external' => true],
                        ])),
                        'Support' => [
                            ['label' => 'Frequently Asked Questions', 'href' => '#faq'],
                            ['label' => 'Contact Us', 'contact' => 'General Inquiry'],
                            ['label' => 'Privacy Policy', 'soon' => true],
                            ['label' => 'Terms of Service', 'soon' => true],
                        ],
                    ];
                @endphp

                <div class="grid gap-10 sm:grid-cols-3 lg:col-span-7">
                    @foreach ($footerCols as $title => $links)
                        <nav aria-label="{{ $title }}">
                            <p class="v5-display text-sm font-bold uppercase tracking-[0.14em] text-[#15203B]">{{ $title }}</p>
                            <ul class="mt-4 space-y-1">
                                @foreach ($links as $link)
                                    <li>
                                        @if (! empty($link['contact']))
                                            <button type="button" data-contact="{{ $link['contact'] }}" class="inline-flex min-h-10 items-center rounded-lg text-left text-[#46526B] transition-colors hover:text-[#26335C]">{{ $link['label'] }}</button>
                                        @elseif (! empty($link['soon']))
                                            <span class="inline-flex min-h-10 items-center gap-2 text-[#5E6A84]">
                                                {{ $link['label'] }}
                                                <span class="rounded-full bg-[#F0F7FD] px-2 py-0.5 text-[0.7rem] font-bold text-[#2F6DA6]">Soon</span>
                                            </span>
                                        @elseif (! empty($link['external']))
                                            <a href="{{ $link['href'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-10 items-center rounded-lg text-[#46526B] transition-colors hover:text-[#26335C]">
                                                {{ $link['label'] }}<span class="sr-only"> (opens WhatsApp in a new tab)</span>
                                            </a>
                                        @else
                                            <a href="{{ $link['href'] }}" class="inline-flex min-h-10 items-center rounded-lg text-[#46526B] transition-colors hover:text-[#26335C]">{{ $link['label'] }}</a>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </nav>
                    @endforeach
                </div>
            </div>

            <div class="mt-14 flex flex-col items-start justify-between gap-4 border-t border-[#EEF3F8] pt-8 text-[0.95rem] text-[#5E6A84] sm:flex-row sm:items-center">
                <p>&copy; {{ date('Y') }} iCanSee. Made with ❤️ by <strong><a href="https://smartlogy.id" target="_blank">Smartlogy.id</a></strong></p>
                <a href="{{ route('login') }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg font-semibold text-[#26335C] hover:text-[#2F6DA6]">
                    {!! $svg('login', 'size-4', 2.2) !!} Management Portal
                </a>
            </div>
        </div>
    </footer>

    {{-- =========================================================
         CONTACT DIALOG (native <dialog>: focus trap + Esc built in)
         ========================================================= --}}
    <dialog id="v5-contact" class="v5-dialog m-auto" aria-labelledby="v5-contact-title" aria-describedby="v5-contact-desc">
        <div class="relative overflow-hidden rounded-[32px] bg-white p-7 v5-shadow-lg sm:p-9">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-32" style="background: radial-gradient(60% 100% at 0% 0%, rgba(119,177,220,0.28), transparent 70%), radial-gradient(50% 100% at 100% 0%, rgba(127,209,199,0.24), transparent 70%);" aria-hidden="true"></div>

            <button type="button" data-close class="absolute right-5 top-5 inline-flex size-11 items-center justify-center rounded-full bg-white/80 text-[#46526B] ring-1 ring-[#E3EBF4] transition-colors hover:bg-[#F0F7FD] hover:text-[#15203B]" aria-label="Close dialog">
                {!! $svg('x', 'size-5', 2.2) !!}
            </button>

            <div class="relative">
                <p class="v5-eyebrow"><span class="v5-eyebrow-dot" aria-hidden="true"></span><span id="v5-contact-topic">General Inquiry</span></p>
                <h2 id="v5-contact-title" class="mt-4 text-2xl font-extrabold text-[#15203B] sm:text-[1.75rem]">Connect with iCanSee</h2>
                <p id="v5-contact-desc" class="mt-2 text-[#46526B]">Share a few details and our community team will get back to you.</p>
            </div>

            <form id="v5-contact-form" class="relative mt-7 space-y-4" novalidate>
                <input type="hidden" name="topic" value="General Inquiry">

                <div>
                    <label for="v5-name" class="mb-1.5 block text-sm font-bold text-[#15203B]">Full name</label>
                    <input id="v5-name" name="name" type="text" class="v5-input" autocomplete="name" required data-validate data-required="Please enter your name." aria-describedby="v5-name-error">
                    <p id="v5-name-error" class="mt-1.5 flex items-center gap-1.5 text-sm font-semibold text-[#B03A0A]" hidden></p>
                </div>

                <div>
                    <label for="v5-email" class="mb-1.5 block text-sm font-bold text-[#15203B]">Email address</label>
                    <input id="v5-email" name="email" type="email" class="v5-input" autocomplete="email" inputmode="email" required data-validate data-required="Please enter your email address." data-invalid="Enter an email in the format name@example.com." aria-describedby="v5-email-error">
                    <p id="v5-email-error" class="mt-1.5 text-sm font-semibold text-[#B03A0A]" hidden></p>
                </div>

                <div>
                    <label for="v5-org" class="mb-1.5 block text-sm font-bold text-[#15203B]">Organization or community <span class="font-medium text-[#5E6A84]">(optional)</span></label>
                    <input id="v5-org" name="organization" type="text" class="v5-input" autocomplete="organization">
                </div>

                <div>
                    <label for="v5-message" class="mb-1.5 block text-sm font-bold text-[#15203B]">Message</label>
                    <textarea id="v5-message" name="message" rows="3" class="v5-input !min-h-[110px] resize-y" required data-validate data-required="Please tell us how you'd like to help or participate." aria-describedby="v5-message-error"></textarea>
                    <p id="v5-message-error" class="mt-1.5 text-sm font-semibold text-[#B03A0A]" hidden></p>
                </div>

                <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
                    <button type="button" data-close class="v5-btn v5-btn-secondary">Cancel</button>
                    <button type="submit" class="v5-btn v5-btn-primary">Send message <span class="v5-btn-arrow">{!! $svg('arrow-right', 'size-4', 2.4) !!}</span></button>
                </div>
            </form>

            <div id="v5-contact-success" class="relative mt-7 text-center" hidden>
                <span class="v5-tile v5-tile-aqua mx-auto size-16 rounded-full">{!! $svg('check', 'size-8', 2.4) !!}</span>
                <h3 id="v5-contact-success-title" class="mt-5 text-xl font-extrabold text-[#15203B]" tabindex="-1">Thank you!</h3>
                <p id="v5-contact-success-text" class="mx-auto mt-2 max-w-sm text-[#46526B]" role="status"></p>
                <button type="button" data-close class="v5-btn v5-btn-primary mt-7">Done</button>
            </div>
        </div>
    </dialog>

    {{-- =========================================================
         SCRIPTS (vanilla, progressive enhancement)
         ========================================================= --}}
    <script>
        (() => {
            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

            /* ---------- Header elevation on scroll ---------- */
            const header = document.getElementById('v5-header');
            let ticking = false;
            const syncHeader = () => {
                header.dataset.scrolled = window.scrollY > 12 ? 'true' : 'false';
                ticking = false;
            };
            window.addEventListener('scroll', () => {
                if (!ticking) { ticking = true; requestAnimationFrame(syncHeader); }
            }, { passive: true });
            syncHeader();

            /* ---------- Mobile menu ---------- */
            const menuBtn = document.getElementById('v5-menu-btn');
            const menu = document.getElementById('v5-menu');
            const setMenu = (open) => {
                menu.dataset.open = String(open);
                menuBtn.setAttribute('aria-expanded', String(open));
                menuBtn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
                menuBtn.querySelector('[data-menu-icon="open"]').classList.toggle('hidden', open);
                menuBtn.querySelector('[data-menu-icon="close"]').classList.toggle('hidden', !open);
            };
            menuBtn.addEventListener('click', () => setMenu(menu.dataset.open !== 'true'));
            menu.querySelectorAll('a').forEach((a) => a.addEventListener('click', () => setMenu(false)));
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && menu.dataset.open === 'true') { setMenu(false); menuBtn.focus(); }
            });
            document.addEventListener('click', (e) => {
                if (menu.dataset.open === 'true' && !menu.contains(e.target) && !menuBtn.contains(e.target)) setMenu(false);
            });
            window.matchMedia('(min-width: 1024px)').addEventListener('change', (e) => { if (e.matches) setMenu(false); });

            /* ---------- Scroll reveal ---------- */
            const reveals = document.querySelectorAll('.v5-reveal');
            if (reduceMotion.matches || !('IntersectionObserver' in window)) {
                reveals.forEach((el) => el.classList.add('is-in'));
            } else {
                const revealer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) { entry.target.classList.add('is-in'); revealer.unobserve(entry.target); }
                    });
                }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
                reveals.forEach((el) => revealer.observe(el));
            }

            /* ---------- Active section in nav ---------- */
            const navLinks = [...document.querySelectorAll('[data-nav-link]')];
            const sectionIds = [...new Set(navLinks.map((l) => l.getAttribute('href')))];
            const sections = sectionIds.map((id) => document.querySelector(id)).filter(Boolean);
            if ('IntersectionObserver' in window) {
                const spy = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) return;
                        const id = '#' + entry.target.id;
                        navLinks.forEach((l) => {
                            if (l.getAttribute('href') === id) l.setAttribute('aria-current', 'true');
                            else l.removeAttribute('aria-current');
                        });
                    });
                }, { rootMargin: '-45% 0px -50% 0px' });
                sections.forEach((s) => spy.observe(s));
            }

            /* ---------- Hero phone: pause looping motion (WCAG 2.2.2) ---------- */
            const demoRoot = document.querySelector('[data-demo-root]');
            const toggle = document.getElementById('v5-demo-toggle');
            const setPlaying = (playing) => {
                demoRoot.dataset.paused = String(!playing);
                toggle.setAttribute('aria-pressed', String(!playing));
                toggle.querySelector('[data-toggle-label]').textContent = playing ? 'Pause animation' : 'Play animation';
                toggle.querySelector('[data-toggle-icon="pause"]').classList.toggle('hidden', !playing);
                toggle.querySelector('[data-toggle-icon="play"]').classList.toggle('hidden', playing);
            };
            toggle.addEventListener('click', () => setPlaying(demoRoot.dataset.paused === 'true'));
            setPlaying(!reduceMotion.matches);

            /* ---------- FAQ accordion ---------- */
            document.querySelectorAll('[data-faq-item]').forEach((item) => {
                const trigger = item.querySelector('[data-faq-trigger]');
                trigger.setAttribute('aria-expanded', item.dataset.open === 'true' ? 'true' : 'false');
                trigger.addEventListener('click', () => {
                    const open = item.dataset.open !== 'true';
                    item.dataset.open = String(open);
                    trigger.setAttribute('aria-expanded', String(open));
                });
            });

            /* ---------- Contact dialog ---------- */
            const dialog = document.getElementById('v5-contact');
            const form = document.getElementById('v5-contact-form');
            const success = document.getElementById('v5-contact-success');
            const topicLabel = document.getElementById('v5-contact-topic');
            let lastTrigger = null;

            const resetDialog = () => {
                form.reset();
                form.hidden = false;
                success.hidden = true;
                form.querySelectorAll('[data-validate]').forEach((field) => {
                    field.removeAttribute('aria-invalid');
                    document.getElementById(field.id + '-error').hidden = true;
                });
            };

            document.querySelectorAll('[data-contact]').forEach((btn) => btn.addEventListener('click', () => {
                lastTrigger = btn;
                const topic = btn.dataset.contact || 'General Inquiry';
                resetDialog();
                topicLabel.textContent = topic;
                form.elements.topic.value = topic;
                dialog.showModal();
            }));
            dialog.querySelectorAll('[data-close]').forEach((b) => b.addEventListener('click', () => dialog.close()));
            dialog.addEventListener('click', (e) => { if (e.target === dialog) dialog.close(); });
            dialog.addEventListener('close', () => { if (lastTrigger) lastTrigger.focus(); });

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                let firstInvalid = null;
                form.querySelectorAll('[data-validate]').forEach((field) => {
                    const error = document.getElementById(field.id + '-error');
                    const valid = field.checkValidity();
                    field.setAttribute('aria-invalid', String(!valid));
                    error.hidden = valid;
                    if (!valid) {
                        error.textContent = field.validity.valueMissing ? field.dataset.required : (field.dataset.invalid || field.dataset.required);
                        if (!firstInvalid) firstInvalid = field;
                    }
                });
                if (firstInvalid) { firstInvalid.focus(); return; }

                // Front-end only for now — no backend endpoint is wired yet.
                const name = form.elements.name.value.trim().split(' ')[0];
                document.getElementById('v5-contact-success-text').textContent =
                    `Thanks, ${name}. We've noted your interest in “${form.elements.topic.value}” and will reach out to ${form.elements.email.value.trim()}.`;
                form.hidden = true;
                success.hidden = false;
                document.getElementById('v5-contact-success-title').focus();
            });
        })();
    </script>
</body>
</html>
