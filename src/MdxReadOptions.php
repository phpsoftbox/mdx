<?php

declare(strict_types=1);

namespace PhpSoftBox\Mdx;

final readonly class MdxReadOptions
{
    /**
     * @param list<string> $defaultComponents
     */
    public function __construct(
        public ?string $contentRoot = null,
        public string $inertiaComponentPrefix = 'Mdx/',
        public ?string $inertiaComponent = null,
        public ?string $viteModule = null,
        public array $defaultComponents = [],
    ) {
    }
}
