# nkhoj — UI/UX Design System

> Design language for नखोज — a Nepali community news and blogging platform.

---

## 1. Design Philosophy

| Principle | Implementation |
|-----------|---------------|
| **Nepali-first** | Nepali (Devanagari) text in all primary headings and labels; English as secondary |
| **Lightweight** | No build step, CDN-only assets, no JavaScript framework bundles |
| **Familiar patterns** | Card-grid inspired by Google Plus / Naver Blog; sidebar like Medium |
| **Readable** | Large body text, generous line-height, strong contrast |
| **Dark-mode ready** | Full `dark:` variant coverage via Tailwind; respects OS preference + manual toggle |

---

## 2. Colour Palette

### Brand (Primary)

The `brand` colour alias maps to a custom Tailwind colour. Default is an indigo-blue:

| Token | Hex (approx) | Usage |
|-------|-------------|-------|
| `brand-50` | `#eef2ff` | Tag backgrounds, hover states |
| `brand-100` | `#e0e7ff` | Light chip backgrounds |
| `brand-200` | `#c7d2fe` | Borders, subtle accents |
| `brand-400` | `#818cf8` | Icon accents |
| `brand-500` | `#6366f1` | Primary CTA buttons, active pills, progress bars |
| `brand-600` | `#4f46e5` | Hover state on brand-500 |
| `brand-700` | `#4338ca` | Admin sidebar active |
| `brand-900` | `#312e81` | Dark-mode deep bg |

### Semantic Colours

| Usage | Light | Dark |
|-------|-------|------|
| Page background | `gray-50` | `gray-900` |
| Card background | `white` | `gray-800` |
| Card border | `gray-100` | `gray-700` |
| Primary text | `gray-900` | `white` |
| Secondary text | `gray-500` | `gray-400` |
| Muted text | `gray-400` | `gray-500` |
| Danger / delete | `red-500` | `red-400` |
| Success | `green-500` | `green-400` |
| Warning | `amber-500` | `amber-400` |
| Premium / gold | `amber-500` | `amber-400` |

---

## 3. Typography

### Fonts

| Role | Font | Source |
|------|------|--------|
| Nepali body / headings | System font with `font-nepali` class | Devanagari system stack |
| English UI | System UI (`font-sans`) | Tailwind default stack |
| Monospace (slugs, code) | `font-mono` | Tailwind default |

### Scale

| Class | Size | Weight | Usage |
|-------|------|--------|-------|
| `text-3xl font-bold` | 30px / 700 | Post title in editor |
| `text-xl font-bold` | 20px / 700 | Page headings (admin) |
| `text-lg font-bold` | 18px / 700 | Card headings |
| `text-sm font-bold` | 14px / 700 | Section labels, section dividers |
| `text-sm` | 14px / 400 | Body text, form inputs |
| `text-xs font-semibold` | 12px / 600 | Labels, tags, badges |
| `text-xs` | 12px / 400 | Meta info, timestamps, counts |

---

## 4. Component Library

### 4.1 Cards

**Post card** — used in feeds, editor's pick grid:
```
┌─────────────────────────────┐
│  [Thumbnail 16:9]           │
│  [Category badge]           │
├─────────────────────────────┤
│  Post Title (2-line clamp)  │
│  Author · N views           │
└─────────────────────────────┘
```
- Rounded: `rounded-xl`
- Border: `border border-gray-100`
- Shadow: `shadow-sm` → `hover:shadow-md`
- Image hover: `scale-105` transform (500ms)

**Hero card** — full-bleed image with gradient overlay:
```
┌─────────────────────────────┐
│                             │
│  [Full-bleed background]    │
│     gradient overlay ↓      │
│  Category | Featured badge  │
│  Title (2-line clamp)       │
│  Avatar · Author · Time ago │
└─────────────────────────────┘
```
- Min height: `200px`
- Overlay: `bg-gradient-to-t from-black/80 via-black/20 to-transparent`
- Text: white on dark overlay

### 4.2 Buttons

| Variant | Classes | Usage |
|---------|---------|-------|
| Primary | `bg-brand-500 text-white hover:bg-brand-600 rounded-lg px-4 py-2 text-sm font-semibold` | Main CTA |
| Secondary | `border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-lg` | Cancel, secondary action |
| Danger | `bg-red-50 text-red-600 border border-red-200 hover:bg-red-100` | Delete |
| Ghost | `text-gray-400 hover:text-brand-500` | Icon buttons, back links |
| Pill (active) | `bg-brand-500 text-white rounded-full px-4 py-1.5` | Category tabs |
| Pill (inactive) | `text-gray-600 hover:bg-gray-100 rounded-full px-4 py-1.5` | Category tabs |

### 4.3 Toggle Switch

Custom Alpine.js toggle — no native checkbox shown:

```html
<!-- x-data="{ on: false }" on parent div -->
<div class="relative flex-shrink-0 cursor-pointer" @click="on = !on">
    <input type="hidden" name="field" :value="on ? '1' : '0'">
    <div class="w-9 h-5 rounded-full transition-colors"
         :class="on ? 'bg-brand-500' : 'bg-gray-200'"></div>
    <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform"
         :class="on ? 'translate-x-4' : ''"></div>
</div>
```

Track: `w-9 h-5` (36×20px)  
Thumb: `w-4 h-4` (16px) white circle  
Active: brand-500 track + `translate-x-4` thumb  
Premium variant: `amber-500` track

### 4.4 Form Inputs

```
Text input:
  border border-gray-200 dark:border-gray-600
  dark:bg-gray-700 dark:text-white
  rounded-lg px-3 py-2 text-sm
  focus:outline-none focus:ring-2 focus:ring-brand-500

Textarea:
  Same + resize-none

Select:
  Same as text input
```

**Tag input** — chip-style tag manager:
- Chips: `bg-brand-50 border border-brand-200 text-brand-600 rounded-full px-2.5 py-0.5 text-xs`
- Remove button: `×` inline, `hover:text-red-400`
- Inline text field grows to fill remaining width

### 4.5 Sidebar Widgets

Consistent widget card shell:
```
bg-white dark:bg-gray-800
rounded-xl border border-gray-100 dark:border-gray-700
shadow-sm p-4
```

Widget types rendered by `partials/_widget.blade.php`:
- **Trending** — numbered list with rank colour (brand for top 3, gray for rest)
- **Categories** — list with post count badge
- **Popular tags** — pill cloud
- **About us** — avatar + bio text
- **Follow us** — social icon links
- **Voting poll** — radio options with live vote counts
- **Recommended** — mini post cards

### 4.6 Section Dividers

```
<div class="flex items-center gap-2 mb-4">
    <span class="text-xs font-bold text-brand-600 uppercase tracking-widest">सम्पादकको छनोट</span>
    <div class="flex-1 h-px bg-gray-100"></div>
    <span class="text-xs text-gray-400">Editor's Pick</span>
</div>
```

Nepali label left · horizontal rule · English label right.

### 4.7 Badges / Chips

| Type | Classes |
|------|---------|
| Category | `text-xs px-2 py-0.5 bg-brand-500 text-white rounded-full font-semibold` |
| Tag | `bg-brand-50 text-brand-600 border border-brand-200 rounded-full px-2.5 py-0.5 text-xs` |
| Featured | `text-xs font-bold text-brand-300 uppercase tracking-widest` |
| Role: Admin | `bg-red-100 text-red-700 rounded-full text-xs px-2 py-0.5` |
| Role: Editor | `bg-purple-100 text-purple-700 rounded-full text-xs px-2 py-0.5` |
| Role: Member | `bg-gray-100 text-gray-600 rounded-full text-xs px-2 py-0.5` |

---

## 5. Page Layouts

### 5.1 Homepage

```
┌─────────────────────────────────────────────────────┐
│  HEADER (logo · nav · search · dark toggle · auth)  │
├─────────────────────────────────────────────────────┤
│  HERO STRIP — 3 cards, sm:grid-cols-3               │
├─────────────────────────────────────────────────────┤
│  CATEGORY PILLS — horizontal scroll (AJAX switch)   │
├────────────────────────────┬────────────────────────┤
│  MAIN (lg:col-span-3)      │  SIDEBAR (lg:col-span-1)│
│  ┌──────────────────────┐  │  ┌──────────────────┐  │
│  │ Editor's Pick 3-col  │  │  │ User panel       │  │
│  └──────────────────────┘  │  │ (auth / CTA)     │  │
│  ── Latest / नवीनतम ──     │  ├──────────────────┤  │
│  #posts-feed (AJAX target) │  │ Widgets stack    │  │
│  [post cards 3-col grid]   │  │ (dynamic)        │  │
│  [pagination]              │  └──────────────────┘  │
└────────────────────────────┴────────────────────────┘
│  FOOTER                                             │
└─────────────────────────────────────────────────────┘
```

Grid: `grid grid-cols-1 lg:grid-cols-4` — main takes 3, sidebar takes 1.

### 5.2 Post Editor (Dashboard)

```
┌─────────────────────────────────────────────────────┐
│  TOPBAR — breadcrumb · View Post link               │
├───────────────────────────────┬─────────────────────┤
│  LEFT (1fr, min-width 0)      │  RIGHT (320px fixed)│
│  ┌───────────────────────┐    │  ┌───────────────┐  │
│  │ Title input (xl text) │    │  │ Publish panel │  │
│  │ Slug row              │    │  │ Status select │  │
│  └───────────────────────┘    │  │ Category      │  │
│  ┌───────────────────────┐    │  │ Visibility    │  │
│  │ Excerpt textarea      │    │  │ Schedule date │  │
│  └───────────────────────┘    │  ├───────────────┤  │
│  ┌───────────────────────┐    │  │ Toggles       │  │
│  │ Tags chip input       │    │  │ Featured      │  │
│  └───────────────────────┘    │  │ Breaking News │  │
│  ┌───────────────────────┐    │  │ Slider        │  │
│  │ TinyMCE editor        │    │  │ Recommended   │  │
│  │ (body content)        │    │  │ Premium       │  │
│  └───────────────────────┘    │  ├───────────────┤  │
│  ┌───────────────────────┐    │  │ Thumbnail     │  │
│  │ Sources (repeatable)  │    │  ├───────────────┤  │
│  └───────────────────────┘    │  │ SEO Meta      │  │
│  ┌───────────────────────┐    │  │ (collapsible) │  │
│  │ FAQ accordion items   │    │  └───────────────┘  │
│  └───────────────────────┘    │                     │
└───────────────────────────────┴─────────────────────┘
```

Grid: `xl:grid-cols-[1fr_320px]` — responsive, single column on mobile.

### 5.3 Admin Panel

```
┌──────────┬──────────────────────────────────────────┐
│  SIDEBAR │  TOPBAR — breadcrumb · user avatar       │
│  (fixed) ├──────────────────────────────────────────┤
│          │  PAGE CONTENT                            │
│  Nav:    │  ┌─────────────────────────────────────┐ │
│  Dashboard│  │ Stats tiles (4-col grid)            │ │
│  Posts   │  ├─────────────────────────────────────┤ │
│  Users   │  │ Data table with actions              │ │
│  ...     │  │ (search, filter, pagination)         │ │
│          │  └─────────────────────────────────────┘ │
└──────────┴──────────────────────────────────────────┘
```

---

## 6. Motion & Interaction

| Element | Animation |
|---------|----------|
| Card thumbnail hover | `transition-transform duration-500` → `scale-105` |
| Toggle thumb | `transition-transform` → `translate-x-4` |
| Toggle track | `transition-colors` → colour swap |
| Button hover | `transition-colors` (no duration specified → 150ms default) |
| AJAX loading bar | `animate-pulse` on thin `h-0.5` bar at top of pill strip |
| Collapsible sections | `x-collapse` (Alpine collapse plugin) with height transition |
| Dark mode | Instant (class swap on `<html>`) |

---

## 7. Responsive Breakpoints

| Breakpoint | Width | Key changes |
|-----------|-------|-------------|
| Default (mobile) | < 640px | Single column everywhere, hero stacks |
| `sm` | ≥ 640px | Hero 3-column grid, some hidden elements show |
| `lg` | ≥ 1024px | Main + sidebar two-panel layout |
| `xl` | ≥ 1280px | Editor two-panel `[1fr_320px]` |

---

## 8. Dark Mode Implementation

Theme stored in a cookie. On every page load:
- Cookie `theme=dark` → `<html data-theme="dark">` → Tailwind applies all `dark:` variants
- Cookie `theme=light` → `<html data-theme="light">`
- No cookie → `prefers-color-scheme` media query controls

Toggle button calls `POST /theme/toggle` which sets the cookie and redirects back.

---

## 9. Nepali Language & i18n

- Primary content language: Nepali (Devanagari script)
- UI labels: bilingual — Nepali primary, English secondary
- Category names stored in both `name_ne` (Nepali) and `name_en` (English)
- Tag names: `name_ne` / `name_en`
- Display priority: `$cat->name_ne ?? $cat->name_en`
- Blade class `font-nepali` applied to all Nepali text blocks for correct font rendering
- No RTL — Devanagari is left-to-right
- Locale switching available via `LanguageController` (`/language/{code}`)

---

## 10. Admin Design Patterns

### Stats Tile
```
┌──────────────────────────┐
│ Icon (w-10 h-10 rounded) │
│ Large number             │
│ Label text               │
│ Delta badge (↑ +12%)     │
└──────────────────────────┘
```
Grid: `grid-cols-2 lg:grid-cols-4 gap-4`

### Data Table
```
┌──────────────────────────────────────────────────────┐
│ Search input          [Filters]          [+ Add btn] │
├──────┬──────────────────────────────────┬────────────┤
│ #    │ Row data                         │ Actions    │
│ ...  │ ...                              │ Edit/Delete│
├──────┴──────────────────────────────────┴────────────┤
│ Pagination (prev/next + page numbers)               │
└──────────────────────────────────────────────────────┘
```

### Modal / Confirm Dialog
Inline confirmation via `onclick="return confirm('...')"` on delete buttons. No custom modal component.

### Flash Messages
```
@if(session('success'))
<div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-4">
    {{ session('success') }}
</div>
@endif
```
