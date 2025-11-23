# Bitchest - Frontend

This folder contains the Vue 3 / Vite frontend for the BitChest admin and client dashboards.

## Quick start
1. Install:
   - npm install
2. Dev:
   - npm run dev
3. Build:
   - npm run build

## Environment
Create a .env file (or set env in your shell) with:
- VITE_API_URL=http://localhost:8000   (API base used by services)
Example:
VITE_API_URL=http://localhost:8000

The frontend expects backend API under `/api/v1/...`. Adjust VITE_API_URL if backend is hosted elsewhere.

## API usage notes (important)
- Profile upload endpoints:
  - Upload a profile picture:
    - PUT  {VITE_API_URL}/api/v1/profile/picture with FormData key `profile_picture`
    - `POST /api/v1/profile/picture/upload` also supported (legacy)
  - Delete profile picture:
    - DELETE {VITE_API_URL}/api/v1/profile/picture
  - Upload a profile banner:
    - PUT  {VITE_API_URL}/api/v1/profile/banner with FormData key `profile_banner`
    - `POST /api/v1/profile/banner/upload` also supported (legacy)
  - Responses include `{ data: { path, url, user } }`. Use `url` to show images.
- When backend returns a storage-relative path (e.g. profile_pictures/...), the frontend constructs public URLs using VITE_API_URL + `/storage/{path}` or uses the provided `url` field.

## Notes
- Ensure backend `php artisan storage:link` is run so uploaded images are publicly accessible at `{VITE_API_URL}/storage/...`.
- Use FormData in upload forms; examples in components/CryptoForm.vue and Profile-related components.

