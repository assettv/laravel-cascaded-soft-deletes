<?php

namespace AssetTV\LaravelCascadedSoftDeletes\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use AssetTV\LaravelCascadedSoftDeletes\Traits\CascadedSoftDeletes;

class Block extends Model
{
    use CascadedSoftDeletes;
    use SoftDeletes;

    protected $table = 'blocks';

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $fillable = ['page_id', 'name'];

    public $timestamps = false;

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function plugins(): HasMany
    {
        return $this->hasMany(Plugin::class);
    }

    protected function getCascadedSoftDeletes(): array
    {
        return ['plugins'];
    }
}
