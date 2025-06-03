<?php

namespace AssetTV\LaravelCascadedSoftDeletes\Traits;

use AssetTV\LaravelCascadedSoftDeletes\Exceptions\LogicException;
use AssetTV\LaravelCascadedSoftDeletes\Exceptions\RuntimeException;
use AssetTV\LaravelCascadedSoftDeletes\Jobs\CascadeSoftDeletes;
use Carbon\Carbon;

trait CascadedSoftDeletes
{
    public static Carbon $instanceDeletedAt;

    protected static function bootCascadedSoftDeletes(): void
    {
        static::deleted(static function ($model) {
            $model->deleteCascadedSoftDeletes();
        });

        if (static::instanceUsesSoftDelete()) {
            static::restoring(static function ($model) {
                static::$instanceDeletedAt = $model->{$model->getDeletedAtColumn()};
            });

            static::restored(static function ($model) {
                $model->restoreCascadedSoftDeleted();
            });
        }
    }

    protected function deleteCascadedSoftDeletes(): void
    {
        if ($this->isHardDeleting()) {
            return;
        }

        if (config('cascaded-soft-deletes.queue_cascades_by_default') === true) {
            CascadeSoftDeletes::dispatch($this, 'delete', null)->onQueue(config('cascaded-soft-deletes.queue_name'));
        } else {
            CascadeSoftDeletes::dispatchSync($this, 'delete', null);
        }
    }

    protected function restoreCascadedSoftDeleted(): void
    {
        if (config('cascaded-soft-deletes.queue_cascades_by_default') === true) {
            CascadeSoftDeletes::dispatch($this, 'restore', static::$instanceDeletedAt)->onQueue(config('cascaded-soft-deletes.queue_name'));
        } else {
            CascadeSoftDeletes::dispatchSync($this, 'restore', static::$instanceDeletedAt);
        }
    }

    public function cascadeSoftDeletes(string $action, ?Carbon $instanceDeletedAt = null): void
    {
        if (method_exists($this, 'getCascadedSoftDeletes')) {
            $relations = collect($this->getCascadedSoftDeletes());
        } elseif (property_exists($this, 'cascadedSoftDeletes')) {
            $relations = collect($this->cascadedSoftDeletes);
        } else {
            throw new RuntimeException('neither getCascadedSoftDeletes function or cascaded_soft_deletes property exists!');
        }

        $relations->each(function ($item, $key) use ($action, $instanceDeletedAt) {
            $relation = $key;
            if (is_numeric($key)) {
                $relation = $item;
            }

            if (! is_callable($item) && ! $this->relationUsesSoftDelete($relation)) {
                throw new LogicException('relationship '.$relation.' does not use SoftDeletes trait.');
            }

            if (is_callable($item)) {
                $query = $item();
            } else {
                $query = $this->{$relation}();
            }

            if ($action === 'delete') {
                $query->get()->each->delete();
            } else {
                $query
                    ->onlyTrashed()
                    ->where($this->getDeletedAtColumn(), '>=', $instanceDeletedAt->format('Y-m-d H:i:s.u'))
                    ->get()
                    ->each
                    ->restore();
            }
        });
    }

    protected function isHardDeleting(): bool
    {
        return ! self::instanceUsesSoftDelete() || $this->forceDeleting;
    }

    protected static function instanceUsesSoftDelete(): bool
    {
        static $softDelete;

        if (is_null($softDelete)) {
            $instance = new static;

            return $softDelete = method_exists($instance, 'bootSoftDeletes');
        }

        return $softDelete;
    }

    public static function relationUsesSoftDelete($relation): bool
    {
        static $softDeletes;

        if (is_null($softDeletes)) {
            $softDeletes = collect([]);
        }

        return $softDeletes->get($relation, function () use ($relation, $softDeletes) {
            $instance = new static;
            $cls = $instance->{$relation}()->getRelated();
            $relationInstance = new $cls;

            return $softDeletes->put($relation, method_exists($relationInstance, 'bootSoftDeletes'))->get($relation);
        });
    }
}
