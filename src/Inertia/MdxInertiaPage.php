<?php

declare(strict_types=1);

namespace PhpSoftBox\Mdx\Inertia;

use PhpSoftBox\Mdx\MdxDiagnostic;

final readonly class MdxInertiaPage
{
    /**
     * @param array<string, mixed> $props
     * @param list<MdxDiagnostic> $diagnostics
     */
    public function __construct(
        private string $component,
        private array $props,
        private array $diagnostics = [],
    ) {
    }

    public function component(): string
    {
        return $this->component;
    }

    /**
     * @return array<string, mixed>
     */
    public function props(): array
    {
        return $this->props;
    }

    /**
     * @return list<MdxDiagnostic>
     */
    public function diagnostics(): array
    {
        return $this->diagnostics;
    }

    /**
     * @return array{component:string,props:array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'component' => $this->component,
            'props'     => $this->props,
        ];
    }
}
