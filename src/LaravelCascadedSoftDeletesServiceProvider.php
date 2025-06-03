<?php

namespace AssetTV\LaravelCascadedSoftDeletes;

use AssetTV\LaravelCascadedSoftDeletes\Commands\LaravelCascadedSoftDeletesCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelCascadedSoftDeletesServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-cascaded-soft-deletes')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_cascaded_soft_deletes_table')
            ->hasCommand(LaravelCascadedSoftDeletesCommand::class);
    }
}
