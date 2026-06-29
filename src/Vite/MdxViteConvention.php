<?php

declare(strict_types=1);

namespace PhpSoftBox\Mdx\Vite;

use PhpSoftBox\Mdx\MdxDocument;

final readonly class MdxViteConvention
{
    public function __construct(
        private string $glob = 'resources/**/*.mdx',
        private string $defaultComponentsModule = '@/Components/Mdx',
        private string $defaultComponentsExport = 'mdxComponents',
    ) {
    }

    public function glob(): string
    {
        return $this->glob;
    }

    public function defaultComponentsModule(): string
    {
        return $this->defaultComponentsModule;
    }

    public function defaultComponentsExport(): string
    {
        return $this->defaultComponentsExport;
    }

    /**
     * @return array<string, mixed>
     */
    public function props(MdxDocument $document): array
    {
        return [
            'module'                  => $document->module(),
            'defaultComponents'       => $document->defaultComponents(),
            'defaultComponentsModule' => $this->defaultComponentsModule,
            'defaultComponentsExport' => $this->defaultComponentsExport,
        ];
    }
}
