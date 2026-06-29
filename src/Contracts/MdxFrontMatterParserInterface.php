<?php

declare(strict_types=1);

namespace PhpSoftBox\Mdx\Contracts;

use PhpSoftBox\Mdx\MdxSource;

interface MdxFrontMatterParserInterface
{
    public function parse(string $source): MdxSource;
}
