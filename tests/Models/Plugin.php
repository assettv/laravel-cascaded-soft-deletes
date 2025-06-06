<?php

namespace AssetTV\LaravelCascadedSoftDeletes\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plugin extends Model
{
    use SoftDeletes;

    protected $table = 'plugins';

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $fillable = ['block_id', 'name'];

    public $timestamps = false;

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }
}
