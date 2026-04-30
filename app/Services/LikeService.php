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

        $like = Like::where('user_id', $user->id)   
                    ->where('post_id', $postId)
                    ->first();  

        if ($like) {
            $like->delete();
            $status = 'unliked';
            $liked = false;
        } else {
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

    public function getLikes(int $postId)
    {
        $post = Post::findOrFail($postId);
        return $post->likes()->with('user')->paginate(15);
    }
}
