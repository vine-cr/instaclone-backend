<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Models\Like;

class LikeService
{
    public function toggle(User $user, int $postId)
    {
        $post = Post::findOrFail($postId);

        // Verifica se o like já existe
        $like = Like::where('user_id', $user->id)
                    ->where('post_id', $postId)
                    ->first();

        if ($like) {
            // Se existe, deleta (remover like)
            $like->delete();
            $status = 'unliked';
            $liked = false;
        } else {
            // Se não existe, cria (adicionar like)
            Like::create([
                'user_id' => $user->id,
                'post_id' => $postId
            ]);
            $status = 'liked';
            $liked = true;
        }

        return [
            'status' => $status,
            'liked' => $liked,
            'likes_count' => $post->likes()->count()
        ];
    }

    public function like(User $user, int $postId)
    {
        $post = Post::findOrFail($postId);

        Like::firstOrCreate([
            'user_id' => $user->id,
            'post_id' => $postId
        ]);

        $isLiked = Like::where('user_id', $user->id)
                       ->where('post_id', $postId)
                       ->exists();

        return [
            'status' => 'liked',
            'liked' => $isLiked,
            'likes_count' => $post->likes()->count()
        ];
    }

    public function unlike(User $user, int $postId)
    {
        Like::where('user_id', $user->id)
            ->where('post_id', $postId)
            ->delete();

        $post = Post::findOrFail($postId);

        return [
            'status' => 'unliked',
            'liked' => false,
            'likes_count' => $post->likes()->count()
        ];
    }

    public function getLikes(int $postId)
    {
        $post = Post::findOrFail($postId);
        return $post->likes()->with('user')->paginate(15);
    }
}
