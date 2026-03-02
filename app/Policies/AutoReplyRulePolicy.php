<?php

namespace App\Policies;

use App\Models\AutoReplyRule;
use App\Models\User;

class AutoReplyRulePolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AutoReplyRule $autoReplyRule): bool
    {
        return $user->id === $autoReplyRule->user_id;
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
    public function update(User $user, AutoReplyRule $autoReplyRule): bool
    {
        return $user->id === $autoReplyRule->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AutoReplyRule $autoReplyRule): bool
    {
        return $user->id === $autoReplyRule->user_id;
    }
}
