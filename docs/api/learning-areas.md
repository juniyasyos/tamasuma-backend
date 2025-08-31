# API: Learning Areas & Programs

Base URL: `/api/v1`

## List Learning Areas

- Method: `GET /learning-areas`
- Query:
  - `per_page` (int, optional): default `15`.
  - `active` (bool, optional): default `true`. `false` to include non‑aktif.
  - `include` (string, optional): `programs` to sertakan daftar program.

Response: paginated collection of learning areas.

Contoh:

```
curl \
  -X GET "https://your-domain.test/api/v1/learning-areas?include=programs&per_page=10" \
  -H "Accept: application/json"
```

## Get One Learning Area

- Method: `GET /learning-areas/{id}`
- Menyertakan program yang dipublikasi.

Contoh:

```
curl \
  -X GET "https://your-domain.test/api/v1/learning-areas/1" \
  -H "Accept: application/json"
```

## List Programs by Learning Area

- Method: `GET /learning-areas/{id}/programs`
- Query:
  - `per_page` (int, optional): default `15`.
  - `certified` (bool, optional): hanya program bersertifikat bila `true`.

Contoh:

```
curl \
  -X GET "https://your-domain.test/api/v1/learning-areas/1/programs?per_page=20&certified=true" \
  -H "Accept: application/json"
```

## Catatan

- Hanya learning area aktif dan program berstatus publik yang ditampilkan secara default.
- Gunakan header `Accept: application/json` untuk hasil JSON.

