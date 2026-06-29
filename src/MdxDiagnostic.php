<?php

declare(strict_types=1);

namespace PhpSoftBox\Mdx;

final readonly class MdxDiagnostic
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        private MdxDiagnosticLevel $level,
        private string $code,
        private string $message,
        private ?int $line = null,
        private ?int $column = null,
        private array $context = [],
    ) {
    }

    public function level(): MdxDiagnosticLevel
    {
        return $this->level;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function line(): ?int
    {
        return $this->line;
    }

    public function column(): ?int
    {
        return $this->column;
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->context;
    }
}
