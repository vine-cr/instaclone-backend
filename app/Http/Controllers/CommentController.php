<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CommentController extends Controller
{
    use AuthorizesRequests;

    protected $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    public function store(Request $request, $postId)
    {
        $request->validate(['body' => 'required|string|max:1000']);

        $comment = $this->commentService->createComment($request->user(), (int) $postId, $request->only('body'));
        return response()->json($comment, 201);
    }

    public function index($postId)
    {
        return response()->json($this->commentService->getComments((int) $postId));
    }

    public function update(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);
        $this->authorize('update', $comment);

        $request->validate(['body' => 'required|string|max:1000']);

        return response()->json($this->commentService->updateComment($comment, $request->only('body')));
    }

    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        $this->authorize('delete', $comment);

        $this->commentService->deleteComment($comment);
        return response()->json(null, 204);
    }
}
