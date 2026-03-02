<?php

namespace App\Policies;

use App\Models\KbArticle;
use App\Models\User;

class KbArticlePolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, KbArticle $kbArticle): bool
    {
        return $user->id === $kbArticle->user_id;
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
    public function update(User $user, KbArticle $kbArticle): bool
    {
        return $user->id === $kbArticle->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, KbArticle $kbArticle): bool
    {
        return $user->id === $kbArticle->user_id;
    }
}
