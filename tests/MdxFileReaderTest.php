<?php

declare(strict_types=1);

namespace PhpSoftBox\Mdx\Tests;

use PhpSoftBox\Mdx\Inertia\MdxInertiaAdapter;
use PhpSoftBox\Mdx\MdxDiagnostic;
use PhpSoftBox\Mdx\MdxDocument;
use PhpSoftBox\Mdx\MdxFileReader;
use PhpSoftBox\Mdx\MdxReadOptions;
use PhpSoftBox\Mdx\Vite\MdxBuildResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function array_map;
use function file_put_contents;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

#[CoversClass(MdxFileReader::class)]
#[CoversClass(MdxDocument::class)]
#[CoversClass(MdxInertiaAdapter::class)]
#[CoversClass(MdxBuildResult::class)]
#[CoversMethod(MdxFileReader::class, 'read')]
#[CoversMethod(MdxDocument::class, 'hasErrors')]
#[CoversMethod(MdxInertiaAdapter::class, 'page')]
#[CoversMethod(MdxBuildResult::class, 'buildFailed')]
#[CoversMethod(MdxBuildResult::class, 'importFailed')]
final class MdxFileReaderTest extends TestCase
{
    /**
     * Проверяет, что reader извлекает front matter, сохраняет MDX body и строит module/Inertia metadata.
     *
     * @see MdxFileReader::read()
     * @see MdxDocument::frontMatter()
     * @see MdxDocument::body()
     * @see MdxDocument::module()
     * @see MdxDocument::inertiaComponent()
     */
    #[Test]
    public function testReadsFrontMatterAndKeepsMdxBody(): void
    {
        $root = $this->createTempDir();
        $file = $root . '/home.mdx';
        file_put_contents($file, <<<'MDX'
---
title: Home
layout: home
---

import { Hero } from '@/Components/Mdx/Hero'

<Hero title="Docs" />
MDX);

        $document = new MdxFileReader()->read($file, new MdxReadOptions(
            contentRoot: $root,
            defaultComponents: ['a', 'img'],
        ));

        $this->assertSame(['title' => 'Home', 'layout' => 'home'], $document->frontMatter());
        $this->assertStringContainsString('import { Hero }', $document->body());
        $this->assertSame('home.mdx', $document->module());
        $this->assertSame('Mdx/Home', $document->inertiaComponent());
        $this->assertSame(['a', 'img'], $document->defaultComponents());
        $this->assertFalse($document->hasErrors());

        unlink($file);
        rmdir($root);
    }

    /**
     * Проверяет, что отсутствующий MDX-файл возвращает diagnostic и документ с ошибкой.
     *
     * @see MdxFileReader::read()
     * @see MdxDocument::hasErrors()
     * @see MdxDocument::diagnostics()
     */
    #[Test]
    public function testReturnsDiagnosticForMissingFile(): void
    {
        $document = new MdxFileReader()->read('/missing/page.mdx');

        $this->assertTrue($document->hasErrors());
        $this->assertContains('mdx.file_missing', $this->diagnosticCodes($document));
    }

    /**
     * Проверяет, что невалидный YAML front matter не ломает чтение и попадает в diagnostics.
     *
     * @see MdxFileReader::read()
     * @see MdxDocument::diagnostics()
     */
    #[Test]
    public function testReturnsDiagnosticForInvalidFrontMatter(): void
    {
        $root = $this->createTempDir();
        $file = $root . '/broken.mdx';
        file_put_contents($file, <<<'MDX'
---
title: [broken
---

<Broken />
MDX);

        $document = new MdxFileReader()->read($file, new MdxReadOptions(contentRoot: $root));

        $this->assertContains('front_matter.invalid', $this->diagnosticCodes($document));

        unlink($file);
        rmdir($root);
    }

    /**
     * Проверяет, что Inertia adapter собирает component name и MDX props для app-layer render.
     *
     * @see MdxInertiaAdapter::page()
     */
    #[Test]
    public function testBuildsInertiaPagePayload(): void
    {
        $document = new MdxDocument(
            path: '/content/home.mdx',
            module: 'home.mdx',
            inertiaComponent: 'Mdx/Home',
            frontMatter: ['title' => 'Home'],
            body: '<Hero />',
            source: '<Hero />',
            defaultComponents: ['a'],
        );

        $page = new MdxInertiaAdapter()->page($document, ['nav' => ['home']]);

        $this->assertSame('Mdx/Home', $page->component());
        $this->assertSame('home.mdx', $page->props()['mdx']['module']);
        $this->assertSame(['title' => 'Home'], $page->props()['mdx']['frontMatter']);
        $this->assertSame(['home'], $page->props()['nav']);
    }

    /**
     * Проверяет, что build/import failures оформляются как MDX diagnostics.
     *
     * @see MdxBuildResult::buildFailed()
     * @see MdxBuildResult::importFailed()
     */
    #[Test]
    public function testBuildAndImportDiagnostics(): void
    {
        $build  = MdxBuildResult::buildFailed('Vite build failed.', 'resources/content/home.mdx');
        $import = MdxBuildResult::importFailed('@edoc/plugin-pricing', 'Plugin component import failed.');

        $this->assertFalse($build->successful());
        $this->assertFalse($import->successful());
        $this->assertSame('mdx.build_failed', $build->diagnostics()[0]->code());
        $this->assertSame('mdx.import_failed', $import->diagnostics()[0]->code());
    }

    private function createTempDir(): string
    {
        $path = sys_get_temp_dir() . '/psb-mdx-' . uniqid('', true);
        mkdir($path);

        return $path;
    }

    /**
     * @return list<string>
     */
    private function diagnosticCodes(MdxDocument $document): array
    {
        return array_map(static fn (MdxDiagnostic $diagnostic): string => $diagnostic->code(), $document->diagnostics());
    }
}
