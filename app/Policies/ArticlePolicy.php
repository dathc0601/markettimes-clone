<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function delete(User $user, Article $article): bool
    {
        // Admins and editors can delete any article
        if (in_array($user->role, ['admin', 'editor'])) {
            return true;
        }

        // Authors can only delete their own articles
        return $user->role === 'author' && $article->author_id === $user->id;
    }
}
