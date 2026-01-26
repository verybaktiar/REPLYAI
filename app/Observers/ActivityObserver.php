<?php

namespace App\Observers;

use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Model;

class ActivityObserver
{
    /**
     * Handle the model "created" event.
     */
    public function created(Model $model): void
    {
        ActivityLogService::logCreated($model);
    }

    /**
     * Handle the model "updated" event.
     */
    public function updated(Model $model): void
    {
        ActivityLogService::logUpdated($model);
    }

    /**
     * Handle the model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        ActivityLogService::logDeleted($model);
    }
}
