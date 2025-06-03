<?php

namespace AssetTV\LaravelCascadedSoftDeletes\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \AssetTV\LaravelCascadedSoftDeletes\LaravelCascadedSoftDeletes
 */
class LaravelCascadedSoftDeletes extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \AssetTV\LaravelCascadedSoftDeletes\LaravelCascadedSoftDeletes::class;
    }
}
