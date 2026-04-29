<?php

namespace App\Services;

use App\Models\User;
use App\Exceptions\SelfFollowException;

class FollowService
{
    public function toggleFollow(User $follower, int $followingId)
    {
        // Dispara o erro 403 se tentar seguir a si mesmo
        if ($follower->id === $followingId) {
            throw new SelfFollowException();
        }

        User::findOrFail($followingId);

        // Se já segue, desseguimos (detach). Se não, seguimos (attach).
        if ($follower->following()->whereKey($followingId)->exists()) {
            $follower->following()->detach($followingId);
            return ['status' => 'unfollowed'];
        }

        $follower->following()->attach($followingId);
        return ['status' => 'followed'];
    }

    public function getFollowers(int $userId)
    {
        $user = User::findOrFail($userId);
        return $user->followers()->paginate(15);
    }

    public function getFollowing(int $userId)
    {
        $user = User::findOrFail($userId);
        return $user->following()->paginate(15);
    }

    public function isFollowing(User $follower, int $followingId)
    {
        return [
            'is_following' => $follower->following()->whereKey($followingId)->exists()
        ];
    }
}
