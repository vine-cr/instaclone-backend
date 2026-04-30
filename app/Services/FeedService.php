<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;

class FeedService
{
    public function getFeed(User $user)
    {
        return Post::whereIn('user_id', function ($query) use ($user) {
            $query->select('following_id')
                ->from('follows')
                ->where('follower_id', $user->id);
        })
            ->orWhere('user_id', $user->id)
            ->with('user')
            ->withCount(['likes', 'comments'])
            ->latest()
            ->paginate(15);
    }
}
