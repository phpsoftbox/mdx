<?php

declare(strict_types=1);

namespace PhpSoftBox\Mdx\Vite;

use PhpSoftBox\Mdx\MdxDiagnostic;
use PhpSoftBox\Mdx\MdxDiagnosticLevel;

final readonly class MdxBuildResult
{
    /**
     * @param list<MdxDiagnostic> $diagnostics
     */
    public function __construct(
        private bool $successful,
        private array $diagnostics = [],
    ) {
    }

    /**
     * @param list<MdxDiagnostic> $diagnostics
     */
    public static function success(array $diagnostics = []): self
    {
        return new self(true, $diagnostics);
    }

    public static function buildFailed(string $message, ?string $target = null): self
    {
        return new self(false, [
            new MdxDiagnostic(
                MdxDiagnosticLevel::Error,
                'mdx.build_failed',
                $message,
                context: ['target' => $target],
            ),
        ]);
    }

    public static function importFailed(string $import, string $message): self
    {
        return new self(false, [
            new MdxDiagnostic(
                MdxDiagnosticLevel::Error,
                'mdx.import_failed',
                $message,
                context: ['import' => $import],
            ),
        ]);
    }

    public function successful(): bool
    {
        return $this->successful;
    }

    /**
     * @return list<MdxDiagnostic>
     */
    public function diagnostics(): array
    {
        return $this->diagnostics;
    }
}
