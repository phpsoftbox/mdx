# PhpSoftBox MDX

Пакет для `.mdx`-документов в PhpSoftBox.

`phpsoftbox/mdx` не компилирует MDX в PHP и не выполняет JSX. Он отвечает за
серверную часть пайплайна: чтение `.mdx` файла, извлечение YAML front matter,
описание Vite/React conventions, подготовку props для Inertia page rendering и
diagnostics для ошибок файла, front matter, build/import.

## Разделение Ответственности

- `.md` обрабатывается через `phpsoftbox/markdown`;
- `.mdx` обрабатывается через `phpsoftbox/mdx` и frontend pipeline;
- routing, nav, sorting и metadata могут читать одинаковый front matter из обоих
  типов файлов;
- React-компоненты, imports, plugin-компоненты и интерактивные блоки живут в MDX.

## Security Model

- `.md` - это контент;
- `.mdx` - это код;
- `.mdx` редактируется через git;
- `.mdx` не должен исполняться из пользовательского ввода;
- безопасность `.mdx` равна безопасности frontend-кода проекта.

## Чтение MDX

```php
use PhpSoftBox\Mdx\MdxFileReader;
use PhpSoftBox\Mdx\MdxReadOptions;

$document = new MdxFileReader()->read(
    __DIR__ . '/resources/content/home.mdx',
    new MdxReadOptions(
        contentRoot: __DIR__ . '/resources/content',
        defaultComponents: ['a', 'img', 'pre', 'code'],
    ),
);

$document->frontMatter();       // ['title' => 'Главная', ...]
$document->body();              // MDX body without YAML front matter
$document->module();            // home.mdx
$document->inertiaComponent();  // Mdx/Home
$document->diagnostics();
```

Пример целевого `.mdx`:

```mdx
---
title: Главная
layout: home
nav_label: Главная
---

import { Hero } from '@/Components/Mdx/Hero'
import { Grid } from '@/Components/Mdx/Grid'
import { PricingTable } from '@edoc/plugin-pricing'

<Hero title="E-Doc" />

<Grid columns={3}>
  <Feature title="Markdown" />
  <Feature title="Без БД" />
</Grid>

<PricingTable />
```

Пакет не анализирует и не исполняет imports. Они остаются частью MDX body и
компилируются Vite/React pipeline.

## Vite / React Conventions

`MdxViteConvention` хранит договоренности, которые frontend entrypoint может
использовать для `import.meta.glob` и default MDX components:

```php
use PhpSoftBox\Mdx\Vite\MdxViteConvention;

$vite = new MdxViteConvention(
    glob: 'resources/content/**/*.mdx',
    defaultComponentsModule: '@/Components/Mdx',
    defaultComponentsExport: 'mdxComponents',
);

$vite->props($document);
```

На frontend стороне это обычно соответствует MDX setup через `@mdx-js/react`,
`@mdx-js/rollup` или другой Vite-compatible MDX plugin.

## Inertia Adapter

`MdxInertiaAdapter` готовит payload, который приложение может передать в свой
Inertia renderer:

```php
use PhpSoftBox\Mdx\Inertia\MdxInertiaAdapter;

$page = new MdxInertiaAdapter()->page($document, [
    'nav' => $nav,
]);

$page->component(); // Mdx/Home
$page->props();     // ['nav' => ..., 'mdx' => ['module' => 'home.mdx', ...]]
```

Адаптер намеренно не зависит от `phpsoftbox/inertia`: он возвращает компонент и
props, а app-layer сам вызывает свой `Inertia::render()`.

## Diagnostics

Минимальные коды:

- `mdx.file_missing`;
- `mdx.file_unreadable`;
- `front_matter.invalid`;
- `mdx.build_failed`;
- `mdx.import_failed`.

Build/import diagnostics предназначены для адаптера вокруг Vite/React build
pipeline:

```php
use PhpSoftBox\Mdx\Vite\MdxBuildResult;

$result = MdxBuildResult::importFailed(
    '@edoc/plugin-pricing',
    'Plugin component import failed.',
);
```

## Не Делает

- не компилирует MDX в PHP;
- не выполняет JSX;
- не парсит props;
- не содержит app-specific блоки `Hero`, `Grid`, `PricingTable`;
- не принимает MDX из пользовательского ввода.

## Лицензия

MIT
