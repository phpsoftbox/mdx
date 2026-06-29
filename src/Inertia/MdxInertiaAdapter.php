<?php

declare(strict_types=1);

namespace PhpSoftBox\Mdx\Inertia;

use PhpSoftBox\Mdx\MdxDocument;

use function array_replace_recursive;
use function is_array;

final readonly class MdxInertiaAdapter
{
    public function __construct(
        private string $propName = 'mdx',
    ) {
    }

    /**
     * @param array<string, mixed> $props
     */
    public function page(MdxDocument $document, array $props = [], ?string $component = null): MdxInertiaPage
    {
        $mdxProps = [
            'path'              => $document->path(),
            'module'            => $document->module(),
            'frontMatter'       => $document->frontMatter(),
            'defaultComponents' => $document->defaultComponents(),
        ];

        if (isset($props[$this->propName]) && is_array($props[$this->propName])) {
            $mdxProps = array_replace_recursive($mdxProps, $props[$this->propName]);
        }

        $props[$this->propName] = $mdxProps;

        return new MdxInertiaPage(
            $component ?? $document->inertiaComponent(),
            $props,
            $document->diagnostics(),
        );
    }
}
