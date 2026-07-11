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

Run the PHP app separately with the `public` directory as the document root:

```bash
composer serve
```

Or:

```bash
php -S 127.0.0.1:8000 -t public public/index.php
```

## PHP Asset Tags

`WTD\View\AssetManager` reads the Vite manifest in production and the hot file in development.

```php
$assets->tags(['resources/js/app.js']);
```

For normal application code, use the `vite()` helper and pass the generated tags into a view:

```php
return Response::make($this->views->render('home', [
    'assetTags' => vite('resources/js/app.js'),
]));
```

Render trusted asset tags with the raw placeholder syntax:

```html
{!! assetTags !!}
```

Use escaped placeholders for normal user or model data:

```html
{{ name }}
```

The default Vite config supports Vue and React plugins. Applications can keep one, both, or replace them with another Vite-supported frontend stack.
