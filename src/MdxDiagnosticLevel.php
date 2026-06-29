<?php

declare(strict_types=1);

namespace PhpSoftBox\Mdx;

enum MdxDiagnosticLevel: string
{
    case Info    = 'info';
    case Warning = 'warning';
    case Error   = 'error';
}
