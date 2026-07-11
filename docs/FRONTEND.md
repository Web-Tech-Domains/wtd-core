# Frontend Assets

WTD Core includes a market-standard Vite asset pipeline that can be used with plain JavaScript, Vue, or React.

## Files

- `package.json`
- `vite.config.js`
- `resources/js/app.js`
- `resources/js/vue.js`
- `resources/js/react.jsx`
- `resources/css/app.css`
- `config/assets.php`

## Commands

```bash
npm install
npm run dev
npm run build
```

The framework itself does not require Node to run. Vite is only needed when developing or building frontend assets.

## PHP Asset Tags

`WTD\View\AssetManager` reads the Vite manifest in production and the hot file in development.

```php
$assets->tags(['resources/js/app.js']);
```

The default Vite config supports Vue and React plugins. Applications can keep one, both, or replace them with another Vite-supported frontend stack.

