<?php

declare(strict_types=1);

namespace PhpSoftBox\Mdx;

final readonly class MdxDocument
{
    /**
     * @param array<string, mixed> $frontMatter
     * @param list<string> $defaultComponents
     * @param list<MdxDiagnostic> $diagnostics
     */
    public function __construct(
        private string $path,
        private string $module,
        private string $inertiaComponent,
        private array $frontMatter,
        private string $body,
        private string $source,
        private array $defaultComponents = [],
        private array $diagnostics = [],
    ) {
    }

    public function path(): string
    {
        return $this->path;
    }

    public function module(): string
    {
        return $this->module;
    }

    public function inertiaComponent(): string
    {
        return $this->inertiaComponent;
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

    public function source(): string
    {
        return $this->source;
    }

    /**
     * @return list<string>
     */
    public function defaultComponents(): array
    {
        return $this->defaultComponents;
    }

    /**
     * @return list<MdxDiagnostic>
     */
    public function diagnostics(): array
    {
        return $this->diagnostics;
    }

    public function hasErrors(): bool
    {
        foreach ($this->diagnostics as $diagnostic) {
            if ($diagnostic->level() === MdxDiagnosticLevel::Error) {
                return true;
            }
        }

        return false;
    }
}
