# Cascade Soft Delete for Laravel Models

[![Latest Version on Packagist](https://img.shields.io/packagist/v/assettv/laravel-cascaded-soft-deletes.svg?style=flat-square)](https://packagist.org/packages/assettv/laravel-cascaded-soft-deletes)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/assettv/laravel-cascaded-soft-deletes/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/assettv/laravel-cascaded-soft-deletes/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/assettv/laravel-cascaded-soft-deletes/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/assettv/laravel-cascaded-soft-deletes/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/assettv/laravel-cascaded-soft-deletes.svg?style=flat-square)](https://packagist.org/packages/assettv/laravel-cascaded-soft-deletes)

## Features

1. **Cascade Soft Delete for Relations**  
   Soft delete related records automatically when a parent is soft deleted.

2. **Cascade Restore for Relations**  
   Automatically restore related models if their `deleted_at` is later than or equal to the parent's restore date.

3. **Custom Query Support**  
   Use a custom query to control cascade actions.

4. **Configurable Queue Behavior**  
   All cascade actions are queued by default. This behavior can be customized by publishing and editing the package's config file.

---

**Note:**  
This package is based on [Laravel Cascaded Soft Deletes (original, no longer maintained)](https://github.com/razisayyed/laravel-cascaded-soft-deletes).

## Installation

You can install the package via composer:

```bash
composer require assettv/laravel-cascaded-soft-deletes
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-cascaded-soft-deletes-config"
```

## Usage

To set up CascadedSoftDeletes, you need to use the trait on the parent model and define `$cascadedSoftDeletes` property or `getCascadedSoftDeletes()` method.

### Simple example with `$cascadedSoftDeletes` property

```php
<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use AssetTV\LaravelCascadedSoftDeletes\Traits\CascadedSoftDeletes;

class Page extends Model {

    use SoftDeletes;
    use CascadedSoftDeletes;

    protected $cascadedSoftDeletes = [ 'blocks' ];

    public function blocks()
    {
        return $this->hasMany(Block::class);
    }

}
```

```php
<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Block extends Model {

    use SoftDeletes;

    public function page() 
    {
        return $this->belongsTo(Page::class);
    }

}
```

### Advanced example with `getCascadedSoftDeletes` and custom queries

You can also define a custom query to cascade soft deletes and restores through.

The following example describes a scenario where Folder is a model that uses `NodeTrait` from [laravel-nestedset](https://github.com/lazychaser/laravel-nestedset) class and each folder has many albums. getCascadedSoftDeletes() in the example will cascade soft deletes and restores to albums related to the folder and all its descendants.

```php
<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use AssetTV\LaravelCascadedSoftDeletes\Traits\CascadedSoftDeletes;

class Folder extends Model {

    use SoftDeletes;
    use NodeTrait;
    use CascadedSoftDeletes;

    public function albums()
    {
        return $this->hasMany(Album::class);
    }

    protected function getCascadedSoftDeletes()
    {
        return [
            'albums' => function() {
                return Album::whereHas('folder', function($q) {
                    $q->withTrashed()
                        ->where('_lft', '>=', $this->getLft())
                        ->where('_rgt', '<=', $this->getRgt());
                });  
            }
        ];
    }

}
```

### Requirements for the Parent & Child model classes

* Both classes must use SoftDeletes trait.
* Parent class must use CascadedSoftDeletes trait.
* Parent class must define `$cascadedSoftDeletes` or implement `getCascadedSoftDeletes` method which must return a list of cascaded HasMany relations and/or custom queries.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.
