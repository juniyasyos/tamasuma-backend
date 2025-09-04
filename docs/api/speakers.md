# Speakers API

Returns speakers for the frontend. Maps from users with role "Pengajar" when available, otherwise falls back to sample speakers.

## Endpoint

- Method: `GET`
- Path: `/api/v1/speakers`
- Auth: none

## Response Shape

Each item has the following fields:

- `id` (string): Slug of the user name (fallback `user-{id}`).
- `visible` (boolean): Always `true` for mapped records.
- `image` (string): Avatar URL or placeholder.
- `name` (string): User name.
- `designation` (string): Default `Pengajar`.
- `email` (string): User email.
- `company` (object): `{ name: 'Tamasuma', url: 'https://tamasuma.local' }` default.
- `city` (string): Default empty.
- `country` (string): Default `Indonesia`.
- `bio` (string): Default empty.
- `socialLinks` (object): `{ twitter, linkedin, github, web, facebook, medium }` — default empty strings.

## Example

```json
[
  {
    "id": "dea_putri",
    "visible": true,
    "image": "https://placehold.co/200x200?text=Dea",
    "name": "Dea Putri",
    "designation": "Instruktur AI",
    "email": "dea@tamasuma.local",
    "company": { "name": "Tamasuma Academy", "url": "https://tamasuma.local" },
    "city": "Bandung",
    "country": "Indonesia",
    "bio": "Instruktur AI yang berfokus pada penerapan AI dalam proses belajar-mengajar untuk pendidik.",
    "socialLinks": {
      "twitter": "https://twitter.com/tamasuma",
      "linkedin": "https://linkedin.com/company/tamasuma",
      "github": "",
      "web": "https://tamasuma.local/mentor/dea",
      "facebook": "",
      "medium": ""
    }
  }
]
```

## Implementation References

- Route: `routes/api.php` (GET `/api/v1/speakers`)
- Controller: `app/Http/Controllers/Api/SpeakersController.php`

