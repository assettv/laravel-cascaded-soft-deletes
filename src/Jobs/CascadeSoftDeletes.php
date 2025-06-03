<?php

namespace AssetTV\LaravelCascadedSoftDeletes\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CascadeSoftDeletes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Model $model;

    public string $event;

    public ?Carbon $instanceDeletedAt;

    public function __construct($model, $event, $instanceDeletedAt)
    {
        $this->model = $model;
        $this->event = $event;
        $this->instanceDeletedAt = $instanceDeletedAt;
    }

    public function handle(): void
    {
        $this->model->cascadeSoftDeletes($this->event, $this->instanceDeletedAt);
    }
}
