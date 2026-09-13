# Core Architecture — Vebto vs nkhoj

_Last updated: 2026-09-13_

---

## Overview

| Aspect | Vebto Core | nkhoj |
|--------|-----------|-------|
| **Frontend paradigm** | React 18 SPA (TypeScript) | Blade templates + Alpine.js (PHP/Blade) |
| **State management** | TanStack Query (React Query) | None — per-request Blade `$variables` |
| **Data bootstrapping** | `window.bootstrapData` JSON inline in HTML | Individual `$variable` passed to each view |
| **Theme system** | DB-stored CSS themes + cookie `be-active-theme` | Tailwind `dark:` classes + `data-theme` attr |
| **i18n (frontend)** | `Localization` object in bootstrap data | Blade `__()` only — no JS i18n |
| **Settings (frontend)** | Full typed `Settings` interface via TanStack Query | None — no consolidated JS settings object |
| **SiteConfig pattern** | `deepMerge(BaseSiteConfig, SiteConfig)` per app | None |
| **Provider tree** | `CommonProvider` → QueryClient → LazyMotion → SiteConfig → BootstrapData → Theme | None — layout.blade.php + Alpine |
| **Auth state in JS** | `data.user` in bootstrap data; null = guest | Laravel `@auth` / `@guest` Blade directives |
| **CSRF** | `data.csrf_token` in bootstrap data | `<meta name="csrf-token">` + `document.querySelector` |

---

## Bootstrap Data Pattern

### Vebto

Server inlines all session state once in the initial HTML response:

```html
<script>window.bootstrapData = { ...json... };</script>
```

`BootstrapData` interface (from `bootstrap-data.ts`):
```ts
interface BootstrapData {
  themes:             { all: Theme[]; selectedTheme: Theme | null };
  sentry_release:     string | null;
  is_mobile_device:   boolean;
  csrf_token:         string;
  settings:           Settings;           // full app settings
  user:               AuthUser | null;    // logged-in user or null
  guest_role:         Role;
  i18n:               Record<string, string>;
  default_meta_tags:  MetaTag[];
  show_cookie_notice: boolean;
  rendered_ssr:       boolean;
}
```

TanStack Query caches it permanently (`staleTime: Infinity`):
```ts
// use-backend-bootstrap-data.ts
queryClient.setQueryData(bootstrapDataQueryKey, window.bootstrapData);
useQuery({ queryKey: bootstrapDataQueryKey, staleTime: Infinity });
```

Components anywhere in the tree access it with:
```ts
const { data } = useBootstrapData();
const { auth } = useSettings(); // thin wrapper over data.settings
```

### nkhoj (current)

No `window.bootstrapData`. Every controller passes individual variables:
```php
// BlogController.php
return view('blog.index', ['posts' => $posts, 'categories' => $categories]);
```

Alpine.js components that need dynamic data use inline `x-data`:
```html
<div x-data="{ open: false, count: {{ $count }} }">
```

**Problem:** Settings accessed in JS must be duplicated manually (blade echoes into JS).

### Improvement for nkhoj

Add a Blade layout partial that inlines auth/settings as a compact JSON object:

```blade
{{-- layouts/partials/bootstrap-data.blade.php --}}
<script>
window.appData = {
    csrfToken: "{{ csrf_token() }}",
    locale: "{{ app()->getLocale() }}",
    user: @auth {{ Js::from(['id' => auth()->id(), 'name' => auth()->user()->name, 'avatar' => auth()->user()->avatar]) }} @else null @endauth,
    settings: @json([
        'siteName'    => config('app.name'),
        'timezone'    => config('app.timezone'),
        'uploadLimit' => config('media.max_size', 10),
    ])
};
</script>
```

Alpine store wrapping it:
```js
// resources/js/app.js
document.addEventListener('alpine:init', () => {
    Alpine.store('app', window.appData ?? {});
});
// Usage anywhere: $store.app.user, $store.app.csrfToken
```

---

## Settings Interface

### Vebto `Settings` (from `settings.ts`)

Full typed interface — 60+ fields grouped by concern:

```ts
interface Settings {
  // Branding
  site_name: string; logo: string; favicon: string; site_description: string;
  // Auth
  registration_disabled: boolean; single_device_login: boolean; require_email_confirmation: boolean;
  // Billing (Stripe + PayPal)
  billing: { stripe_key: string; paypal_client_id: string; ... };
  // Social login
  social: { google: boolean; facebook: boolean; twitter: boolean; ... };
  // Uploads
  uploads: { disk: string; max_size: number; chunk_size: number; s3_direct_upload: boolean; ... };
  // i18n
  i18n: { default_localization: string; languages: Language[]; ... };
  // Recaptcha, analytics, notifications, ads, etc.
}
```

Used app-wide as:
```ts
const { uploads } = useSettings();
const maxSize = uploads.max_size;
```

### nkhoj (current)

No typed settings interface. Config values read via Laravel `config()` in PHP, never exposed to JS. No front-end settings contract.

### Improvement for nkhoj

Create `config/frontend.php` (or extend `config/app.php`) with a curated subset of settings that the front-end legitimately needs, then echo it in the bootstrap-data partial. This avoids leaking server-only settings.

---

## Theme System

### Vebto (`theme-provider.tsx`)

```ts
// Cookie: be-active-theme
// Reads: data.themes.all  (DB-stored CSS variable overrides)
// Calls: applyThemeToDom(theme)  — sets CSS custom properties on <html>
// Settings gate: canChangeTheme (per settings.themes.user_change)
```

Themes are stored in DB, users can have their own active theme. The `ThemeProvider` mounts inside `CommonProvider` and immediately reads the cookie to apply the theme before React paints (no flash).

### nkhoj (current)

```html
<!-- Alpine toggle in navbar: -->
<button @click="dark = !dark; document.documentElement.classList.toggle('dark', dark)">
```

Persistence via `localStorage` only; no DB user preference; no multi-theme — only light/dark.

### Improvement for nkhoj (incremental)

Save dark mode to user's `settings` JSON column (already exists in the `users` table if there is a `settings` column, or add one):

```php
// UserSettingsController
$user->update(['settings->theme' => $request->theme]);  // 'dark' | 'light'
```

Apply on page load:
```blade
{{-- layouts/app.blade.php --}}
<html data-theme="{{ auth()->user()?->settings['theme'] ?? 'light' }}">
```

---

## CommonProvider / Provider Tree

### Vebto (`common-provider.tsx`)

```tsx
export function CommonProvider({ children, config }) {
    const merged = useMemo(() => deepMerge(BaseSiteConfig, config), [config]);
    return (
        <React.StrictMode>
          <QueryClientProvider client={queryClient}>
            <LazyMotion features={loadFeatures}>
              <SiteConfigContext.Provider value={merged}>
                <BootstrapDataProvider>
                  <ThemeProvider>
                    {children}
                  </ThemeProvider>
                </BootstrapDataProvider>
              </SiteConfigContext.Provider>
            </LazyMotion>
          </QueryClientProvider>
        </React.StrictMode>
    );
}
```

Each Laravel app (Bedrive, BeMusic, BeDesk) calls:
```tsx
createRoot(document.getElementById('root')).render(
    <CommonProvider config={SiteConfig}><App /></CommonProvider>
);
```

### nkhoj (current)

`layouts/app.blade.php` is the equivalent — includes navbar, footer, Alpine initialisation, and pushes stacks. No explicit provider concept; Alpine `$store` is the closest analogue.

### Improvement for nkhoj

Create an Alpine plugin or global `x-data` on `<body>` that acts as the "provider":

```blade
{{-- layouts/app.blade.php --}}
<body x-data="appRoot()" x-bind:data-theme="theme">
```

```js
// resources/js/app.js
Alpine.data('appRoot', () => ({
    theme: window.appData?.settings?.theme ?? localStorage.getItem('theme') ?? 'light',
    user:  window.appData?.user ?? null,
    init() {
        this.$watch('theme', v => {
            document.documentElement.setAttribute('data-theme', v);
            localStorage.setItem('theme', v);
        });
    },
}));
```

---

## SiteConfig / BaseSiteConfig

### Vebto

`BaseSiteConfig` defines per-app overrides — which social login buttons to show, which homepage variant, whether notifications are enabled:

```ts
interface BaseSiteConfig {
    auth: { redirectUri; forgotPasswordRoute; ... };
    notifications: { pageSize; ... };
    tags: { types: TagType[] };
    customPages: Record<string, CustomPage>;
    admin: { ads: AdConfig[] };
    demo: DemoConfig;
    homepage: { type; variant; ... };
}
```

Each Vebto app (Bedrive, BeMusic) calls `CommonProvider` with its own `SiteConfig` partial that overrides these defaults.

### nkhoj (current)

No equivalent. Feature flags / toggles are done via `config/` PHP files or environment variables, not exposed to the front-end.

---

## Data Flow Comparison

```
Vebto:
  Server → window.bootstrapData (all state)
        → React/TanStack Query → useBootstrapData() hook
        → useSettings(), useAuth(), useTheme() — read from cache
        → Components render with full data, zero extra requests

nkhoj:
  Server → Blade compiles → HTML with embedded PHP values
         → Alpine x-data reads inline values
         → fetch() calls if dynamic update needed (ad-hoc, not cached)
```

---

## Gap Summary & Improvement Roadmap

| Gap | Effort | Recommendation |
|-----|--------|---------------|
| No JS bootstrap data | Low | Add `window.appData` partial in layout; Alpine `$store('app', ...)` |
| No settings in JS | Low | Expose curated settings in `window.appData.settings` |
| Dark mode not persisted per user | Medium | Store `settings->theme` in user record; read in layout |
| No i18n in JS | Medium | Echo `__()` translations for used keys into `window.appData.i18n` |
| No TanStack Query cache | High | Not needed — nkhoj is not a SPA; Blade handles data delivery |
| No React provider tree | Very High | Not recommended — full React rewrite; stay with Alpine |

**Verdict:** nkhoj is a server-rendered multi-page app; adopting Vebto's React/TanStack architecture wholesale is unnecessary. The practical wins are:
1. `window.appData` bootstrap pattern (Alpine-readable, one place for all JS-accessible state)
2. Theme preference persisted to DB per user
3. Curated i18n keys echoed for any Alpine components that need them

These three improvements give ~80% of Vebto's benefit for nkhoj's actual stack without a rewrite.
