<?php

declare(strict_types=1);

namespace PhpSoftBox\Mdx;

use PhpSoftBox\Mdx\Contracts\MdxFrontMatterParserInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

use function is_array;
use function preg_match;

final class YamlMdxFrontMatterParser implements MdxFrontMatterParserInterface
{
    public function parse(string $source): MdxSource
    {
        if (preg_match('~\A---\R(.*?)\R---\R?(.*)\z~s', $source, $matches) !== 1) {
            return new MdxSource([], $source);
        }

        $diagnostics = [];
        $frontMatter = [];

        try {
            $parsed      = Yaml::parse($matches[1]);
            $frontMatter = is_array($parsed) ? $parsed : [];
        } catch (ParseException $exception) {
            $diagnostics[] = new MdxDiagnostic(
                MdxDiagnosticLevel::Error,
                'front_matter.invalid',
                $exception->getMessage(),
                $exception->getParsedLine(),
            );
        }

        return new MdxSource($frontMatter, $matches[2], $diagnostics);
    }
}
