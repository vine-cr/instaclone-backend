<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

class CommentService
{
    public function createComment(User $user, int $postId, array $data): Comment
    {
        $post = Post::findOrFail($postId);

        return $post->comments()->create([
            'user_id' => $user->id,
            'body' => $data['body']
        ]);
    }

    public function updateComment(Comment $comment, array $data): Comment
    {
        $comment->update(['body' => $data['body']]);
        return $comment;
    }

    public function deleteComment(Comment $comment): void
    {
        $comment->delete();
    }

    public function getComments(int $postId)
    {
        $post = Post::findOrFail($postId);
        return $post->comments()->with('user')->oldest()->paginate(15);
    }
}
