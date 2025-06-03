<?php

namespace AssetTV\LaravelCascadedSoftDeletes\Tests\Models;

use AssetTV\LaravelCascadedSoftDeletes\Traits\CascadedSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MissingSoftDeletesBlock extends Model
{
    use CascadedSoftDeletes;

    protected $table = 'blocks';

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $fillable = ['page_id', 'name'];

    public $timestamps = false;

    public function page(): BelongsTo
    {
        return $this->belongsTo(MissingSoftDeletesPage::class, 'page_id', 'id');
    }

    public function plugins(): HasMany
    {
        return $this->hasMany(Plugin::class, 'block_id', 'id');
    }

    protected function getCascadedSoftDeletes(): array
    {
        return ['plugins'];
    }
}
