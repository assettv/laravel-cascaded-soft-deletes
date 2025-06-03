<?php

namespace AssetTV\LaravelCascadedSoftDeletes\Tests;

use AssetTV\LaravelCascadedSoftDeletes\LaravelCascadedSoftDeletesServiceProvider;
use AssetTV\LaravelCascadedSoftDeletes\Tests\Models\Block;
use AssetTV\LaravelCascadedSoftDeletes\Tests\Models\Page;
use AssetTV\LaravelCascadedSoftDeletes\Tests\Models\Plugin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('pages', static function ($table) {
            $table->id();
            $table->string('name');
            $table->softDeletes('deleted_at', 6);
        });

        Schema::create('blocks', static function ($table) {
            $table->id();
            $table->string('name');
            $table->foreignId('page_id')->constrained()->onDelete('cascade');
            $table->softDeletes('deleted_at', 6);
        });

        Schema::create('plugins', static function ($table) {
            $table->id();
            $table->string('name');
            $table->foreignId('block_id')->constrained()->onDelete('cascade');
            $table->softDeletes('deleted_at', 6);
        });

        Page::insert([
            ['name' => 'page 1'],
            ['name' => 'page 2'],
            ['name' => 'page 3'],
        ]);

        Block::insert([
            ['name' => 'block 1 - page 1', 'page_id' => 1],
            ['name' => 'block 2 - page 1', 'page_id' => 1],
            ['name' => 'block 1 - page 2', 'page_id' => 2],
            ['name' => 'block 2 - page 2', 'page_id' => 2],
        ]);

        Plugin::insert([
            ['id' => 1, 'name' => 'plugin 1 - block 1 - page 1', 'block_id' => 1],
            ['id' => 2, 'name' => 'plugin 2 - block 1 - page 1', 'block_id' => 1],
            ['id' => 3, 'name' => 'plugin 3 - block 1 - page 1', 'block_id' => 1],
            ['id' => 4, 'name' => 'plugin 1 - block 1 - page 2', 'block_id' => 3],
        ]);
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testbench');
        config()->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelCascadedSoftDeletesServiceProvider::class,
        ];
    }
}
