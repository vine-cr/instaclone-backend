<?php

namespace App\Services;

use App\Models\User;
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
        // Deleta o avatar antigo se existir
        if ($user->avatar_url) {
            Storage::disk('public')->delete($user->avatar_url);
        }

        $path = $image->store('avatars', 'public');
        $user->update(['avatar_url' => $path]);

        return $path;
    }

    public function search(string $query)
    {
        return User::where('username', 'like', "%{$query}%")
            ->orWhere('name', 'like', "%{$query}%")
            ->limit(10)
            ->get();
    }

    public function getSuggestions(User $authenticatedUser)
    {
        return User::where('id', '!=', $authenticatedUser->id)
            ->whereDoesntHave('followers', function($query) use ($authenticatedUser) {
                $query->where('follower_id', $authenticatedUser->id);
            })
            ->inRandomOrder()
            ->limit(5)
            ->get();
    }
}
