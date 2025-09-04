# Modules API

Endpoint returning frontend-ready modules derived from published Programs. Falls back to dummy items when none exist.

## Endpoint

- Method: `GET`
- Path: `/api/v1/modules`
- Auth: none
- Status codes: `200 OK`

## Behavior

- When there are published `Program` records, each is mapped into a module object.
- If there are no published programs, the response is a static array of two dummy modules (identical to the provided spec).

## Response Shape

Each item has the following fields:

- `id` (string): Program `slug`, or `program-{id}` fallback.
- `type` (string): Always `custom`.
- `name` (string): Program title.
- `visible` (boolean): Mirrors `is_published`.
- `date` (string | null): `starts_at` in `YYYY-MM-DD`.
- `endDate` (string | null): `ends_at` in `YYYY-MM-DD`.
- `time` (object): `{ starttime: "09:00", endtime: "12:00" }` default.
- `active` (boolean): True if current date is within the start/end window.
- `venue` (object): `{ googlemapsurl: "", name: "Online" | "Onsite" }` (`Online` for `source=external`).
- `links` (object): Registration URL, social links (defaults given below).
- `partners` (string[]): Defaults to empty.
- `team` (string[]): Defaults to empty.
- `speakers` (string[]): Defaults to empty.
- `image` (string): Placeholder URL derived from title.
- `thumbnail` (string): Placeholder URL derived from learning area name.
- `hashtags` (string[]): Defaults to `["Tamasuma"]`.
- `category` (string[]): `[learningArea.name]` if present, else empty.
- `difficulty` (string): Maps `level` to `Beginner` | `Intermediate` | `Advanced`.
- `language` (string): `id`.
- `durationHours` (number): Default `10`.
- `authors` (string[]): Default `["Tamasuma Academy"]`.
- `prerequisites` (string[]): Default empty.
- `outcomes` (string[]): Default empty.
- `resources` (array): Default empty.
- `des` (string): HTML paragraph from program `description` or `<p>-</p>`.
- `agenda` (array): Default empty.

## Example

Success (200):

```json
[
  {
    "id": "your-program-slug",
    "type": "custom",
    "name": "Judul Program",
    "visible": true,
    "date": "2025-01-15",
    "endDate": "2025-02-15",
    "time": { "starttime": "09:00", "endtime": "12:00" },
    "active": true,
    "venue": { "googlemapsurl": "", "name": "Online" },
    "links": {
      "registration": "https://your-app.test/programs/your-program-slug",
      "youtube": "https://youtube.com/@tamasuma",
      "facebook": "",
      "meetup": "",
      "callforspeaker": "",
      "feedback": ""
    },
    "partners": [],
    "team": [],
    "speakers": [],
    "image": "https://placehold.co/800x400?text=Judul+Program",
    "thumbnail": "https://placehold.co/600x400?text=Nama+Bidang",
    "hashtags": ["Tamasuma"],
    "category": ["Nama Bidang"],
    "difficulty": "Beginner",
    "language": "id",
    "durationHours": 10,
    "authors": ["Tamasuma Academy"],
    "prerequisites": [],
    "outcomes": [],
    "resources": [],
    "des": "<p>-</p>",
    "agenda": []
  }
]
```

If there are zero published programs, the response is a predefined array containing two sample modules: "Dasar AI untuk Pendidik" and "Kurikulum Berbasis Proyek".

## Usage

- cURL:

```bash
curl -sS http://your-app.test/api/v1/modules | jq
```

- Fetch (browser/Node):

```js
const res = await fetch('/api/v1/modules');
const modules = await res.json();
```

- Axios:

```js
const { data: modules } = await axios.get('/api/v1/modules');
```

## Notes

- Registration link currently uses `url('/programs/{slug}')`. If your frontend has a dedicated registration route (e.g., `https://tamasuma.local/registrasi/{slug}`), adjust in `ModulesController@index`.
- You can populate `speakers` from the `teachers()` relation if desired; decide on the identifier format (names vs IDs) before surfacing it.

## Implementation References

- Route: `routes/api.php:22`
- Controller: `app/Http/Controllers/Api/ModulesController.php:16`

