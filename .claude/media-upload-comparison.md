# Media Upload Architecture — Vebto vs nkhoj

_Last updated: 2026-09-13_

---

## Vebto Core Approach

| Aspect | Vebto |
|--------|-------|
| **Storage driver** | Laravel `Storage` facade; configurable disk (`public`, `uploads`, S3, Backblaze, DigitalOcean, Dropbox) |
| **File model** | `FileEntry` — a DB record per file (id, name, mime, size, owner_id, disk_prefix, public) |
| **Upload action** | `StoreFile::execute(FileEntryPayload, options)` — handles `UploadedFile`, string contents, or local path; uses `putFileAs()` on chosen disk |
| **Folder strategy** | `diskPrefix` on the payload (e.g. `uploads/avatars`, `uploads/file-entries`) — **not** per-content-type, but per-owner or per-feature |
| **User scoping** | `file_entry_user` pivot — files are owned by user; `FileEntry->users()` belongs-to-many |
| **Resume/chunked upload** | TUS protocol (`TusServer`, `TusFileEntryController`) for large files |
| **S3/cloud** | `S3FileEntryController`, `S3SimpleUploadController`, `S3MultipartUploadController` |
| **Image conversion** | Not built-in — left to consuming app |
| **Security** | Blocks `.htaccess`, PHP files in public disk; path traversal prevention on `diskPrefix` |
| **Deletion** | Soft-delete via `DeleteEntries`; `RestoreDeletedEntriesController` to restore |
| **Browser/picker** | React-based file manager UI (built into Vebto front-end) |

---

## nkhoj Current Approach (before this PR)

| Aspect | nkhoj (before) |
|--------|----------------|
| **Storage driver** | `public_path()` — writes directly to `public/uploads/` |
| **File model** | None — just a URL string stored in the parent record's column |
| **Upload action** | `SavesOptimizedThumbnail` trait (GD/Intervention Image → WebP, 1200×675 max, 80% quality) |
| **Folder strategy** | **Inconsistent**: posts → `uploads/` (root!), events → `uploads/events/`, recipes → `uploads/recipes/`, stories → `storage/public/stories/`, QnA → `storage/public/questions/` (different disk!), profiles/avatars → `storage/public/avatars/` |
| **User scoping** | None — all user uploads share one flat folder per type |
| **Resume/chunked** | Not supported |
| **S3/cloud** | Not configured |
| **Image conversion** | WebP via `toWebp(80)`, scale-down to 1200×675 |
| **Security** | `basename()` for admin delete; no PHP-file check |
| **Deletion** | Admin panel only; no soft delete |
| **Browser/picker** | TinyMCE "Image URL" field only — no browse button, no history |

---

## nkhoj Improved (this PR — 2026-09-13)

| Aspect | nkhoj (improved) |
|--------|-----------------|
| **Storage driver** | `public/uploads/` (unchanged — cPanel shared hosting, no S3) |
| **Folder strategy** | **Regulated per content type + per user**: `uploads/{type}/{user_id}/filename.webp` |
| **Folder map** | `posts/{uid}/`, `events/{uid}/`, `pages/{uid}/`, `questions/{uid}/`, `recipes/{uid}/`, `profiles/{uid}/`, `stories/{uid}/`, `reels/thumbs/` |
| **User scoping** | Each user's files live in `uploads/{type}/{user_id}/` — no cross-user pollution |
| **Image conversion** | WebP via `SavesOptimizedThumbnail` (unchanged) |
| **Admin media manager** | Upgraded: folder filter tabs (all/posts/events/…), scans all subdirs, per-folder upload, safe delete with subfolder path |
| **TinyMCE integration** | `images_upload_handler` → `POST /user/media/upload` (saves to user-scoped folder); `file_picker_callback` → `GET /user/media?type={type}` (browse own uploads with image grid) |
| **`mediaType` param** | Each `@include('partials.tinymce', [..., 'mediaType' => 'posts'])` sets the folder context |

---

## Folder Structure (target)

```
public/uploads/
├── posts/
│   ├── 1/          ← user_id 1's post images
│   │   └── 1726123456_abc12345.webp
│   └── 2/
├── events/
│   └── 1/
├── pages/
│   └── 1/
├── questions/
│   └── 1/
├── recipes/
│   └── 1/
├── profiles/
│   └── 1/
├── stories/
│   └── 1/
└── reels/
    └── thumbs/
```

---

## What Vebto Does Better (future improvements)

| Gap | Vebto solution | nkhoj path |
|-----|---------------|-----------|
| No file DB record | `FileEntry` model | Add `user_media` table if search/delete-by-record needed |
| No chunked upload | TUS protocol | Add TUS for video/large files |
| No cloud storage | Multi-disk (S3, B2, DO) | Add `FILESYSTEM_DISK=s3` when budget allows |
| No soft delete | Trash + restore | Add soft-delete to `UserMediaController` |
| No media library UI | React file manager | Could add Alpine-based grid in account settings |

---

## Controllers Modified

| File | Change |
|------|--------|
| `app/Traits/SavesOptimizedThumbnail.php` | Unchanged — already accepts `$subdir` |
| `app/Domains/Blog/Http/Controllers/DashboardController.php` | Pass `'posts'` subdir (was empty → root uploads) |
| `app/Domains/QnA/Http/Controllers/QuestionController.php` | Switch from `Storage::disk('public')` to `saveOptimizedThumbnail(..., 'questions')` |
| `app/Domains/Admin/Http/Controllers/ContentController.php` | Media manager: scan subdirs, folder filter, per-folder upload, safe delete |
| `app/Http/Controllers/UserMediaController.php` | NEW — `GET /user/media` (list own images), `POST /user/media/upload` |
| `resources/views/partials/tinymce.blade.php` | Add `images_upload_handler` + `file_picker_callback` |
| `routes/web.php` | Add `user/media` routes |
