<?php

namespace AssetTV\LaravelCascadedSoftDeletes\Tests;

use AssetTV\LaravelCascadedSoftDeletes\Exceptions\LogicException;
use AssetTV\LaravelCascadedSoftDeletes\Exceptions\RuntimeException;
use AssetTV\LaravelCascadedSoftDeletes\Tests\Models\Block;
use AssetTV\LaravelCascadedSoftDeletes\Tests\Models\MissingMethodAndPropertyPage;
use AssetTV\LaravelCascadedSoftDeletes\Tests\Models\MissingSoftDeletesPage;
use AssetTV\LaravelCascadedSoftDeletes\Tests\Models\Page;
use AssetTV\LaravelCascadedSoftDeletes\Tests\Models\PageCallbackCascade;
use AssetTV\LaravelCascadedSoftDeletes\Tests\Models\PageMethod;
use AssetTV\LaravelCascadedSoftDeletes\Tests\Models\Plugin;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\DataProvider;

class CascadeSoftDeletesTest extends TestCase
{
    public static function dispatchProvider(): array
    {
        return [
            'async' => [true],
            'sync' => [false],
        ];
    }

    public static function pageModelProvider(): array
    {
        return [
            'page with model' => [PageMethod::class],
            'page with property' => [Page::class],
        ];
    }

    public static function combinedProvider(): array
    {
        return [
            'page with model sync' => [PageMethod::class, true],
            'page with model async' => [PageMethod::class, false],
            'page with property sync' => [Page::class, true],
            'page with property async' => [Page::class, false],
        ];
    }

    #[DataProvider('pageModelProvider')]
    public function test_delete($pageClass): void
    {
        $page = (new $pageClass)->whereName('page 1')->first();

        $originalBlocksCount = $this->pageBlocksCount($pageClass, 'page 1');

        $page->delete();

        self::assertEquals(0, $this->pageBlocksCount($pageClass, 'page 1'));
        self::assertEquals($this->pageBlocksCount($pageClass, 'page 1', true), $originalBlocksCount);
    }

    public function pageBlocksCount($pageClass, $pageName, $trashed = false): int
    {
        if ($trashed) {
            return (new $pageClass)->withTrashed()->whereName($pageName)->first()->blocks()->onlyTrashed()->count();
        }

        return (new $pageClass)->withTrashed()->whereName($pageName)->first()->blocks()->count();
    }

    #[DataProvider('pageModelProvider')]
    public function test_two_level_delete($pageClass): void
    {
        $page = (new $pageClass)->whereName('page 1')->first();

        $originalPluginsCount = $this->blockPluginsCount('block 1 - page 1');

        $page->delete();

        self::assertEquals(0, $this->blockPluginsCount('block 1 - page 1'));
        self::assertEquals($this->blockPluginsCount('block 1 - page 1', true), $originalPluginsCount);
    }

    public function blockPluginsCount($blockName, $trashed = false): int
    {
        if ($trashed) {
            return $this->findBlock($blockName, true)->plugins()->onlyTrashed()->count();
        }

        return $this->findBlock($blockName, true)->plugins()->count();
    }

    public function findBlock($name, $withTrashed = false): ?Block
    {
        return $withTrashed ?
            Block::withTrashed()->whereName($name)->first() :
            Block::whereName($name)->first();
    }

    #[DataProvider('pageModelProvider')]
    public function test_force_delete($pageClass): void
    {
        (new $pageClass)->whereName('page 1')->first()->forceDelete();

        self::assertNull(
            (new $pageClass)->whereName('page 1')->withTrashed()->first()
        );
    }

    #[DataProvider('combinedProvider')]
    public function test_restore($pageClass, $sync): void
    {
        config()->set('cascaded-soft-deletes.queue_cascades_by_default', ! $sync);

        $page = (new $pageClass)->whereName('page 1')->first();

        $block = Block::whereName('block 2 - page 1')->first();
        $block->delete();

        $originalBlocksCount = $this->pageBlocksCount($pageClass, 'page 1');
        self::assertEquals(1, $originalBlocksCount, 'wrong blocks count after deleting one block!');

        Plugin::whereName('plugin 1 - block 1 - page 1')->first()->delete();
        $originalPluginsCount = $this->blockPluginsCount('block 1 - page 1');
        self::assertEquals(2, $originalPluginsCount, 'wrong plugins count after deleting one plugin!');

        $page->delete();

        self::assertEquals(0, $this->pageBlocksCount($pageClass, 'page 1'), 'undeleted block(s) found after deleting page!');

        $page->restore();

        self::assertEquals(1, $this->pageBlocksCount($pageClass, 'page 1'), 'wrong blocks count after restoring page!');
        self::assertEquals(2, $this->blockPluginsCount('block 1 - page 1'), 'wrong plugins count after restoring page!');
    }

    public function test_missing_method_and_property(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('neither getCascadedSoftDeletes function or cascaded_soft_deletes property exists!');

        MissingMethodAndPropertyPage::whereName('page 1')->first()->delete();
    }

    public function test_missing_soft_deletes(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('relationship blocks does not use SoftDeletes trait.');

        MissingSoftDeletesPage::whereName('page 1')->first()->delete();
    }

    public function test_callback_cascade_delete(): void
    {
        $page = PageCallbackCascade::whereName('page 1')->first();

        $page->delete();

        self::assertEquals(2, $this->pageBlocksCount(PageCallbackCascade::class, 'page 1'));
        self::assertEquals(3, $this->blockPluginsCount('block 1 - page 1'));

        self::assertEquals(0, $this->pageBlocksCount(PageCallbackCascade::class, 'page 2'));
        self::assertEquals(0, $this->blockPluginsCount('block 1 - page 2'));
    }

    #[DataProvider('dispatchProvider')]
    public function test_callback_cascade_restore($sync): void
    {
        config()->set('cascaded-soft-deletes.queue_cascades_by_default', ! $sync);

        $page = PageCallbackCascade::whereName('page 1')->first();

        $page->delete();

        $page->restore();

        self::assertEquals(2, $this->pageBlocksCount(PageCallbackCascade::class, 'page 2'));
        self::assertEquals(1, $this->blockPluginsCount('block 1 - page 2'));
    }

    public function findPage($name, $withTrashed = false): ?Model
    {
        return $withTrashed ?
            Page::withTrashed()->whereName($name)->first() :
            Page::whereName($name)->first();
    }

    public function findPlugin($name, $withTrashed = false): ?Plugin
    {
        return $withTrashed ?
            Plugin::withTrashed()->whereName($name)->first() :
            Plugin::whereName($name)->first();
    }
}
