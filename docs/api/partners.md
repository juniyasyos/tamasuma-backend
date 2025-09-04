# Partners API

Returns partners for the frontend. Falls back to two sample partners if the database has none (or when filtering hides them).

## Endpoint

- Method: `GET`
- Path: `/api/v1/partners`
- Auth: none
- Query params:
  - `visible` (boolean, default `true`) — if `true`, only returns partners with `is_visible = 1`.

## Response Shape

Each item has the following fields:

- `id` (string): Partner slug.
- `name` (string): Partner name.
- `des` (string): Description.
- `image` (string): Logo URL. Falls back to placeholder if missing.
- `visible` (boolean): Visibility flag.
- `active` (boolean): Mirrors `visible`.
- `socialLinks` (object):
  - `linkedin`, `web`, `github`, `twitter`, `facebook` — strings, default empty; `web` surfaces `website_url`.

## Example

```json
[
  {
    "id": "kampus_nusantara",
    "name": "Kampus Nusantara",
    "des": "Mitra pendidikan untuk pengembangan modul pelatihan pendidik.",
    "image": "https://placehold.co/300x120?text=Kampus+Nusantara",
    "visible": true,
    "active": true,
    "socialLinks": {
      "linkedin": "https://linkedin.com/school/kampus-nusantara",
      "web": "https://kampus-nusantara.example",
      "github": "",
      "twitter": "",
      "facebook": ""
    }
  }
]
```

## Implementation References

- Route: `routes/api.php` (GET `/api/v1/partners`)
- Controller: `app/Http/Controllers/Api/PartnersController.php`

