<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function show($username)
    {
        return response()->json($this->userService->getProfileByUsername($username));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'username' => 'string|max:255|unique:users,username,' . $request->user()->id,
            'bio' => 'nullable|string|max:500',
        ]);

        return response()->json($this->userService->updateProfile($request->user(), $validated));
    }

    public function updateAvatar(Request $request)
    {
        $request->validate(['avatar' => 'required|image|max:2048']);

        $path = $this->userService->updateAvatar($request->user(), $request->file('avatar'));

        return response()->json(['avatar_url' => asset('storage/' . $path)]);
    }

    public function search(Request $request)
    {
        return response()->json($this->userService->search($request->query('q', '')));
    }

    public function suggestions(Request $request)
    {
        return response()->json($this->userService->getSuggestions($request->user()));
    }
}
