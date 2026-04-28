<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;

class LikeService
{
    public function like(User $user, int $postId)
    {
        $post = Post::findOrFail($postId);

        // firstOrCreate é idempotente: só cria se não existir
        $post->likes()->firstOrCreate(['user_id' => $user->id]);

        return [
            'status' => 'liked',
            'likes_count' => $post->likes()->count()
        ];
    }

    public function unlike(User $user, int $postId)
    {
        $post = Post::findOrFail($postId);

        // delete() é idempotente: se não achar nada, não dá erro e segue o jogo
        $post->likes()->where('user_id', $user->id)->delete();

        return [
            'status' => 'unliked',
            'likes_count' => $post->likes()->count()
        ];
    }

    public function getLikes(int $postId)
    {
        $post = Post::findOrFail($postId);
        // Traz as curtidas com os dados do usuário que curtiu
        return $post->likes()->with('user')->paginate(15);
    }
}
