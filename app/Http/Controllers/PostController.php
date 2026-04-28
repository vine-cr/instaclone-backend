<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PostController extends Controller
{
    use AuthorizesRequests;

    protected $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120',
            'caption' => 'nullable|string|max:1000'
        ]);

        $post = $this->postService->createPost($request->user(), $request->only('caption'), $request->file('image'));
        return response()->json($post, 201);
    }

    public function show($id)
    {
        return response()->json($this->postService->getPost((int) $id));
    }

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        $this->authorize('update', $post);

        $request->validate(['caption' => 'required|string|max:1000']);

        return response()->json($this->postService->updateCaption($post, $request->caption));
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $this->authorize('delete', $post);

        $this->postService->deletePost($post);
        return response()->json(null, 204);
    }

    public function userPosts($userId)
    {
        return response()->json($this->postService->getUserPosts((int) $userId));
    }
}
