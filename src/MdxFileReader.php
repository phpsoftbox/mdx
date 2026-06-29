<?php

declare(strict_types=1);

namespace PhpSoftBox\Mdx;

use PhpSoftBox\Mdx\Contracts\MdxFrontMatterParserInterface;

use function array_filter;
use function array_map;
use function array_values;
use function explode;
use function file_get_contents;
use function implode;
use function is_file;
use function preg_replace;
use function rtrim;
use function sprintf;
use function str_ends_with;
use function str_replace;
use function str_starts_with;
use function strlen;
use function strtolower;
use function substr;
use function trim;
use function ucwords;

final readonly class MdxFileReader
{
    public function __construct(
        private MdxFrontMatterParserInterface $frontMatterParser = new YamlMdxFrontMatterParser(),
    ) {
    }

    public function read(string $path, ?MdxReadOptions $options = null): MdxDocument
    {
        $options ??= new MdxReadOptions();

        $relativePath      = $this->relativePath($path, $options->contentRoot);
        $module            = $options->viteModule ?? $relativePath;
        $inertiaComponent  = $options->inertiaComponent ?? $this->componentName($relativePath, $options->inertiaComponentPrefix);
        $defaultComponents = $options->defaultComponents;

        if (!is_file($path)) {
            return new MdxDocument(
                $path,
                $module,
                $inertiaComponent,
                [],
                '',
                '',
                $defaultComponents,
                [
                    new MdxDiagnostic(
                        MdxDiagnosticLevel::Error,
                        'mdx.file_missing',
                        sprintf('MDX file not found: %s.', $path),
                        context: ['path' => $path],
                    ),
                ],
            );
        }

        $source = file_get_contents($path);
        if ($source === false) {
            return new MdxDocument(
                $path,
                $module,
                $inertiaComponent,
                [],
                '',
                '',
                $defaultComponents,
                [
                    new MdxDiagnostic(
                        MdxDiagnosticLevel::Error,
                        'mdx.file_unreadable',
                        sprintf('MDX file could not be read: %s.', $path),
                        context: ['path' => $path],
                    ),
                ],
            );
        }

        $parsed = $this->frontMatterParser->parse($source);

        return new MdxDocument(
            $path,
            $module,
            $inertiaComponent,
            $parsed->frontMatter(),
            $parsed->body(),
            $source,
            $defaultComponents,
            $parsed->diagnostics(),
        );
    }

    private function relativePath(string $path, ?string $root): string
    {
        $normalized = str_replace('\\', '/', $path);
        if ($root === null || trim($root) === '') {
            return trim($normalized, '/');
        }

        $normalizedRoot = rtrim(str_replace('\\', '/', $root), '/') . '/';
        if (str_starts_with($normalized, $normalizedRoot)) {
            return trim(substr($normalized, strlen($normalizedRoot)), '/');
        }

        return trim($normalized, '/');
    }

    private function componentName(string $relativePath, string $prefix): string
    {
        $withoutExtension = str_ends_with(strtolower($relativePath), '.mdx')
            ? substr($relativePath, 0, -4)
            : $relativePath;

        $segments = array_values(array_filter(explode('/', $withoutExtension), static fn (string $segment): bool => trim($segment) !== ''));
        $segments = array_map(fn (string $segment): string => $this->studly($segment), $segments);

        $name             = implode('/', $segments);
        $normalizedPrefix = trim($prefix, '/');

        return $normalizedPrefix !== '' ? $normalizedPrefix . '/' . $name : $name;
    }

    private function studly(string $value): string
    {
        $words = (string) preg_replace('~[^A-Za-z0-9]+~', ' ', $value);

        return str_replace(' ', '', ucwords(strtolower(trim($words))));
    }
}
