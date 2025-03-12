## About Alfred

Alfred boosts your efficiency in administration panels with hotkeys, keywords, text expansion and more.

---

## Updating Alfred

Run in terminal: `yarn build-library`

---

## Add Vue plugin

### Composer

To keep the assets up-to-date and avoid issues in future updates, please add the following to your project's composer file:
```json
{
    "scripts": {
        "post-autoload-dump": [
            "@php artisan vendor:publish --tag=alfred-assets --ansi --force"
        ]
    }
}
```

### JS

```js
import Alfred from './vendor/alfred/alfred.common';

Vue.use(Alfred);
```

### SCSS

```scss
@import './vendor/alfred/alfred.css';
```

### HTML

```html
<alfred></alfred>
```

With settings:
```html
<alfred :settings='@json(Startselect\Alfred\Support\AlfredSettingManager::init())'></alfred>
```
