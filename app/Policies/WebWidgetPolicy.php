<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WebWidget;

class WebWidgetPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WebWidget $webWidget): bool
    {
        return $user->id === $webWidget->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WebWidget $webWidget): bool
    {
        return $user->id === $webWidget->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WebWidget $webWidget): bool
    {
        return $user->id === $webWidget->user_id;
    }
}
