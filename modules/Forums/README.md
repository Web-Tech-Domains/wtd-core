# Forums Module

Open-source discussion module for WTD Core applications.

## Routes

- `GET /forums` renders the forum workspace.

## Frontend

The module uses Vue through Vite:

```bash
npm install
npm run dev
npm run build
```

The page entry is `resources/js/modules/forums.js`.

Production domains should point to `public/`. The module ships a public CSS fallback at
`public/assets/modules/forums.css`, while Vue is loaded from Vite's `/build/...`
manifest output after `npm run build`.
