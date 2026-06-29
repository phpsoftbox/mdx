<?php

declare(strict_types=1);

namespace PhpSoftBox\Mdx;

final readonly class MdxSource
{
    /**
     * @param array<string, mixed> $frontMatter
     * @param list<MdxDiagnostic> $diagnostics
     */
    public function __construct(
        private array $frontMatter,
        private string $body,
        private array $diagnostics = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function frontMatter(): array
    {
        return $this->frontMatter;
    }

    public function body(): string
    {
        return $this->body;
    }

    /**
     * @return list<MdxDiagnostic>
     */
    public function diagnostics(): array
    {
        return $this->diagnostics;
    }
}
