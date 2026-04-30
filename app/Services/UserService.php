<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class UserService
{
    public function getProfileByUsername(string $username)
    {
        return User::where('username', $username)
            ->withCount(['followers', 'following'])
            ->firstOrFail();
    }

    public function updateProfile(User $user, array $data)
    {
        $user->update($data);
        return $user;
    }

    public function updateAvatar(User $user, $image)
    {
        if ($user->avatar_url) {
            Storage::disk('public')->delete($user->avatar_url);
        }

        $path = $image->store('avatars', 'public');
        $user->update(['avatar_url' => $path]);

        return $path;
    }

    public function search(string $query, int $perPage = 20): LengthAwarePaginator
    {
        return User::where('username', 'like', "%{$query}%")
            ->orWhere('name', 'like', "%{$query}%")
            ->paginate($perPage);
    }

    public function getSuggestions(User $authenticatedUser, int $perPage = 20): LengthAwarePaginator
    {
        return User::where('id', '!=', $authenticatedUser->id)
            ->whereNotIn('id', function (Builder $query) use ($authenticatedUser) {
                $query->select('following_id')
                    ->from('follows')
                    ->where('follower_id', $authenticatedUser->id);
            })
            ->inRandomOrder()
            ->paginate($perPage);
    }
}
