<?php

namespace AssetTV\LaravelCascadedSoftDeletes\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use AssetTV\LaravelCascadedSoftDeletes\Traits\CascadedSoftDeletes;

class PageCallbackCascade extends Model
{
    use CascadedSoftDeletes;
    use SoftDeletes;

    protected $table = 'pages';

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $fillable = ['name'];

    public $timestamps = false;

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class, 'page_id', 'id');
    }

    protected function getCascadedSoftDeletes(): array
    {
        return [
            'blocks' => function () {
                return Block::where('page_id', 2);
            },
        ];
    }
}
