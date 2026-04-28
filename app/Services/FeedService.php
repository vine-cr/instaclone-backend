<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;

class FeedService
{
    public function getFeed(User $user)
    {
        return Post::whereIn('user_id', function ($query) use ($user) {
            // Subquery: busca direto no banco quem o usuário segue
            $query->select('following_id')
                ->from('follows')
                ->where('follower_id', $user->id);
        })
            ->orWhere('user_id', $user->id) // Inclui também os posts do próprio usuário
            ->with('user') // Traz os dados de quem postou junto com o post
            // PREPARAÇÃO: Adicionamos a contagem de likes.
            // ->withCount(['likes' /*, 'comments'*/])
            ->latest() // Ordena do mais novo para o mais antigo
            ->paginate(15); // Paginação de 15 em 15
    }
}
