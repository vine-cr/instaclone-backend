<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;

class PostService
{
    public function createPost(User $user, array $data, $image): Post
    {
        $path = $image->store('posts', 'public');

        return $user->posts()->create([
            'image_url' => $path,
            'caption' => $data['caption'] ?? null
        ]);
    }

    public function getPost(int $id): Post
    {
        return Post::with('user')->withCount(['likes', 'comments'])->findOrFail($id);
    }

    public function updateCaption(Post $post, string $caption): Post
    {
        $post->update(['caption' => $caption]);
        return $post->load('user')->loadCount(['likes', 'comments']);
    }

    public function deletePost(Post $post): void
    {
        Storage::disk('public')->delete($post->image_url);
        $post->delete();
    }

    public function getUserPosts(int $userId): LengthAwarePaginator
    {
        return Post::where('user_id', $userId)->with('user')->withCount(['likes', 'comments'])->latest()->paginate(12);
    }
}
